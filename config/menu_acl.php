<?php
// config/menu_acl.php
return [
    // Leaves (no submenu), keyed by URL
    '/'                  => 'dashboard_show',
    '/admins'             => 'admins_show',
    '/users'             => 'users_show',
    '/product-templates' => 'product-templates_show',
    '/flags'             => 'flags_show',
    '/mockups'           => 'mockups_show',
    '/orders'            => 'orders_show',
    '/invoices'          => 'invoices_show',
    '/roles'             => 'roles_show',

    // Parents keyed by their "name" (exactly as in verticalMenu.json); children keyed by URL
    'Products' => [
        'children' => [
            '/categories'     => 'categories_show',
            '/sub-categories' => 'sub-categories_show',
            '/products'       => 'products_show',
            '/tags'           => 'tags_show',
        ],
    ],
    'Marketing' => [
        'children' => [
            '/discount-codes' => 'discount-codes_show',
            '/offers'         => 'offers_show',
        ],
    ],
    'Logistics' => [
        'children' => [
            '/logistics' => 'locations_show',
        ],
    ],
    'FAQs & Help' => [
        'children' => [
            '/faqs'     => 'faqs_show',
            '/messages' => 'messages_show',
        ],
    ],
    'Settings' => [
        'children' => [
            '/settings/details'        => 'settings-details_show',
            '/settings/payments'       => 'settings-payments_show',
            '/settings/notifications'  => 'settings-notifications_show',
            '/settings/website'        => 'settings-website_show',
        ],
    ],
    'Print command' => [
        'children' => [
            '/jobs'             => 'jobs_show',
            '/board'            => 'board_show',
            '/inventories'      => 'inventories_show',
            '/station-statuses' => 'station-statuses_show',
        ],
    ],
];
