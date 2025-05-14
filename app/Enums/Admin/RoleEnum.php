<?php

namespace App\Enums\Admin;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';

    public function permissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => [
                'create-templates',
                'edit-templates',
                'publish-templates',
            ],

        };
    }
}
