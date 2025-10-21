<?php

namespace App\Enums\Admin;

enum PermissionEnum: string
{
    // Dashboards
    case DASHBOARDS = 'dashboards_show';

    // Admins
    case INDEX_ADMINS   = 'admins_index';
    case CREATE_ADMINS  = 'admins_create';
    case SHOW_ADMINS    = 'admins_show';
    case UPDATE_ADMINS  = 'admins_update';
    case DELETE_ADMINS  = 'admins_delete';

    // Users
    case INDEX_USERS    = 'users_index';
    case CREATE_USERS   = 'users_create';
    case SHOW_USERS     = 'users_show';
    case UPDATE_USERS   = 'users_update';
    case DELETE_USERS   = 'users_delete';

    // Products
    case INDEX_PRODUCTS   = 'products_index';
    case CREATE_PRODUCTS  = 'products_create';
    case SHOW_PRODUCTS    = 'products_show';
    case UPDATE_PRODUCTS  = 'products_update';
    case DELETE_PRODUCTS  = 'products_delete';

    // Categories
    case INDEX_CATEGORIES   = 'categories_index';
    case CREATE_CATEGORIES  = 'categories_create';
    case SHOW_CATEGORIES    = 'categories_show';
    case UPDATE_CATEGORIES  = 'categories_update';
    case DELETE_CATEGORIES  = 'categories_delete';

    // SubCategories
    case INDEX_SUBCATEGORIES   = 'sub-categories_index';
    case CREATE_SUBCATEGORIES  = 'sub-categories_create';
    case SHOW_SUBCATEGORIES    = 'sub-categories_show';
    case UPDATE_SUBCATEGORIES  = 'sub-categories_update';
    case DELETE_SUBCATEGORIES  = 'sub-categories_delete';

    // Tags
    case INDEX_TAGS   = 'tags_index';
    case CREATE_TAGS  = 'tags_create';
    case SHOW_TAGS    = 'tags_show';
    case UPDATE_TAGS  = 'tags_update';
    case DELETE_TAGS  = 'tags_delete';

    // Flags
    case CREATE_FLAGS = 'flags_create';
    case INDEX_FLAGS  = 'flags_index';
    case SHOW_FLAGS   = 'flags_show';
    case UPDATE_FLAGS = 'flags_update';
    case DELETE_FLAGS = 'flags_delete';

    // Templates
    case INDEX_TEMPLATES   = 'product-templates_index';
    case CREATE_TEMPLATES  = 'product-templates_create';
    case SHOW_TEMPLATES    = 'product-templates_show';
    case UPDATE_TEMPLATES  = 'product-templates_update';
    case DELETE_TEMPLATES  = 'product-templates_delete';

    // Mockups
    case INDEX_MOCKUPS   = 'mockups_index';
    case CREATE_MOCKUPS  = 'mockups_create';
    case SHOW_MOCKUPS    = 'mockups_show';
    case UPDATE_MOCKUPS  = 'mockups_update';
    case DELETE_MOCKUPS  = 'mockups_delete';

    // Orders
    case INDEX_ORDERS   = 'orders_index';
    case CREATE_ORDERS  = 'orders_create';
    case SHOW_ORDERS    = 'orders_show';
    case UPDATE_ORDERS  = 'orders_update';
    case DELETE_ORDERS  = 'orders_delete';

    // Discount Codes
    case INDEX_DISCOUNT_CODES   = 'discount-codes_index';
    case CREATE_DISCOUNT_CODES  = 'discount-codes_create';
    case SHOW_DISCOUNT_CODES    = 'discount-codes_show';
    case UPDATE_DISCOUNT_CODES  = 'discount-codes_update';
    case DELETE_DISCOUNT_CODES  = 'discount-codes_delete';

    // Offers
    case INDEX_OFFERS   = 'offers_index';
    case CREATE_OFFERS  = 'offers_create';
    case SHOW_OFFERS    = 'offers_show';
    case UPDATE_OFFERS  = 'offers_update';
    case DELETE_OFFERS  = 'offers_delete';

