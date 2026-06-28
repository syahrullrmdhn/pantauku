<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Event $event
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $botToken = Setting::get('telegram_bot_token');
        $chatId = Setting::get('telegram_chat_id');

        if (empty($botToken) || empty($chatId)) {
            Log::warning('TelegramNotification: Bot token or chat ID not configured.');
            return;
        }

        $message = $this->buildMessage();

        try {
            $response = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]
            );

            if (!$response->successful()) {
                Log::error('TelegramNotification: Failed to send message.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('TelegramNotification: Exception while sending message.', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build the Telegram message based on event type and suspicion.
     */
    private function buildMessage(): string
    {
        $event = $this->event;
        $occurredAt = $event->occurred_at instanceof \DateTime
            ? $event->occurred_at->format('d M Y H:i:s')
            : $event->occurred_at;

        if ($event->is_suspicious) {
            // Suspicious alert
            return "🚨 <b>ALERT! Aktivitas Mencurigakan Terdeteksi</b>\n\n"
                . "📱 Device: <code>{$event->device_id}</code>\n"
                . "🌐 Domain: <code>{$event->value}</code>\n"
                . "⏰ Waktu: {$occurredAt}\n\n"
                . "⚠️ Domain ini terdaftar dalam blacklist PantauKu!";
        }

        // Normal notification
        $typeLabel = $event->type === 'app_open' ? '📂 Aplikasi Dibuka' : '🌐 Browser Diakses';

        return "ℹ️ <b>Aktivitas Baru Terdeteksi</b>\n\n"
            . "{$typeLabel}\n"
            . "📱 Device: <code>{$event->device_id}</code>\n"
            . "📄 Value: <code>{$event->value}</code>\n"
            . "⏰ Waktu: {$occurredAt}";
    }
}
