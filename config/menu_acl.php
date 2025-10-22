<?php
// config/menu_acl.php
return [
    // leaves (no submenu), keyed by URL
    '/'                  => 'dashboards_show',
    '/admins'            => 'admins_index',
    '/users'             => 'users_index',
    '/product-templates' => 'templates_index',
    '/flags'             => 'flags_index',
    '/mockups'           => 'mockups_index',
    '/orders'            => 'orders_index',
    '/invoices'          => 'invoices_index',
    '/roles'             => 'roles_index',

    // parents keyed by their "name" in JSON; children by URL
    'Products' => [
        // parent permission optional; null => show if any child is visible
        'permission' => null,
        'children' => [
            '/categories'     => 'categories_index',
            '/sub-categories' => 'subproducts_index',
            '/products'       => 'products_index',
            '/tags'           => 'tags_index',
        ],
    ],
    'Marketing' => [
        'permission' => null,
        'children' => [
            '/discount-codes' => 'discountcodes_index',
            '/offers'         => 'offers_index',
        ],
    ],
    'Logistics' => [
        'permission' => null,
        'children' => [
            '/logistics' => 'locations_index',
        ],
    ],
    'FAQs & Help' => [
        'permission' => null,
        'children' => [
            '/faqs'     => 'faqs_index',
            '/messages' => 'messages_index',
        ],
    ],
    'Settings' => [
        'permission' => null,
        'children' => [
            '/settings/details'        => 'settings_details',
            '/settings/payments'       => 'settings_payments',
            '/settings/notifications'  => 'settings_notifications',
            '/settings/website'        => 'settings_website',
        ],
    ],
    'Print command' => [
        'permission' => null,
        'children' => [
            '/jobs'             => 'jobs_index',
            '/board'            => 'board_index',
            '/inventories'      => 'inventories_index',
            '/station-statuses' => 'station_statuses_index',
        ],
    ],
];
