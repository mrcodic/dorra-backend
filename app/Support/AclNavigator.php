<?php

namespace App\Support;

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

            if ($permissions->contains(fn($p) => $user->can($p))) {
                return url($url);
            }
        }

        return null;
    }

    /**
     * Check whether the given user has access to a specific URL,
     * based on the same menu_acl map.
     */
    public function userCanAccessUrl($user, string $targetUrl): bool
    {
        $map = config('menu_acl', []);

        // Normalize both sides to compare paths only (ignore domain/query string)
        $targetPath = ltrim(parse_url($targetUrl, PHP_URL_PATH) ?? '', '/');

        foreach ($map as $url => $permissions) {
            $mapPath = ltrim(parse_url(url($url), PHP_URL_PATH) ?? '', '/');

            if ($mapPath !== $targetPath) {
                continue;
            }

            $permissions = collect($permissions)->filter()->values();

            if ($permissions->isEmpty()) {
                return true; // no permissions required for this route
            }

            return $permissions->contains(fn($p) => $user->can($p));
        }

        // URL not found in the ACL map at all — decide default behavior.
        // Returning false is safer (deny access to unmapped intended URLs).
        return false;
    }
}
