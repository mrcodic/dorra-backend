<?php

namespace App\Enums\Setting;

use App\Helpers\EnumHelpers;

enum SocialEnum: string
{
    use EnumHelpers;

    case FACEBOOK  = 'facebook';
    case X   = 'x';
    case INSTAGRAM = 'instagram';
    case SNAPCHAT = 'snapchat';
    case TIKTOK = 'tiktok';

    public function label(): string
    {
        return match ($this) {
            self::FACEBOOK  => 'Facebook',
            self::X   => 'X',
            self::INSTAGRAM => 'Instagram',
            self::SNAPCHAT => 'Snapchat',
            self::TIKTOK => 'Tiktok',
        };
    }


    public static function imageUrl($platform): string
    {
        return match ($platform) {
            self::FACEBOOK  => asset('images/social/facebook.svg'),
            self::X   => asset('images/social/x.svg'),
            self::INSTAGRAM => asset('images/social/instagram.svg'),
            self::SNAPCHAT => asset('images/social/snapchat.svg'),
            self::TIKTOK => asset('images/social/tiktok.svg'),
        };
    }




}
