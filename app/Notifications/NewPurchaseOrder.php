<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewPurchaseOrder extends Notification
{
    use Queueable;

    protected $message;
    protected $title;

    /**
     * Create a new notification instance.
     *
     * @param $message
     * @param string $title
     */
    public function __construct($message, $title = "Pedido de Compra")
    {
        $this->message = $message;
        $this->title = $title;
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'title' => $this->title,
            'url' => route("purchase.order.index")
        ];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
