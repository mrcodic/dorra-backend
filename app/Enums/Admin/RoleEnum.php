<?php

namespace App\Enums\Admin;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
    case CUSTOMER_SERVICE = 'customer_service';
    case MANAGER = 'manager';
    case MARKETING_SPECIALIST = 'marketing_specialist';


    public function permissions()
    {
        return match ($this) {
            self::ADMIN => [

            ],
            self::CUSTOMER => [

            ],
            self::CUSTOMER_SERVICE => [

            ],
            self::MANAGER => [

            ],
            self::MARKETING_SPECIALIST => [

            ],
        };
    }
}
