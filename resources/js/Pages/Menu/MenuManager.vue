<script setup>
import { computed, ref, watch } from 'vue';
import draggable from 'vuedraggable';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    menus: {
        type: Array,
        default: () => [],
    },
    permissions: {
        type: Array,
        default: () => [],
    },
    routeOptions: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const list = ref([]);
const isProcessing = ref(false);
const showModal = ref(false);
const editingMenu = ref(null);
const formData = ref({
    name: '',
    route: '',
    icon: '',
    parent_id: null,
    permission_name: null,
});

const searchQuery = ref('');
const expandedParentIds = ref(new Set());
const selectedRoute = ref('');

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const formErrors = computed(() => page.props.errors ?? {});
const routeOptions = computed(() => props.routeOptions ?? []);
const routeOptionsByName = computed(() =>
    Object.fromEntries(routeOptions.value.map((routeOption) => [routeOption.name, routeOption]))
);
const routeModules = computed(() => [...new Set(routeOptions.value.map((item) => item.module))]);

const displayList = computed({
    get() {
        const query = searchQuery.value.trim().toLowerCase();
        if (!query) {
            return list.value;
        }

        return list.value.filter((parent) => {
            const parentText = [parent.name, parent.route, parent.permission_name]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            const childText = (parent.children || [])
                .map((child) => [child.name, child.route, child.permission_name].filter(Boolean).join(' '))
                .join(' ')
                .toLowerCase();

            return parentText.includes(query) || childText.includes(query);
        });
    },
    set(newValue) {
        if (!searchQuery.value.trim()) {
            list.value = newValue;
            return;
        }

        const visibleIds = new Set(newValue.map((item) => item.id));
        const hiddenItems = list.value.filter((item) => !visibleIds.has(item.id));
        list.value = [...newValue, ...hiddenItems];
    },
});

const assignedRouteNames = computed(() => {
    const names = [];

    list.value.forEach((menu) => {
        if (menu.route) {
            names.push(menu.route);
        }

        (menu.children || []).forEach((child) => {
            if (child.route) {
                names.push(child.route);
            }
        });
    });

    return new Set(names);
});

const unassignedRoutes = computed(() =>
    routeOptions.value.filter((routeOption) => !assignedRouteNames.value.has(routeOption.name))
);

watch(
    () => props.menus,
    (newMenus) => {
        if (isProcessing.value) {
            return;
        }

        const sanitizedMenus = (newMenus || []).map((item) => ({
            ...item,
            children: [...(item.children || [])].sort((a, b) => a.order - b.order),
        }));

        list.value = [...sanitizedMenus].sort((a, b) => a.order - b.order);
    },
    { immediate: true }
);

watch(selectedRoute, (routeName) => {
    formData.value.route = routeName || '';

    const routeOption = routeOptionsByName.value[routeName];
    if (!routeOption) {
        formData.value.permission_name = null;
        return;
    }

    if (routeOption.required_permission) {
        formData.value.permission_name = routeOption.required_permission;
    } else if (!editingMenu.value || editingMenu.value.route !== routeName) {
        formData.value.permission_name = null;
    }

    if (!formData.value.name.trim()) {
        formData.value.name = routeOption.label;
    }
});

const currentRouteRequirement = computed(() => routeOptionsByName.value[selectedRoute.value] ?? null);
const permissionLockedByRoute = computed(() => Boolean(currentRouteRequirement.value?.required_permission));

const toggleParent = (id) => {
    if (expandedParentIds.value.has(id)) {
        expandedParentIds.value.delete(id);
        return;
    }

    expandedParentIds.value.add(id);
};

const isExpanded = (id) => expandedParentIds.value.has(id);
const expandAll = () => list.value.forEach((parent) => expandedParentIds.value.add(parent.id));
const collapseAll = () => expandedParentIds.value.clear();

const resetForm = () => {
    formData.value = {
        name: '',
        route: '',
        icon: '',
        parent_id: null,
        permission_name: null,
    };
    selectedRoute.value = '';
};

