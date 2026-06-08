<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    auth: Object
});

// State
const loading = ref(false);
const error = ref('');
const penjualanData = ref([]);
const filters = ref({
    start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
    kategori: 'ATK'
});

const availableKategori = [
    { value: 'ATK', label: 'Alat Tulis Kantor' },
    { value: 'MAKANAN', label: 'Makanan' },
    { value: 'MINUMAN', label: 'Minuman' },
    { value: 'LAINNYA', label: 'Lainnya' },
];

// Fetch data
const fetchPenjualan = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await axios.get(route('produk-eksternal.penjualan'), {
            params: filters.value
        });

        if (response.data.success) {
            penjualanData.value = response.data.data;
        } else {
            error.value = response.data.message || 'Gagal mengambil data';
        }
    } catch (err) {
        console.error('Error fetch penjualan:', err);
        error.value = 'Koneksi ke server gagal. ' + (err.message || '');
    } finally {
        loading.value = false;
    }
};

// Format currency
const formatRupiah = (number) => {
    if (!number && number !== 0) return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

// Calculate totals
const totalPenjualan = computed(() => {
    if (!Array.isArray(penjualanData.value)) return 0;
    return penjualanData.value.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
});

const totalQty = computed(() => {
    if (!Array.isArray(penjualanData.value)) return 0;
    return penjualanData.value.reduce((sum, item) => sum + (parseInt(item.jumlah) || 0), 0);
});

const totalTransaksi = computed(() => {
    if (!Array.isArray(penjualanData.value)) return 0;
    return new Set(penjualanData.value.map(item => item.no_transaksi || item.id)).size;
});

// Watch filter changes
watch(filters, () => {
    fetchPenjualan();
}, { deep: true });

// Load on mount
onMounted(() => {
    fetchPenjualan();
});
</script>

<template>
    <Head title="Data Penjualan External" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">Data Penjualan External</h2>
                    <p class="text-sm text-gray-500 mt-1">Data penjualan dari Server external</p>
                </div>
                <button @click="fetchPenjualan" :disabled="loading" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors text-sm flex items-center gap-2 disabled:opacity-50">
                    <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4">

                <!-- Filter Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input v-model="filters.start_date" type="date" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                            <input v-model="filters.end_date" type="date" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select v-model="filters.kategori" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm bg-white">
                                <option v-for="kat in availableKategori" :key="kat.value" :value="kat.value">
                                    {{ kat.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white">
                        <div class="text-sm opacity-80 mb-1">Total Penjualan</div>
                        <div class="text-2xl font-bold">{{ formatRupiah(totalPenjualan) }}</div>
                    </div>
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white">
                        <div class="text-sm opacity-80 mb-1">Total Item Terjual</div>
                        <div class="text-2xl font-bold">{{ totalQty.toLocaleString() }} Pcs</div>
                    </div>
                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-6 text-white">
                        <div class="text-sm opacity-80 mb-1">Jumlah Transaksi</div>
                        <div class="text-2xl font-bold">{{ totalTransaksi }} Transaksi</div>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                    {{ error }}
                </div>

                <!-- Data Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left py-4 px-6 font-semibold text-gray-700">No</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-700">No Transaksi</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-700">Tanggal</th>
                                    <th class="text-left py-4 px-6 font-semibold text-gray-700">Produk</th>
                                    <th class="text-center py-4 px-6 font-semibold text-gray-700">Qty</th>
                                    <th class="text-right py-4 px-6 font-semibold text-gray-700">Harga</th>
                                    <th class="text-right py-4 px-6 font-semibold text-gray-700">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="loading && penjualanData.length === 0">
                                    <td colspan="7" class="py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="animate-spin w-10 h-10 text-indigo-500 mb-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <p class="text-gray-500">Memuat data...</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-else-if="penjualanData.length === 0">
                                    <td colspan="7" class="py-12 text-center text-gray-500">
                                        Tidak ada data penjualan untuk periode ini.
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in penjualanData" :key="index" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-6 text-gray-600">{{ index + 1 }}</td>
                                    <td class="py-4 px-6 font-mono text-sm">{{ item.no_transaksi || '-' }}</td>
                                    <td class="py-4 px-6">{{ formatDate(item.tanggal || item.created_at) }}</td>
                                    <td class="py-4 px-6 font-medium">{{ item.produk || item.nama_produk || item.barang || '-' }}</td>
                                    <td class="py-4 px-6 text-center">{{ item.jumlah || 0 }}</td>
                                    <td class="py-4 px-6 text-right">{{ formatRupiah(item.harga || item.harga_jual) }}</td>
                                    <td class="py-4 px-6 text-right font-semibold text-indigo-600">{{ formatRupiah(item.total || item.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>