<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotif extends Notification
{
    use Queueable;
    private $link, $message, $emailDetails, $view;

    /**
     * Create a new notification instance.
     */
    public function __construct($link, $messages, $emailDetails, $view)
    {
        $this->link = $link;
        $this->message = $messages;
        $this->emailDetails = $emailDetails;
        $this->view = $view;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject($this->emailDetails['title'])
                    ->markdown($this->view, ['details' => $this->emailDetails]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'link' => $this->link,
            'message' => $this->message,
        ];
    }
}
