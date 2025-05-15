<?php

namespace App\Enums\Admin;

enum PermissionEnum : string
{
    case CREATE_TEMPLATES = 'templatesCreate';
    case UPDATE_TEMPLATES = 'templatesUpdate';
    case PUBLISH_TEMPLATES = 'templatesPublish';

    public function group(): string
    {
        return match ($this) {
            self::CREATE_TEMPLATES,
            self::UPDATE_TEMPLATES ,
            self::PUBLISH_TEMPLATES => 'templates',
        };
    }
    public function routes(): array
    {
        return match ($this) {
          self::CREATE_TEMPLATES => ['templates.create','templates.store'],
          self::UPDATE_TEMPLATES => ['templates.edit','templates.update'],
          self::PUBLISH_TEMPLATES => ['templates.publish'],
        };
    }



}
