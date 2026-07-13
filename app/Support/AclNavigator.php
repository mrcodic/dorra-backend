<?php

namespace App\Support;

class AclNavigator
{
    protected function flattenMap(): array
    {
        $map = config('menu_acl', []);
        $flat = [];

        foreach ($map as $key => $value) {
            if (is_array($value) && array_key_exists('children', $value)) {
                foreach ($value['children'] as $childUrl => $childPermissions) {
                    $flat[$childUrl] = (array) $childPermissions;
                }
                continue;
            }

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
     * matching by path prefix (segment-aware) so /jobs/124, /users/112
     * etc. correctly match their parent /jobs, /users entries.
     */
    public function userCanAccessUrl($user, string $targetUrl): bool
    {
        $targetPath = $this->normalizePath(parse_url($targetUrl, PHP_URL_PATH) ?? '');
        $targetSegments = $targetPath === '' ? [] : explode('/', $targetPath);

        $bestMatch = null;
        $bestMatchLength = -1;

        foreach ($this->flattenMap() as $url => $permissions) {
            $mapPath = $this->normalizePath($url);
            $mapSegments = $mapPath === '' ? [] : explode('/', $mapPath);

            if (!$this->segmentsMatchPrefix($mapSegments, $targetSegments)) {
                continue;
            }

            // Prefer the most specific (longest) matching prefix,
            // e.g. '/settings/details' over a hypothetical '/settings'.
            if (count($mapSegments) > $bestMatchLength) {
                $bestMatchLength = count($mapSegments);
                $bestMatch = $permissions;
            }
        }

        if ($bestMatch === null) {
            return false;
        }

        $permissions = collect($bestMatch)->filter()->values();

        if ($permissions->isEmpty()) {
            return true;
        }

        return $permissions->contains(fn($p) => $user->can($p));
    }

    protected function normalizePath(string $path): string
    {
        return trim($path, '/');
    }

    /**
     * True if $mapSegments is a prefix of $targetSegments
     * (matching whole segments, not partial strings).
     */
    protected function segmentsMatchPrefix(array $mapSegments, array $targetSegments): bool
    {
        if (count($mapSegments) > count($targetSegments)) {
            return false;
        }

        foreach ($mapSegments as $i => $segment) {
            if ($targetSegments[$i] !== $segment) {
                return false;
            }
        }

        return true;
    }
}
