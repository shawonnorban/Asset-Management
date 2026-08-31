<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $menuConfig = config('menu', []);
            $user = auth()->user();

            // roleName falls back to null for guests
            $roleName = $user?->canonicalRole();

            // filter the menu by role
            $filtered = [];
            foreach ($menuConfig as $block) {
                $items = [];
                foreach ($block['items'] as $item) {
                    $allowedRoles = array_map(fn ($role) => \App\Models\User::ROLE_ALIASES[$role] ?? $role, $item['roles']);
                    $allowedPermissions = $item['permissions'] ?? [];
                    $hasPermission = $user?->hasAnyPermission($allowedPermissions);
                    $hasRole = $roleName && in_array($roleName, $allowedRoles, true);
                    if ($user && ($allowedPermissions ? $hasPermission : $hasRole)) {
                        $items[] = $item;
                    }
                }
                if (!empty($items)) {
                    $filtered[] = [
                        'header' => $block['header'] ?? null,
                        'items' => $items,
                    ];
                }
            }

            $view->with('sidebarMenu', $filtered);
        });
    }
}
