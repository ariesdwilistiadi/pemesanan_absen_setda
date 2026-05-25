<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    currentUserId: {
        type: Number,
        default: null,
    },
    users: {
        type: Array,
        default: () => [],
    },
    permissions: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const selectedUser = ref(null);
const userPermissions = ref([]);
const savingPermissions = ref(false);
const searchUserQuery = ref('');
const selectedModule = ref('all');
const searchPermission = ref('');

const usersList = computed(() => props.users ?? []);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const filteredUsers = computed(() => {
    let result = usersList.value;

    if (searchUserQuery.value) {
        const query = searchUserQuery.value.toLowerCase();
        result = result.filter((user) =>
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query)
        );
    }

    return result.slice(0, 50);
});

const selectedUserRecord = computed(() => {
    if (!selectedUser.value) {
        return null;
    }

    return usersList.value.find((user) => user.id === selectedUser.value.id) ?? null;
});

const selectedUserIsProtected = computed(() => Boolean(selectedUserRecord.value?.is_protected_account));

watch(selectedUser, (newUser) => {
    userPermissions.value = newUser ? (newUser.permissions || []).map((permission) => permission.name) : [];
}, { immediate: true });

watch(usersList, (newUsers) => {
    if (!selectedUser.value) {
        return;
    }

    const freshUser = newUsers.find((user) => user.id === selectedUser.value.id);
    if (freshUser) {
        selectedUser.value = freshUser;
        userPermissions.value = (freshUser.permissions || []).map((permission) => permission.name);
    }
}, { deep: true });

const selectUser = (user) => {
    selectedUser.value = user;
};

const togglePermission = (permissionName) => {
    if (selectedUserIsProtected.value) {
        return;
    }

    const index = userPermissions.value.indexOf(permissionName);

    if (index > -1) {
        userPermissions.value.splice(index, 1);
    } else {
        userPermissions.value.push(permissionName);
    }
};

const savePermissions = () => {
    if (!selectedUser.value || selectedUserIsProtected.value) {
        return;
    }

    savingPermissions.value = true;

    router.put(route('permissions.update-user', selectedUser.value.id), {
        permissions: userPermissions.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            savingPermissions.value = false;
        },
    });
};

const hasPermission = (permissionName) => userPermissions.value.includes(permissionName);

const displayNames = {
    view_users: 'Lihat User',
    create_users: 'Buat User',
    edit_users: 'Edit User',
    delete_users: 'Hapus User',
    manage_user_permissions: 'Kelola Hak Akses User',
    view_menus: 'Lihat Menu',
    create_menus: 'Buat Menu',
    edit_menus: 'Edit Menu',
    delete_menus: 'Hapus Menu',
    reorder_menus: 'Atur Urutan Menu',
    view_anggotas: 'Lihat Anggota',
    create_anggotas: 'Buat Anggota',
    edit_anggotas: 'Edit Anggota',
    delete_anggotas: 'Hapus Anggota',
    view_produks: 'Lihat Produk',
    create_produks: 'Buat Produk',
    edit_produks: 'Edit Produk',
    delete_produks: 'Hapus Produk',
    view_kasir: 'Lihat Kasir',
    create_kasir: 'Buat Transaksi Kasir',
    edit_kasir: 'Ubah Status Kasir',
    delete_kasir: 'Hapus Transaksi Kasir',
    view_rapat: 'Lihat Rapat',
    create_rapat: 'Buat Rapat',
    edit_rapat: 'Edit Rapat',
    view_dana_dkks: 'Lihat Dana DKK',
    create_dana_dkks: 'Buat Dana DKK',
    view_pinjaman: 'Lihat Pinjaman',
    create_pinjaman: 'Buat Pinjaman',
    view_pinjaman_bayar: 'Lihat Pembayaran Pinjaman',
    create_pinjaman_bayar: 'Buat Pembayaran Pinjaman',
};

const descriptions = {
    view_users: 'Dapat melihat daftar user.',
    create_users: 'Dapat membuat user baru.',
    edit_users: 'Dapat mengubah data user.',
    delete_users: 'Dapat menghapus user.',
    manage_user_permissions: 'Dapat memberi dan mencabut hak akses user lain.',
    view_menus: 'Dapat melihat daftar menu.',
    create_menus: 'Dapat membuat menu baru.',
    edit_menus: 'Dapat mengubah menu.',
    delete_menus: 'Dapat menghapus menu.',
    reorder_menus: 'Dapat mengubah urutan menu.',
    view_anggotas: 'Dapat melihat data anggota.',
    create_anggotas: 'Dapat membuat data anggota baru.',
    edit_anggotas: 'Dapat mengubah data anggota.',
    delete_anggotas: 'Dapat menghapus data anggota.',
    view_produks: 'Dapat melihat daftar produk.',
    create_produks: 'Dapat menambah produk baru.',
    edit_produks: 'Dapat mengubah data produk.',
    delete_produks: 'Dapat menghapus produk.',
    view_kasir: 'Dapat membuka modul kasir dan pesanan.',
    create_kasir: 'Dapat membuat transaksi kasir.',
    edit_kasir: 'Dapat mengubah status transaksi kasir.',
    delete_kasir: 'Dapat menghapus transaksi kasir.',
    view_rapat: 'Dapat melihat daftar dan detail rapat.',
    create_rapat: 'Dapat membuat rapat dan input kehadiran dari admin.',
    edit_rapat: 'Dapat mengubah data rapat.',
    view_dana_dkks: 'Dapat melihat data dana DKK.',
    create_dana_dkks: 'Dapat membuat data dana DKK.',
    view_pinjaman: 'Dapat melihat daftar pinjaman.',
    create_pinjaman: 'Dapat membuat pinjaman baru.',
    view_pinjaman_bayar: 'Dapat melihat pembayaran pinjaman.',
    create_pinjaman_bayar: 'Dapat membuat pembayaran pinjaman.',
};

