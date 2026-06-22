<script setup>
import { ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    produks: {
        type: Array,
        default: () => [],
    },
});

const showModal = ref(false);
const editingProduk = ref(null);

const getInitialFormData = () => ({
    kode_barang: '',
    nama_barang: '',
    kategori: '',
    harga_beli: 0,
    harga_jual: 0,
    stok: 0,
    satuan: 'Pcs',
    deskripsi: '',
    gambar: null,
    is_active: true,
});

const formData = ref(getInitialFormData());

const openAddModal = () => {
    editingProduk.value = null;
    formData.value = getInitialFormData();
    showModal.value = true;
};

const openEditModal = (produk) => {
    editingProduk.value = produk;
    formData.value = {
        kode_barang: produk.kode_barang,
        nama_barang: produk.nama_barang,
        kategori: produk.kategori || '',
        harga_beli: produk.harga_beli,
        harga_jual: produk.harga_jual,
        stok: produk.stok,
        satuan: produk.satuan,
        deskripsi: produk.deskripsi || '',
        gambar: null, // Don't bind the old image file
        is_active: produk.is_active !== undefined ? produk.is_active : true,
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingProduk.value = null;
    formData.value = getInitialFormData();
};

const handleFileChange = (e) => {
    formData.value.gambar = e.target.files[0];
};

const submitForm = () => {
    // Basic validation
    const requiredFields = [
        'kode_barang', 'nama_barang', 'harga_beli', 'harga_jual', 'stok', 'satuan'
    ];
    
    for (const field of requiredFields) {
        if (formData.value[field] === null || formData.value[field] === '' || formData.value[field] === undefined) {
            alert(`Field ${field.replace('_', ' ')} harus diisi!`);
            return;
        }
    }

    const submitCallback = () => {
        if (editingProduk.value) {
            // When updating with file, Inertia recommends sending via POST and Laravel route is already POST
            router.post(route('produks.update', editingProduk.value.id), formData.value, {
                preserveScroll: true,
                forceFormData: true,
                onSuccess: () => closeModal(),
                onError: (errors) => {
                    if (errors.status === 419) {
                        alert('Sesi telah berakhir. Halaman akan di-refresh.');
                        window.location.reload();
                    } else {
                        alert('Gagal memperbarui produk: ' + JSON.stringify(errors));
                    }
                }
            });
        } else {
            router.post(route('produks.store'), formData.value, {
                preserveScroll: true,
                forceFormData: true,
                onSuccess: () => closeModal(),
                onError: (errors) => {
                    if (errors.status === 419) {
                        alert('Sesi telah berakhir. Halaman akan di-refresh.');
                        window.location.reload();
                    } else {
                        alert('Gagal membuat produk: ' + JSON.stringify(errors));
                    }
                }
            });
        }
    };

    submitCallback();
};

const deleteProduk = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        router.delete(route('produks.destroy', id), {
            preserveScroll: true,
            onError: (errors) => alert('Gagal menghapus produk: ' + JSON.stringify(errors))
        });
    }
};

const toggleActive = (produk) => {
    const newStatus = produk.is_active ? 'nonaktifkan' : 'aktifkan';
    if (confirm(`Apakah Anda yakin ingin ${newStatus} produk "${produk.nama_barang}"?`)) {
        router.post(route('produks.toggleActive', produk.id), {
            preserveScroll: true,
            onError: (errors) => alert('Gagal mengubah status produk: ' + JSON.stringify(errors))
        });
    }
};

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};
</script>

