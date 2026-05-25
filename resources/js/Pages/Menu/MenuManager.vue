<script setup>
import { ref, watch, computed } from 'vue';
import draggable from 'vuedraggable';
import { router, Head } from '@inertiajs/vue3';
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
});

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

const displayList = computed({
    get() {
        const query = searchQuery.value.trim().toLowerCase();
        if (!query) {
            return list.value;
        }

        return list.value.filter(parent => {
            const parentText = [parent.name, parent.route, parent.permission_name]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            const childText = (parent.children || [])
                .map(child => [child.name, child.route, child.permission_name].filter(Boolean).join(' '))
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

        const visibleIds = new Set(newValue.map(item => item.id));
        const hiddenItems = list.value.filter(item => !visibleIds.has(item.id));
        list.value = [...newValue, ...hiddenItems];
    }
});

const toggleParent = (id) => {
    if (expandedParentIds.value.has(id)) {
        expandedParentIds.value.delete(id);
    } else {
        expandedParentIds.value.add(id);
    }
};

const isExpanded = (id) => expandedParentIds.value.has(id);

const expandAll = () => {
    list.value.forEach(parent => expandedParentIds.value.add(parent.id));
};

const collapseAll = () => {
    expandedParentIds.value.clear();
};

// Sinkronisasi data: Jangan timpa list lokal jika sedang dalam proses drag/update
watch(() => props.menus, (newMenus) => {
    if (isProcessing.value) return; 
    
    // Pastikan children diurutkan juga berdasarkan order
    const sanitizedMenus = (newMenus || []).map(item => ({
        ...item,
        children: [...(item.children || [])].sort((a, b) => a.order - b.order)
    }));
    
    list.value = [...sanitizedMenus].sort((a, b) => a.order - b.order);
}, { immediate: true });

const openAddModal = () => {
    editingMenu.value = null;
    formData.value = { name: '', route: '', icon: '', parent_id: null, permission_name: null };
    showModal.value = true;
};

const openEditModal = (menu) => {
    editingMenu.value = menu;
    formData.value = { ...menu };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingMenu.value = null;
    formData.value = { name: '', route: '', icon: '', parent_id: null, permission_name: null };
};

const submitForm = () => {
    if (!formData.value.name.trim()) {
        alert('Nama menu tidak boleh kosong!');
        return;
    }

    if (editingMenu.value) {
        router.put(route('menus.update', editingMenu.value.id), formData.value, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => alert('Gagal memperbarui menu: ' + JSON.stringify(errors))
        });
    } else {
        router.post(route('menus.store'), formData.value, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => alert('Gagal membuat menu: ' + JSON.stringify(errors))
        });
    }
};

const deleteMenu = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus menu ini?')) {
        router.delete(route('menus.destroy', id), {
            preserveScroll: true,
            onError: (errors) => alert('Gagal menghapus menu: ' + JSON.stringify(errors))
        });
    }
};

// Fungsi Drag untuk Menu Utama & Sub Menu
const onDragEnd = () => {
    isProcessing.value = true;

    let payload = [];

    // 1. Ekstrak urutan Menu Utama
    list.value.forEach((parent, parentIndex) => {
        parent.order = parentIndex + 1;
        payload.push({ id: parent.id, order: parent.order });

        // 2. Ekstrak urutan Sub-menu (jika ada)
        if (parent.children && parent.children.length > 0) {
            parent.children.forEach((child, childIndex) => {
                child.order = childIndex + 1;
                payload.push({ id: child.id, order: child.order });
            });
        }
    });

    // Kirim seluruh urutan (parent & child) ke backend sekaligus
    router.put(route('menus.reorder'), {
        menus: payload
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['menus'],
        onFinish: () => { isProcessing.value = false; },
        onSuccess: () => { console.log('Urutan berhasil diperbarui'); },
        onError: () => {
            alert('Gagal menyimpan urutan. Halaman akan dimuat ulang.');
            router.reload();
        }
    });
};
</script>

