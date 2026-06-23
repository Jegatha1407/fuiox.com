<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiFlowController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate(['prompt' => 'required|string|max:500', 'app_type' => 'required|string']);

        $appType = $request->app_type;
        $config = AppsController::appConfig($appType);
        $appInfo = AppsController::catalog()[$appType] ?? ['name' => $appType];
        $resourceLabel = $config['resource_label'];
        $isTimeBased = $config['is_time_based'] ? 'true' : 'false';

        $systemPrompt = <<<PROMPT
You are a WhatsApp conversation flow builder for a {$appInfo['name']} app.
Generate a valid flow JSON based on the user's description.

AVAILABLE NODE TYPES:
1. trigger - Entry point. data: {"trigger_value": "comma,separated,keywords"}
2. message - Send plain text. data: {"text": "message text"}
3. list - Show options menu. data: {"title": "question text", "options": "Option1\nOption2\nOption3"}
4. resource - Show a {$resourceLabel} and book/order it. data: {"resource_id": "1", "resource_name": "{$resourceLabel} Name", "category": "category name"}
5. form - Collect customer details. data: {"fields": [{"key": "name", "label": "Patient Name", "type": "text", "required": true}]}
6. end - End the flow. data: {}

CONNECTIONS FORMAT:
- Regular nodes connect via: {"from": "node_id", "fromPort": "out", "to": "next_node_id"}
- List nodes connect each option via: {"from": "node_id", "fromPort": "opt0", "to": "node_id"}, {"from": "node_id", "fromPort": "opt1", "to": "node_id"} etc.

RULES:
- Every flow MUST start with a trigger node
- Every flow MUST end with an end node
- List options connect to next nodes via opt0, opt1, opt2 etc (one per option)
- For {$appInfo['name']} app, resource nodes handle {$resourceLabel} booking (is_time_based: {$isTimeBased})
- After resource node, always add a form node to collect customer details
- After form node, always add an end node
- Generate unique node IDs like n1, n2, n3 etc.
- Keep it simple and practical for WhatsApp customers

RETURN ONLY VALID JSON. No explanation, no markdown, no backticks. Just the raw JSON object.
Example format:
{"nodes":[{"node_id":"n1","type":"trigger","data":{"trigger_value":"hello"},"position_x":100,"position_y":100}],"connections":[]}
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $request->prompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 2000,
                'response_format' => ['type' => 'json_object'],
            ]);

            if (!$response->successful()) {
                Log::error('Groq API error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'AI service error. Please try again.'], 500);
            }

            $content = $response->json('choices.0.message.content');
            $flow = json_decode($content, true);

            if (!$flow || !isset($flow['nodes'])) {
                return response()->json(['error' => 'AI returned invalid flow. Please try a different description.'], 422);
            }

            // Auto-position nodes if positions are missing or overlapping
            foreach ($flow['nodes'] as $i => &$node) {
                if (empty($node['position_x'])) $node['position_x'] = 100 + ($i % 3) * 280;
                if (empty($node['position_y'])) $node['position_y'] = 80 + floor($i / 3) * 200;
            }

            return response()->json(['success' => true, 'flow' => $flow]);

        } catch (\Exception $e) {
            Log::error('AiFlowController error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to generate flow: ' . $e->getMessage()], 500);
        }
    }

    public function generateOutside(Request $request)
    {
        $request->validate(['prompt' => 'required|string|max:500']);

        $systemPrompt = <<<PROMPT
You are a WhatsApp automation flow builder. OUTPUT ONLY JSON matching this exact structure.

EXAMPLE OUTPUT:
{"nodes":[{"node_id":"n1","type":"trigger","data":{"trigger_type":"keyword","trigger_value":"hello"},"position_x":100,"position_y":100},{"node_id":"n2","type":"message","data":{"text":"Welcome!"},"position_x":400,"position_y":100},{"node_id":"n3","type":"end","data":{},"position_x":700,"position_y":100}],"connections":[{"from":"n1","fromPort":"out","to":"n2"},{"from":"n2","fromPort":"out","to":"n3"}]}

STRICT RULES - YOU MUST FOLLOW:
1. Every node needs: node_id (n1,n2,n3...), type, data{}, position_x, position_y
2. node_id must be n1/n2/n3 - NEVER use type names as IDs
3. First node must be type "trigger"
4. Last node must be type "end"  
5. Connections use fromPort:"out" for regular nodes
6. Button nodes: fromPort "btn0","btn1" per button
7. Condition nodes: fromPort "true" and "false"
8. Space nodes 300px horizontally, 150px vertically

TYPES: trigger|message|template|buttons|list|delay|condition|tag|remove_tag|assign|end

DATA:
trigger={"trigger_type":"keyword","trigger_value":"hi,hello"}
message={"text":"msg"}
buttons={"text":"choose:","buttons":["Opt1","Opt2"]}
list={"title":"choose:","options":"Opt1\nOpt2"}
delay={"delay_minutes":60}
condition={"condition":"contains","value":"yes"}
tag={"tag":"name"}
assign={"agent":"name"}
end={}

RETURN ONLY RAW JSON. NO explanation. NO markdown. NO backticks.
PROMPT;

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $request->prompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 2000,
                'response_format' => ['type' => 'json_object'],
            ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'AI service error. Please try again.'], 500);
            }

            $content = $response->json('choices.0.message.content');
            $flow = json_decode($content, true);

            if (!$flow || !isset($flow['nodes'])) {
                return response()->json(['error' => 'AI returned invalid flow. Please try a different description.'], 422);
            }

            foreach ($flow['nodes'] as $i => &$node) {
                if (empty($node['position_x'])) $node['position_x'] = 100 + ($i % 3) * 300;
                if (empty($node['position_y'])) $node['position_y'] = 80 + floor($i / 3) * 180;
            }

            return response()->json(['success' => true, 'flow' => $flow]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }}
