<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
    produks: Array,
    nomorTransaksi: String,
});

const form = useForm({
    nomor_transaksi: props.nomorTransaksi,
    tanggal: new Date().toISOString().split('T')[0],
    penanggung_jawab: '',
    keterangan: '',
    items: [],
});

const selectedProduk = ref(null);
const stokFisik = ref(0);
const keteranganItem = ref('');

const addItem = () => {
    if (!selectedProduk.value) {
        alert('Pilih produk terlebih dahulu!');
        return;
    }

    if (stokFisik.value < 0) {
        alert('Stok fisik tidak boleh kurang dari 0');
        return;
    }

    const existingIndex = form.items.findIndex(item => item.id === selectedProduk.value.id);
    
    if (existingIndex !== -1) {
        alert('Produk ini sudah ditambahkan di daftar. Silakan ubah langsung pada tabel.');
        return;
    }

    form.items.push({
        id: selectedProduk.value.id,
        kode_barang: selectedProduk.value.kode_barang,
        nama_barang: selectedProduk.value.nama_barang,
        stok_sistem: selectedProduk.value.stok,
        stok_fisik: stokFisik.value,
        selisih: stokFisik.value - selectedProduk.value.stok,
        keterangan: keteranganItem.value,
    });

    // Reset input
    selectedProduk.value = null;
    stokFisik.value = 0;
    keteranganItem.value = '';
};

const removeCartItem = (index) => {
    form.items.splice(index, 1);
};

// Update selisih automatically when stok fisik in table changes
const updateSelisih = (item) => {
    item.selisih = item.stok_fisik - item.stok_sistem;
};

const submit = () => {
    if (form.items.length === 0) {
        alert('Tambahkan setidaknya 1 produk untuk di-opname.');
        return;
    }
    
    form.post(route('stok-opname.store'));
};
</script>

<template>
    <Head title="Stok Opname Baru" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('stok-opname.index')" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Proses Stok Opname</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <form @submit.prevent="submit">
                    <!-- Data Header -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Informasi Opname</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Transaksi</label>
                                <input v-model="form.nomor_transaksi" type="text" class="w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 text-gray-500" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                <input v-model="form.tanggal" type="date" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab</label>
                                <input v-model="form.penanggung_jawab" type="text" required placeholder="Nama petugas pemeriksa" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Umum</label>
                                <input v-model="form.keterangan" type="text" placeholder="Catatan opsional" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Input Barang -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Pemeriksaan Barang</h3>
                        <div class="flex flex-col md:flex-row gap-4 items-end">
                            <div class="flex-grow w-full md:w-auto">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Produk</label>
                                <v-select 
                                    v-model="selectedProduk" 
                                    :options="produks" 
                                    label="nama_barang" 
                                    placeholder="Cari produk..."
                                    class="bg-white w-full rounded-lg text-sm"
                                >
                                    <template #option="{ nama_barang, kode_barang, stok }">
                                        <div class="flex justify-between items-center w-full">
                                            <span>{{ nama_barang }} <span class="text-xs text-gray-500">({{ kode_barang }})</span></span>
                                            <span class="text-xs font-bold text-indigo-500">Stok Sistem: {{ stok }}</span>
                                        </div>
                                    </template>
                                </v-select>
                            </div>
                            <div class="w-full md:w-32">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stok Aktual/Fisik</label>
                                <input v-model.number="stokFisik" type="number" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="w-full md:w-48">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Item (Opsional)</label>
                                <input v-model="keteranganItem" type="text" placeholder="Misal: 2 pcs rusak" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button type="button" @click="addItem" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-medium shadow-sm transition">
                                Tambah ke Daftar
                            </button>
                        </div>
                    </div>

                    <!-- Daftar Barang -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Daftar Hasil Stok Opname</h3>
                        
                        <div class="overflow-x-auto mb-4">
                            <table class="w-full text-sm text-left border-collapse border border-gray-200">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3 border-r border-gray-200">Produk</th>
                                        <th class="px-4 py-3 text-center border-r border-gray-200 bg-gray-100/50">Stok Sistem</th>
                                        <th class="px-4 py-3 text-center border-r border-gray-200">Stok Fisik</th>
                                        <th class="px-4 py-3 text-center border-r border-gray-200">Selisih</th>
                                        <th class="px-4 py-3 text-left border-r border-gray-200">Keterangan</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in form.items" :key="index" class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 border-r border-gray-200">
                                            <div class="font-medium text-gray-900">{{ item.nama_barang }}</div>
                                            <div class="text-xs text-gray-500">{{ item.kode_barang }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-gray-600 border-r border-gray-200 bg-gray-100/50">
                                            {{ item.stok_sistem }}
                                        </td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200">
                                            <input v-model.number="item.stok_fisik" @input="updateSelisih(item)" type="number" min="0" class="w-20 px-2 py-1 border-gray-300 rounded text-sm text-center">
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold border-r border-gray-200" :class="item.selisih < 0 ? 'text-red-500' : (item.selisih > 0 ? 'text-emerald-500' : 'text-gray-500')">
                                            <span v-if="item.selisih > 0">+</span>{{ item.selisih }}
                                        </td>
                                        <td class="px-4 py-3 border-r border-gray-200">
                                            <input v-model="item.keterangan" type="text" class="w-full px-2 py-1 border-gray-300 rounded text-sm placeholder-gray-300" placeholder="-">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" @click="removeCartItem(index)" class="text-red-500 hover:bg-red-50 p-1.5 rounded">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="form.items.length === 0">
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada barang yang dicek.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end pt-4 mt-4">
                            <button 
                                type="submit" 
                                :disabled="form.processing || form.items.length === 0"
                                class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-md transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan & Menyesuaikan Stok...' : 'Simpan & Sesuaikan Stok' }}
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>