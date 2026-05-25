<script setup>
import { ref, watch, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    permissions: {
        type: Object,
        default: () => ({}),
    },
});

const selectedUser = ref(null);
const userPermissions = ref([]);
const savingPermissions = ref(false);
const searchUserQuery = ref('');

const usersList = computed(() => {
    if (Array.isArray(props.users)) {
        return props.users;
    }

    if (props.users && Array.isArray(props.users.data)) {
        return props.users.data;
    }

    return [];
});

// Filter user berdasarkan pencarian dan batasi maksimal 50 user agar DOM tidak lag jika ada 1000+ user
const filteredUsers = computed(() => {
    let result = usersList.value;
    if (searchUserQuery.value) {
        const q = searchUserQuery.value.toLowerCase();
        result = result.filter(u =>
            u.name.toLowerCase().includes(q) ||
            u.email.toLowerCase().includes(q)
        );
    }
    
    return result.slice(0, 50);
});

// Watch untuk selectedUser
watch(selectedUser, (newUser) => {
    if (newUser) {
        userPermissions.value = (newUser.permissions || []).map(p => p.name);
    } else {
        userPermissions.value = [];
    }
}, { immediate: true });

const selectUser = (user) => {
    selectedUser.value = user;
};

const togglePermission = (permissionName) => {
    const index = userPermissions.value.indexOf(permissionName);
    if (index > -1) {
        userPermissions.value.splice(index, 1);
    } else {
        userPermissions.value.push(permissionName);
    }
};

const savePermissions = () => {
    if (!selectedUser.value) {
        alert('Pilih user terlebih dahulu!');
        return;
    }

    savingPermissions.value = true;

    router.put(route('permissions.update-user', selectedUser.value.id), {
        permissions: userPermissions.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            savingPermissions.value = false;
            alert('Permissions berhasil diperbarui!');
        },
        onError: (errors) => {
            savingPermissions.value = false;
            alert('Gagal memperbarui permissions: ' + JSON.stringify(errors));
        }
    });
};

const hasPermission = (permissionName) => {
    return userPermissions.value.includes(permissionName);
};

