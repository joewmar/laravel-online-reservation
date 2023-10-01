<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Message;

class SystemNotification extends Notification
{
    use Queueable;
    protected $title,$message, $link, $telegramBot, $telegramText, $telegramLink, $telegramButton; 

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message = null, $link = null, $telegramBot = 'bot1')
    {
        $this->title=$title;
        $this->title=$title;
        if(isset($message)) $this->message=$message;
        if(isset($$telegramBot)) $this->$telegramBot=$$telegramBot;
        if(isset($link)) $this->link=$link;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
        ];
    }
}