const openAddModal = () => {
    editingMenu.value = null;
    resetForm();
    showModal.value = true;
};

const openEditModal = (menu) => {
    editingMenu.value = menu;
    formData.value = {
        name: menu.name ?? '',
        route: menu.route ?? '',
        icon: menu.icon ?? '',
        parent_id: menu.parent_id ?? null,
        permission_name: menu.permission_name ?? null,
    };
    selectedRoute.value = menu.route ?? '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingMenu.value = null;
    resetForm();
};

const submitForm = () => {
    if (!formData.value.name.trim()) {
        return;
    }

    const payload = {
        ...formData.value,
        route: formData.value.route || null,
        permission_name: formData.value.permission_name || null,
    };

    if (editingMenu.value) {
        router.put(route('menus.update', editingMenu.value.id), payload, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
        return;
    }

    router.post(route('menus.store'), payload, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const deleteMenu = (id) => {
    if (!confirm('Apakah Anda yakin ingin menghapus menu ini?')) {
        return;
    }

    router.delete(route('menus.destroy', id), {
        preserveScroll: true,
    });
};

const onDragEnd = () => {
    isProcessing.value = true;

    const payload = [];

    list.value.forEach((parent, parentIndex) => {
        parent.order = parentIndex + 1;
        payload.push({ id: parent.id, order: parent.order });

        (parent.children || []).forEach((child, childIndex) => {
            child.order = childIndex + 1;
            payload.push({ id: child.id, order: child.order });
        });
    });

    router.put(route('menus.reorder'), {
        menus: payload,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['menus', 'flash', 'errors'],
        onFinish: () => {
            isProcessing.value = false;
        },
        onError: () => {
            router.reload();
        },
    });
};
</script>

<template>
    <Head title="Manajemen Menu" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-bold leading-tight text-gray-800">Manajemen Menu</h2>
                    <span v-if="isProcessing" class="flex items-center text-sm font-medium text-indigo-600">
                        <svg class="-ml-1 mr-2 h-4 w-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Menyimpan urutan...
                    </span>
                </div>

                <button
                    @click="openAddModal"
                    class="rounded-lg bg-indigo-600 px-4 py-2 font-bold text-white shadow-sm transition-colors hover:bg-indigo-700"
                >
                    + Tambah Menu
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl px-4">
                <div v-if="flashSuccess" class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ flashSuccess }}
                </div>

                <div v-if="flashError" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ flashError }}
                </div>

                <div class="mb-6 grid gap-4 lg:grid-cols-[2fr_1fr]">
                    <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900">Sinkronisasi Route ke Menu</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Menu sekarang wajib mengacu ke route aktif. Jika route punya permission, permission menu akan ikut dikunci agar user, menu, dan route tetap sinkron.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-amber-100 bg-amber-50 p-6 shadow-sm">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-amber-800">Route Belum Ada Menu</h3>
                        <p class="mt-2 text-3xl font-bold text-amber-900">{{ unassignedRoutes.length }}</p>
                        <p class="mt-1 text-sm text-amber-700">Cek daftar ini supaya navigasi tidak tertinggal dari route aktif.</p>
                    </div>
                </div>

                <div v-if="unassignedRoutes.length > 0" class="mb-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700">Belum Masuk Sidebar</h4>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            v-for="routeOption in unassignedRoutes"
                            :key="routeOption.name"
                            class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-700"
                        >
                            {{ routeOption.label }} · {{ routeOption.name }}
                        </span>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-50 bg-gray-50/30 p-6">
                        <h3 class="text-lg font-bold text-gray-900">Atur Urutan Menu & Sub-menu</h3>
                        <p class="text-sm text-gray-500">Geser menu untuk mengatur urutan. Gunakan route aktif agar sidebar tetap sinkron dengan akses user.</p>
                    </div>

                    <div class="p-6">
                        <div class="mb-4 grid items-center gap-4 md:grid-cols-[1fr_auto]">
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 pr-12 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                    placeholder="Cari menu, route, atau hak akses..."
                                />
                                <div class="absolute inset-y-0 right-4 flex items-center text-gray-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="expandAll"
                                    class="rounded-full border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:border-indigo-300 hover:text-indigo-700"
                                >
                                    Buka Semua
                                </button>
                                <button
                                    @click="collapseAll"
                                    class="rounded-full border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:border-indigo-300 hover:text-indigo-700"
                                >
                                    Tutup Semua
                                </button>
                            </div>
                        </div>

                        <div class="mb-4 text-sm text-slate-500">
                            Menampilkan <span class="font-semibold text-slate-800">{{ displayList.length }}</span> dari <span class="font-semibold text-slate-800">{{ list.length }}</span> menu.
                        </div>

                        <draggable
                            v-model="displayList"
                            item-key="id"
                            tag="div"
                            class="space-y-3"
                            handle=".parent-handle"
                            :animation="300"
                            :disabled="isProcessing"
                            @end="onDragEnd"
                            ghost-class="sortable-ghost"
                            drag-class="sortable-drag"
                        >
                            <template #item="{ element: parent, index: parentIndex }">
                                <div class="select-none overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all">
                                    <div class="flex items-center gap-3 bg-gray-50/50 p-4 transition-colors hover:bg-gray-50">
                                        <button
                                            v-if="parent.children && parent.children.length > 0"
                                            @click.stop="toggleParent(parent.id)"
                                            class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:border-indigo-300 hover:text-indigo-700"
                                            :aria-expanded="isExpanded(parent.id)"
                                            :title="isExpanded(parent.id) ? 'Tutup submenu' : 'Buka submenu'"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>

                                        <div class="parent-handle cursor-grab p-2 text-gray-400 active:cursor-grabbing hover:text-indigo-600">
                                            <svg class="pointer-events-none h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-gray-900">{{ parent.name }}</p>
                                            <p class="font-mono text-xs text-gray-400">{{ parent.route || 'Parent Menu (Tanpa Route)' }}</p>
                                            <p v-if="parent.permission_name" class="mt-1 text-[10px] font-semibold uppercase tracking-widest text-indigo-600">
                                                Hak akses: {{ parent.permission_name.replace(/_/g, ' ').toUpperCase() }}
                                            </p>
                                        </div>

                                        <div class="mr-2 flex h-7 w-7 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white">
                                            {{ parentIndex + 1 }}
                                        </div>

                                        <button @click="openEditModal(parent)" class="rounded-md p-1.5 text-blue-600 hover:bg-blue-100" title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-4l7-7m0 0l-7 7" /></svg>
                                        </button>
                                        <button @click="deleteMenu(parent.id)" class="rounded-md p-1.5 text-red-600 hover:bg-red-100" title="Hapus">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>

                                    <div v-if="parent.children && parent.children.length > 0 && isExpanded(parent.id)" class="border-t border-gray-100 bg-white p-3 pl-12">
                                        <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">Sub Menu:</p>

                                        <draggable
                                            v-model="parent.children"
                                            item-key="id"
                                            tag="div"
                                            class="space-y-2"
                                            handle=".child-handle"
                                            :animation="200"
                                            :disabled="isProcessing"
                                            @end="onDragEnd"
                                            ghost-class="sortable-ghost-child"
                                        >
                                            <template #item="{ element: child, index: childIndex }">
                                                <div class="flex items-center rounded-xl border border-gray-100 bg-gray-50/30 p-3 transition-all hover:border-indigo-200">
                                                    <div class="child-handle mr-3 cursor-grab p-1 text-gray-300 active:cursor-grabbing hover:text-indigo-500">
                                                        <svg class="pointer-events-none h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                        </svg>
                                                    </div>

                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-800">{{ child.name }}</p>
                                                        <p class="font-mono text-[11px] text-gray-400">{{ child.route || 'No Route' }}</p>
                                                        <p v-if="child.permission_name" class="mt-1 text-[10px] font-semibold uppercase tracking-widest text-indigo-600">
                                                            Hak akses: {{ child.permission_name.replace(/_/g, ' ').toUpperCase() }}
                                                        </p>
                                                    </div>

                                                    <span class="mr-2 rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-600">
                                                        {{ parentIndex + 1 }}.{{ childIndex + 1 }}
                                                    </span>
                                                    <button @click="openEditModal(child)" class="rounded p-1 text-blue-600 hover:bg-blue-50" title="Edit">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-4l7-7m0 0l-7 7" /></svg>
                                                    </button>
                                                    <button @click="deleteMenu(child.id)" class="rounded p-1 text-red-600 hover:bg-red-50" title="Hapus">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </draggable>
                                    </div>
                                </div>
                            </template>
                        </draggable>

                        <div v-if="list.length === 0" class="py-12 text-center">
                            <p class="font-medium text-gray-500">Belum ada data menu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-2xl overflow-hidden rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-bold text-gray-900">
                    {{ editingMenu ? 'Edit Menu' : 'Tambah Menu Baru' }}
                </h2>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nama Menu</label>
                        <input v-model="formData.name" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-600" placeholder="Contoh: Dashboard" />
                        <p v-if="formErrors.name" class="mt-1 text-sm text-red-600">{{ formErrors.name }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Route Aktif</label>
                        <select v-model="selectedRoute" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:ring-2 focus:ring-indigo-600">
                            <option value="">-- Tidak ada route --</option>
                            <optgroup v-for="module in routeModules" :key="module" :label="module.replace(/_/g, ' ').toUpperCase()">
                                <option v-for="routeOption in routeOptions.filter((item) => item.module === module)" :key="routeOption.name" :value="routeOption.name">
                                    {{ routeOption.label }} · {{ routeOption.name }}
                                </option>
                            </optgroup>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Pilih dari route aktif supaya menu tidak mengarah ke halaman yang salah atau sudah tidak ada.</p>
                        <p v-if="formErrors.route" class="mt-1 text-sm text-red-600">{{ formErrors.route }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Icon</label>
                        <input v-model="formData.icon" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-600" placeholder="Contoh: home" />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Parent Menu</label>
                        <select v-model="formData.parent_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2">
                            <option :value="null">-- Tidak ada (Menu Utama) --</option>
                            <option v-for="menu in list.filter((item) => item.id !== editingMenu?.id)" :key="menu.id" :value="menu.id">
                                {{ menu.name }}
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Permission Menu</label>
                        <select
                            v-model="formData.permission_name"
                            :disabled="permissionLockedByRoute"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:ring-2 focus:ring-indigo-600 disabled:bg-slate-100"
                        >
                            <option :value="null">-- Bebas Diakses Semua User --</option>
                            <option v-for="permission in permissions" :key="permission.id" :value="permission.name">
                                {{ permission.name.replace(/_/g, ' ').toUpperCase() }}
                            </option>
                        </select>
                        <p v-if="currentRouteRequirement?.required_permission" class="mt-1 text-xs text-indigo-600">
                            Route ini mewajibkan permission <strong>{{ currentRouteRequirement.required_permission }}</strong>, jadi permission menu dikunci agar tetap sinkron.
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500">
                            Jika route tidak punya middleware permission, Anda boleh membiarkan menu terbuka atau memberi batas tambahan.
                        </p>
                        <p v-if="formErrors.permission_name" class="mt-1 text-sm text-red-600">{{ formErrors.permission_name }}</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="closeModal" class="rounded-lg px-4 py-2 text-gray-700 hover:bg-gray-100">Batal</button>
                    <button @click="submitForm" class="rounded-lg bg-indigo-600 px-4 py-2 text-white">
                        {{ editingMenu ? 'Simpan' : 'Tambah' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.parent-handle,
.child-handle {
    touch-action: none;
}

.sortable-ghost {
    opacity: 0.5;
    background: #f1f5f9 !important;
    border: 2px dashed #94a3b8 !important;
}

.sortable-ghost-child {
    opacity: 0.4;
    background: #eef2ff !important;
    border: 2px dashed #6366f1 !important;
}

.sortable-drag {
    opacity: 1 !important;
    cursor: grabbing;
    transform: scale(1.01);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    z-index: 50;
}
</style>