const getPermissionDisplayName = (permissionName) => {
    const displayNames = {
        'view_users': 'Lihat User',
        'create_users': 'Buat User',
        'edit_users': 'Edit User',
        'delete_users': 'Hapus User',
        'manage_user_permissions': 'Kelola Hak Akses User',
        'view_menus': 'Lihat Menu',
        'create_menus': 'Buat Menu',
        'edit_menus': 'Edit Menu',
        'delete_menus': 'Hapus Menu',
        'reorder_menus': 'Atur Urutan Menu',
    };
    return displayNames[permissionName] || permissionName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const selectedModule = ref('all');
const searchPermission = ref('');

// Filter permission berdasarkan module dan pencarian text
const displayPermissions = computed(() => {
    const result = {};
    const query = searchPermission.value.trim().toLowerCase();

    Object.entries(props.permissions).forEach(([module, modulePermissions]) => {
        if (selectedModule.value !== 'all' && selectedModule.value !== module) {
            return;
        }

        const filtered = modulePermissions.filter((p) => {
            if (!query) {
                return true;
            }

            const nameMatch = getPermissionDisplayName(p.name).toLowerCase().includes(query);
            const descMatch = getPermissionDescription(p.name).toLowerCase().includes(query);
            const rawMatch = p.name.toLowerCase().includes(query);

            return nameMatch || descMatch || rawMatch;
        });

        if (filtered.length > 0) {
            result[module] = filtered;
        }
    });

    return result;
});

const toggleModulePermissions = (modulePermissions) => {
    const allSelected = modulePermissions.every(p =>
        userPermissions.value.includes(p.name)
    );

    if (allSelected) {
        userPermissions.value = userPermissions.value.filter(
            p => !modulePermissions.some(mp => mp.name === p)
        );
    } else {
        modulePermissions.forEach(p => {
            if (!userPermissions.value.includes(p.name)) {
                userPermissions.value.push(p.name);
            }
        });
    }
};

const isModuleAllSelected = (modulePermissions) => {
    if (!modulePermissions || modulePermissions.length === 0) return false;
    return modulePermissions.every(p => userPermissions.value.includes(p.name));
};

const getPermissionDescription = (permissionName) => {
    const descriptions = {
        'view_users': 'Dapat melihat daftar user',
        'create_users': 'Dapat membuat user baru',
        'edit_users': 'Dapat mengedit data user',
        'delete_users': 'Dapat menghapus user',
        'manage_user_permissions': 'Dapat memberikan/mencabut hak akses user',
        'view_menus': 'Dapat melihat daftar menu',
        'create_menus': 'Dapat membuat menu baru',
        'edit_menus': 'Dapat mengedit menu',
        'delete_menus': 'Dapat menghapus menu',
        'reorder_menus': 'Dapat mengubah urutan menu',
    };
    return descriptions[permissionName] || '';
};
</script>
<template>
    <Head title="Manajemen Hak Akses" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        Manajemen Hak Akses
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Kelola hak akses user secara realtime dan responsive.
                    </p>
                </div>

                <div
                    v-if="selectedUser"
                    class="bg-white border border-slate-200 rounded-2xl px-4 py-3 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center font-bold text-lg"
                        >
                            {{ selectedUser.name.charAt(0).toUpperCase() }}
                        </div>

                        <div>
                            <p class="font-semibold text-slate-800">
                                {{ selectedUser.name }}
                            </p>

                            <p class="text-sm text-slate-500">
                                {{ userPermissions.length }} Hak Akses
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 md:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

                    <!-- USER LIST -->
                    <div class="xl:col-span-1">

                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

                            <div class="p-5 border-b border-slate-100">
                                <h3 class="font-bold text-slate-800">
                                    Daftar User
                                </h3>

                                <p class="text-sm text-slate-500 mt-1 mb-4">
                                    Pilih user untuk mengatur permission.
                                </p>

                                <div class="relative">
                                    <input
                                        v-model="searchUserQuery"
                                        type="text"
                                        placeholder="Cari nama atau email..."
                                        class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-shadow"
                                    />
                                    <div class="absolute left-3 top-2.5 text-slate-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z" />
                                        </svg>
                                    </div>
                                </div>

                                <p v-if="filteredUsers.length === 50 && usersList.length > 50" class="text-xs text-amber-600 mt-3 font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Menampilkan 50 data teratas. Gunakan pencarian untuk hasil spesifik.
                                </p>
                            </div>

                            <div class="max-h-[700px] overflow-y-auto p-3 space-y-3">

                                <div v-if="filteredUsers.length === 0" class="text-center py-8 text-sm text-slate-500">
                                    User tidak ditemukan.
                                </div>

                                <button
                                    v-for="user in filteredUsers"
                                    :key="user.id"
                                    @click="selectUser(user)"
                                    class="w-full text-left rounded-2xl border transition-all duration-200 p-4"
                                    :class="
                                        selectedUser?.id === user.id
                                            ? 'bg-indigo-600 border-indigo-600 shadow-lg shadow-indigo-100'
                                            : 'bg-white border-slate-200 hover:border-indigo-300 hover:bg-slate-50'
                                    "
                                >
                                    <div class="flex items-center gap-3">

                                        <div
                                            class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold"
                                            :class="
                                                selectedUser?.id === user.id
                                                    ? 'bg-white/20 text-white'
                                                    : 'bg-indigo-100 text-indigo-700'
                                            "
                                        >
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>

                                        <div class="flex-1 min-w-0">

                                            <p
                                                class="font-semibold truncate"
                                                :class="
                                                    selectedUser?.id === user.id
                                                        ? 'text-white'
                                                        : 'text-slate-800'
                                                "
                                            >
                                                {{ user.name }}
                                            </p>

                                            <p
                                                class="text-sm truncate"
                                                :class="
                                                    selectedUser?.id === user.id
                                                        ? 'text-indigo-100'
                                                        : 'text-slate-500'
                                                "
                                            >
                                                {{ user.email }}
                                            </p>

                                            <div
                                                class="inline-flex items-center mt-2 px-2 py-1 rounded-lg text-xs font-medium"
                                                :class="
                                                    selectedUser?.id === user.id
                                                        ? 'bg-white/20 text-white'
                                                        : 'bg-slate-100 text-slate-600'
                                                "
                                            >
                                                {{ user.permissions.length }} Permission
                                            </div>

                                        </div>
                                    </div>
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- PERMISSION -->
                    <div class="xl:col-span-3">

                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

                            <div class="p-5 border-b border-slate-100">

                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg">
                                            Hak Akses User
                                        </h3>

                                        <p class="text-sm text-slate-500 mt-1">
                                            Aktifkan atau nonaktifkan permission.
                                        </p>
                                    </div>

                                    <button
                                        v-if="selectedUser"
                                        @click="savePermissions"
                                        :disabled="savingPermissions"
                                        class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-400 text-white px-5 py-3 font-semibold transition-all shadow-lg shadow-indigo-100"
                                    >
                                        <svg
                                            v-if="savingPermissions"
                                            class="animate-spin w-4 h-4 mr-2"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            />

                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                            />
                                        </svg>

                                        {{
                                            savingPermissions
                                                ? 'Menyimpan...'
                                                : 'Simpan Perubahan'
                                        }}
                                    </button>

                                </div>
                            </div>

                            <div
                                v-if="selectedUser"
                                class="p-5 space-y-6"
                            >

                                <!-- Toolbar Filter & Search Permissions -->
                                <div class="flex flex-col sm:flex-row gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                                    <div class="flex-1 relative">
                                        <input
                                            v-model="searchPermission"
                                            type="text"
                                            placeholder="Cari hak akses atau menu..."
                                            class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white"
                                        />
                                        <div class="absolute left-3 top-2.5 text-slate-400">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z" /></svg>
                                        </div>
                                    </div>
                                    <div class="sm:w-56">
                                        <select
                                            v-model="selectedModule"
                                            class="w-full border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white"
                                        >
                                            <option value="all">-- Semua Modul --</option>
                                            <option v-for="mod in Object.keys(permissions)" :key="mod" :value="mod">
                                                Modul: {{ mod || 'General' }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div v-if="Object.keys(displayPermissions).length === 0" class="text-center py-10 text-slate-500">
                                    Tidak ada hak akses yang cocok dengan pencarian Anda.
                                </div>

                                <div
                                    v-for="(modulePermissions, moduleName) in displayPermissions"
                                    :key="'display-'+moduleName"
                                    class="border border-slate-200 rounded-3xl overflow-hidden shadow-sm"
                                >

                                    <div class="bg-slate-50 border-b border-slate-100 px-5 py-4 flex justify-between items-center">
                                        <h4 class="font-bold text-slate-800 capitalize">
                                            {{ moduleName || 'General' }}
                                        </h4>
                                        <button
                                            @click="toggleModulePermissions(modulePermissions)"
                                            class="text-xs font-bold px-3 py-1.5 rounded-lg transition-colors"
                                            :class="isModuleAllSelected(modulePermissions) ? 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100'"
                                        >
                                            {{ isModuleAllSelected(modulePermissions) ? 'Batalkan Semua' : 'Pilih Semua' }}
                                        </button>
                                    </div>

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 p-5"
                                    >

                                        <label
                                            v-for="permission in modulePermissions"
                                            :key="permission.id"
                                            class="group border rounded-2xl p-4 cursor-pointer transition-all duration-200"
                                            :class="
                                                hasPermission(permission.name)
                                                    ? 'border-indigo-500 bg-indigo-50'
                                                    : 'border-slate-200 hover:border-indigo-300 hover:bg-slate-50'
                                            "
                                        >

                                            <div class="flex items-start gap-3">

                                                <input
                                                    type="checkbox"
                                                    :checked="hasPermission(permission.name)"
                                                    @change="togglePermission(permission.name)"
                                                    class="mt-1 w-5 h-5 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                                />

                                                <div class="flex-1 min-w-0">

                                                    <p class="font-semibold text-slate-800">
                                                        {{ getPermissionDisplayName(permission.name) }}
                                                    </p>

                                                    <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                                                        {{ getPermissionDescription(permission.name) }}
                                                    </p>

                                                </div>
                                            </div>
                                        </label>

                                    </div>
                                </div>

                            </div>

                            <div
                                v-else
                                class="flex flex-col items-center justify-center py-24 px-6"
                            >
                                <div
                                    class="w-24 h-24 rounded-full bg-slate-100 flex items-center justify-center mb-6"
                                >
                                    <svg
                                        class="w-10 h-10 text-slate-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M12 15v2m6-6V7a6 6 0 10-12 0v4"
                                        />
                                    </svg>
                                </div>

                                <h3 class="text-xl font-bold text-slate-700">
                                    Pilih User
                                </h3>

                                <p class="text-slate-500 mt-2 text-center max-w-md">
                                    Silakan pilih user terlebih dahulu untuk mulai
                                    mengatur hak akses dan permission.
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>