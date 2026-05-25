<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Support\RoutePermissionCatalog;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $catalog = app(RoutePermissionCatalog::class);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->only(['id', 'name', 'email']) : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'print_id' => fn () => $request->session()->get('print_id'),
            ],
            'security' => [
                'sessionTimeoutMinutes' => (int) config('security.session_idle_timeout', config('session.lifetime', 120)),
            ],
            'menus' => function () use ($request, $catalog) {
                $user = $request->user();
                if (!$user) {
                    return [];
                }

                $menus = Menu::whereNull('parent_id')->with(['children' => function ($query) {
                    $query->orderBy('order');
                }])->orderBy('order')->get();

                return $menus
                    ->map(function (Menu $menu) use ($user, $catalog) {
                        $visibleChildren = $menu->children
                            ->filter(fn (Menu $child) => $this->canAccessMenu($child, $user, $catalog))
                            ->values();

                        $menu->setRelation('children', $visibleChildren);

                        return $menu;
                    })
                    ->filter(function (Menu $menu) use ($user, $catalog) {
                        if ($menu->children->isNotEmpty()) {
                            return true;
                        }

                        return $this->canAccessMenu($menu, $user, $catalog);
                    })
                    ->values();
            },
        ];
    }

    private function canAccessMenu(Menu $menu, $user, RoutePermissionCatalog $catalog): bool
    {
        if (!empty($menu->permission_name) && !$user->can($menu->permission_name)) {
            return false;
        }

        foreach ($catalog->routePermissionsByName($menu->route) as $routePermission) {
            if (!$user->can($routePermission)) {
                return false;
            }
        }

        return !$menu->route || $catalog->routeExists($menu->route);
    }
}
