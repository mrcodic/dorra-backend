<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferPlaced extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Offer $offer)
    {
//        $this->delay(now()->addSeconds(2));

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
            ->subject('A New Offer Has Been Placed')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new offer has just been placed on your platform.')
            ->action('View Offer', config('services.site_url').'Home')
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
