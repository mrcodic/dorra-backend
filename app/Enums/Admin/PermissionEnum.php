<?php

namespace App\Enums\Admin;

enum PermissionEnum : string
{
    case CREATE_TEMPLATES = 'templatesCreate';
    case READ_TEMPLATES = 'templatesRead';
    case UPDATE_TEMPLATES = 'templatesUpdate';
    case DELETE_TEMPLATES = 'templatesDelete';

    public function group(): string
    {
        return match ($this) {
            self::CREATE_TEMPLATES,
            self::DELETE_TEMPLATES ,
            self::UPDATE_TEMPLATES ,
            self::READ_TEMPLATES => 'templates',
        };
    }
    public function routes(): array
    {
        return match ($this) {
          self::CREATE_TEMPLATES => ['templates.create','templates.store'],
          self::UPDATE_TEMPLATES => ['templates.edit','templates.update'],
          self::READ_TEMPLATES => ['templates.show'],
          self::DELETE_TEMPLATES => ['templates.destroy','templates.bulk-delete'],
        };
    }



}
