<script setup>
import { ref } from 'vue';
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
    jenis_retur: 'Masuk',
    pihak_terkait: '',
    keterangan: '',
    items: [],
});

const selectedProduk = ref(null);
const qty = ref(1);
const keteranganItem = ref('');

const addItem = () => {
    if (!selectedProduk.value) {
        alert('Pilih produk terlebih dahulu!');
        return;
    }

    if (qty.value < 1) {
        alert('Kuantitas minimal 1');
        return;
    }

    // Jika retur keluar (ke supplier), pastikan stok mencukupi
    if (form.jenis_retur === 'Keluar' && qty.value > selectedProduk.value.stok) {
        alert(`Stok tidak mencukupi! Sisa stok: ${selectedProduk.value.stok}`);
        return;
    }

    const existingIndex = form.items.findIndex(item => item.id === selectedProduk.value.id);
    
    if (existingIndex !== -1) {
        // Cek kembali limit stok jika retur keluar
        const newQty = form.items[existingIndex].kuantitas + qty.value;
        if (form.jenis_retur === 'Keluar' && newQty > selectedProduk.value.stok) {
            alert(`Total kuantitas melebihi stok tersedia! Sisa stok: ${selectedProduk.value.stok}`);
            return;
        }
        form.items[existingIndex].kuantitas = newQty;
    } else {
        form.items.push({
            id: selectedProduk.value.id,
            kode_barang: selectedProduk.value.kode_barang,
            nama_barang: selectedProduk.value.nama_barang,
            stok_saat_ini: selectedProduk.value.stok,
            kuantitas: qty.value,
            keterangan: keteranganItem.value,
        });
    }

    // Reset input form
    selectedProduk.value = null;
    qty.value = 1;
    keteranganItem.value = '';
};

const removeCartItem = (index) => {
    form.items.splice(index, 1);
};

const submit = () => {
    if (form.items.length === 0) {
        alert('Tambahkan setidaknya 1 produk ke daftar retur.');
        return;
    }
    
    form.post(route('retur.store'));
};
</script>

<template>
    <Head title="Tambah Retur Barang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('retur.index')" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Proses Retur Barang</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <form @submit.prevent="submit">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Informasi Retur</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Transaksi</label>
                                <input v-model="form.nomor_transaksi" type="text" class="w-full rounded-lg border-gray-300 shadow-sm bg-gray-50 text-gray-500" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Retur</label>
                                <input v-model="form.tanggal" type="date" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Retur</label>
                                <div class="flex items-center space-x-4 mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" v-model="form.jenis_retur" value="Masuk" class="form-radio text-indigo-600 focus:ring-indigo-500" @change="form.items = []">
                                        <span class="ml-2 text-sm text-gray-700">Masuk (Dari Pelanggan, Stok +)</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" v-model="form.jenis_retur" value="Keluar" class="form-radio text-orange-600 focus:ring-orange-500" @change="form.items = []">
                                        <span class="ml-2 text-sm text-gray-700">Keluar (Ke Supplier, Stok -)</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pihak Terkait (Supplier / Pelanggan)</label>
                                <input v-model="form.pihak_terkait" type="text" required placeholder="Cth: Bapak Budi / PT. ABC" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Umum</label>
                                <input v-model="form.keterangan" type="text" placeholder="Alasan retur secara umum..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Pilih Barang</h3>
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
                                            <span class="text-xs font-bold" :class="stok > 0 ? 'text-indigo-500' : 'text-red-500'">Stok: {{ stok }}</span>
                                        </div>
                                    </template>
                                </v-select>
                            </div>
                            <div class="w-full md:w-24">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kuantitas</label>
                                <input v-model.number="qty" type="number" min="1" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-center">
                            </div>
                            <div class="w-full md:w-48">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan (Opsional)</label>
                                <input v-model="keteranganItem" type="text" placeholder="Cth: Rusak fisik" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button type="button" @click="addItem" class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg font-medium shadow-sm transition">
                                Tambah
                            </button>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Daftar Barang Retur</h3>
                        
                        <div class="overflow-x-auto mb-4">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3">Produk</th>
                                        <th class="px-4 py-3 text-center">Kuantitas</th>
                                        <th class="px-4 py-3">Keterangan</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in form.items" :key="index" class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">{{ item.nama_barang }}</div>
                                            <div class="text-xs text-gray-500">{{ item.kode_barang }} (Stok saat ini: {{ item.stok_saat_ini }})</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input v-model.number="item.kuantitas" type="number" min="1" class="w-16 px-1 py-1 text-center border-gray-300 rounded text-sm">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input v-model="item.keterangan" type="text" class="w-full px-2 py-1 border-gray-300 rounded text-sm placeholder-gray-300" placeholder="-">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" @click="removeCartItem(index)" class="text-red-500 hover:text-red-700 p-1">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="form.items.length === 0">
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">Daftar masih kosong.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end pt-4 mt-4 border-t">
                            <button 
                                type="submit" 
                                :disabled="form.processing || form.items.length === 0"
                                class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-md transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Transaksi Retur' }}
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>