<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppFlowEngine
{
    public static function tryHandle($userId, $fromPhone, $incomingText, $channel, $apiKey = null)
    {
        Log::info('ABT_FLOW: tryHandle called', ['user_id' => $userId, 'text' => $incomingText]);

        $state = DB::table('ai_conversation_state')
            ->where('user_id', $userId)->where('wa_id', $fromPhone)->where('channel', $channel)
            ->first();

        if ($state && $state->active_app && $state->active_flow_node) {
            return self::continueFlow($userId, $fromPhone, $channel, $state, $incomingText);
        }

        $activeApps = DB::table('installed_apps')->where('user_id', $userId)->where('is_active', 1)->where('is_bot_active', 1)->pluck('app_type')->toArray();
        if (empty($activeApps)) return false;

        foreach ($activeApps as $appType) {
            $flow = self::loadFlow($userId, $appType);
            if (!$flow) continue;

            $startNode = self::findMatchingTrigger($flow, $incomingText);
            if (!$startNode) continue;

            Log::info('ABT_FLOW: matched app via keyword', ['app' => $appType]);

            $nextNode = self::getNextNode($flow, $startNode['node_id'], null);
            if (!$nextNode) continue;

            self::executeNode($userId, $fromPhone, $channel, $appType, $flow, $nextNode, []);
            return true;
        }

        return false;
    }

    private static function loadFlow($userId, $appType)
    {
        $row = DB::table('app_flows')->where('user_id', $userId)->where('app_type', $appType)->first();
        if (!$row || !$row->flow_data) return null;
        $decoded = json_decode($row->flow_data, true);
        if (!is_array($decoded) || empty($decoded['nodes'])) return null;
        return $decoded;
    }

    private static function findMatchingTrigger($flow, $text)
    {
        $textLower = strtolower($text);
        foreach ($flow['nodes'] as $node) {
            if ($node['type'] !== 'trigger') continue;
            $data = is_string($node['data']) ? json_decode($node['data'], true) : $node['data'];
            $keywords = array_filter(array_map('trim', explode(',', strtolower($data['trigger_value'] ?? ''))));
            foreach ($keywords as $kw) {
                if ($kw && str_contains($textLower, $kw)) return $node;
            }
        }
        return null;
    }

    private static function getNextNode($flow, $fromNodeId, $fromPort)
    {
        foreach ($flow['connections'] as $conn) {
            if ($conn['from'] === $fromNodeId && (($conn['fromPort'] ?? 'out') === ($fromPort ?? 'out'))) {
                foreach ($flow['nodes'] as $node) {
                    if ($node['node_id'] === $conn['to']) return $node;
                }
            }
        }
        return null;
    }

    private static function isTimeBased($appType)
    {
        return AppsController::appConfig($appType)['is_time_based'] ?? false;
    }

    private static function executeNode($userId, $fromPhone, $channel, $appType, $flow, $node, $context)
    {
        $user = \App\Models\User::find($userId);
        $data = is_string($node['data']) ? json_decode($node['data'], true) : $node['data'];
        $type = $node['type'];

        if ($type === 'message') {
            self::sendText($user, $fromPhone, $channel, $data['text'] ?? '');
            self::advanceOrWait($userId, $fromPhone, $channel, $appType, $flow, $node, $context);

        } elseif ($type === 'template') {
            self::sendTemplate($user, $fromPhone, $data['template_name'] ?? '');
            self::advanceOrWait($userId, $fromPhone, $channel, $appType, $flow, $node, $context);

        } elseif ($type === 'list') {
            $options = array_values(array_filter(array_map('trim', explode("\n", $data['options'] ?? ''))));
            $title = $data['title'] ?? 'Please choose an option';
            self::sendInteractiveList($user, $fromPhone, $channel, $title, $options);
            self::saveFlowState($userId, $fromPhone, $channel, $appType, $node['node_id'], array_merge($context, ['list_options' => $options]));

        } elseif ($type === 'resource') {
            $resourceId = $data['resource_id'] ?? null;
            $resourceName = $data['resource_name'] ?? 'this';
            $category = $data['category'] ?? null;
            $context['resource_id'] = $resourceId;
            $context['resource_name'] = $resourceName;
            $context['category'] = $category;

            // Send the resource's full details first (name, category, price, description) before anything else
            $resourceRow = $resourceId ? DB::table('app_resources')->where('id', $resourceId)->first() : null;
            if ($resourceRow) {
                $detailMsg = "*{$resourceRow->name}*";
                if (!empty($resourceRow->category)) $detailMsg .= "\n{$resourceRow->category}";
                if (!empty($resourceRow->price)) $detailMsg .= "\nPrice: ₹{$resourceRow->price}";
                if (!empty($resourceRow->description)) $detailMsg .= "\n\n{$resourceRow->description}";
                self::sendText($user, $fromPhone, $channel, $detailMsg);
            }

            if (self::isTimeBased($appType)) {
                $dates = [];
                if ($resourceRow && !empty($resourceRow->available_dates)) {
                    $configuredDates = json_decode($resourceRow->available_dates, true);
                    if (is_array($configuredDates)) {
                        // Only show dates that are today or in the future
                        $today = now()->format('Y-m-d');
                        foreach ($configuredDates as $d) {
                            if ($d >= $today) $dates[] = $d;
                        }
                        sort($dates);
                    }
                }
                if (empty($dates)) {
                    // Fallback: no specific dates configured, offer next 7 days
                    for ($i = 0; $i < 7; $i++) { $dates[] = now()->addDays($i)->format('Y-m-d'); }
                }

                if (empty($dates)) {
                    self::sendText($user, $fromPhone, $channel, "No available dates for {$resourceName} right now. A team member will follow up shortly.");
                    self::clearFlowState($userId, $fromPhone, $channel);
                    return;
                }

                $dateLabels = array_map(fn($d) => \Carbon\Carbon::parse($d)->format('D, d M'), $dates);
                self::sendInteractiveList($user, $fromPhone, $channel, "Choose a date for {$resourceName}", $dateLabels);
                $context['pending_dates'] = $dates;
                $context['step'] = 'choose_date';
                self::saveFlowState($userId, $fromPhone, $channel, $appType, $node['node_id'], $context);
            } else {
                // Catalog item — go straight to next node (usually a Form node) to collect order details
                self::advanceOrWait($userId, $fromPhone, $channel, $appType, $flow, $node, $context);
            }

        } elseif ($type === 'form') {
            $fields = $data['fields'] ?? AppsController::appConfig($appType)['form_fields'];
            if (empty($fields)) { self::advanceOrWait($userId, $fromPhone, $channel, $appType, $flow, $node, $context); return; }

            $msg = "Please share your details:\n\n";
            foreach ($fields as $fld) {
                $msg .= "{$fld['label']}: \n";
            }
            self::sendText($user, $fromPhone, $channel, trim($msg));

            $context['awaiting_form_reply'] = true;
            self::saveFlowState($userId, $fromPhone, $channel, $appType, $node['node_id'], $context);

        } elseif ($type === 'end') {
            if (!empty($data['text'])) self::sendText($user, $fromPhone, $channel, $data['text']);
            self::clearFlowState($userId, $fromPhone, $channel);
        }
    }

    private static function advanceOrWait($userId, $fromPhone, $channel, $appType, $flow, $node, $context)
    {
        $next = self::getNextNode($flow, $node['node_id'], 'out');
        if ($next) {
            self::executeNode($userId, $fromPhone, $channel, $appType, $flow, $next, $context);
        } else {
            self::clearFlowState($userId, $fromPhone, $channel);
        }
    }

    private static function continueFlow($userId, $fromPhone, $channel, $state, $incomingText)
    {
        $flow = self::loadFlow($userId, $state->active_app);
        if (!$flow) { self::clearFlowState($userId, $fromPhone, $channel); return false; }

        $currentNode = null;
        foreach ($flow['nodes'] as $n) { if ($n['node_id'] === $state->active_flow_node) { $currentNode = $n; break; } }
        if (!$currentNode) { self::clearFlowState($userId, $fromPhone, $channel); return false; }

        $context = json_decode($state->flow_context ?? '{}', true) ?: [];
        $user = \App\Models\User::find($userId);
        $appType = $state->active_app;

        if ($currentNode['type'] === 'list') {
            $options = $context['list_options'] ?? [];
            $picked = self::matchOption($incomingText, $options);
            if ($picked === null) return true; // unmatched — stay silent

            $optIndex = array_search($picked, $options);
            $next = self::getNextNode($flow, $currentNode['node_id'], 'opt'.$optIndex);
            if (!$next) {
                self::sendText($user, $fromPhone, $channel, "Thanks! A team member will follow up shortly.");
                self::clearFlowState($userId, $fromPhone, $channel);
                return true;
            }
            $context['selected_option'] = $picked;
            self::executeNode($userId, $fromPhone, $channel, $appType, $flow, $next, $context);
            return true;

        } elseif ($currentNode['type'] === 'resource') {
            $step = $context['step'] ?? 'choose_date';

            if ($step === 'choose_date') {
                $dates = $context['pending_dates'] ?? [];
                $dateLabels = array_map(fn($d) => \Carbon\Carbon::parse($d)->format('D, d M'), $dates);
                $pickedLabel = self::matchOption($incomingText, $dateLabels);
                if ($pickedLabel === null) return true;

                $idx = array_search($pickedLabel, $dateLabels);
                $pickedDate = $dates[$idx];
                $slots = AppointmentsController::getAvailableSlots($userId, $appType, $context['resource_id'], $pickedDate);

                if (empty($slots)) {
                    self::sendText($user, $fromPhone, $channel, "No available times for {$context['resource_name']} on {$pickedLabel}. Please choose another date or a team member will follow up.");
                    return true;
                }

                self::sendInteractiveList($user, $fromPhone, $channel, "Available times on {$pickedLabel}", $slots);
                $context['booking_date'] = $pickedDate;
                $context['available_slots'] = $slots;
                $context['step'] = 'choose_time';
                self::saveFlowState($userId, $fromPhone, $channel, $appType, $currentNode['node_id'], $context);
                return true;

            } elseif ($step === 'choose_time') {
                $slots = $context['available_slots'] ?? [];
                $picked = self::matchOption($incomingText, $slots);
                if ($picked === null) return true;

                $context['picked_slot'] = $picked;
                $next = self::getNextNode($flow, $currentNode['node_id'], 'out');
                if (!$next) {
                    self::sendText($user, $fromPhone, $channel, "Got it — {$picked}. What name should we book this under?");
                    $context['step'] = 'awaiting_name_fallback';
                    self::saveFlowState($userId, $fromPhone, $channel, $appType, $currentNode['node_id'], $context);
                    return true;
                }
                self::executeNode($userId, $fromPhone, $channel, $appType, $flow, $next, $context);
                return true;

            } elseif ($step === 'awaiting_name_fallback') {
                self::finalizeTimeBasedBooking($userId, $fromPhone, $channel, $appType, $context, trim($incomingText));
                return true;
            }
            return false;

        } elseif ($currentNode['type'] === 'form') {
            // Customer's entire reply is captured as their submitted details — no per-field parsing,
            // but we validate it contains both a name (some letters) and a phone number (7+ digit run)
            $rawReply = trim($incomingText);

            $hasPhone = preg_match('/\d{7,}/', $rawReply);
            $nameOnly = trim(preg_replace('/\d{7,}/', '', $rawReply));
            $hasName = preg_match('/[a-zA-Z]{2,}/', $nameOnly);

            if (!$hasPhone || !$hasName) {
                return true; // missing name or phone — stay silent, wait for a valid reply
            }

            preg_match('/\d{7,}/', $rawReply, $phoneMatch);
            $extractedPhone = $phoneMatch[0] ?? $fromPhone;
            $extractedName = trim(preg_replace('/[:\-,]/', ' ', $nameOnly));
            $extractedName = trim(preg_replace('/\s+/', ' ', $extractedName));

            $context['form_answers'] = ['raw' => $rawReply, 'name' => $extractedName, 'phone' => $extractedPhone];

            if (self::isTimeBased($appType) && !empty($context['picked_slot'])) {
                self::finalizeTimeBasedBooking($userId, $fromPhone, $channel, $appType, $context, $extractedName);
            } else {
                self::finalizeOrder($userId, $fromPhone, $channel, $appType, $context);
            }
            return true;
        }

        return false;
    }

    private static function finalizeTimeBasedBooking($userId, $fromPhone, $channel, $appType, $context, $patientName)
    {
        $user = \App\Models\User::find($userId);
        $result = AppointmentsController::bookSlot(
            $userId, $appType, $context['resource_id'], $context['booking_date'],
            $context['picked_slot'], $patientName, $fromPhone, $context['category'] ?? null, null
        );

        if (!$result['success']) {
            self::sendText($user, $fromPhone, $channel, $result['error']);
        } else {
            $dateLabel = \Carbon\Carbon::parse($context['booking_date'])->format('D, d M Y');
            $bookingNo = $result['booking_number'] ?? $result['appointment_id'];
            $msg = "✅ *Appointment Confirmed*\n";
            $msg .= "━━━━━━━━━━━━━━━━━━\n";
            $msg .= "🆔 Appointment #{$bookingNo}\n\n";
            $msg .= "👤 Patient: {$patientName}\n";
            $msg .= "🩺 With: {$context['resource_name']}\n";
            if (!empty($context['category'])) $msg .= "🏥 Department: {$context['category']}\n";
            $msg .= "📅 Date: {$dateLabel}\n";
            $msg .= "🕐 Time: {$context['picked_slot']}\n";
            $msg .= "━━━━━━━━━━━━━━━━━━\n\n";
            $msg .= "We look forward to seeing you! Please save your appointment ID for reference.";
            self::sendText($user, $fromPhone, $channel, $msg);
        }
        self::clearFlowState($userId, $fromPhone, $channel);
    }

    private static function finalizeOrder($userId, $fromPhone, $channel, $appType, $context)
    {
        $user = \App\Models\User::find($userId);
        $answers = $context['form_answers'] ?? [];
        $name = $answers['name'] ?? $answers['raw'] ?? 'Customer';
        $address = $answers['address'] ?? null;
        $bookingNumber = AppointmentsController::nextBookingNumber($userId, $appType);

        $orderId = DB::table('app_orders')->insertGetId([
            'user_id' => $userId, 'app_type' => $appType, 'booking_number' => $bookingNumber,
            'resource_id' => $context['resource_id'] ?? 0, 'resource_name' => $context['resource_name'] ?? null,
            'customer_name' => $name, 'customer_phone' => $fromPhone, 'customer_address' => $address,
            'status' => 'new', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $msg = "✅ *Order Confirmed*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🆔 Order #{$bookingNumber}\n\n";
        $msg .= "👤 Name: {$name}\n";
        if (!empty($context['resource_name'])) $msg .= "📦 Item: {$context['resource_name']}\n";
        if (!empty($context['category'])) $msg .= "🏷️ Category: {$context['category']}\n";
        if ($address) $msg .= "📍 Delivery to: {$address}\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n\n";
        $msg .= "Thank you! We'll be in touch shortly. Please save your order ID for reference.";
        self::sendText($user, $fromPhone, $channel, $msg);

        Log::info('Order created', ['order_id' => $orderId, 'booking_number' => $bookingNumber, 'app_type' => $appType]);
        self::clearFlowState($userId, $fromPhone, $channel);
    }

    private static function matchOption($text, $options)
    {
        $text = trim($text);
        if (is_numeric($text)) {
            $idx = (int)$text - 1;
            if (isset($options[$idx])) return $options[$idx];
        }
        foreach ($options as $opt) {
            if (stripos($opt, $text) !== false || stripos($text, $opt) !== false) return $opt;
        }
        return null;
    }

    private static function saveFlowState($userId, $fromPhone, $channel, $appType, $nodeId, $context)
    {
        DB::table('ai_conversation_state')->updateOrInsert(
            ['user_id' => $userId, 'wa_id' => $fromPhone, 'channel' => $channel],
            ['active_app' => $appType, 'active_flow_node' => $nodeId, 'flow_context' => json_encode($context), 'updated_at' => now()]
        );
    }

    private static function clearFlowState($userId, $fromPhone, $channel)
    {
        DB::table('ai_conversation_state')
            ->where('user_id', $userId)->where('wa_id', $fromPhone)->where('channel', $channel)
            ->update(['active_app' => null, 'active_flow_node' => null, 'flow_context' => null, 'updated_at' => now()]);
    }

    private static function sendText($user, $to, $channel, $text)
    {
        if (!$text) return;
        if ($channel === 'whatsapp') {
            Http::withToken($user->access_token)->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'text', 'text' => ['body' => $text],
            ]);
        } elseif ($channel === 'messenger' || $channel === 'instagram') {
            $conn = DB::table('channel_connections')->where('user_id', $user->id)->where('channel', $channel)->where('is_active', 1)->first();
            if ($conn) {
                Http::withToken($conn->access_token)->post("https://graph.facebook.com/v19.0/{$conn->page_id}/messages", [
                    'recipient' => ['id' => $to], 'message' => ['text' => $text],
                ]);
            }
        } elseif ($channel === 'telegram') {
            $conn = DB::table('channel_connections')->where('user_id', $user->id)->where('channel', 'telegram')->where('is_active', 1)->first();
            if ($conn) Http::post("https://api.telegram.org/bot{$conn->bot_token}/sendMessage", ['chat_id' => $to, 'text' => $text]);
        }

        DB::table('messages')->insert([
            'user_id' => $user->id, 'wa_id' => $to, 'message' => $text,
            'type' => 'outgoing', 'status' => 'sent', 'channel' => $channel,
            'read' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private static function sendInteractiveList($user, $to, $channel, $title, $options)
    {
        if (empty($options)) return;
        $rows = [];
        foreach (array_slice($options, 0, 10) as $i => $opt) {
            $rows[] = ['id' => 'opt_' . $i, 'title' => mb_substr($opt, 0, 24)];
        }

        if ($channel === 'whatsapp') {
            Http::withToken($user->access_token)->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'body' => ['text' => mb_substr($title, 0, 1024)],
                    'action' => ['button' => 'View Options', 'sections' => [['title' => mb_substr($title, 0, 24), 'rows' => $rows]]],
                ],
            ]);
        } else {
            $lines = [];
            foreach ($options as $i => $o) { $lines[] = ($i+1).". ".$o; }
            self::sendText($user, $to, $channel, $title . "\n\n" . implode("\n", $lines));
            return;
        }

        DB::table('messages')->insert([
            'user_id' => $user->id, 'wa_id' => $to, 'message' => 'List: ' . $title,
            'type' => 'outgoing', 'status' => 'sent', 'channel' => $channel,
            'read' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private static function sendTemplate($user, $to, $templateName)
    {
        if (!$templateName) return;
        Http::withToken($user->access_token)->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
            'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'template',
            'template' => ['name' => $templateName, 'language' => ['code' => 'en_US']],
        ]);
        DB::table('messages')->insert([
            'user_id' => $user->id, 'wa_id' => $to, 'message' => 'Template: '.$templateName,
            'type' => 'outgoing', 'status' => 'sent', 'channel' => 'whatsapp',
            'read' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
