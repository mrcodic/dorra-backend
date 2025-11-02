<?php

namespace App\Enums\Admin;

use App\Helpers\EnumHelpers;
use Illuminate\Support\Str;

enum PermissionEnum: string
{
    use EnumHelpers;

    // Dashboards
    case DASHBOARDS = 'dashboard_show';

    // Admins

    case CREATE_ADMINS = 'admins_create';
    case UPDATE_ADMINS = 'admins_update';
    case SHOW_ADMINS = 'admins_show';
    case DELETE_ADMINS = 'admins_delete';

    // Users

    case CREATE_USERS = 'users_create';
    case SHOW_USERS = 'users_show';
    case UPDATE_USERS = 'users_update';
    case DELETE_USERS = 'users_delete';

    // Products

    case CREATE_PRODUCTS = 'categories_create';
    case SHOW_PRODUCTS = 'categories_show';
    case UPDATE_PRODUCTS = 'categories_update';
    case DELETE_PRODUCTS = 'categories_delete';

    // Categories

    case CREATE_CATEGORIES = 'products_create';
    case SHOW_CATEGORIES = 'products_show';
    case UPDATE_CATEGORIES = 'products_update';
    case DELETE_CATEGORIES = 'products_delete';

    // SubCategories

    case CREATE_SUBCATEGORIES = 'sub-categories_create';
    case SHOW_SUBCATEGORIES = 'sub-categories_show';
    case UPDATE_SUBCATEGORIES = 'sub-categories_update';
    case DELETE_SUBCATEGORIES = 'sub-categories_delete';

    // Tags
    case CREATE_TAGS = 'tags_create';
    case SHOW_TAGS = 'tags_show';
    case UPDATE_TAGS = 'tags_update';
    case DELETE_TAGS = 'tags_delete';

    // Flags
    case CREATE_FLAGS = 'flags_create';

    case SHOW_FLAGS = 'flags_show';
    case UPDATE_FLAGS = 'flags_update';
    case DELETE_FLAGS = 'flags_delete';

    // Templates

    case CREATE_TEMPLATES = 'product-templates_create';
    case SHOW_TEMPLATES = 'product-templates_show';
    case UPDATE_TEMPLATES = 'product-templates_update';
    case DELETE_TEMPLATES = 'product-templates_delete';

    // Mockups
    case CREATE_MOCKUPS = 'mockups_create';
    case SHOW_MOCKUPS = 'mockups_show';
    case UPDATE_MOCKUPS = 'mockups_update';
    case DELETE_MOCKUPS = 'mockups_delete';

    // Orders
    case CREATE_ORDERS = 'orders_create';
    case SHOW_ORDERS = 'orders_show';
    case UPDATE_ORDERS = 'orders_update';
    case DELETE_ORDERS = 'orders_delete';

    // Discount Codes
    case CREATE_DISCOUNT_CODES = 'discount-codes_create';
    case SHOW_DISCOUNT_CODES = 'discount-codes_show';
    case UPDATE_DISCOUNT_CODES = 'discount-codes_update';
    case DELETE_DISCOUNT_CODES = 'discount-codes_delete';

    // Offers
    case CREATE_OFFERS = 'offers_create';
    case SHOW_OFFERS = 'offers_show';
    case UPDATE_OFFERS = 'offers_update';
    case DELETE_OFFERS = 'offers_delete';

    // Invoices (kept no create)
    case SHOW_INVOICES = 'invoices_show';
    case DELETE_INVOICES = 'invoices_delete';

    // Logistics
//    case SHOW_LOGISTICS_DASHBOARD = 'logistics_dashboard_show';

    // Locations
    case CREATE_LOCATIONS = 'locations_create';
    case UPDATE_LOCATIONS = 'locations_update';
    case SHOW_LOCATIONS = 'locations_show';
    case DELETE_LOCATIONS = 'locations_delete';

    // Roles
    case CREATE_ROLES = 'roles_create';
    case SHOW_ROLES = 'roles_show';
    case UPDATE_ROLES = 'roles_update';
    case DELETE_ROLES = 'roles_delete';

    // FAQs
    case CREATE_FAQS = 'faqs_create';
    case UPDATE_FAQS = 'faqs_update';
    case SHOW_FAQS = 'faqs_show';
    case DELETE_FAQS = 'faqs_delete';

