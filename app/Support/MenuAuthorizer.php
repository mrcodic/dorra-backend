<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;

class MenuAuthorizer
{
    public static function filter(object|array $verticalMenuData, ?Authenticatable $user): object|array
    {
        $isObject = is_object($verticalMenuData);
        $items = $isObject ? ($verticalMenuData->menu ?? []) : ($verticalMenuData['menu'] ?? []);
        $filtered = self::filterItems($items, $user);

        if ($isObject) {
            $verticalMenuData->menu = $filtered;
            return $verticalMenuData;
        }
        $verticalMenuData['menu'] = $filtered;
        return $verticalMenuData;
    }

    private static function filterItems(array $items, ?Authenticatable $user): array
    {
        $acl   = config('menu_acl', []);
        $perms = $user?->getAllPermissions()?->pluck('name')->all() ?? [];

        $normalizeUrl = fn ($url) => '/' . ltrim(parse_url($url ?? '', PHP_URL_PATH) ?: '', '/');

        $out = [];

        foreach ($items as $raw) {
            $item    = (array) $raw;
            $name    = Arr::get($item, 'name');
            $url     = $normalizeUrl(Arr::get($item, 'url'));
            $submenu = Arr::get($item, 'submenu', []);

            // Parent with children
            if (!empty($submenu)) {
                $parentAcl   = $acl[$name] ?? null; // parent keyed by "name"
                $childrenMap = is_array($parentAcl) ? Arr::get($parentAcl, 'children', []) : [];

                $childOut = [];
                foreach ((array) $submenu as $childRaw) {
                    $child = (array) $childRaw;
                    $cUrl  = $normalizeUrl(Arr::get($child, 'url'));

                    // Required permission (optional) from config; might be null
                    $required = $childrenMap[$cUrl] ?? ($acl[$cUrl] ?? null);

                    if (self::granted($required, $cUrl, $childrenMap, $perms)) {
                        $childOut[] = is_object($childRaw) ? (object) $child : $child;
                    }
                }

                // Show parent if at least one child visible
                if (count($childOut)) {
                    // Neutralize parent link if user cannot access parent URL directly
                    $parentRequired = $acl[$url] ?? null;
                    if ($url !== '/' && !self::granted($parentRequired, $url, $childrenMap, $perms)) {
                        $item['url'] = 'javascript:void(0)';
                    }
                    $item['submenu'] = $childOut;
                    $out[] = is_object($raw) ? (object) $item : $item;
                }
                continue;
            }

            // Leaf
            $required = $acl[$url] ?? null;
            if (self::granted($required, $url, /* siblings */ [], $perms)) {
                $out[] = is_object($raw) ? (object) $item : $item;
            }
        }

        return $out;
    }

    /**
     * Grant if the user has ANY CRUD permission for the computed group.
     * Resolution order:
     *   1) If a specific permission name is provided (e.g., 'users_show'):
     *        - exact match OR any '<group>_*'
     *   2) Else (no ACL), infer group from URL:
     *        - if in a parent childrenMap, use that child permission's group
     *        - otherwise, use the first URL segment as group (e.g., '/admins' => 'admins')
     */
    private static function granted(string|array|null $required, string $url, array $childrenMap, array $userPerms): bool
    {
        // 1) Explicit requirements from ACL
        if (is_string($required)) {
            // exact match OR any CRUD for that permission's group
            $group = self::groupFromPermission($required);
            return in_array($required, $userPerms, true) || self::hasAnyCrudForGroup($userPerms, $group);
        }

        if (is_array($required)) {
            // any of the listed perms OR any CRUD for the group of the first listed perm
            if (self::hasAny($userPerms, $required)) {
                return true;
            }
            $first = $required[0] ?? null;
            if (is_string($first)) {
                $group = self::groupFromPermission($first);
                return self::hasAnyCrudForGroup($userPerms, $group);
            }
            return false;
        }

        // 2) No explicit requirement: resolve via children map or URL
        if (array_key_exists($url, $childrenMap)) {
            $childReq = $childrenMap[$url];
            // could be string or array
            return self::granted($childReq, $url, [], $userPerms);
        }

        // 3) Fallback: infer group from URL (first segment)
        $group = self::groupFromUrl($url);
        return $group ? self::hasAnyCrudForGroup($userPerms, $group) : false;
    }

    private static function hasAny(array $userPerms, array $requiredList): bool
    {
        // true if user has at least one of $requiredList
        return (bool) array_intersect($userPerms, $requiredList);
    }


    private static function hasAnyCrudForGroup(array $userPerms, string $group): bool
    {
        // Check if user has ANY permission that starts with "<group>_"
        $prefix = $group . '_';
        foreach ($userPerms as $p) {
            if (str_starts_with($p, $prefix)) {
                return true;
            }
        }
        return false;
    }

    private static function groupFromPermission(string $permission): string
    {
        // group is everything before the first underscore (can contain hyphens)
        $pos = strpos($permission, '_');
        return $pos === false ? $permission : substr($permission, 0, $pos);
    }

    private static function groupFromUrl(string $url): ?string
    {
        // '/settings/details' -> 'settings-details' only if you need that;
        // By default, first segment: '/product-templates' -> 'product-templates'
        $path = ltrim($url, '/');
        if ($path === '') return 'dashboard';
        $first = explode('/', $path, 2)[0];
        return $first ?: null;
    }
}
