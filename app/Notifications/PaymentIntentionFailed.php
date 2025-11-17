<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentIntentionFailed extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public array $ctx, public array $channels = ['mail','database'])
    {
        $this->afterCommit();
        $this->delay(now()->addSeconds(3));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }
    /**
     * Get the mail representation of the notification.
     */

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment error: failed to create payment intent')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A payment error occurred while creating a payment intention.')
            ->line('Gateway: ' . ($this->ctx['gateway'] ?? '-'))
            ->line('HTTP Status: ' . ($this->ctx['status_code'] ?? '-'))
            ->line('Cart: #' . ($this->ctx['cart_id'] ?? '-'))
            ->line('User: ' . ($this->ctx['user_email'] ?? '-'))
            ->line('Message: ' . ($this->ctx['message'] ?? 'Unknown error'))
            ->action('Open Admin', url('/admin'))
            ->line('This alert was generated automatically.');
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */

    public function toArray(object $notifiable): array
    {
        return $this->ctx;
    }
}
