<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
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
                    'role'  => optional($user->role)->role,
                ] : null,
            ],

            // the same sidebar the Blade pages render, so both halves of the
            // app show one navigation while the migration is in progress
            'menu' => $this->menu($request),

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
        $roleName = $user ? optional($user->role)->role : null;

        if (! $roleName) {
            return [];
        }

        $blocks = [];

        foreach (config('menu', []) as $block) {
            $items = [];

            foreach ($block['items'] as $item) {
                if (! in_array($roleName, $item['roles'], true)) {
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
                    'items'  => $items,
                ];
            }
        }

        return $blocks;
    }
}
