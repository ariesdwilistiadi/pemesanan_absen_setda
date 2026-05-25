<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MenuPermissionSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_menu_remains_visible_when_child_route_is_accessible(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Permission::findOrCreate('view_users', 'web');
        Permission::findOrCreate('view_anggotas', 'web');
        $user->givePermissionTo('view_anggotas');

        $parent = Menu::create([
            'name' => 'Master Data',
            'route' => null,
            'permission_name' => 'view_users',
            'order' => 1,
        ]);

        Menu::create([
            'name' => 'Anggota',
            'route' => 'anggotas.index',
            'permission_name' => 'view_anggotas',
            'parent_id' => $parent->id,
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->withHeaders($this->statefulHeaders())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('menus', 1)
                ->where('menus.0.name', 'Master Data')
                ->has('menus.0.children', 1)
                ->where('menus.0.children.0.route', 'anggotas.index')
            );
    }

    public function test_menu_route_permission_must_match_the_route_requirement(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Permission::findOrCreate('create_menus', 'web');
        Permission::findOrCreate('view_users', 'web');
        Permission::findOrCreate('create_users', 'web');
        $user->givePermissionTo('create_menus');

        $this->actingAs($user)
            ->withHeaders($this->statefulHeaders())
            ->post(route('menus.store'), [
                'name' => 'User List',
                'route' => 'users.index',
                'permission_name' => 'create_users',
            ])
            ->assertSessionHasErrors([
                'permission_name' => 'Permission menu harus sama dengan permission route agar menu dan akses user tetap sinkron.',
            ]);
    }
}
