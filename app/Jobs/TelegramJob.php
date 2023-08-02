<?php

namespace App\Jobs;

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatId;
    protected $message;
    protected $keyboard;
    protected $bot;
    /**
     * Create a new job instance.
     */
    public function __construct($chatId, $message, $keyboard = null, $bot = 'bot1')
    {
        $this->chatId = $chatId;
        $this->message = $message;
        $this->keyboard = $keyboard;
        $this->bot = $bot;
    }

    /**
     * Execute the job.
     */
    public function handle(Telegram $telegramService): void
    {
        try{
            if($this->keyboard != null){
                $telegramService->bot($this->bot)->sendMessage([
                    'chat_id' => $this->chatId,
                    'parse_mode' => 'HTML',
                    'text' => $this->message,
                    'reply_markup' => json_encode(['inline_keyboard' => $this->keyboard]) ,
                ]);
            }
            else{
                $telegramService->bot($this->bot)->sendMessage([
                    'chat_id' => $this->chatId,
                    'parse_mode' => 'HTML',
                    'text' => $this->message,
                ]);
            }
        }
        catch(Exception $e){
    
        }
    }
}
