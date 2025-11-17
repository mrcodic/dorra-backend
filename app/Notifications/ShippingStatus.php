<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ShippingStatus extends Notification implements ShouldQueue
{
    use Queueable;

    public array $channels;

    /**
     * $scenario: 'picked_up' (for SHIPPED) or 'delivered'
     */
    public function __construct(public Order $order, public string $scenario)
    {
        $this->afterCommit();

        $emailOn = (bool) Setting::where('group','notifications')->where('key',"shipping.$scenario.email")->value('value');
        $dbOn    = (bool) Setting::where('group','notifications')->where('key',"shipping.$scenario.notification")->value('value');

        $this->channels = array_values(array_filter([
            $emailOn ? 'mail'     : null,
            $dbOn    ? 'database' : null,
        ])) ?: [];
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->scenario === 'picked_up'
            ? 'Order picked up'
            : 'Order delivered';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello '.$notifiable->name.',')
            ->line("Order #{$this->order->order_number} status: ".ucwords(strtolower($this->order->status->label() ?? (string)$this->order->status)))
            ->lineWhen($this->scenario === 'picked_up', 'The carrier has picked up the order.')
        ->lineWhen($this->scenario === 'delivered', 'The order has been delivered to the customer.');

            $mail->action('Open order', route("order.show", $this->order->id));


        return $mail->line('Thanks!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'     => $this->order->id,
            'number'       => $this->order->order_number,
            'status'       => $this->order->status->label() ?? (string)$this->order->status,
            'scenario'     => $this->scenario,
        ];
    }
}
