<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Data shared with every Inertia page.
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role_label,
                    'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
                    'image_url' => $user->image
                        ? Storage::url($user->image)
                        : ($user->employee?->image ? Storage::url($user->employee->image) : null),
                ] : null,
            ],

            // the same sidebar the Blade pages render, so both halves of the
            // app show one navigation while the migration is in progress
            'menu' => $this->menu($request),

            // header badge, shared with every page so the count is never stale
            'notifications' => [
                'unread_count' => fn () => $user
                    ? $user->notifications()->where('is_read', false)->count()
                    : 0,
                'can_view' => (bool) $user?->hasAnyPermission(['notifications.view', 'notifications.manage']),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }

    /**
     * config/menu.php filtered by the current user's role and resolved to URLs.
     */
    private function menu(Request $request): array
    {
        $user = $request->user();
        $roleName = $user?->canonicalRole();

        if (! $roleName) {
            return [];
        }

        $blocks = [];

        foreach (config('menu', []) as $block) {
            $items = [];

            foreach ($block['items'] as $item) {
                $allowedRoles = array_map(fn ($role) => \App\Models\User::ROLE_ALIASES[$role] ?? $role, $item['roles']);
                $allowedPermissions = $item['permissions'] ?? [];
                $hasPermission = $allowedPermissions && $user->hasAnyPermission($allowedPermissions);
                if ($allowedPermissions ? ! $hasPermission : ! in_array($roleName, $allowedRoles, true)) {
                    continue;
                }

                $url = \Route::has($item['route']) ? route($item['route']) : null;

                $items[] = [
                    'label'  => $item['label'],
                    'icon'   => $item['icon'],
                    'url'    => $url,
                    'active' => $url !== null && $request->url() === $url,
                ];
            }

            if ($items) {
                $blocks[] = [
                    'header' => $block['header'] ?? null,
                    'collapsible' => $block['collapsible'] ?? true,
                    'items'  => $items,
                ];
            }
        }

        return $blocks;
    }
}
