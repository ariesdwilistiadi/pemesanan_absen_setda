<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Schema::defaultStringLength(191);
        Vite::prefetch(concurrency: 3);

        Gate::before(function ($user, $ability) {
            if ($user->email === 'dwilistiadi.aries51@gmail.com') {
                return true;
            }
            
            if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                return true;
            }

            return null;
        });

        Gate::define('manage-users', fn ($user) =>
            $user->can('view_users') ||
            $user->can('create_users') ||
            $user->can('edit_users') ||
            $user->can('delete_users')
        );

        Gate::define('manage-menus', fn ($user) =>
            $user->can('view_menus') ||
            $user->can('create_menus') ||
            $user->can('edit_menus') ||
            $user->can('delete_menus') ||
            $user->can('reorder_menus')
        );

        Gate::define('manage-permissions', fn ($user) =>
            $user->can('manage_user_permissions')
        );

        Gate::define('access-all-records', fn ($user) =>
            $user->can('manage_user_permissions')
        );

        if ($this->app->environment('production')) {
            config(['session.secure' => true]);
            config(['session.same_site' => 'strict']);
        }
    }
}