    // Invoices (kept no create)
    case INDEX_INVOICES = 'invoices_index';
    case SHOW_INVOICES  = 'invoices_show';
    case UPDATE_INVOICES= 'invoices_update';
    case DELETE_INVOICES= 'invoices_delete';

    // Logistics
//    case SHOW_LOGISTICS_DASHBOARD = 'logistics_dashboard_show';

    // Locations
    case INDEX_LOCATIONS   = 'locations_index';
    case CREATE_LOCATIONS  = 'locations_create';
    case SHOW_LOCATIONS    = 'locations_show';
    case UPDATE_LOCATIONS  = 'locations_update';
    case DELETE_LOCATIONS  = 'locations_delete';

    // Roles
    case INDEX_ROLES   = 'roles_index';
    case CREATE_ROLES  = 'roles_create';
    case SHOW_ROLES    = 'roles_show';
    case UPDATE_ROLES  = 'roles_update';
    case DELETE_ROLES  = 'roles_delete';

    // FAQs
    case INDEX_FAQS   = 'faqs_index';
    case CREATE_FAQS  = 'faqs_create';
    case SHOW_FAQS    = 'faqs_show';
    case UPDATE_FAQS  = 'faqs_update';
    case DELETE_FAQS  = 'faqs_delete';

    // Messages
    case SHOW_MESSAGES  = 'messages_show';
    case INDEX_MESSAGES = 'messages_index';
    case DELETE_MESSAGES= 'messages_delete';

    // Settings
    case SETTINGS_DETAILS        = 'settings_details';
    case SETTINGS_PAYMENTS       = 'settings_payments';
    case SETTINGS_NOTIFICATIONS  = 'settings_notifications';
    case SETTINGS_WEBSITE        = 'settings_website';

    // Jobs
    case INDEX_JOBS   = 'jobs_index';
    case SHOW_JOBS    = 'jobs_show';
    case UPDATE_JOBS  = 'jobs_update';

    // Board
    case SHOW_BOARD   = 'board_show';

    // Inventories
    case INDEX_INVENTORIES  = 'inventories_index';
    case SHOW_INVENTORIES   = 'inventories_show';
    case CREATE_INVENTORIES = 'inventories_create';
    case UPDATE_INVENTORIES = 'inventories_update';
    case DELETE_INVENTORIES = 'inventories_delete';

    // Station Statuses
    case INDEX_STATION_STATUSES  = 'station-statuses_index';
    case SHOW_STATION_STATUSES   = 'station-statuses_show';
    case CREATE_STATION_STATUSES = 'station-statuses_create';
    case DELETE_STATION_STATUSES = 'station-statuses_delete';
    case UPDATE_STATION_STATUSES = 'station-statuses_update';


