<?php

namespace App\Support;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;
use Spatie\Permission\Models\Permission;

class RoutePermissionCatalog
{
    /**
     * Route-name yang tidak relevan untuk menu aplikasi.
     *
     * @var array<int, string>
     */
    private array $excludedMenuRoutes = [
        'login',
        'register',
        'logout',
        'password.request',
        'password.email',
        'password.reset',
        'password.store',
        'password.confirm',
        'password.update',
        'verification.notice',
        'verification.send',
        'verification.verify',
        'rapat.public.show',
        'rapat.public.store',
    ];

    public function syncPermissionsFromRoutes(): void
    {
        foreach ($this->allRoutePermissions() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }
    }

    public function groupedPermissions(): Collection
    {
        $this->syncPermissionsFromRoutes();

        $permissionUsage = $this->permissionUsage();

        return Permission::query()
            ->orderBy('name')
            ->get()
            ->map(function (Permission $permission) use ($permissionUsage) {
                $usedByRoutes = $permissionUsage->get($permission->name, []);

                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'module' => $this->permissionModule($permission->name),
                    'used_by_routes' => array_values($usedByRoutes),
                    'is_used_by_route' => !empty($usedByRoutes),
                ];
            })
            ->groupBy('module')
            ->map(fn (Collection $group, string $module) => [
                'key' => $module,
                'label' => $this->permissionModuleLabel($module),
                'permissions' => $group->sortBy('name')->values()->all(),
            ])
            ->values();
    }

    public function permissionUsage(): Collection
    {
        return $this->allNamedRoutes()
            ->reduce(function (Collection $carry, Route $route) {
                foreach ($this->routePermissions($route) as $permission) {
                    $carry->put(
                        $permission,
                        array_values(array_unique(array_merge($carry->get($permission, []), [$route->getName()])))
                    );
                }

                return $carry;
            }, collect())
            ->sortKeys();
    }

    public function menuRouteOptions(): Collection
    {
        return $this->allNamedRoutes()
            ->filter(fn (Route $route) => $this->isMenuCandidate($route))
            ->map(function (Route $route) {
                $requiredPermissions = $this->routePermissions($route);
                $requiredPermission = count($requiredPermissions) === 1 ? $requiredPermissions[0] : null;

                return [
                    'name' => $route->getName(),
                    'uri' => $route->uri(),
                    'module' => $this->routeModule($route),
                    'label' => $this->routeLabel($route),
                    'required_permission' => $requiredPermission,
                    'required_permissions' => $requiredPermissions,
                ];
            })
            ->sortBy(fn (array $routeOption) => $routeOption['module'].'|'.$routeOption['label'])
            ->values();
    }

    public function routePermissionsByName(?string $routeName): array
    {
        if (!$routeName) {
            return [];
        }

        $route = RouteFacade::getRoutes()->getByName($routeName);

        return $route ? $this->routePermissions($route) : [];
    }

    public function allRoutePermissions(): array
    {
        return $this->allNamedRoutes()
            ->flatMap(fn (Route $route) => $this->routePermissions($route))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function routeExists(?string $routeName): bool
    {
        if (!$routeName) {
            return true;
        }

        return RouteFacade::has($routeName);
    }

    public function routeModule(Route $route): string
    {
        $routeName = (string) $route->getName();

        return match (true) {
            str_starts_with($routeName, 'users.') || str_starts_with($routeName, 'permissions.') => 'users',
            str_starts_with($routeName, 'menus.') => 'menus',
            str_starts_with($routeName, 'anggotas.') => 'anggotas',
            str_starts_with($routeName, 'produks.') => 'produks',
            str_starts_with($routeName, 'kasir.') => 'kasir',
            str_starts_with($routeName, 'rapat.') => 'rapat',
            str_starts_with($routeName, 'dana-dkks.') => 'dana_dkks',
            str_starts_with($routeName, 'pinjaman-bayar.') => 'pinjaman_bayar',
            str_starts_with($routeName, 'pinjaman.') => 'pinjaman',
            str_starts_with($routeName, 'profile.') => 'profile',
            $routeName === 'dashboard' => 'dashboard',
            default => 'general',
        };
    }

    public function permissionModule(string $permissionName): string
    {
        return match (true) {
            str_contains($permissionName, '_users') || str_contains($permissionName, 'user_permissions') => 'users',
            str_contains($permissionName, '_menus') => 'menus',
            str_contains($permissionName, '_anggotas') => 'anggotas',
            str_contains($permissionName, '_produks') => 'produks',
            str_contains($permissionName, '_kasir') => 'kasir',
            str_contains($permissionName, '_rapat') => 'rapat',
            str_contains($permissionName, '_dana_dkks') => 'dana_dkks',
            str_contains($permissionName, '_pinjaman_bayar') => 'pinjaman_bayar',
            str_contains($permissionName, '_pinjaman') => 'pinjaman',
            default => 'general',
        };
    }

    public function permissionModuleLabel(string $module): string
    {
        return match ($module) {
            'users' => 'User',
            'menus' => 'Menu',
            'anggotas' => 'Anggota',
            'produks' => 'Produk',
            'kasir' => 'Kasir',
            'rapat' => 'Rapat',
            'dana_dkks' => 'Dana DKK',
            'pinjaman' => 'Pinjaman',
            'pinjaman_bayar' => 'Pembayaran Pinjaman',
            'dashboard' => 'Dashboard',
            'profile' => 'Profil',
            default => 'General',
        };
    }

    private function allNamedRoutes(): Collection
    {
        return collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route) => filled($route->getName()))
            ->values();
    }

    /**
     * @return array<int, string>
     */
    private function routePermissions(Route $route): array
    {
        return collect($route->gatherMiddleware())
            ->filter(fn (string $middleware) => str_starts_with($middleware, 'permission:'))
            ->map(fn (string $middleware) => trim(substr($middleware, strlen('permission:'))))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function isMenuCandidate(Route $route): bool
    {
        $routeName = (string) $route->getName();

        if (in_array($routeName, $this->excludedMenuRoutes, true)) {
            return false;
        }

        if (str_starts_with($routeName, 'api.')) {
            return false;
        }

        if (!in_array('GET', $route->methods(), true) && !in_array('HEAD', $route->methods(), true)) {
            return false;
        }

        return empty($route->parameterNames());
    }

    private function routeLabel(Route $route): string
    {
        $routeName = (string) $route->getName();

        return match ($routeName) {
            'dashboard' => 'Dashboard',
            'profile.edit' => 'Profil',
            default => str($routeName)
                ->replace(['.', '-'], ' ')
                ->title()
                ->toString(),
        };
    }
}
