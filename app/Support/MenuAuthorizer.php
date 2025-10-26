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
// App\Support\MenuAuthorizer

    private static function granted(string|array|null $required, string $url, array $childrenMap, array $userPerms): bool
    {
        // 1) Explicit requirements from ACL
        if (is_string($required)) {
            $group = self::groupFromPermission($required);

            // If config asked specifically for a *_show permission, require it exactly
            if (str_ends_with($required, '_show')) {
                return in_array($required, $userPerms, true);
            }

            // Otherwise (create/update/delete/etc.), still allow if user has the exact perm
            // OR at least the group's *_show to render the menu entry.
            return in_array($required, $userPerms, true) || self::hasShowForGroup($userPerms, $group);
        }

        if (is_array($required)) {
            // If any explicit *_show is listed, require possessing at least one of them
            $showNames = array_values(array_filter($required, fn ($p) => is_string($p) && str_ends_with($p, '_show')));
            if (!empty($showNames)) {
                return self::hasAny($userPerms, $showNames);
            }

            // Otherwise, allow if user has any of the listed perms OR the group's *_show
            if (self::hasAny($userPerms, $required)) {
                return true;
            }
            $first = $required[0] ?? null;
            if (is_string($first)) {
                $group = self::groupFromPermission($first);
                return self::hasShowForGroup($userPerms, $group);
            }
            return false;
        }

        // 2) No explicit requirement: resolve via children map or URL
        if (array_key_exists($url, $childrenMap)) {
            $childReq = $childrenMap[$url];
            return self::granted($childReq, $url, [], $userPerms);
        }

        // 3) Fallback: infer group from URL and require <group>_show
        $group = self::groupFromUrl($url);
        return $group ? self::hasShowForGroup($userPerms, $group) : false;
    }

    private static function hasAny(array $userPerms, array $requiredList): bool
    {
        return (bool) array_intersect($userPerms, $requiredList);
    }

    private static function hasShowForGroup(array $userPerms, string $group): bool
    {
        // Only consider the *_show permission as the gate for visibility
        $show = $group . '_show';
        return in_array($show, $userPerms, true);
    }

    private static function groupFromPermission(string $permission): string
    {
        $pos = strpos($permission, '_');
        return $pos === false ? $permission : substr($permission, 0, $pos);
    }

    private static function groupFromUrl(string $url): ?string
    {
        $path = ltrim($url, '/');
        if ($path === '') return 'dashboard';
        $first = explode('/', $path, 2)[0];
        return $first ?: null;
    }

}
