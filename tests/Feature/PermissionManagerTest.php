<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_manager_groups_permissions_and_marks_protected_accounts(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin A',
            'email' => 'admin@example.com',
        ]);

        $protected = User::factory()->create([
            'name' => 'Dwi Protected',
            'email' => 'dwilistiadi.aries51@gmail.com',
        ]);

        $normalUser = User::factory()->create([
            'name' => 'User Z',
            'email' => 'user@example.com',
        ]);

        Permission::findOrCreate('manage_user_permissions', 'web');
        Permission::findOrCreate('view_users', 'web');
        Permission::findOrCreate('create_dana_dkks', 'web');
        $admin->givePermissionTo('manage_user_permissions');

        $this->actingAs($admin)
            ->withHeaders($this->statefulHeaders())
            ->get(route('permissions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Permission/PermissionManager')
                ->where('currentUserId', $admin->id)
                ->has('users', 3)
                ->where('users.1.is_protected_account', true)
                ->has('permissions')
                ->where('permissions.0.permissions.0.is_used_by_route', true)
            );
    }
}
