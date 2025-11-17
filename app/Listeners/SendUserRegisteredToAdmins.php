<?php

namespace App\Listeners;

use App\Models\Admin;
use App\Models\Setting;
use App\Notifications\UserRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendUserRegisteredToAdmins
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

    public function handle(Registered $event): void
    {
        $user = $event->user;
        $emailOn = (bool) Setting::where('group', 'notifications')
            ->where('key', 'customers.new_customer_signed_up.email')
            ->value('value');

        $notifOn = (bool) Setting::where('group', 'notifications')
            ->where('key', 'customers.new_customer_signed_up.notification')
            ->value('value');

        $channels = [];
        if ($emailOn)  $channels[] = 'mail';
        if ($notifOn)  $channels[] = 'database';
        if (empty($channels)) return;

        Admin::query()
            ->select('id','name','email')
            ->role(['Super Admin'])
            ->chunkById(200, function ($admins) use ($user, $channels) {
                Notification::send($admins, new UserRegistered($user, $channels));
            });
    }
}
