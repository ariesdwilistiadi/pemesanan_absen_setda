<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Menampilkan halaman User Manager
     */
    public function index()
    {
        $users = User::select(['id', 'name', 'email', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('User/UserManager', [
            'users' => $users,
        ]);
    }

    /**
     * Menyimpan user baru
     */
    public function store(StoreUserRequest $request)
    {
        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return back()->with('success', 'User berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('SYSTEM FAILURE: Failed to create user', [
                'error_message' => $e->getMessage(),
                'actor_id' => auth()->id(),
                'request_ip' => $request->ip(),
            ]);

            return back()->with('error', 'Gagal menambahkan user. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Mengupdate user
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            return back()->with('success', 'User berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('SYSTEM FAILURE: Failed to update user', [
                'error_message' => $e->getMessage(),
                'actor_id' => auth()->id(),
                'target_user_id' => $user->id,
            ]);

            return back()->with('error', 'Gagal memperbarui user. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Menghapus user
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deleting own account
            if ($user->id === auth()->id()) {
                return back()->with('error', 'Tidak dapat menghapus akun sendiri!');
            }

            $user->delete();

            // Security Audit: Catat di sistem saat ada admin yang menghapus user
            Log::warning('Security Audit: User deleted', [
                'performed_by' => auth()->id(),
                'deleted_user_id' => $user->id,
                'deleted_email' => $user->email,
                'ip_address' => request()->ip(),
            ]);

            return back()->with('success', 'User berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
}