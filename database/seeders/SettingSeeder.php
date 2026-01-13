<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        // -------- Existing settings --------
        $settings = [
            // General
            ['key' => 'phone', 'value' => '01060538209', 'group' => 'general_setting'],
            ['key' => 'store_email', 'value' => 'dorraprint@gmail.com', 'group' => 'general_setting'],
            ['key' => 'enable_design_payment', 'value' => true, 'group' => 'general_setting'],
            ['key' => 'order_format', 'value' => '#ORD'],
            ['key' => 'delivery', 'value' => 30],
            ['key' => 'shipping_visibility', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'tax', 'value' => 0.1],

            // Landing page visibility
            ['key' => 'navbar_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'hero_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'categories_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'designs_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'statistics_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'logo_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'testimonials_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'reviews_with_images_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'reviews_without_images_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'partners_section', 'value' => true, 'group' => 'visibility_sections_landing'],
            ['key' => 'faq_section', 'value' => true, 'group' => 'visibility_sections_landing'],

            // Statistics
            ['key' => 'customers', 'value' => 1200, 'group' => 'statistics_landing'],
            ['key' => 'orders', 'value' => 3400, 'group' => 'statistics_landing'],
            ['key' => 'rate', 'value' => 4.9, 'group' => 'statistics_landing'],
        ];

        // -------- Notification toggles (group = notifications) --------
        // Key format: "<category>.<event>.<channel>"
        // Channels: "email", "notification" (in-app/push)
        $notificationMatrix = [
            'customers' => [
                'new_customer_signed_up' => ['email' => true,  'notification' => true],
            ],
            'orders' => [
//                'purchased'        => ['email' => true,  'notification' => true],
//                'cancelled'        => ['email' => true,  'notification' => true],
                'confirmed'        => ['email' => true,  'notification' => true],
//                'refund_request'   => ['email' => true,  'notification' => true],
                'payment_error'    => ['email' => true,  'notification' => true],
            ],
            'shipping' => [
                'picked_up'        => ['email' => true,  'notification' => true],
                'delivered'        => ['email' => true,  'notification' => true],
            ],

        ];

        foreach ($notificationMatrix as $category => $events) {
            foreach ($events as $event => $channels) {
                foreach ($channels as $channel => $default) {
                    $settings[] = [
                        'key'   => "{$category}.{$event}.{$channel}",
                        'value' => $default,
                        'group' => 'notifications',
                    ];
                }
            }
        }

        // -------- Upsert all settings --------
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'] ?? null,
                ]
            );
        }
    }
}
