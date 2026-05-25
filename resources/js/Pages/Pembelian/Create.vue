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
    tanggal_pembelian: new Date().toISOString().split('T')[0],
    nama_supplier: '',
    keterangan: '',
    cart: [],
});

const selectedProduk = ref(null);
const qty = ref(1);
const hargaBeli = ref(0);

const addToCart = () => {
    if (!selectedProduk.value) {
        alert('Pilih produk terlebih dahulu!');
        return;
    }

    if (qty.value < 1) {
        alert('Jumlah minimal 1');
        return;
    }

    if (hargaBeli.value < 0) {
        alert('Harga beli tidak valid');
        return;
    }

    const existingIndex = form.cart.findIndex(item => item.id === selectedProduk.value.id);
    
    if (existingIndex !== -1) {
        form.cart[existingIndex].jumlah += qty.value;
        form.cart[existingIndex].harga_beli = hargaBeli.value; // Update to latest price
    } else {
        form.cart.push({
            id: selectedProduk.value.id,
            kode_barang: selectedProduk.value.kode_barang,
            nama_barang: selectedProduk.value.nama_barang,
            jumlah: qty.value,
            harga_beli: hargaBeli.value,
        });
    }

    // Reset input form
    selectedProduk.value = null;
    qty.value = 1;
    hargaBeli.value = 0;
};

const removeCartItem = (index) => {
    form.cart.splice(index, 1);
};

const onProdukSelected = (produk) => {
    if (produk) {
        // Auto fill last purchase price or 0
        hargaBeli.value = parseFloat(produk.harga_beli) || 0;
    } else {
        hargaBeli.value = 0;
    }
};

const totalHarga = computed(() => {
    return form.cart.reduce((total, item) => total + (item.jumlah * item.harga_beli), 0);
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};

const submit = () => {
    if (form.cart.length === 0) {
        alert('Tambahkan setidaknya 1 produk ke daftar pembelian.');
        return;
    }
    
    form.post(route('pembelian.store'));
};
</script>

<template>
    <Head title="Tambah Pembelian" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('pembelian.index')" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pembelian / Stok Masuk</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <form @submit.prevent="submit">
                    <!-- Data Header -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Informasi Pembelian</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Transaksi</label>
                                <input v-model="form.nomor_transaksi" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pembelian</label>
                                <input v-model="form.tanggal_pembelian" type="date" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <div v-if="form.errors.tanggal_pembelian" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal_pembelian }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier</label>
                                <input v-model="form.nama_supplier" type="text" required placeholder="Contoh: PT. Sumber Makmur" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <div v-if="form.errors.nama_supplier" class="text-red-500 text-xs mt-1">{{ form.errors.nama_supplier }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Catatan</label>
                                <input v-model="form.keterangan" type="text" placeholder="Opsional" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Input Barang -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Tambah Barang ke Daftar</h3>
                        <div class="flex flex-col md:flex-row gap-4 items-end">
                            <div class="flex-grow w-full md:w-auto">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Produk</label>
                                <v-select 
                                    v-model="selectedProduk" 
                                    :options="produks" 
                                    label="nama_barang" 
                                    @update:modelValue="onProdukSelected"
                                    placeholder="Cari produk..."
                                    class="bg-white w-full rounded-lg text-sm"
                                >
                                    <template #option="{ nama_barang, kode_barang, stok }">
                                        <div class="flex justify-between items-center w-full">
                                            <span>{{ nama_barang }} <span class="text-xs text-gray-500">({{ kode_barang }})</span></span>
                                            <span class="text-xs font-bold text-indigo-500">Stok: {{ stok }}</span>
                                        </div>
                                    </template>
                                </v-select>
                            </div>
                            <div class="w-full md:w-32">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli Satuan</label>
                                <input v-model.number="hargaBeli" type="number" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="w-full md:w-24">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
                                <input v-model.number="qty" type="number" min="1" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-center">
                            </div>
                            <button type="button" @click="addToCart" class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg font-medium shadow-sm transition">
                                Tambah
                            </button>
                        </div>
                    </div>

                    <!-- Daftar Barang -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Daftar Barang Dibeli</h3>
                        
                        <div class="overflow-x-auto mb-4">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3">Produk</th>
                                        <th class="px-4 py-3 text-center">Harga Beli</th>
                                        <th class="px-4 py-3 text-center">Qty</th>
                                        <th class="px-4 py-3 text-right">Subtotal</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in form.cart" :key="index" class="border-b">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">{{ item.nama_barang }}</div>
                                            <div class="text-xs text-gray-500">{{ item.kode_barang }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input v-model.number="item.harga_beli" type="number" min="0" class="w-24 px-2 py-1 border-gray-300 rounded text-sm text-center">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" @click="item.jumlah > 1 ? item.jumlah-- : null" class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200">-</button>
                                                <input v-model.number="item.jumlah" type="number" min="1" class="w-16 px-1 py-1 text-center border-gray-300 rounded text-sm">
                                                <button type="button" @click="item.jumlah++" class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200">+</button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-indigo-600">{{ formatRupiah(item.jumlah * item.harga_beli) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" @click="removeCartItem(index)" class="text-red-500 hover:text-red-700 p-1">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="form.cart.length === 0">
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Daftar masih kosong.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-col md:flex-row justify-between items-center pt-4 border-t gap-4">
                            <div class="text-lg font-bold">
                                <span class="text-gray-600">Total Harga: </span>
                                <span class="text-indigo-600 text-2xl">{{ formatRupiah(totalHarga) }}</span>
                            </div>
                            <button 
                                type="submit" 
                                :disabled="form.processing || form.cart.length === 0"
                                class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-md transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Transaksi Pembelian' }}
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>