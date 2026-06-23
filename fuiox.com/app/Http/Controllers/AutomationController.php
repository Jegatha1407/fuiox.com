<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AutomationController extends Controller
{
    private function userId(): int { return session('auth_user'); }

    // Automation page
    public function automation()
    {
        $userId     = $this->userId();
        $user       = User::findOrFail($userId);
        $automations = DB::table('automations')->where('user_id',$userId)->orderBy('name')->get();
        $stats = [
            'total'   => $automations->count(),
            'active'  => $automations->where('is_active',1)->count(),
            'keyword' => $automations->where('trigger_type','keyword')->count(),
            'welcome' => $automations->where('trigger_type','welcome')->count(),
        ];
        return view('user.automation', compact('user','automations','stats'));
    }

    public function list()
    {
        $userId = $this->userId();
        $automations = DB::table('automations')->where('user_id',$userId)->orderBy('name')->get();
        return response()->json(['automations' => $automations]);
    }
    // Stats JSON
    public function stats()
    {
        $userId = $this->userId();
        $autos  = DB::table('automations')->where('user_id',$userId)->get();
        return response()->json(['total'=>$autos->count(),'active'=>$autos->where('is_active',1)->count(),'keyword'=>$autos->where('trigger_type','keyword')->count(),'welcome'=>$autos->where('trigger_type','welcome')->count()]);
    }

    // Store
    public function store(Request $request)
    {
        $request->validate(['name'=>'required','trigger_type'=>'required','response_message'=>'required']);
        $id = DB::table('automations')->insertGetId([
            'user_id'          => $this->userId(),
            'name'             => $request->name,
            'trigger_type'     => $request->trigger_type,
            'trigger_value'    => $request->trigger_value,
            'match_type'       => $request->match_type ?? 'contains',
            'response_message' => $request->response_message,
            'is_active'        => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        return response()->json(['success'=>true,'id'=>$id]);
    }

    // Update
    public function update(Request $request, $id)
    {
        DB::table('automations')->where('id',$id)->where('user_id',$this->userId())->update([
            'name'             => $request->name,
            'trigger_type'     => $request->trigger_type,
            'trigger_value'    => $request->trigger_value,
            'match_type'       => $request->match_type ?? 'contains',
            'response_message' => $request->response_message,
            'updated_at'       => now(),
        ]);
        return response()->json(['success'=>true]);
    }

    // Toggle
    public function toggle($id)
    {
        $auto = DB::table('automations')->where('id',$id)->where('user_id',$this->userId())->first();
        if ($auto) DB::table('automations')->where('id',$id)->update(['is_active'=>!$auto->is_active,'updated_at'=>now()]);
        return response()->json(['success'=>true]);
    }

    // Destroy
    public function destroy($id)
    {
        DB::table('automations')->where('id',$id)->where('user_id',$this->userId())->delete();
        return response()->json(['success'=>true]);
    }

    // ── PROCESS INCOMING MESSAGE ──────────────────
    public static function processIncoming(int $userId, string $fromPhone, string $text): void
    {
        $user = \App\Models\User::find($userId);
        if (!$user) return;

        $automations = \Illuminate\Support\Facades\DB::table('automations')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->get();

        foreach ($automations as $auto) {
            $matched = false;
            $t = strtolower(trim($text));
            $kw = strtolower(trim($auto->trigger_value ?? ''));

            if ($auto->trigger_type === 'welcome') {
                // Check if this is first message from this contact
                $count = \Illuminate\Support\Facades\DB::table('messages')
                    ->where('user_id', $userId)->where('wa_id', $fromPhone)->count();
                $matched = $count <= 1;
            } elseif ($auto->trigger_type === 'keyword' && $kw) {
                if ($auto->match_type === 'exact')       $matched = $t === $kw;
                elseif ($auto->match_type === 'starts_with') $matched = str_starts_with($t, $kw);
                else $matched = str_contains($t, $kw); // contains (default)
            } elseif ($auto->trigger_type === 'out_of_office') {
                $matched = true; // always reply with out of office
            }

            if (!$matched) continue;

            // Send reply
            $delay = (int)($auto->delay_minutes ?? 0);
            if ($delay > 0) sleep(min($delay * 60, 5)); // max 5s delay in webhook

            try {
                \Illuminate\Support\Facades\Http::withToken($user->access_token)
                    ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                        'messaging_product' => 'whatsapp',
                        'to'                => $fromPhone,
                        'type'              => 'text',
                        'text'              => ['body' => $auto->response_message],
                    ]);
                \Illuminate\Support\Facades\Log::info('Automation replied', ['auto' => $auto->name, 'to' => $fromPhone]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Automation reply failed', ['error' => $e->getMessage()]);
            }
            break; // only first matching automation
        }

        // Process flows
        \App\Http\Controllers\FlowController::processIncoming($userId, $fromPhone, $text);
    }}
