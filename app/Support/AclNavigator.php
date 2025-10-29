<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

class AclNavigator
{
    public function firstAllowedUrl($user): ?string
    {
        $map = config('menu_acl', []);

        foreach ($map as $url => $permissions) {
            $permissions = collect($permissions)->filter()->values();

            if ($permissions->isEmpty()) {
                continue;
            }

            $preferred = $permissions->first(fn($p) => str_ends_with($p, '_show')) ?? $permissions->first();


            if ($preferred && $user->can($preferred)) {
                return url($url);
            }

            if ($permissions->some(fn($p) => $user->can($p))) {
                return url($url);
            }
        }

        return null;
    }
}
