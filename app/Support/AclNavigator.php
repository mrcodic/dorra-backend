<?php

namespace App\Support;

class AclNavigator
{
    /**
     * Flatten menu_acl into a single ['/url' => [permissions]] map,
     * handling both leaf entries and 'children' nested entries.
     */
    protected function flattenMap(): array
    {
        $map = config('menu_acl', []);
        $flat = [];

        foreach ($map as $key => $value) {
            // Nested parent with children
            if (is_array($value) && array_key_exists('children', $value)) {
                foreach ($value['children'] as $childUrl => $childPermissions) {
                    $flat[$childUrl] = (array) $childPermissions;
                }
                continue;
            }

            // Leaf entry, e.g. '/orders' => [...]
            $flat[$key] = (array) $value;
        }

        return $flat;
    }

    public function firstAllowedUrl($user): ?string
    {
        foreach ($this->flattenMap() as $url => $permissions) {
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
     * based on the flattened menu_acl map.
     */
    public function userCanAccessUrl($user, string $targetUrl): bool
    {
        $targetPath = '/' . ltrim(parse_url($targetUrl, PHP_URL_PATH) ?? '', '/');

        foreach ($this->flattenMap() as $url => $permissions) {
            $mapPath = '/' . ltrim($url, '/');

            if ($mapPath !== $targetPath) {
                continue;
            }

            $permissions = collect($permissions)->filter()->values();

            if ($permissions->isEmpty()) {
                return true;
            }

            return $permissions->contains(fn($p) => $user->can($p));
        }

        return false;
    }
}
