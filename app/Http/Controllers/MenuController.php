<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderMenuRequest;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class MenuController extends Controller
{
    /**
     * Menampilkan halaman Menu Manager
     */
    public function index()
    {
        $menus = Menu::whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->orderBy('order', 'asc');
            }])
            ->orderBy('order', 'asc')
            ->get();

        return Inertia::render('Menu/MenuManager', [
            'menus' => $menus,
            'permissions' => Permission::select(['id', 'name'])->orderBy('name')->get(),
        ]);
    }

    /**
     * Menyimpan menu baru
     */
    public function store(StoreMenuRequest $request)
    {
        try {
            $maxOrder = Menu::where('parent_id', $request->parent_id)->max('order') ?? 0;

            Menu::create([
                'name'      => $request->name,
                'route'     => $request->route,
                'icon'      => $request->icon,
                'parent_id' => $request->parent_id,
                'permission_name' => $request->permission_name,
                'order'     => $maxOrder + 1,
            ]);

            return back()->with('success', 'Menu berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('SYSTEM FAILURE: Failed to store menu', [
                'error_message' => $e->getMessage(),
                'actor_id' => auth()->id(),
            ]);

            return back()->with('error', 'Gagal menambahkan menu. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Mengupdate menu
     */
    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        try {
            $menu->update([
                'name'      => $request->name,
                'route'     => $request->route,
                'icon'      => $request->icon,
                'parent_id' => $request->parent_id,
                'permission_name' => $request->permission_name,
            ]);

            return back()->with('success', 'Menu berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui menu: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus menu
     */
    public function destroy(Menu $menu)
    {
        try {
            // Jika menu memiliki children, pindahkan mereka ke parent menu ini sebelum dihapus
            if ($menu->children()->count() > 0) {
                $menu->children()->update(['parent_id' => $menu->parent_id]);
            }

            $menu->delete();

            return back()->with('success', 'Menu berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus menu: ' . $e->getMessage());
        }
    }

    /**
     * Mengatur ulang urutan menu (Reorder)
     */
    public function reorder(ReorderMenuRequest $request)
    {
        // Validasi payload yang dikirimkan oleh frontend

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->menus as $item) {
                    Menu::where('id', $item['id'])->update([
                        'order' => $item['order']
                    ]);
                }
            });

            return back()->with('success', 'Urutan menu berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan urutan: ' . $e->getMessage());
        }
    }
}