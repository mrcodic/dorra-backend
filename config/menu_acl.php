<?php
// config/menu_acl.php
return [
    // Leaves (no submenu), keyed by URL
    '/dashboard' => [
        'dashboard_show'
    ],
    '/admins' => [
        'admins_create',
        'admins_update',
        'admins_delete',
    ],
    '/users' => [
        'users_show',
        'users_create',
        'users_update',
        'users_delete',
    ],
    '/product-templates' => [
        'product-templates_show',
        'product_templates_create',
        'product_templates_update',
        'product_templates_delete',
    ],
    '/flags' => [
        'flags_show',
        'flags_create',
        'flags_update',
        'flags_delete',
    ],
    '/mockups' => [
        'mockups_show',
        'mockups_create',
        'mockups_update',
        'mockups_delete',
    ],
    '/orders' => [
        'orders_show',
        'orders_create',
        'orders_update',
        'orders_delete',
    ],
    '/invoices' => [
        'invoices_show',
        'invoices_delete',
    ],
    '/roles' => [
        'roles_show',
        'roles_create',
        'roles_update',
        'roles_delete',
    ],

    // Parents keyed by their "name" (exactly as in verticalMenu.json); children keyed by URL
    'Products' => [
        'children' => [
            '/categories' => [
                'categories_show',
                'categories_create',
                'categories_update',
                'categories_delete',
            ],
            '/sub-categories' => [
                'sub-categories_show',
                'sub-categories_create',
                'sub-categories_update',
                'sub-categories_delete',
            ],
            '/products' => [
                'products_show',
                'products_create',
                'products_update',
                'products_delete',
            ],
            '/tags' => [
                'tags_show',
                'tags_create',
                'tags_update',
                'tags_delete',
            ]

        ],
    ],
    'Industries' =>[
        'children' => [
            '/industries' => [
                'industries_show',
                'industries_create',
                'industries_update',
                'industries_delete',
            ],
            '/sub-industries' => [
                'sub-industries_show',
                'sub-industries_create',
                'sub-industries_update',
                'sub-industries_delete',
            ]
            ]
    ],
    'Marketing' => [
        'children' => [
            '/discount-codes' => [
                'discount-codes_show',
                'discount-codes_create',
                'discount-codes_update',
                'discount-codes_delete',
            ],
            '/offers' => [
                'offers_show',
                'offers_create',
                'offers_update',
                'offers_delete',
            ],
        ],
    ],
    'Logistics' => [
        'children' => [
            '/logistics' => 'locations_show',
        ],
    ],
    'FAQs & Help' => [
        'children' => [
            '/faqs' => [
                'faqs_show',
                'faqs_create',
                'faqs_update',
                'faqs_delete',
            ],
            '/messages' => [
                'messages_show',
                'messages_delete',
            ],
        ],
    ],
    'Settings' => [
        'children' => [
            '/settings/details' => 'settings-details_show',
            '/settings/payments' => 'settings-payments_show',
            '/settings/notifications' => 'settings-notifications_show',
            '/settings/website' => 'settings-website_show',
        ],
    ],
    'Print command' => [
        'children' => [
            '/jobs' => [
                'jobs_show',
                'jobs_update',
            ],
            '/board' => 'board_show',
            '/inventories' => [
                'inventories_show',
                'inventories_create',
                'inventories_delete',],
            '/station-statuses' =>
                [
                    'station-statuses_show',
                    'station-statuses_create',
                    'station-statuses_update',
                    'station-statuses_delete',
                ],
        ],
    ],
];
