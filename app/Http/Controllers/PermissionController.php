<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserPermissionsRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Menampilkan halaman Permission Manager
     */
    public function index()
    {
        $users = User::select(['id', 'name', 'email'])
            ->with(['permissions:id,name'])
            ->get();

        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('_', $permission->name);
            return $parts[1] ?? 'general';
        });

        return Inertia::render('Permission/PermissionManager', [
            'users' => $users,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Mengupdate permissions untuk user tertentu
     */
    public function updateUserPermissions(UpdateUserPermissionsRequest $request, User $user)
    {
        $requestingUser = $request->user();

        if (!$requestingUser || !$requestingUser->can('manage_user_permissions')) {
            Log::critical('SECURITY INCIDENT: Unauthorized permission modification attempt bypass', [
                'actor_id' => $requestingUser?->id,
                'target_user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            abort(403, 'Pelanggaran Keamanan: Percobaan modifikasi hak akses tidak sah dicatat.');
        }

        $permissions = array_values(array_unique($request->permissions));

        if ($requestingUser->id === $user->id && !in_array('manage_user_permissions', $permissions, true)) {
            return back()->with('error', 'Anda tidak dapat mencabut hak akses manajemen sendiri.');
        }

        try {
            $oldPermissions = $user->permissions->pluck('name')->toArray();

            DB::beginTransaction();

            $user->syncPermissions($permissions);

            DB::commit();

            $added = array_values(array_diff($permissions, $oldPermissions));
            $removed = array_values(array_diff($oldPermissions, $permissions));

            Log::notice('SECURITY AUDIT: User permissions successfully modified', [
                'event_type' => 'permission_modification',
                'actor' => [
                    'id' => $requestingUser->id,
                    'email' => $requestingUser->email,
                ],
                'target' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
                'changes' => [
                    'permissions_added' => $added,
                    'permissions_removed' => $removed,
                ],
                'context' => [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => session()->getId(),
                ],
            ]);

            return back()->with('success', 'Permissions berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SYSTEM FAILURE: Failed to update permissions', [
                'actor_id' => $requestingUser->id,
                'target_user_id' => $user->id,
                'error_message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan sistem saat memperbarui hak akses. Silakan hubungi tim dukungan.');
        }
    }
}
