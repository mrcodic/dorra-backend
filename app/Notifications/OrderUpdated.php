<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class OrderUpdated extends Notification implements ShouldQueue
{
    use Queueable;


    /**
     * Create a new notification instance.
     */
    public function __construct(public Order $order)
    {
        $this->delay(now()->addSeconds(2));
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ($notifiable->is_email_notifications_enabled ?? false) ? ['mail'] : [];
    }
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting("Hello $notifiable->name!")
            ->subject('Order status updated')
            ->line('Order '.$this->order->order_number.' status has been updated to '.Str::headline((string)($this->order->status->label())).'.')
                    ->action('Go To Order',config('services.site_url') . 'order-history/' . $this->order->id)
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
          //
        ];
    }
}