<template>
    <Head title="Manajemen Produk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Manajemen Produk</h2>

                <button
                    @click="openAddModal"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm"
                >
                    + Tambah Produk
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <h3 class="text-lg font-bold text-gray-900">Daftar Produk</h3>
                        <p class="text-sm text-gray-500">Kelola semua data produk/barang di sistem.</p>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Gambar</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Kode Barang</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Nama Barang</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Kategori</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Harga Beli</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Harga Jual</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Stok</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Status</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="produk in produks" :key="produk.id" class="border-b border-gray-100 hover:bg-gray-50" :class="{'opacity-50 bg-gray-50': !produk.is_active}">
                                        <td class="py-3 px-2">
                                            <div v-if="produk.gambar" class="w-12 h-12 rounded overflow-hidden">
                                                <img :src="'/storage/' + produk.gambar" alt="Gambar" class="object-cover w-full h-full" />
                                            </div>
                                            <div v-else class="w-12 h-12 rounded bg-gray-200 flex items-center justify-center text-xs text-gray-500">
                                                No Img
                                            </div>
                                        </td>
                                        <td class="py-3 px-2">
                                            <span class="font-mono text-gray-600">{{ produk.kode_barang }}</span>
                                        </td>
                                        <td class="py-3 px-2">
                                            <div class="font-semibold text-gray-900">{{ produk.nama_barang }}</div>
                                            <div class="text-xs text-gray-500">{{ produk.satuan }}</div>
                                        </td>
                                        <td class="py-3 px-2 text-gray-600">{{ produk.kategori || '-' }}</td>
                                        <td class="py-3 px-2 text-gray-900">{{ formatRupiah(produk.harga_beli) }}</td>
                                        <td class="py-3 px-2 text-gray-900">{{ formatRupiah(produk.harga_jual) }}</td>
                                        <td class="py-3 px-2">
                                            <span :class="{'text-red-600 font-bold': produk.stok <= 0, 'text-green-600 font-bold': produk.stok > 0}">
                                                {{ produk.stok }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-2">
                                            <button
                                                @click="toggleActive(produk)"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                :class="produk.is_active ? 'bg-green-500' : 'bg-gray-300'"
                                                :title="produk.is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan'"
                                            >
                                                <span
                                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                                    :class="produk.is_active ? 'translate-x-6' : 'translate-x-1'"
                                                />
                                            </button>
                                            <div class="text-xs mt-1" :class="produk.is_active ? 'text-green-600' : 'text-gray-400'">
                                                {{ produk.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-2">
                                            <div class="flex space-x-2">
                                                <button
                                                    @click="openEditModal(produk)"
                                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Edit"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-4l7-7m0 0l-7 7" />
                                                    </svg>
                                                </button>
                                                <button
                                                    @click="deleteProduk(produk.id)"
                                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Hapus"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="produks.length === 0" class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 text-gray-300 mb-4">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">Belum ada data produk.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex-shrink-0">
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ editingProduk ? 'Edit Produk' : 'Tambah Produk Baru' }}
                    </h3>
                </div>

                <div class="p-6 overflow-y-auto">
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Kolom Kiri -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Barang *</label>
                                    <input v-model="formData.kode_barang" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang *</label>
                                    <input v-model="formData.nama_barang" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                    <input v-model="formData.kategori" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Satuan *</label>
                                    <input v-model="formData.satuan" type="text" placeholder="Pcs, Kg, dll" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok *</label>
                                    <input v-model="formData.stok" type="number" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli *</label>
                                    <input v-model="formData.harga_beli" type="number" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual *</label>
                                    <input v-model="formData.harga_jual" type="number" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                    <textarea v-model="formData.deskripsi" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                                <div v-if="editingProduk">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Produk</label>
                                    <div class="flex items-center space-x-3">
                                        <button
                                            type="button"
                                            @click="formData.is_active = !formData.is_active"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            :class="formData.is_active ? 'bg-green-500' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                                :class="formData.is_active ? 'translate-x-6' : 'translate-x-1'"
                                            />
                                        </button>
                                        <span class="text-sm" :class="formData.is_active ? 'text-green-600' : 'text-gray-500'">
                                            {{ formData.is_active ? 'Aktif - Produk ditampilkan di kasir' : 'Nonaktif - Produk disembunyikan dari kasir' }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Produk</label>
                                    <input @change="handleFileChange" type="file" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100 flex justify-end space-x-3">
                            <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
