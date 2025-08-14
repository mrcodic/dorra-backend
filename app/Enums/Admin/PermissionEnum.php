<?php

namespace App\Enums\Admin;

enum PermissionEnum: string
{
    // Dashboards
    case DASHBOARDS = 'dashboards_show';

    // Admins
    case CREATE_ADMINS = 'admins_create';
    case SHOW_ADMINS = 'admins_show';
    case UPDATE_ADMINS = 'admins_update';
    case DELETE_ADMINS = 'admins_delete';

    // Users
    case CREATE_USERS = 'users_create';
    case SHOW_USERS = 'users_show';
    case UPDATE_USERS = 'users_update';
    case DELETE_USERS = 'users_delete';

    // Products
    case CREATE_PRODUCTS = 'products_create';
    case SHOW_PRODUCTS = 'products_show';
    case UPDATE_PRODUCTS = 'products_update';
    case DELETE_PRODUCTS = 'products_delete';

    // Categories
    case CREATE_CATEGORIES = 'categories_create';
    case SHOW_CATEGORIES = 'categories_show';
    case UPDATE_CATEGORIES = 'categories_update';
    case DELETE_CATEGORIES = 'categories_delete';

    // SubCategories
    case CREATE_SUB_CATEGORIES = 'sub_categories_create';
    case SHOW_SUB_CATEGORIES = 'sub_categories_show';
    case UPDATE_SUB_CATEGORIES = 'sub_categories_update';
    case DELETE_SUB_CATEGORIES = 'sub_categories_delete';

    // Tags
    case CREATE_TAGS = 'tags_create';
    case SHOW_TAGS = 'tags_show';
    case UPDATE_TAGS = 'tags_update';
    case DELETE_TAGS = 'tags_delete';

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
    case CREATE_DISCOUNT_CODES = 'discount_codes_create';
    case SHOW_DISCOUNT_CODES = 'discount_codes_show';
    case UPDATE_DISCOUNT_CODES = 'discount_codes_update';
    case DELETE_DISCOUNT_CODES = 'discount_codes_delete';

    // Offers
    case CREATE_OFFERS = 'offers_create';
    case SHOW_OFFERS = 'offers_show';
    case UPDATE_OFFERS = 'offers_update';
    case DELETE_OFFERS = 'offers_delete';

    // Invoices

    case SHOW_INVOICES = 'invoices_show';
    case UPDATE_INVOICES = 'invoices_update';
    case DELETE_INVOICES = 'invoices_delete';

    // Logistics
    case SHOW_LOGISTICS_DASHBOARD = 'logistics_dashboard_show';


    // Locations
    case CREATE_LOCATIONS = 'locations_create';
    case SHOW_LOCATIONS = 'locations_show';
    case UPDATE_LOCATIONS = 'locations_update';
    case DELETE_LOCATIONS = 'locations_delete';

    // Roles
    case CREATE_ROLES = 'roles_create';
    case SHOW_ROLES = 'roles_show';
    case UPDATE_ROLES = 'roles_update';
    case DELETE_ROLES = 'roles_delete';
    // Permissions
    case SHOW_PERMISSIONS = 'permissions_show';

    // FAQs & Help
    case CREATE_FAQS = 'faqs_create';
    case SHOW_FAQS = 'faqs_show';
    case UPDATE_FAQS = 'faqs_update';
    case DELETE_FAQS = 'faqs_delete';

    // Settings
    case CREATE_SETTINGS = 'settings_create';
    case SHOW_SETTINGS = 'settings_show';
    case UPDATE_SETTINGS = 'settings_update';
    case DELETE_SETTINGS = 'settings_delete';


    public function groupKey(): string
    {
        return explode('_', $this->value)[0];
    }
    public function group(): string
    {
        return match ($this) {
            // Dashboards
            self::DASHBOARDS => 'Dashboards',

            // Admins
            self::CREATE_ADMINS, self::SHOW_ADMINS, self::UPDATE_ADMINS, self::DELETE_ADMINS
            => 'Admins',

            // Users
            self::CREATE_USERS, self::SHOW_USERS, self::UPDATE_USERS, self::DELETE_USERS
            => 'Users',

            // Products
            self::CREATE_PRODUCTS, self::SHOW_PRODUCTS, self::UPDATE_PRODUCTS, self::DELETE_PRODUCTS
            => 'Products',

            // Categories
            self::CREATE_CATEGORIES, self::SHOW_CATEGORIES, self::UPDATE_CATEGORIES, self::DELETE_CATEGORIES
            => 'Categories',

            // SubCategories
            self::CREATE_SUB_CATEGORIES, self::SHOW_SUB_CATEGORIES, self::UPDATE_SUB_CATEGORIES, self::DELETE_SUB_CATEGORIES
            => 'Sub Categories',

            // Tags
            self::CREATE_TAGS, self::SHOW_TAGS, self::UPDATE_TAGS, self::DELETE_TAGS
            => 'Tags',

            // Templates
            self::CREATE_TEMPLATES, self::SHOW_TEMPLATES, self::UPDATE_TEMPLATES, self::DELETE_TEMPLATES
            => 'Product Templates',

            // Mockups
            self::CREATE_MOCKUPS, self::SHOW_MOCKUPS, self::UPDATE_MOCKUPS, self::DELETE_MOCKUPS
            => 'Mockups',

            // Orders
            self::CREATE_ORDERS, self::SHOW_ORDERS, self::UPDATE_ORDERS, self::DELETE_ORDERS
            => 'Orders',

            // Discount Codes
            self::CREATE_DISCOUNT_CODES, self::SHOW_DISCOUNT_CODES, self::UPDATE_DISCOUNT_CODES, self::DELETE_DISCOUNT_CODES
            => 'Discount Codes',

            // Offers
            self::CREATE_OFFERS, self::SHOW_OFFERS, self::UPDATE_OFFERS, self::DELETE_OFFERS
            => 'Offers',

            // Invoices
            self::SHOW_INVOICES, self::UPDATE_INVOICES, self::DELETE_INVOICES
            => 'Invoices',

            // Logistics
            self::SHOW_LOGISTICS_DASHBOARD
            => 'Logistics',

            // Locations
            self::CREATE_LOCATIONS, self::SHOW_LOCATIONS, self::UPDATE_LOCATIONS, self::DELETE_LOCATIONS
            => 'Locations',

            // Roles
            self::CREATE_ROLES, self::SHOW_ROLES, self::UPDATE_ROLES, self::DELETE_ROLES
            => 'Roles',

            // Permissions
            self::SHOW_PERMISSIONS
            => 'Permissions',

            // FAQs & Help
            self::CREATE_FAQS, self::SHOW_FAQS, self::UPDATE_FAQS, self::DELETE_FAQS
            => 'FAQs & Help',

            // Settings
            self::CREATE_SETTINGS, self::SHOW_SETTINGS, self::UPDATE_SETTINGS, self::DELETE_SETTINGS
            => 'Settings',
        };
    }

    public function routes(): array
    {
        $resource = $this->groupKey();

        return match (true) {
            str_contains($this->value, '_create') => [$resource . '.create', $resource . '.store'],
            str_contains($this->value, '_update') => [$resource . '.edit', $resource . '.update'],
            str_contains($this->value, '_show') => [$resource . '.show', $resource . '.index'],
            str_contains($this->value, '_delete') => [$resource . '.destroy', $resource . '.bulk-delete'],
            default => [],
        };
    }
}