    // Messages
    case SHOW_MESSAGES = 'messages_show';
    case DELETE_MESSAGES = 'messages_delete';

    // Settings
    case SETTINGS_DETAILS = 'settings-details_show';
    case SETTINGS_PAYMENTS = 'settings-payments_show';
    case SETTINGS_NOTIFICATIONS = 'settings-notifications_show';
    case SETTINGS_WEBSITE = 'settings-website_show';

    // Jobs

    case SHOW_JOBS = 'jobs_show';
    case UPDATE_JOBS = 'jobs_update';

    // Board
    case SHOW_BOARD = 'board_show';

    // Inventories
    case SHOW_INVENTORIES = 'inventories_show';
    case CREATE_INVENTORIES = 'inventories_create';

    case DELETE_INVENTORIES = 'inventories_delete';

    // Station Statuses
    case SHOW_STATION_STATUSES = 'station-statuses_show';
    case CREATE_STATION_STATUSES = 'station-statuses_create';
    case DELETE_STATION_STATUSES = 'station-statuses_delete';
    case UPDATE_STATION_STATUSES = 'station-statuses_update';

    // Industries
    case SHOW_INDUSTRIES = 'industries_show';
    case CREATE_INDUSTRIES = 'industries_create';
    case DELETE_INDUSTRIES = 'industries_delete';
    case UPDATE_INDUSTRIES = 'industries_update';
    case SHOW_SUB_INDUSTRIES = 'sub-industries_show';
    case CREATE_SUB_INDUSTRIES = 'sub-industries_create';
    case DELETE_SUB_INDUSTRIES = 'sub-industries_delete';
    case UPDATE_SUB_INDUSTRIES = 'sub-industries_update';

    case TEMPLATES_PUBLISHED_SHOW = 'product-templates.change-status.publish_show';
    case TEMPLATES_DRAFTED_SHOW   = 'product-templates.change-status.draft_show';
    case TEMPLATES_LIVE_SHOW      = 'product-templates.change-status.live_show';


    public function group(): array
    {
        return [
            'key'   => $this->groupKey(),
            'value' => $this->groupLabel(),
        ];
    }

    public function groupKey(): string
    {
        return explode('_', $this->value)[0];
    }

    private function groupLabel(): string
    {
        return match ($this->groupKey()) {
            'dashboard'          => 'Dashboards',
            'admins'              => 'Admins',
            'users'               => 'Users',
            'products'            => 'Categories',
            'categories'          => 'Products',
            'sub-categories'      => 'Sub Products',
            'tags'                => 'Tags',
            'flags'               => 'Flags',
            'product-templates'   => 'Templates',
            'product-templates.change-status.publish'=> 'Publish Templates',
            'product-templates.change-status.draft'=> 'Draft Templates',
            'product-templates.change-status.live'=> 'Live Templates',
            'mockups'             => 'Mockups',
            'orders'              => 'Orders',
            'discount-codes'      => 'Discount Codes',
            'offers'              => 'Offers',
            'invoices'            => 'Invoices',
            'locations'           => 'Locations',
            'roles'               => 'Roles',
            'faqs'                => 'FAQs',
            'messages'            => 'Messages',
            'settings-details'    => 'Settings Details',
            'settings-payments'   => 'Settings Payments',
            'settings-notifications' => 'Settings Notifications',
            'settings-website'    => 'Settings Website',
            'jobs'                => 'Jobs',
            'board'               => 'Board',
            'inventories'         => 'Inventories',
            'station-statuses'    => 'Custom Statuses',
            default               => Str::headline(str_replace('-', ' ', $this->groupKey())),
        };
    }


    public function routes(): array
    {
        $resource = $this->groupKey();

        return match (true) {
            str_contains($this->value, '_create') => [$resource . '.create', $resource . '.store'],
            str_contains($this->value, '_update') => [$resource . '.edit', $resource . '.update'],
            str_contains($this->value, '_show')   => [$resource . '.index', $resource . '.show'],
            str_contains($this->value, '_delete') => [$resource . '.destroy', $resource . '.bulk-delete'],
            default                               => [],
        };
    }
}
