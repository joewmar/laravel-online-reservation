<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $chat_id, $message, $keyboard, $bot;
    
    /**
     * Create a new job instance.
     */
    public function __construct(string $chatID, string $message, $keyboard = null, string $bot = 'bot1')
    {
        $this->chat_id = $chatID;
        $this->message = $message;
        $this->keyboard = $keyboard;
        $this->bot = $bot;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tgParams = [
            'chat_id' => $this->chat_id,
            'parse_mode' => 'HTML',
            'text' => $this->message,
        ];
        if($this->keyboard != null) $tgParams['reply_markup'] = json_encode(['inline_keyboard' => $this->keyboard]);
        Telegram::bot($this->bot)->sendMessage($tgParams);
    }
}