<template>
    <Head title="Manajemen Menu" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">Manajemen Menu</h2>
                    <span v-if="isProcessing" class="text-sm font-medium text-indigo-600 animate-pulse flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan urutan...
                    </span>
                </div>
                
                <button 
                    @click="openAddModal"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm"
                >
                    + Tambah Menu
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <h3 class="text-lg font-bold text-gray-900">Atur Urutan Menu & Sub-menu</h3>
                        <p class="text-sm text-gray-500">Geser ikon garis tiga pada menu utama atau sub-menu untuk mengatur posisinya masing-щим.</p>
                    </div>

                    <div class="p-6">
                        <div class="mb-4 grid gap-4 md:grid-cols-[1fr_auto] items-center">
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="w-full border border-gray-300 rounded-2xl px-4 py-3 pr-12 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                    placeholder="Cari menu, route, atau hak akses..."
                                />
                                <div class="absolute inset-y-0 right-4 flex items-center text-gray-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 justify-end">
                                <button
                                    @click="expandAll"
                                    class="px-4 py-2 rounded-full border border-slate-200 bg-white text-slate-700 hover:border-indigo-300 hover:text-indigo-700"
                                >
                                    Buka Semua
                                </button>
                                <button
                                    @click="collapseAll"
                                    class="px-4 py-2 rounded-full border border-slate-200 bg-white text-slate-700 hover:border-indigo-300 hover:text-indigo-700"
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
                                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden select-none transition-all">
                                    
                                    <div class="flex items-center p-4 bg-gray-50/50 hover:bg-gray-50 transition-colors gap-3">
                                        <button
                                            v-if="parent.children && parent.children.length > 0"
                                            @click.stop="toggleParent(parent.id)"
                                            class="flex items-center justify-center w-9 h-9 rounded-full border border-slate-200 text-slate-600 hover:border-indigo-300 hover:text-indigo-700"
                                            :aria-expanded="isExpanded(parent.id)"
                                            :title="isExpanded(parent.id) ? 'Tutup submenu' : 'Buka submenu'"
                                        >
                                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>

                                        <div class="parent-handle cursor-grab active:cursor-grabbing p-2 text-gray-400 hover:text-indigo-600">
                                            <svg class="w-5 h-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>

                                        <div class="flex-1 flex items-center gap-3 min-w-0">
                                            <div v-if="parent.icon" class="mr-2 p-2 bg-indigo-100 text-indigo-700 rounded-lg w-9 h-9 flex items-center justify-center font-bold">
                                                <span v-if="parent.icon.length < 5">{{ parent.icon }}</span>
                                                <i v-else :class="parent.icon"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900">{{ parent.name }}</p>
                                                <p class="text-xs text-gray-400 font-mono">{{ parent.route || 'Parent Menu (No Route)' }}</p>
                                                <p v-if="parent.permission_name" class="mt-1 text-[10px] uppercase tracking-widest text-indigo-600 font-semibold">
                                                    Hak akses: {{ parent.permission_name.replace(/_/g, ' ').toUpperCase() }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <div class="h-7 w-7 flex items-center justify-center bg-gray-900 text-white font-bold rounded-full text-xs mr-2">
                                                {{ parentIndex + 1 }}
                                            </div>
                                            <button @click="openEditModal(parent)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-md" title="Edit">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-4l7-7m0 0l-7 7" /></svg>
                                            </button>
                                            <button @click="deleteMenu(parent.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-md" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div v-if="parent.children && parent.children.length > 0 && isExpanded(parent.id)" class="p-3 bg-white border-t border-gray-100 pl-12">
                                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Sub Menu:</p>
                                        
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
                                                <div class="flex items-center p-3 bg-gray-50/30 border border-gray-100 rounded-xl hover:border-indigo-200 transition-all">
                                                    
                                                    <div class="child-handle cursor-grab active:cursor-grabbing p-1 mr-3 text-gray-300 hover:text-indigo-500">
                                                        <svg class="w-5 h-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                        </svg>
                                                    </div>

                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-800">{{ child.name }}</p>
                                                        <p class="text-[11px] text-gray-400 font-mono">{{ child.route || 'No Route' }}</p>
                                                        <p v-if="child.permission_name" class="text-[10px] uppercase tracking-widest text-indigo-600 font-semibold mt-1">
                                                            Hak akses: {{ child.permission_name.replace(/_/g, ' ').toUpperCase() }}
                                                        </p>
                                                    </div>

                                                    <div class="flex items-center space-x-1">
                                                        <span class="text-[10px] bg-indigo-50 text-indigo-600 font-bold px-2 py-0.5 rounded mr-2">
                                                            {{ parentIndex + 1 }}.{{ childIndex + 1 }}
                                                        </span>
                                                        <button @click="openEditModal(child)" class="p-1 text-blue-600 hover:bg-blue-50 rounded" title="Edit">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-4l7-7m0 0l-7 7" /></svg>
                                                        </button>
                                                        <button @click="deleteMenu(child.id)" class="p-1 text-red-600 hover:bg-red-50 rounded" title="Hapus">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </draggable>
                                    </div>
                                    
                                </div>
                            </template>
                        </draggable>

                        <div v-if="list.length === 0" class="text-center py-12">
                            <p class="text-gray-500 font-medium">Belum ada data menu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6 overflow-hidden">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    {{ editingMenu ? 'Edit Menu' : 'Tambah Menu Baru' }}
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Menu</label>
                        <input v-model="formData.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" placeholder="Contoh: Dashboard" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Route</label>
                        <input v-model="formData.route" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" placeholder="Contoh: dashboard" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                        <input v-model="formData.icon" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" placeholder="Contoh: 🏠" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent Menu (Opsional)</label>
                        <select v-model="formData.parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white">
                            <option :value="null">-- Tidak ada (Menu Utama) --</option>
                            <option v-for="menu in list" :key="menu.id" :value="menu.id">
                                {{ menu.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Syarat Hak Akses (Opsional)</label>
                        <select v-model="formData.permission_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-600">
                            <option :value="null">-- Bebas Diakses Semua User --</option>
                            <option v-for="permission in permissions" :key="permission.id" :value="permission.name">
                                {{ permission.name.replace(/_/g, ' ').toUpperCase() }}
                            </option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hanya user dengan hak akses ini yang dapat melihat menu.</p>
                    </div>
                </div>

                <div class="mt-6 flex space-x-3 justify-end">
                    <button @click="closeModal" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button @click="submitForm" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">{{ editingMenu ? 'Simpan' : 'Tambah' }}</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.parent-handle, .child-handle {
    touch-action: none;
}

/* Efek saat Menu Utama melayang */
.sortable-ghost {
    opacity: 0.5;
    background: #f1f5f9 !important;
    border: 2px dashed #94a3b8 !important;
}

/* Efek saat Sub Menu melayang */
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