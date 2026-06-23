<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class FlowController extends Controller
{
    private function userId() { return session('auth_user'); }

    // List all flows
public function builder()
{
    $userId = $this->userId();
    $user   = User::findOrFail($userId);
    $flows  = DB::table('flows')->where('user_id', $userId)->orderByDesc('created_at')->get();
    return view('user.flow_builder', compact('user', 'flows'));
}

public function index()
{
    $flows = DB::table('flows')->where('user_id', $this->userId())->orderByDesc('created_at')->get();
    return response()->json(['flows' => $flows]);
}
    // Get single flow with nodes and connections
    public function show($id)
    {
        $flow = DB::table('flows')->where('id', $id)->where('user_id', $this->userId())->first();
        if (!$flow) return response()->json(['error' => 'Not found'], 404);
        $nodes = DB::table('flow_nodes')->where('flow_id', $id)->get();

        $connections = [];
        foreach ($nodes as $node) {
            $data = json_decode($node->data, true) ?? [];

            // Default out connection
            if ($node->next_node_id) {
                $connections[] = ['from' => $node->node_id, 'to' => $node->next_node_id, 'port' => 'out'];
            }
            // Yes/No condition branches
            if (!empty($data['yes_node'])) {
                $connections[] = ['from' => $node->node_id, 'to' => $data['yes_node'], 'port' => 'yes'];
            }
            if (!empty($data['no_node'])) {
                $connections[] = ['from' => $node->node_id, 'to' => $data['no_node'], 'port' => 'no'];
            }
            // Button branches btn0, btn1, btn2...
            for ($i = 0; $i < 5; $i++) {
                if (!empty($data['btn'.$i.'_next'])) {
                    $connections[] = ['from' => $node->node_id, 'to' => $data['btn'.$i.'_next'], 'port' => 'btn'.$i];
                }
            }
        }

        return response()->json(['flow' => $flow, 'nodes' => $nodes, 'connections' => $connections]);
    }

    // Save/update flow
    public function store(Request $request)
    {
        $userId = $this->userId();
        $name = $request->name ?? 'Untitled Flow';
        $triggerType = $request->trigger_type ?? 'keyword';
        $triggerValue = $request->trigger_value ?? '';
        $nodes = $request->nodes ?? [];
        $connections = $request->connections ?? [];

        // Build connection map with port support
        $connMap = [];
        foreach ($connections as $conn) {
            $port = $conn['port'] ?? 'out';
            if (!isset($connMap[$conn['from']])) $connMap[$conn['from']] = [];
            $connMap[$conn['from']][$port] = $conn['to'];
            if ($port === 'out') $connMap[$conn['from']]['default'] = $conn['to'];
        }

        if ($request->id) {
            // Update existing
            DB::table('flows')->where('id', $request->id)->where('user_id', $userId)->update([
                'name' => $name, 'trigger_type' => $triggerType, 'trigger_value' => $triggerValue,
                'updated_at' => now()
            ]);
            $flowId = $request->id;
            DB::table('flow_nodes')->where('flow_id', $flowId)->delete();
        } else {
            // Create new
            $flowId = DB::table('flows')->insertGetId([
                'user_id' => $userId, 'name' => $name,
                'trigger_type' => $triggerType, 'trigger_value' => $triggerValue,
                'is_active' => 1, 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // Save nodes
        foreach ($nodes as $node) {
            DB::table('flow_nodes')->insert([
                'flow_id'      => $flowId,
                'node_id'      => $node['node_id'],
                'type'         => $node['type'],
                'data'         => json_encode($node['data'] ?? []),
                'position_x'  => $node['position_x'] ?? 0,
                'position_y'  => $node['position_y'] ?? 0,
                'next_node_id' => $connMap[$node['node_id']]['out'] ?? $connMap[$node['node_id']]['default'] ?? null,
                'created_at'  => now(),
            ]);
        }

        // Save yes/no and button branch connections
        foreach ($nodes as $node) {
            $nodeId = $node['node_id'];
            $yesNode = $connMap[$nodeId]['yes'] ?? null;
            $noNode  = $connMap[$nodeId]['no']  ?? null;
            $hasBranch = $yesNode || $noNode;
            // Check button branches btn0-btn4
            $btnNodes = [];
            for($i=0;$i<5;$i++){
                if(!empty($connMap[$nodeId]['btn'.$i])) $btnNodes['btn'.$i.'_next'] = $connMap[$nodeId]['btn'.$i];
            }
            if($hasBranch || !empty($btnNodes)){
                $existing = DB::table('flow_nodes')->where('flow_id', $flowId)->where('node_id', $nodeId)->first();
                if($existing){
                    $data = json_decode($existing->data, true) ?? [];
                    if($yesNode) $data['yes_node'] = $yesNode;
                    if($noNode) $data['no_node'] = $noNode;
                    foreach($btnNodes as $k=>$v) $data[$k] = $v;
                    DB::table('flow_nodes')->where('flow_id', $flowId)->where('node_id', $nodeId)
                        ->update(['data' => json_encode($data)]);
                }
            }
        }
        return response()->json(['success' => true, 'id' => $flowId]);
    }

    // Toggle active
    public function toggle($id)
    {
        $flow = DB::table('flows')->where('id', $id)->where('user_id', $this->userId())->first();
        if (!$flow) return response()->json(['error' => 'Not found'], 404);
        DB::table('flows')->where('id', $id)->update(['is_active' => !$flow->is_active, 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }

    // Delete flow
    public function destroy($id)
    {
        // Get all tag nodes from this flow to clean up tags
        $tagNodes = DB::table('flow_nodes')->where('flow_id', $id)->where('type', 'tag')->get();
        foreach ($tagNodes as $node) {
            $data = json_decode($node->data, true) ?? [];
            $tagName = $data['tag'] ?? null;
            if ($tagName) {
                // Remove this tag from all contacts of this user
                $userId = $this->userId();
                $contacts = DB::table('contacts')->where('user_id', $userId)->whereNotNull('tags')->get();
                foreach ($contacts as $contact) {
                    $tags = array_filter(array_map('trim', explode(',', $contact->tags)), fn($t) => $t !== $tagName);
                    DB::table('contacts')->where('id', $contact->id)->update(['tags' => implode(',', $tags)]);
                }
            }
        }
        DB::table('flow_nodes')->where('flow_id', $id)->delete();
        DB::table('flow_contacts')->where('flow_id', $id)->delete();
        DB::table('flows')->where('id', $id)->where('user_id', $this->userId())->delete();
        return response()->json(['success' => true]);
    }

    // Execute flow for a contact (called from webhook)
    public static function processMessage(int $userId, string $fromPhone, string $message)
    {
        // Check active flows
        $flows = DB::table('flows')->where('user_id', $userId)->where('is_active', 1)->get();
        foreach ($flows as $flow) {
            $triggered = false;
            // Get trigger value from flow or from trigger node
            $triggerValue = $flow->trigger_value;
            if (!$triggerValue) {
                $triggerNode = DB::table('flow_nodes')->where('flow_id', $flow->id)->where('type', 'trigger')->first();
                if ($triggerNode) {
                    $nodeData = json_decode($triggerNode->data, true) ?? [];
                    $triggerValue = $nodeData['keywords'] ?? $nodeData['trigger_value'] ?? '';
                }
            }
            switch ($flow->trigger_type) {
                case 'keyword':
                    if ($triggerValue) {
                        $keywords = array_map('trim', explode(',', strtolower($triggerValue)));
                        $msgLower = strtolower($message);
                        foreach ($keywords as $kw) {
                            if ($kw && str_contains($msgLower, $kw)) { $triggered = true; break; }
                        }
                    }
                    break;
                case 'welcome':
                    $count = DB::table('messages')->where('user_id', $userId)->where('wa_id', $fromPhone)->count();
                    $triggered = ($count <= 1);
                    break;
                case 'any':
                    $triggered = true;
                    break;
            }

            if (!$triggered) continue;

            // Check if contact already in this flow (active)
            $existing = DB::table('flow_contacts')
                ->where('flow_id', $flow->id)->where('wa_id', $fromPhone)->where('status', 'active')->first();
            if ($existing) continue;
            // Clean up old completed entries
            DB::table('flow_contacts')->where('flow_id', $flow->id)->where('wa_id', $fromPhone)->where('status', 'completed')->delete();

            // Start flow for contact
            Log::info('FLOW STARTING', ['flow' => $flow->id, 'contact' => $fromPhone]);
            $firstNode = DB::table('flow_nodes')->where('flow_id', $flow->id)->orderBy('id')->first();
            if (!$firstNode) continue;

            DB::table('flow_contacts')->insert([
                'flow_id' => $flow->id, 'user_id' => $userId, 'wa_id' => $fromPhone,
                'current_node_id' => $firstNode->node_id, 'status' => 'active',
                'started_at' => now(), 'next_execution_at' => now()
            ]);

            // Execute immediately
            self::executeFlowStep($userId, $fromPhone, $flow->id, $firstNode->node_id);
            break;
        }
    }

    public static function executeFlowStep(int $userId, string $fromPhone, int $flowId, string $nodeId, string $incomingMsg = '')
    {
        $node = DB::table('flow_nodes')->where('flow_id', $flowId)->where('node_id', $nodeId)->first();
        if (!$node) return;
        $user = User::find($userId);
        if (!$user) return;
        $data = json_decode($node->data, true) ?? [];

        Log::info('FLOW EXECUTE NODE', ['type' => $node->type, 'data' => $data]);
        switch ($node->type) {
            case 'message':
                if (!empty($data['message'])) {
                    \Illuminate\Support\Facades\Http::withToken($user->access_token)
                        ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                            'messaging_product' => 'whatsapp', 'to' => $fromPhone,
                            'type' => 'text', 'text' => ['body' => $data['message']]
                        ]);
                    DB::table('messages')->insert([
                        'user_id' => $userId, 'wa_id' => $fromPhone,
                        'message' => $data['message'], 'type' => 'outgoing', 'status' => 'sent',
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }
                break;
            case 'template':
                if (!empty($data['template_name'])) {
                    $lang = $data['language'] ?? 'en_US';
                    $components = [];
                    // Header image
                    if (!empty($data['header_image'])) {
                        $components[] = ['type' => 'header', 'parameters' => [['type' => 'image', 'image' => ['link' => $data['header_image']]]]];
                    }
                    // Variables from var_0, var_1, etc.
                    $varParams = [];
                    $i = 0;
                    while (isset($data['var_'.$i])) {
                        $varParams[] = ['type' => 'text', 'text' => $data['var_'.$i]];
                        $i++;
                    }
                    if (!empty($varParams)) {
                        $components[] = ['type' => 'body', 'parameters' => $varParams];
                    }
                    $tplPayload = ['name' => $data['template_name'], 'language' => ['code' => $lang]];
                    if (!empty($components)) $tplPayload['components'] = $components;
                    \Illuminate\Support\Facades\Http::withToken($user->access_token)
                        ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                            'messaging_product' => 'whatsapp', 'to' => $fromPhone,
                            'type' => 'template', 'template' => $tplPayload
                        ]);
                    DB::table('messages')->insert([
                        'user_id' => $userId, 'wa_id' => $fromPhone,
                        'message' => 'Template: '.$data['template_name'], 'type' => 'outgoing', 'status' => 'sent',
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }
                break;
            case 'condition':
                // If no incoming message, this is first execution - wait for reply
                if (empty($incomingMsg)) {
                    $updated = DB::table('flow_contacts')
                        ->where('flow_id', $flowId)->where('wa_id', $fromPhone)
                        ->update(['next_execution_at' => now()->addYears(1), 'current_node_id' => $nodeId]);
                    Log::info('CONDITION WAITING', ['updated' => $updated, 'flow' => $flowId, 'contact' => $fromPhone, 'node' => $nodeId]);
                    return; // Wait for incoming message
                }
                Log::info('CONDITION CHECKING REPLY', ['last_msg' => DB::table('messages')->where('user_id', $userId)->where('wa_id', $fromPhone)->where('type','incoming')->latest('created_at')->value('message')]);
                // We have a reply - check it against incoming message
                $condition = strtolower($data['condition'] ?? '');
                $checkMsg = $incomingMsg ?: DB::table('messages')->where('user_id', $userId)->where('wa_id', $fromPhone)
                    ->where('type', 'incoming')->latest('created_at')->value('message');
                Log::info('CONDITION MATCH CHECK', ['condition' => $condition, 'msg' => $checkMsg]);
                $matched = $condition && str_contains(strtolower($checkMsg ?? ''), $condition);
                $nextNodeId = $matched ? ($data['yes_node'] ?? null) : ($data['no_node'] ?? null);
                // Clear waiting state
                DB::table('flow_contacts')->where('flow_id', $flowId)->where('wa_id', $fromPhone)
                    ->update(['next_execution_at' => null]);
                if ($nextNodeId) {
                    self::executeFlowStep($userId, $fromPhone, $flowId, $nextNodeId);
                } else {
                    DB::table('flow_contacts')->where('flow_id', $flowId)->where('wa_id', $fromPhone)->update(['status' => 'completed']);
                }
                return;
            case 'buttons':
                if (!empty($data['body']) || !empty($data['text']) || !empty($data['message'])) {
                    $data['body'] = $data['body'] ?? $data['text'] ?? $data['message'] ?? '';
                    $buttons = [];
                    foreach (['btn1','btn2','btn3'] as $i => $key) {
                        if (!empty($data[$key])) {
                            $buttons[] = ['type'=>'reply','reply'=>['id'=>'btn_'.$i,'title'=>substr($data[$key],0,20)]];
                        }
                    }
                    if (!empty($buttons)) {
                        \Illuminate\Support\Facades\Http::withToken($user->access_token)
                            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                                'messaging_product' => 'whatsapp', 'to' => $fromPhone,
                                'type' => 'interactive',
                                'interactive' => [
                                    'type' => 'button',
                                    'body' => ['text' => $data['body']],
                                    'action' => ['buttons' => $buttons]
                                ]
                            ]);
                        DB::table('messages')->insert(['user_id'=>$userId,'wa_id'=>$fromPhone,'message'=>$data['body'],'type'=>'outgoing','status'=>'sent','created_at'=>now(),'updated_at'=>now()]);
                        // Wait for customer to press a button
                        DB::table('flow_contacts')->where('flow_id',$flowId)->where('wa_id',$fromPhone)
                            ->update(['next_execution_at'=>now()->addYears(1),'current_node_id'=>$nodeId]);
                        return;
                    }
                }
                break;
            case 'list':
                if (!empty($data['body']) && !empty($data['options'])) {
                    $rows = [];
                    $lines = array_filter(explode("
", $data['options']));
                    foreach ($lines as $i => $line) {
                        $rows[] = ['id'=>'opt_'.$i,'title'=>substr(trim($line),0,24)];
                    }
                    if (!empty($rows)) {
                        \Illuminate\Support\Facades\Http::withToken($user->access_token)
                            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                                'messaging_product' => 'whatsapp', 'to' => $fromPhone,
                                'type' => 'interactive',
                                'interactive' => [
                                    'type' => 'list',
                                    'body' => ['text' => $data['body']],
                                    'action' => [
                                        'button' => $data['button_label'] ?? 'View Options',
                                        'sections' => [['title'=>'Options','rows'=>$rows]]
                                    ]
                                ]
                            ]);
                        DB::table('messages')->insert(['user_id'=>$userId,'wa_id'=>$fromPhone,'message'=>$data['body'],'type'=>'outgoing','status'=>'sent','created_at'=>now(),'updated_at'=>now()]);
                    }
                }
                break;
            case 'buttons':
                if (!empty($data['body']) || !empty($data['text']) || !empty($data['message'])) {
                    $data['body'] = $data['body'] ?? $data['text'] ?? $data['message'] ?? '';
                    $buttons = [];
                    foreach (['btn1','btn2','btn3'] as $i => $key) {
                        if (!empty($data[$key])) {
                            $buttons[] = ['type'=>'reply','reply'=>['id'=>'btn_'.$i,'title'=>substr($data[$key],0,20)]];
                        }
                    }
                    if (!empty($buttons)) {
                        \Illuminate\Support\Facades\Http::withToken($user->access_token)
                            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                                'messaging_product'=>'whatsapp','to'=>$fromPhone,'type'=>'interactive',
                                'interactive'=>['type'=>'button','body'=>['text'=>$data['body']],'action'=>['buttons'=>$buttons]]
                            ]);
                        DB::table('messages')->insert(['user_id'=>$userId,'wa_id'=>$fromPhone,'message'=>$data['body'],'type'=>'outgoing','status'=>'sent','created_at'=>now(),'updated_at'=>now()]);
                    }
                }
                break;
            case 'list':
                if (!empty($data['body']) && !empty($data['options'])) {
                    $rows = [];
                    foreach (array_filter(explode("\n", $data['options'])) as $i => $line) {
                        $rows[] = ['id'=>'opt_'.$i,'title'=>substr(trim($line),0,24)];
                    }
                    if (!empty($rows)) {
                        \Illuminate\Support\Facades\Http::withToken($user->access_token)
                            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                                'messaging_product'=>'whatsapp','to'=>$fromPhone,'type'=>'interactive',
                                'interactive'=>['type'=>'list','body'=>['text'=>$data['body']],'action'=>['button'=>$data['button_label']??'View Options','sections'=>[['title'=>'Options','rows'=>$rows]]]]
                            ]);
                        DB::table('messages')->insert(['user_id'=>$userId,'wa_id'=>$fromPhone,'message'=>$data['body'],'type'=>'outgoing','status'=>'sent','created_at'=>now(),'updated_at'=>now()]);
                    }
                }
                break;
            case 'remove_tag':
                if (!empty($data['tag'])) {
                    $contact = DB::table('contacts')->where('user_id', $userId)
                        ->whereRaw("REPLACE(phone,'+','') = ?", [preg_replace('/[^0-9]/', '', $fromPhone)])->first();
                    if ($contact && $contact->tags) {
                        $tags = array_filter(array_map('trim', explode(',', $contact->tags)), fn($t) => $t !== $data['tag']);
                        DB::table('contacts')->where('id', $contact->id)->update(['tags' => implode(',', $tags)]);
                    }
                }
                break;
            case 'tag':
                if (!empty($data['tag'])) {
                    $contact = DB::table('contacts')->where('user_id', $userId)
                        ->whereRaw("REPLACE(phone,'+','') = ?", [preg_replace('/[^0-9]/', '', $fromPhone)])->first();
                    if ($contact) {
                        $existing = $contact->tags ? $contact->tags.','.$data['tag'] : $data['tag'];
                        DB::table('contacts')->where('id', $contact->id)->update(['tags' => $existing]);
                    }
                }
                break;
            case 'buttons':
                if (!empty($data['body']) || !empty($data['text']) || !empty($data['message'])) {
                    $data['body'] = $data['body'] ?? $data['text'] ?? $data['message'] ?? '';
                    $buttons = [];
                    foreach (['btn1','btn2','btn3'] as $i => $key) {
                        if (!empty($data[$key])) {
                            $buttons[] = ['type'=>'reply','reply'=>['id'=>'btn_'.$i,'title'=>substr($data[$key],0,20)]];
                        }
                    }
                    if (!empty($buttons)) {
                        \Illuminate\Support\Facades\Http::withToken($user->access_token)
                            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                                'messaging_product' => 'whatsapp', 'to' => $fromPhone,
                                'type' => 'interactive',
                                'interactive' => [
                                    'type' => 'button',
                                    'body' => ['text' => $data['body']],
                                    'action' => ['buttons' => $buttons]
                                ]
                            ]);
                        DB::table('messages')->insert(['user_id'=>$userId,'wa_id'=>$fromPhone,'message'=>$data['body'],'type'=>'outgoing','status'=>'sent','created_at'=>now(),'updated_at'=>now()]);
                    }
                }
                break;
            case 'list':
                if (!empty($data['body']) && !empty($data['options'])) {
                    $rows = [];
                    $lines = array_filter(explode("
", $data['options']));
                    foreach ($lines as $i => $line) {
                        $rows[] = ['id'=>'opt_'.$i,'title'=>substr(trim($line),0,24)];
                    }
                    if (!empty($rows)) {
                        \Illuminate\Support\Facades\Http::withToken($user->access_token)
                            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                                'messaging_product' => 'whatsapp', 'to' => $fromPhone,
                                'type' => 'interactive',
                                'interactive' => [
                                    'type' => 'list',
                                    'body' => ['text' => $data['body']],
                                    'action' => [
                                        'button' => $data['button_label'] ?? 'View Options',
                                        'sections' => [['title'=>'Options','rows'=>$rows]]
                                    ]
                                ]
                            ]);
                        DB::table('messages')->insert(['user_id'=>$userId,'wa_id'=>$fromPhone,'message'=>$data['body'],'type'=>'outgoing','status'=>'sent','created_at'=>now(),'updated_at'=>now()]);
                    }
                }
                break;
            case 'remove_tag':
                if (!empty($data['tag'])) {
                    $contact = DB::table('contacts')->where('user_id', $userId)
                        ->whereRaw("REPLACE(phone,'+','') = ?", [preg_replace('/[^0-9]/', '', $fromPhone)])->first();
                    if ($contact && $contact->tags) {
                        $tags = array_filter(array_map('trim', explode(',', $contact->tags)), fn($t) => $t !== $data['tag']);
                        DB::table('contacts')->where('id', $contact->id)->update(['tags' => implode(',', $tags)]);
                    }
                }
                break;
            case 'assign':
                if (!empty($data['agent'])) {
                    $agent = \App\Models\User::where('parent_user_id', $userId)
                        ->where('name', 'like', '%'.$data['agent'].'%')->first();
                    if ($agent) {
                        DB::table('conversations')->where('user_id', $userId)->where('wa_id', $fromPhone)
                            ->update(['assigned_to' => $agent->id, 'assigned_at' => now()]);
                    }
                }
                break;
            case 'tag':
                if (!empty($data['tag'])) {
                    $contact = DB::table('contacts')->where('user_id', $userId)
                        ->whereRaw("REPLACE(phone,'+','') = ?", [preg_replace('/[^0-9]/', '', $fromPhone)])->first();
                    if ($contact) {
                        $tags = $contact->tags ? $contact->tags.','.$data['tag'] : $data['tag'];
                        DB::table('contacts')->where('id', $contact->id)->update(['tags' => $tags]);
                    }
                }
                break;
            case 'buttons':
                if (!empty($data['body']) || !empty($data['text']) || !empty($data['message'])) {
                    $data['body'] = $data['body'] ?? $data['text'] ?? $data['message'] ?? '';
                    $buttons = [];
                    foreach (['btn1','btn2','btn3'] as $i => $key) {
                        if (!empty($data[$key])) {
                            $buttons[] = ['type'=>'reply','reply'=>['id'=>'btn_'.$i,'title'=>substr($data[$key],0,20)]];
                        }
                    }
                    if (!empty($buttons)) {
                        \Illuminate\Support\Facades\Http::withToken($user->access_token)
                            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                                'messaging_product' => 'whatsapp', 'to' => $fromPhone,
                                'type' => 'interactive',
                                'interactive' => [
                                    'type' => 'button',
                                    'body' => ['text' => $data['body']],
                                    'action' => ['buttons' => $buttons]
                                ]
                            ]);
                        DB::table('messages')->insert(['user_id'=>$userId,'wa_id'=>$fromPhone,'message'=>$data['body'],'type'=>'outgoing','status'=>'sent','created_at'=>now(),'updated_at'=>now()]);
                    }
                }
                break;
            case 'list':
                if (!empty($data['body']) && !empty($data['options'])) {
                    $rows = [];
                    $lines = array_filter(explode("
", $data['options']));
                    foreach ($lines as $i => $line) {
                        $rows[] = ['id'=>'opt_'.$i,'title'=>substr(trim($line),0,24)];
                    }
                    if (!empty($rows)) {
                        \Illuminate\Support\Facades\Http::withToken($user->access_token)
                            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                                'messaging_product' => 'whatsapp', 'to' => $fromPhone,
                                'type' => 'interactive',
                                'interactive' => [
                                    'type' => 'list',
                                    'body' => ['text' => $data['body']],
                                    'action' => [
                                        'button' => $data['button_label'] ?? 'View Options',
                                        'sections' => [['title'=>'Options','rows'=>$rows]]
                                    ]
                                ]
                            ]);
                        DB::table('messages')->insert(['user_id'=>$userId,'wa_id'=>$fromPhone,'message'=>$data['body'],'type'=>'outgoing','status'=>'sent','created_at'=>now(),'updated_at'=>now()]);
                    }
                }
                break;
            case 'remove_tag':
                if (!empty($data['tag'])) {
                    $contact = DB::table('contacts')->where('user_id', $userId)
                        ->whereRaw("REPLACE(phone,'+','') = ?", [preg_replace('/[^0-9]/', '', $fromPhone)])->first();
                    if ($contact && $contact->tags) {
                        $tags = array_filter(array_map('trim', explode(',', $contact->tags)), fn($t) => $t !== $data['tag']);
                        DB::table('contacts')->where('id', $contact->id)->update(['tags' => implode(',', $tags)]);
                    }
                }
                break;
            case 'assign':
                if (!empty($data['agent'])) {
                    $agent = App\Models\User::where('parent_user_id', $userId)
                        ->where('name', 'like', '%'.$data['agent'].'%')->first();
                    if ($agent) {
                        DB::table('conversations')->where('user_id', $userId)->where('wa_id', $fromPhone)
                            ->update(['assigned_to' => $agent->id, 'assigned_at' => now()]);
                    }
                }
                break;
            case 'delay':
                $duration = intval($data['duration'] ?? 5);
                $unit = $data['unit'] ?? 'minutes';
                $nextTime = match($unit) {
                    'hours' => now()->addHours($duration),
                    'days'  => now()->addDays($duration),
                    default => now()->addMinutes($duration),
                };
                DB::table('flow_contacts')
                    ->where('flow_id', $flowId)->where('wa_id', $fromPhone)
                    ->update(['next_execution_at' => $nextTime, 'current_node_id' => $node->next_node_id]);
                return; // Stop here, scheduler will continue
            case 'end':
                DB::table('flow_contacts')
                    ->where('flow_id', $flowId)->where('wa_id', $fromPhone)
                    ->update(['status' => 'completed']);
                return;
        }

        // Move to next node
        if ($node->next_node_id) {
            DB::table('flow_contacts')
                ->where('flow_id', $flowId)->where('wa_id', $fromPhone)
                ->update(['current_node_id' => $node->next_node_id]);
            self::executeFlowStep($userId, $fromPhone, $flowId, $node->next_node_id);
        } else {
            DB::table('flow_contacts')
                ->where('flow_id', $flowId)->where('wa_id', $fromPhone)
                ->update(['status' => 'completed']);
        }
    }


    public static function processIncoming(int $userId, string $fromPhone, string $text): void
    {
        $user = \App\Models\User::find($userId);
        if (!$user) return;

        // Check if this contact is mid-flow (waiting for a reply)
        $contact = DB::table('flow_contacts')
            ->where('user_id', $userId)->where('wa_id', $fromPhone)
            ->where('status', 'active')
            ->whereNotNull('current_node_id')
            ->first();

        if ($contact) {
            // Resume mid-flow with the incoming message
            $node = DB::table('flow_nodes')
                ->where('flow_id', $contact->flow_id)
                ->where('node_id', $contact->current_node_id)
                ->first();

            if ($node) {
                $data = json_decode($node->data, true) ?? [];

                // Handle button reply
                if ($node->type === 'buttons') {
                    $t = strtolower(trim($text));
                    $nextNodeId = null;
                    foreach (['btn1','btn2','btn3'] as $i => $key) {
                        if (!empty($data[$key]) && str_contains($t, strtolower($data[$key]))) {
                            $nextNodeId = $data['btn'.$i.'_next'] ?? null;
                            break;
                        }
                    }
                    // Also check button index replies like "btn_0", "btn_1"
                    if (!$nextNodeId && preg_match('/btn_(\d+)/', $text, $m)) {
                        $nextNodeId = $data['btn'.$m[1].'_next'] ?? null;
                    }
                    if ($nextNodeId) {
                        DB::table('flow_contacts')->where('id', $contact->id)
                            ->update(['current_node_id' => $nextNodeId, 'next_execution_at' => null]);
                        self::executeFlowStep($userId, $fromPhone, $contact->flow_id, $nextNodeId, $text);
                    }
                    return;
                }

                // Handle condition reply
                if ($node->type === 'condition') {
                    DB::table('flow_contacts')->where('id', $contact->id)
                        ->update(['next_execution_at' => null]);
                    self::executeFlowStep($userId, $fromPhone, $contact->flow_id, $node->node_id, $text);
                    return;
                }
            }
            return;
        }

        // No active flow — check if message triggers a new flow
        $flows = DB::table('flows')->where('user_id', $userId)->where('is_active', 1)->get();

        foreach ($flows as $flow) {
            $nodes = DB::table('flow_nodes')->where('flow_id', $flow->id)->get()->keyBy('node_id');
            $startNode = $nodes->first(fn($n) => $n->type === 'trigger');
            if (!$startNode) continue;

            $triggerData = json_decode($startNode->data ?? '{}', true);
            $kw = strtolower(trim($triggerData['keywords'] ?? $triggerData['trigger_value'] ?? $flow->trigger_value ?? ''));
            $triggerType = $triggerData['trigger_type'] ?? $flow->trigger_type ?? 'keyword';
            $t = strtolower(trim($text));

            $matched = false;
            if ($triggerType === 'any') {
                $matched = true;
            } elseif ($triggerType === 'welcome') {
                $count = DB::table('messages')->where('user_id', $userId)->where('wa_id', $fromPhone)->count();
                $matched = $count <= 1;
            } elseif ($kw) {
                foreach (array_map('trim', explode(',', $kw)) as $k) {
                    if ($k && str_contains($t, $k)) { $matched = true; break; }
                }
            }

            if (!$matched) continue;

            // Start this flow for this contact
            DB::table('flow_contacts')->updateOrInsert(
                ['flow_id' => $flow->id, 'wa_id' => $fromPhone],
                ['user_id' => $userId, 'status' => 'active', 'current_node_id' => $startNode->next_node_id, 'next_execution_at' => null, 'created_at' => now(), 'updated_at' => now()]
            );

            if ($startNode->next_node_id) {
                self::executeFlowStep($userId, $fromPhone, $flow->id, $startNode->next_node_id, $text);
            }
            break;
        }
    }
}