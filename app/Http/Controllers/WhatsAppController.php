<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
class WhatsAppController extends Controller
{
//     public function webhook(Request $request)
//     {
//         // For curl testing
//         $message = $request->input('message', 'Hello');
//         $user = $request->input('user', 'Alice');

//         // Compute today's date in YYYY-MM-DD
//         $date = now()->format('Y-m-d');

//         // AI prompt
//         $prompt = "
// You are a friendly HR assistant.
// User message: \"$message\"
// Assume today's date is $date.
// Return ONLY JSON:
// {
//   \"intent\": \"greeting | sick_leave | apply_leave | cancel\",
//   \"date\": \"$date\",
//   \"leave_type\": \"casual | sick | other\"
// }";

//         // Call OpenRouter API
//         $response = Http::withHeaders([
//             'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
//             'Content-Type' => 'application/json'
//         ])->post('https://openrouter.ai/api/v1/chat/completions', [
//             'model' => 'gpt-4o-mini',
//             'messages' => [['role' => 'user', 'content' => $prompt]],
//             'temperature' => 0
//         ]);

//         $data = $response->json();
//         $aiText = $data['choices'][0]['message']['content'] ?? null;

//         // Extract JSON from AI response
//         preg_match('/\{.*\}/s', $aiText, $matches);
//         $json = isset($matches[0]) ? json_decode($matches[0], true) : null;

//         // Return parsed JSON for testing
//         return response()->json($json ?? [
//             'intent' => 'greeting',
//             'date' => $date,
//             'leave_type' => 'sick'
//         ]);
//     }



public function webhook(Request $request)
    {
        // --- Extract message & user (works for curl or real WhatsApp webhook) ---
        $message = $request->input('entry.0.changes.0.value.messages.0.text.body') 
                    ?? $request->input('message', 'Hello');
        $from = $request->input('entry.0.changes.0.value.messages.0.from') 
                ?? $request->input('user', 'Alice');

        if (!$message || !$from) return response('No message', 200);

        // --- Check if user has pending leave confirmation ---
        $pending = Cache::get("pending_leave_$from");

        if ($pending) {
            // User is replying to confirmation
            if (strtolower(trim($message)) === 'yes') {
                $this->submitLeave($from, $pending['date'], $pending['leave_type']);
                $this->sendWhatsAppMessage($from, "✅ Leave applied for {$pending['date']}");
            } else {
                $this->sendWhatsAppMessage($from, "Okay, leave request cancelled.");
            }
            Cache::forget("pending_leave_$from");
            return response('OK', 200);
        }

        // --- Compute today's date ---
        $date = now()->format('Y-m-d');

        // --- Call OpenRouter to extract intent ---
        $aiData = $this->extractIntent($message, $date);

        // --- Handle apply_leave intent ---
        if (in_array($aiData['intent'], ['sick_leave', 'apply_leave'])) {
            // Store pending leave in cache for 10 minutes
            Cache::put("pending_leave_$from", $aiData, now()->addMinutes(10));

            // Send confirmation message
            $this->sendWhatsAppMessage(
                $from,
                "Oh, you are sick! Do you want to take leave for {$aiData['date']}? Reply YES or NO."
            );
        } else {
            // Handle other intents or greetings
            $reply = $this->getAIReply($message);
            $this->sendWhatsAppMessage($from, $reply);
        }

        return response('OK', 200);
    }

    // --- Extract intent from OpenRouter ---
    private function extractIntent($message, $date)
    {
        $prompt = "
You are a friendly HR assistant.
User message: \"$message\"
Assume today's date is $date.
Return ONLY JSON:
{
  \"intent\": \"greeting | sick_leave | apply_leave | cancel\",
  \"date\": \"$date\",
  \"leave_type\": \"casual | sick | other\"
}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0
        ]);

        $data = $response->json();
        $aiText = $data['choices'][0]['message']['content'] ?? null;

        preg_match('/\{.*\}/s', $aiText, $matches);
        $json = isset($matches[0]) ? json_decode($matches[0], true) : null;

        return $json ?? [
            'intent' => 'greeting',
            'date' => $date,
            'leave_type' => 'sick'
        ];
    }

    // --- Optional: friendly replies for other messages ---
    private function getAIReply($message)
    {
        $prompt = "You are a friendly HR assistant. Respond empathetically to: \"$message\"";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.7
        ]);

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? "I didn't understand that.";
    }

    // --- Simulate leave submission (replace with HiBob API call) ---
    private function submitLeave($user, $date, $leaveType = 'sick')
    {
        // Example HiBob API call:
        // Http::withHeaders([...])->post('https://api.hibob.com/v1/timeoff/requests', [...]);
        return true; // For testing
    }

    // --- Send WhatsApp message via Meta Cloud API ---
    private function sendWhatsAppMessage($to, $message)
    {
        $accessToken = env('META_WA_ACCESS_TOKEN');
        $phoneNumberId = env('META_WA_PHONE_NUMBER_ID');

        $url = "https://graph.facebook.com/v16.0/$phoneNumberId/messages";

        Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Content-Type' => 'application/json'
        ])->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'text' => ['body' => $message]
        ]);
    }
}