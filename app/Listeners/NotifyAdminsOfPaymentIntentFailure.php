<?php

namespace App\Listeners;

use App\Events\PaymentIntentFailed;
use App\Models\Admin;
use App\Models\Setting;
use App\Notifications\PaymentIntentionFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyAdminsOfPaymentIntentFailure
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentIntentFailed $event): void
    {
        $emailOn = (bool) Setting::where('group','notifications')
            ->where('key','orders.payment_error.email')->value('value');
        $dbOn = (bool) Setting::where('group','notifications')
            ->where('key','orders.payment_error.notification')->value('value');

        $channels = array_values(array_filter([
            $emailOn ? 'mail'     : null,
            $dbOn    ? 'database' : null,
        ])) ?: ['mail'];

        $ctx = [
            'gateway'     => $event->gateway,
            'status_code' => $event->statusCode,
            'cart_id'     => $event->cart?->id,
            'user_id'     => $event->user?->id,
            'user_email'  => $event->user?->email,
            'message'     => $event->message ?? 'Unknown error',
            'raw'         => $event->raw,
        ];

        Admin::select('id','first_name','last_name','email')
            ->chunkById(200, function ($admins) use ($ctx, $channels) {
                Notification::send($admins, new PaymentIntentionFailed($ctx, $channels));
            });
    }

}