    public function group(): string
    {
        return match ($this) {
            self::DASHBOARDS => 'Dashboards',

            // Admins
            self::INDEX_ADMINS, self::CREATE_ADMINS, self::SHOW_ADMINS, self::UPDATE_ADMINS, self::DELETE_ADMINS
            => 'Admins',

            // Users
            self::INDEX_USERS, self::CREATE_USERS, self::SHOW_USERS, self::UPDATE_USERS, self::DELETE_USERS
            => 'Users',

            // Products
            self::INDEX_PRODUCTS, self::CREATE_PRODUCTS, self::SHOW_PRODUCTS, self::UPDATE_PRODUCTS, self::DELETE_PRODUCTS
            => 'Products',

            // Categories
            self::INDEX_CATEGORIES, self::CREATE_CATEGORIES, self::SHOW_CATEGORIES, self::UPDATE_CATEGORIES, self::DELETE_CATEGORIES
            => 'Categories',

            // Sub Products
            self::INDEX_SUBCATEGORIES, self::CREATE_SUBCATEGORIES, self::SHOW_SUBCATEGORIES, self::UPDATE_SUBCATEGORIES, self::DELETE_SUBCATEGORIES
            => 'Sub Products',

            // Tags
            self::INDEX_TAGS, self::CREATE_TAGS, self::SHOW_TAGS, self::UPDATE_TAGS, self::DELETE_TAGS
            => 'Tags',

            // Templates
            self::INDEX_TEMPLATES, self::CREATE_TEMPLATES, self::SHOW_TEMPLATES, self::UPDATE_TEMPLATES, self::DELETE_TEMPLATES
            => 'Templates',

            // Mockups
            self::INDEX_MOCKUPS, self::CREATE_MOCKUPS, self::SHOW_MOCKUPS, self::UPDATE_MOCKUPS, self::DELETE_MOCKUPS
            => 'Mockups',

            // Orders
            self::INDEX_ORDERS, self::CREATE_ORDERS, self::SHOW_ORDERS, self::UPDATE_ORDERS, self::DELETE_ORDERS
            => 'Orders',

            // Discount Codes
            self::INDEX_DISCOUNT_CODES, self::CREATE_DISCOUNT_CODES, self::SHOW_DISCOUNT_CODES, self::UPDATE_DISCOUNT_CODES, self::DELETE_DISCOUNT_CODES
            => 'Discount Codes',

            // Offers
            self::INDEX_OFFERS, self::CREATE_OFFERS, self::SHOW_OFFERS, self::UPDATE_OFFERS, self::DELETE_OFFERS
            => 'Offers',

            // Invoices
            self::INDEX_INVOICES, self::SHOW_INVOICES, self::UPDATE_INVOICES, self::DELETE_INVOICES
            => 'Invoices',

            // Logistics
//            self::SHOW_LOGISTICS_DASHBOARD => 'Logistics',

            // Locations
            self::INDEX_LOCATIONS, self::CREATE_LOCATIONS, self::SHOW_LOCATIONS, self::UPDATE_LOCATIONS, self::DELETE_LOCATIONS
            => 'Locations',

            // Roles
            self::INDEX_ROLES, self::CREATE_ROLES, self::SHOW_ROLES, self::UPDATE_ROLES, self::DELETE_ROLES
            => 'Roles',

            // FAQs
            self::INDEX_FAQS, self::CREATE_FAQS, self::SHOW_FAQS, self::UPDATE_FAQS, self::DELETE_FAQS
            => 'FAQs',

            // Messages
            self::INDEX_MESSAGES, self::SHOW_MESSAGES, self::DELETE_MESSAGES
            => 'Messages',

            // Settings
            self::SETTINGS_DETAILS, self::SETTINGS_PAYMENTS, self::SETTINGS_NOTIFICATIONS, self::SETTINGS_WEBSITE
            => 'Settings',

            // Jobs
            self::INDEX_JOBS, self::SHOW_JOBS, self::UPDATE_JOBS
            => 'Jobs',

            // Board
            self::SHOW_BOARD
            => 'Board',

            // Inventories
            self::INDEX_INVENTORIES, self::SHOW_INVENTORIES, self::CREATE_INVENTORIES, self::DELETE_INVENTORIES,self::UPDATE_INVENTORIES
            => 'Inventories',

            // Station Statuses
            self::INDEX_STATION_STATUSES, self::SHOW_STATION_STATUSES, self::CREATE_STATION_STATUSES, self::DELETE_STATION_STATUSES,self::UPDATE_STATION_STATUSES
            => 'Station Statuses',
        };
    }

    public function routes(): array
    {
        $resource = $this->groupKey();

        return match (true) {
            str_contains($this->value, '_index')  => [$resource . '.index'],
            str_contains($this->value, '_create') => [$resource . '.create', $resource . '.store'],
            str_contains($this->value, '_update') => [$resource . '.edit', $resource . '.update'],
            str_contains($this->value, '_show')   => [$resource . '.show'],
            str_contains($this->value, '_delete') => [$resource . '.destroy', $resource . '.bulk-delete'],
            str_contains($this->value, '_details') => [$resource . '.details'],
            str_contains($this->value, '_payments') => [$resource . '.payments'],
            str_contains($this->value, '_notifications') => [$resource . '.notifications'],
            str_contains($this->value, '_website') => [$resource . '.website'],
            default => [],
        };
    }

    public function groupKey(): string
    {
        return explode('_', $this->value)[0];
    }
}
