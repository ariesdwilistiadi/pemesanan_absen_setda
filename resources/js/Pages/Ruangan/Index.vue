<script setup>
import { ref, computed } from 'vue';
import { router, Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    ruangans: {
        type: Array,
        default: () => [],
    },
    filter: {
        type: String,
        default: 'semua',
    },
});

// Filter state
const activeFilter = ref(props.filter);

// Form state
const showModal = ref(false);
const isEdit = ref(false);
const editingId = ref(null);

const form = useForm({
    nama_ruangan: '',
    keterangan: '',
    is_active: true,
});

// Open create modal
const openCreateModal = () => {
    isEdit.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    showModal.value = true;
};

// Open edit modal
const openEditModal = (ruangan) => {
    isEdit.value = true;
    editingId.value = ruangan.id;
    form.clearErrors();
    form.nama_ruangan = ruangan.nama_ruangan;
    form.keterangan = ruangan.keterangan || '';
    form.is_active = ruangan.is_active;
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    form.reset();
};

// Submit form
const submitForm = () => {
    if (isEdit.value) {
        form.put(route('ruangans.update', editingId.value), {
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        form.post(route('ruangans.store'), {
            onSuccess: () => {
                closeModal();
            },
        });
    }
};

// Toggle active status
const toggleActive = (ruangan) => {
    if (confirm(`Yakin ingin ${ruangan.is_active ? 'menonaktifkan' : 'mengaktifkan'} ruangan "${ruangan.nama_ruangan}"?`)) {
        router.post(route('ruangans.toggle', ruangan.id));
    }
};

// Delete ruangan
const deleteRuangan = (ruangan) => {
    if (confirm(`Yakin ingin menghapus ruangan "${ruangan.nama_ruangan}"? Tindakan ini tidak dapat dibatalkan.`)) {
        router.delete(route('ruangans.destroy', ruangan.id));
    }
};

// Filter handlers
const setFilter = (filter) => {
    activeFilter.value = filter;
    router.get(route('ruangans.index'), { filter }, { preserveState: true, replace: true });
};

// Computed counts
const countAktif = computed(() => props.ruangans.filter(r => r.is_active).length);
const countNonaktif = computed(() => props.ruangans.filter(r => !r.is_active).length);
</script>

<template>
    <Head title="Manajemen Ruangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">Manajemen Ruangan</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola ruangan untuk kegiatan rapat</p>
                </div>
                <button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Ruangan Baru
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4">
                <!-- Filter Tabs -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="flex border-b border-gray-100">
                        <button
                            @click="setFilter('semua')"
                            class="flex-1 px-6 py-4 text-center font-medium transition-colors text-sm"
                            :class="activeFilter === 'semua' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        >
                            Semua
                            <span class="ml-2 px-2 py-0.5 rounded-full text-xs" :class="activeFilter === 'semua' ? 'bg-indigo-600 text-white' : 'bg-gray-100'">
                                {{ ruangans.length }}
                            </span>
                        </button>
                        <button
                            @click="setFilter('aktif')"
                            class="flex-1 px-6 py-4 text-center font-medium transition-colors text-sm"
                            :class="activeFilter === 'aktif' ? 'text-emerald-600 border-b-2 border-emerald-600 bg-emerald-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        >
                            Aktif
                            <span class="ml-2 px-2 py-0.5 rounded-full text-xs" :class="activeFilter === 'aktif' ? 'bg-emerald-600 text-white' : 'bg-gray-100'">
                                {{ countAktif }}
                            </span>
                        </button>
                        <button
                            @click="setFilter('nonaktif')"
                            class="flex-1 px-6 py-4 text-center font-medium transition-colors text-sm"
                            :class="activeFilter === 'nonaktif' ? 'text-gray-600 border-b-2 border-gray-600 bg-gray-100' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        >
                            Nonaktif
                            <span class="ml-2 px-2 py-0.5 rounded-full text-xs" :class="activeFilter === 'nonaktif' ? 'bg-gray-600 text-white' : 'bg-gray-100'">
                                {{ countNonaktif }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <th class="text-left py-4 px-6 font-semibold text-gray-700">No</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-700">Nama Ruangan</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-700">Keterangan</th>
                                    <th class="text-center py-4 px-6 font-semibold text-gray-700">Status</th>
                                    <th class="text-center py-4 px-6 font-semibold text-gray-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(ruangan, index) in ruangans" :key="ruangan.id" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-6 text-gray-600">{{ index + 1 }}</td>
                                    <td class="py-4 px-6">
                                        <div class="font-semibold text-gray-900">{{ ruangan.nama_ruangan }}</div>
                                    </td>
                                    <td class="py-4 px-6 text-gray-600">
                                        {{ ruangan.keterangan || '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span
                                            class="px-3 py-1.5 rounded-full text-xs font-bold"
                                            :class="ruangan.is_active
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-gray-200 text-gray-600'"
                                        >
                                            {{ ruangan.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex justify-center gap-2">
                                            <button
                                                @click="openEditModal(ruangan)"
                                                class="px-3 py-1.5 bg-amber-100 text-amber-700 hover:bg-amber-200 rounded-md text-xs font-bold transition-colors"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="toggleActive(ruangan)"
                                                class="px-3 py-1.5 rounded-md text-xs font-bold transition-colors"
                                                :class="ruangan.is_active
                                                    ? 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                                    : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'"
                                            >
                                                {{ ruangan.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                            <button
                                                @click="deleteRuangan(ruangan)"
                                                class="px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 rounded-md text-xs font-bold transition-colors"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="ruangans.length === 0">
                                    <td colspan="5" class="py-12 px-6 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <p class="text-gray-500 font-medium">Belum ada ruangan.</p>
                                            <button @click="openCreateModal" class="mt-4 text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                                + Tambah Ruangan Baru
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ isEdit ? 'Edit Ruangan' : 'Tambah Ruangan Baru' }}
                    </h2>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Ruangan <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.nama_ruangan"
                                type="text"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600"
                                placeholder="Contoh: Ruang Rapat Utama"
                                required
                            />
                            <span v-if="form.errors.nama_ruangan" class="text-xs text-red-500 mt-1">{{ form.errors.nama_ruangan }}</span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Keterangan
                            </label>
                            <textarea
                                v-model="form.keterangan"
                                rows="3"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600"
                                placeholder="Keterangan tambahan (opsional)"
                            ></textarea>
                            <span v-if="form.errors.keterangan" class="text-xs text-red-500 mt-1">{{ form.errors.keterangan }}</span>
                        </div>

                        <div v-if="isEdit">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                />
                                <span class="text-sm font-medium text-gray-700">Ruangan Aktif</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 ml-8">Ruangan yang tidak aktif tidak akan muncul di dropdown rapat.</p>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-100 flex justify-end space-x-3 bg-gray-50">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-5 py-2.5 text-gray-700 hover:bg-gray-200 bg-white border border-gray-300 rounded-lg font-medium transition-colors text-sm"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium shadow-sm transition-colors text-sm disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Tambah Ruangan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>