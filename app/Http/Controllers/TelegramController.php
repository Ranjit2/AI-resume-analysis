<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        $message = strtolower($request->input('message.text'));
        $chatId = $request->input('message.chat.id');

        if (!$message || !$chatId) {
            return response('OK', 200);
        }

        // Get user state
        $state = Cache::get("state_$chatId");

        // ===== STEP 1: Waiting for date =====
        if ($state === 'ask_date') {
            try {
                $date = Carbon::parse($message)->format('Y-m-d');

                if ($date < now()->format('Y-m-d')) {
                    $reply = "❌ Please enter today or a future date.";
                } else {
                    Cache::put("leave_date_$chatId", $date, 600);
                    Cache::put("state_$chatId", 'confirm', 600);

                    $draft = $this->generateDraft($date);

                    $reply = "📅 Leave Date: $date\n\n"
                        . "📝 Title: {$draft['title']}\n"
                        . "📄 Description: {$draft['description']}\n\n"
                        . "Reply YES to confirm or NO to cancel.";
                }
            } catch (\Exception $e) {
                $reply = "Please enter a valid date (e.g., 2026-04-10)";
            }

            $this->sendMessage($chatId, $reply);
            return response('OK', 200);
        }

        // ===== STEP 2: Confirmation =====
        if ($state === 'confirm') {
            if ($message === 'yes') {
                $date = Cache::get("leave_date_$chatId");

                Cache::forget("state_$chatId");
                Cache::forget("leave_date_$chatId");

                $reply = "✅ Leave applied successfully for $date";
            } else {
                Cache::forget("state_$chatId");
                $reply = "❌ Leave request cancelled.";
            }

            $this->sendMessage($chatId, $reply);
            return response('OK', 200);
        }

        // ===== STEP 3: Detect intent =====
        $aiData = $this->extractIntent($message);

        if ($aiData['intent'] === 'sick_leave') {

            // Check if user already mentioned date
            if (str_contains($message, 'today')) {
                $date = now()->format('Y-m-d');

                Cache::put("leave_date_$chatId", $date, 600);
                Cache::put("state_$chatId", 'confirm', 600);

                $draft = $this->generateDraft($date);

                $reply = "📅 Leave Date: $date\n\n"
                    . "📝 Title: {$draft['title']}\n"
                    . "📄 Description: {$draft['description']}\n\n"
                    . "Reply YES to confirm or NO to cancel.";
            } else {
                Cache::put("state_$chatId", 'ask_date', 600);
                $reply = "When do you want to take sick leave? (Enter date)";
            }

        } else {
            $reply = $this->friendlyReply($message);
        }

        $this->sendMessage($chatId, $reply);

        return response('OK', 200);
    }

    // ===== AI intent detection =====
    private function extractIntent($message)
    {
        $prompt = "
You are an HR assistant.
User message: \"$message\"
Return ONLY JSON:
{
\"intent\": \"greeting | sick_leave | apply_leave | cancel\"
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
        return isset($matches[0]) ? json_decode($matches[0], true) : ['intent' => 'greeting'];
    }

    // ===== Generate draft (title + description) =====
    private function generateDraft($date)
    {
        $prompt = "
Generate a sick leave request:
- Title (5 to 7 words)
- Description (15 to 20 words)
Date: $date

Return JSON:
{
\"title\": \"...\",
\"description\": \"...\"
}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.7
        ]);

        $data = $response->json();
        $aiText = $data['choices'][0]['message']['content'] ?? null;

        preg_match('/\{.*\}/s', $aiText, $matches);
        return isset($matches[0]) ? json_decode($matches[0], true) : [
            'title' => 'Sick Leave Request',
            'description' => 'I am unwell and need to take leave for recovery.'
        ];
    }

    // ===== Friendly reply =====
    private function friendlyReply($message)
    {
        return "Hello! How can I help you today?";
    }

    // ===== Send message =====
    private function sendMessage($chatId, $text)
    {
        $token = env('TELEGRAM_BOT_TOKEN');

        Http::post("https://api.telegram.org/bot$token/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }
}