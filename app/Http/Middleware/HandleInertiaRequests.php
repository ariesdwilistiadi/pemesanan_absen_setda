<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Menu;

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
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->only(['id', 'name', 'email']) : null,
            ],
            'menus' => function () use ($request) {
                $user = $request->user();
                if (!$user) return [];
                
                // Filter menu menggunakan PHP Collection agar sesuai dengan Hak Akses User
                $menus = Menu::whereNull('parent_id')->with(['children' => function ($query) {
                    $query->orderBy('order');
                }])->orderBy('order')->get();

                return $menus->filter(function ($menu) use ($user) {
                    return empty($menu->permission_name) || $user->can($menu->permission_name);
                })->map(function ($menu) use ($user) {
                    $menu->setRelation('children', $menu->children->filter(function ($child) use ($user) {
                        return empty($child->permission_name) || $user->can($child->permission_name);
                    })->values());
                    return $menu;
                })->values();
            },
        ];
    }
}
