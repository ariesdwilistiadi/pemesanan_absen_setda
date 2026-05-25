<script setup>
import { ref } from 'vue';
import { router, Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    rapats: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ tanggal: '' }),
    }
});

const filterTanggal = ref(props.filters.tanggal || '');

// State Modal
const showModal = ref(false);
const isEdit = ref(false);

// Form menggunakan useForm dari Inertia
const form = useForm({
    id: null,
    nama_kegiatan: '',
    tanggal: '',
    pukul: '',
});

// Fungsi Filter Tanggal
const handleFilter = () => {
    router.get(route('rapat.index'), { tanggal: filterTanggal.value }, { preserveState: true, replace: true });
};

// Buka Modal Buat
const openCreateModal = () => {
    isEdit.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

// Buka Modal Edit
const openEditModal = (rapat) => {
    isEdit.value = true;
    form.clearErrors();
    form.id = rapat.id;
    form.nama_kegiatan = rapat.nama_kegiatan;
    form.tanggal = rapat.tanggal;
    form.pukul = rapat.pukul;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

// Submit Form (Buat atau Edit)
const submitForm = () => {
    if (isEdit.value) {
        form.put(route('rapat.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('rapat.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

// Fungsi ke Halaman Detail
const goToDetail = (id) => {
    router.get(route('rapat.show', id));
};
</script>

<template>
    <Head title="Daftar Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Manajemen Daftar Rapat</h2>
                <button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm">
                    + Buat Rapat Baru
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Daftar Kegiatan Rapat</h3>
                            <p class="text-sm text-gray-500">Kelola jadwal rapat dan lihat daftar hadir peserta.</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Filter Tanggal:</label>
                            <input type="date" v-model="filterTanggal" @change="handleFilter" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-600" />
                            <button v-if="filterTanggal" @click="filterTanggal = ''; handleFilter()" class="text-sm text-red-500 hover:text-red-700 font-medium ml-2">
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">No</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Nama Kegiatan</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Tanggal</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Pukul</th>
                                        <th class="text-center py-3 px-2 font-semibold text-gray-700">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(rapat, index) in rapats" :key="rapat.id" class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3 px-2 text-gray-600">{{ index + 1 }}</td>
                                        <td class="py-3 px-2 font-semibold text-gray-900">{{ rapat.nama_kegiatan }}</td>
                                        <td class="py-3 px-2 text-gray-600">{{ rapat.tanggal }}</td>
                                        <td class="py-3 px-2 text-gray-600 font-mono">{{ rapat.pukul }} WIB</td>
                                        <td class="py-3 px-2 flex justify-center gap-2">
                                            <button @click="openEditModal(rapat)" class="px-3 py-1.5 bg-amber-100 text-amber-700 hover:bg-amber-200 rounded-md text-xs font-bold transition-colors">
                                                Edit
                                            </button>
                                            <button @click="goToDetail(rapat.id)" class="px-3 py-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md text-xs font-bold transition-colors">
                                                Lihat Detail
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="rapats.length === 0" class="text-center py-12">
                            <p class="text-gray-500 font-medium">Belum ada jadwal rapat yang ditemukan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900">{{ isEdit ? 'Edit Data Rapat' : 'Buat Rapat Baru' }}</h2>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                            <input v-model="form.nama_kegiatan" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" required />
                            <span v-if="form.errors.nama_kegiatan" class="text-xs text-red-500">{{ form.errors.nama_kegiatan }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                <input v-model="form.tanggal" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" required />
                                <span v-if="form.errors.tanggal" class="text-xs text-red-500">{{ form.errors.tanggal }}</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pukul <span class="text-red-500">*</span></label>
                                <input v-model="form.pukul" type="time" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" required />
                                <span v-if="form.errors.pukul" class="text-xs text-red-500">{{ form.errors.pukul }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-100 flex justify-end space-x-3 bg-gray-50">
                        <button type="button" @click="closeModal" class="px-5 py-2.5 text-gray-700 hover:bg-gray-200 bg-white border border-gray-300 rounded-lg font-medium transition-colors text-sm">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium shadow-sm transition-colors text-sm disabled:opacity-50">
                            {{ isEdit ? 'Simpan Perubahan' : 'Buat Rapat' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>