const getPermissionDisplayName = (permissionName) =>
    displayNames[permissionName] || permissionName.replace(/_/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

const getPermissionDescription = (permissionName) => descriptions[permissionName] || 'Hak akses khusus untuk modul ini.';

const getPermissionRouteUsage = (permission) => permission.used_by_routes || [];

const displayPermissions = computed(() => {
    const query = searchPermission.value.trim().toLowerCase();

    return props.permissions
        .filter((group) => selectedModule.value === 'all' || selectedModule.value === group.key)
        .map((group) => ({
            ...group,
            permissions: group.permissions.filter((permission) => {
                if (!query) {
                    return true;
                }

                return (
                    permission.name.toLowerCase().includes(query) ||
                    getPermissionDisplayName(permission.name).toLowerCase().includes(query) ||
                    getPermissionDescription(permission.name).toLowerCase().includes(query)
                );
            }),
        }))
        .filter((group) => group.permissions.length > 0);
});

const toggleModulePermissions = (modulePermissions) => {
    if (selectedUserIsProtected.value) {
        return;
    }

    const allSelected = modulePermissions.every((permission) => userPermissions.value.includes(permission.name));

    if (allSelected) {
        userPermissions.value = userPermissions.value.filter(
            (permissionName) => !modulePermissions.some((permission) => permission.name === permissionName)
        );
        return;
    }

    modulePermissions.forEach((permission) => {
        if (!userPermissions.value.includes(permission.name)) {
            userPermissions.value.push(permission.name);
        }
    });
};

const isModuleAllSelected = (modulePermissions) =>
    modulePermissions.length > 0 && modulePermissions.every((permission) => userPermissions.value.includes(permission.name));
</script>

<template>
    <Head title="Manajemen Hak Akses" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Manajemen Hak Akses</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Kelola permission user dengan grouping modul yang lebih jelas.
                    </p>
                </div>

                <div v-if="selectedUserRecord" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-lg font-bold text-white">
                            {{ selectedUserRecord.name.charAt(0).toUpperCase() }}
                        </div>

                        <div>
                            <p class="font-semibold text-slate-800">{{ selectedUserRecord.name }}</p>
                            <p class="text-sm text-slate-500">{{ userPermissions.length }} Hak Akses</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 md:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="flashSuccess" class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ flashSuccess }}
                </div>

                <div v-if="flashError" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ flashError }}
                </div>

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-4">
                    <div class="xl:col-span-1">
                        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 p-5">
                                <h3 class="font-bold text-slate-800">Daftar User</h3>
                                <p class="mb-4 mt-1 text-sm text-slate-500">Pilih user untuk mengatur permission.</p>

                                <div class="relative">
                                    <input
                                        v-model="searchUserQuery"
                                        type="text"
                                        placeholder="Cari nama atau email..."
                                        class="w-full rounded-xl border border-slate-200 py-2 pl-10 pr-4 text-sm transition-shadow focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                    />
                                    <div class="absolute left-3 top-2.5 text-slate-400">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="max-h-[700px] space-y-3 overflow-y-auto p-3">
                                <div v-if="filteredUsers.length === 0" class="py-8 text-center text-sm text-slate-500">
                                    User tidak ditemukan.
                                </div>

                                <button
                                    v-for="user in filteredUsers"
                                    :key="user.id"
                                    @click="selectUser(user)"
                                    class="w-full rounded-2xl border p-4 text-left transition-all duration-200"
                                    :class="selectedUserRecord?.id === user.id ? 'border-indigo-600 bg-indigo-600 shadow-lg shadow-indigo-100' : 'border-slate-200 bg-white hover:border-indigo-300 hover:bg-slate-50'"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl font-bold"
                                            :class="selectedUserRecord?.id === user.id ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-700'"
                                        >
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="truncate font-semibold" :class="selectedUserRecord?.id === user.id ? 'text-white' : 'text-slate-800'">
                                                {{ user.name }}
                                            </p>
                                            <p class="truncate text-sm" :class="selectedUserRecord?.id === user.id ? 'text-indigo-100' : 'text-slate-500'">
                                                {{ user.email }}
                                            </p>

                                            <div class="mt-2 inline-flex items-center rounded-lg px-2 py-1 text-xs font-medium" :class="selectedUserRecord?.id === user.id ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                                                {{ user.permissions.length }} Permission
                                            </div>

                                            <div
                                                v-if="user.is_protected_account"
                                                class="ml-2 mt-2 inline-flex items-center rounded-lg px-2 py-1 text-xs font-medium"
                                                :class="selectedUserRecord?.id === user.id ? 'bg-amber-400/20 text-amber-50' : 'bg-amber-100 text-amber-700'"
                                            >
                                                Protected
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="xl:col-span-3">
                        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 p-5">
                                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-800">Hak Akses User</h3>
                                        <p class="mt-1 text-sm text-slate-500">Aktifkan atau nonaktifkan permission yang relevan.</p>
                                        <p v-if="selectedUserIsProtected" class="mt-2 text-xs font-medium text-amber-700">
                                            Akun ini adalah akun sistem atau super admin. Permission checklist dikunci agar tidak menyesatkan.
                                        </p>
                                    </div>

                                    <button
                                        v-if="selectedUserRecord"
                                        @click="savePermissions"
                                        :disabled="savingPermissions || selectedUserIsProtected"
                                        class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 font-semibold text-white shadow-lg shadow-indigo-100 transition-all hover:bg-indigo-700 disabled:bg-slate-400"
                                    >
                                        <svg v-if="savingPermissions" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                        </svg>
                                        {{ savingPermissions ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                    </button>
                                </div>
                            </div>

                            <div v-if="selectedUserRecord" class="space-y-6 p-5">
                                <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:flex-row">
                                    <div class="relative flex-1">
                                        <input
                                            v-model="searchPermission"
                                            type="text"
                                            placeholder="Cari hak akses atau menu..."
                                            class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                        />
                                        <div class="absolute left-3 top-2.5 text-slate-400">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="sm:w-56">
                                        <select
                                            v-model="selectedModule"
                                            class="w-full rounded-xl border-slate-200 bg-white text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                        >
                                            <option value="all">-- Semua Modul --</option>
                                            <option v-for="group in permissions" :key="group.key" :value="group.key">
                                                Modul: {{ group.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div v-if="displayPermissions.length === 0" class="py-10 text-center text-slate-500">
                                    Tidak ada hak akses yang cocok dengan pencarian Anda.
                                </div>

                                <div
                                    v-for="group in displayPermissions"
                                    :key="'display-' + group.key"
                                    class="overflow-hidden rounded-3xl border border-slate-200 shadow-sm"
                                >
                                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-4">
                                        <h4 class="font-bold text-slate-800">{{ group.label }}</h4>

                                        <button
                                            @click="toggleModulePermissions(group.permissions)"
                                            :disabled="selectedUserIsProtected"
                                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition-colors"
                                            :class="isModuleAllSelected(group.permissions) ? 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200 disabled:bg-slate-200 disabled:text-slate-500' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 disabled:bg-slate-200 disabled:text-slate-500'"
                                        >
                                            {{ isModuleAllSelected(group.permissions) ? 'Batalkan Semua' : 'Pilih Semua' }}
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                                        <label
                                            v-for="permission in group.permissions"
                                            :key="permission.id"
                                            class="group cursor-pointer rounded-2xl border p-4 transition-all duration-200"
                                            :class="hasPermission(permission.name) ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 hover:border-indigo-300 hover:bg-slate-50'"
                                        >
                                            <div class="flex items-start gap-3">
                                                <input
                                                    type="checkbox"
                                                    :checked="hasPermission(permission.name)"
                                                    :disabled="selectedUserIsProtected"
                                                    @change="togglePermission(permission.name)"
                                                    class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                />

                                                <div class="min-w-0 flex-1">
                                                    <p class="font-semibold text-slate-800">
                                                        {{ getPermissionDisplayName(permission.name) }}
                                                    </p>
                                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                                        {{ getPermissionDescription(permission.name) }}
                                                    </p>
                                                    <p v-if="getPermissionRouteUsage(permission).length > 0" class="mt-2 text-xs text-slate-400">
                                                        Dipakai di route: {{ getPermissionRouteUsage(permission).join(', ') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="flex flex-col items-center justify-center px-6 py-24">
                                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100">
                                    <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m6-6V7a6 6 0 10-12 0v4" />
                                    </svg>
                                </div>

                                <h3 class="text-xl font-bold text-slate-700">Pilih User</h3>
                                <p class="mt-2 max-w-md text-center text-slate-500">
                                    Silakan pilih user terlebih dahulu untuk mulai mengatur hak akses dan permission.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
