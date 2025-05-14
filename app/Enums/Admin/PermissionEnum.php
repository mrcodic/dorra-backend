<?php

namespace App\Enums\Admin;

enum PermissionEnum : string
{
    case CREATE_TEMPLATES = 'create-templates';
    case EDIT_TEMPLATES = 'edit-templates';
    case PUBLISH_TEMPLATES = 'publish-templates';

    public function routes(): array
    {
        return match ($this) {
          self::CREATE_TEMPLATES => ['templates.create','templates.store'],
          self::EDIT_TEMPLATES => ['templates.edit','templates.update'],
          self::PUBLISH_TEMPLATES => ['templates.publish'],
        };
    }

}
