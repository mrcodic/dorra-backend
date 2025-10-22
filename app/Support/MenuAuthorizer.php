<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;

class MenuAuthorizer
{
    public static function filter(object|array $verticalMenuData, ?Authenticatable $user): object|array
    {
        // Accept both decoded object and array; normalize to array-of-items, return same shape
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

        $can = function (?string $permission) use ($perms) {
            if (!$permission) return true; // no permission required
            return in_array($permission, $perms, true);
        };

        $normalizeUrl = fn ($url) => '/' . ltrim(parse_url($url ?? '', PHP_URL_PATH) ?: '', '/');

        $out = [];

        foreach ($items as $raw) {
            // $raw is stdClass (from json_decode) or array; treat as array
            $item = (array) $raw;
            $name = Arr::get($item, 'name');
            $url  = $normalizeUrl(Arr::get($item, 'url'));
            $submenu = Arr::get($item, 'submenu', []);

            // Parent with children
            if (!empty($submenu)) {
                $parentAcl = $acl[$name] ?? null; // parent keyed by "name"
                $parentAllowed = true;

                if (is_array($parentAcl) && !empty($parentAcl['permission'])) {
                    $parentAllowed = $can($parentAcl['permission']);
                }

                // Filter children by URL
                $childrenMap = is_array($parentAcl) ? Arr::get($parentAcl, 'children', []) : [];
                $childOut = [];
                foreach ((array) $submenu as $childRaw) {
                    $child = (array) $childRaw;
                    $cUrl = $normalizeUrl(Arr::get($child, 'url'));
                    $required = $childrenMap[$cUrl] ?? ($acl[$cUrl] ?? null);

                    if ($can($required)) {
                        // keep original shape (object vs array)
                        $childOut[] = is_object($childRaw) ? (object) $child : $child;
                    }
                }

                if ($parentAllowed && count($childOut)) {
                    $item['submenu'] = $childOut;
                    $out[] = is_object($raw) ? (object) $item : $item;
                }

                continue;
            }

            // Leaf
            $required = $acl[$url] ?? null;
            if ($can($required)) {
                $out[] = is_object($raw) ? (object) $item : $item;
            }
        }

        return $out;
    }
}
