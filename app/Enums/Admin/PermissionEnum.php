<?php

namespace App\Enums\Admin;

enum PermissionEnum : string
{
    case CREATE_USERS = 'create_users';
    case SHOW_USERS = 'show_users';
    case UPDATE_USERS = 'update_users';
    case MANAGE_ROLES = 'manage_roles';
    case VIEW_DASHBOARD = 'view_dashboard';
    case MANAGE_CATEGORIES = 'manage_categories';

    public function routes()
    {
        return match ($this) {
          self::CREATE_USERS => [],
          self::MANAGE_ROLES => [],
          self::VIEW_DASHBOARD => [],
          self::MANAGE_CATEGORIES => [],
        };
    }

}
