<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    transaksis: {
        type: Array,
        default: () => [],
    },
    totalPendapatan: {
        type: Number,
        default: 0
    },
    totalModal: {
        type: Number,
        default: 0
    },
    totalKeuntungan: {
        type: Number,
        default: 0
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

// Format currency
const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};

// Date filters
const today = new Date().toISOString().split('T')[0];
const startDate = ref(props.filters.start_date || today);
const endDate = ref(props.filters.end_date || today);

const filterData = () => {
    router.get(route('kasir.laporan-keuntungan'), {
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Laporan Keuntungan Kasir" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Laporan Keuntungan</h2>
                <div class="flex space-x-2">
                    <button @click="router.get(route('kasir.index'))" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg font-medium shadow-sm transition-colors text-sm">
                        POS Kasir
                    </button>
                    <button @click="router.get(route('kasir.pesanan'))" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg font-medium shadow-sm transition-colors text-sm">
                        Pesanan Dapur
                    </button>
                    <button @click="router.get(route('kasir.laporan'))" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg font-medium shadow-sm transition-colors text-sm">
                        Lap. Pendapatan
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- FILTER SECTION -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 items-end justify-between">
                    <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date" v-model="startDate" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-auto">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                            <input type="date" v-model="endDate" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-auto">
                        </div>
                        <div class="flex items-end">
                            <button @click="filterData" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md shadow-sm text-sm font-medium transition w-full md:w-auto">Tampilkan</button>
                        </div>
                    </div>
                </div>

                <!-- SUMMARY SECTION -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-6 py-4 rounded-xl shadow-sm text-center">
                        <div class="text-sm font-semibold uppercase tracking-wider mb-1">Total Pendapatan</div>
                        <div class="text-2xl font-black">{{ formatRupiah(totalPendapatan) }}</div>
                    </div>
                    <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl shadow-sm text-center">
                        <div class="text-sm font-semibold uppercase tracking-wider mb-1">Total Modal (HPP)</div>
                        <div class="text-2xl font-black">{{ formatRupiah(totalModal) }}</div>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-xl shadow-sm text-center">
                        <div class="text-sm font-semibold uppercase tracking-wider mb-1">Total Keuntungan</div>
                        <div class="text-2xl font-black">{{ formatRupiah(totalKeuntungan) }}</div>
                    </div>
                </div>

                <!-- TABLE SECTION -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-sm">
                                    <th class="py-3 px-4 font-semibold text-gray-700">Waktu</th>
                                    <th class="py-3 px-4 font-semibold text-gray-700">No. Transaksi</th>
                                    <th class="py-3 px-4 font-semibold text-gray-700 text-right">Pendapatan</th>
                                    <th class="py-3 px-4 font-semibold text-gray-700 text-right">Modal</th>
                                    <th class="py-3 px-4 font-semibold text-gray-700 text-right">Keuntungan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <tr v-for="trx in transaksis" :key="trx.id" class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 text-gray-500 whitespace-nowrap">{{ new Date(trx.tanggal_transaksi).toLocaleString('id-ID') }}</td>
                                    <td class="py-3 px-4 font-mono text-xs text-indigo-600 font-semibold whitespace-nowrap">{{ trx.no_transaksi }}</td>
                                    <td class="py-3 px-4 text-right font-medium text-gray-800">{{ formatRupiah(trx.total_harga) }}</td>
                                    <td class="py-3 px-4 text-right font-medium text-red-600">{{ formatRupiah(trx.modal) }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-emerald-600">{{ formatRupiah(trx.keuntungan) }}</td>
                                </tr>
                                <tr v-if="transaksis.length === 0">
                                    <td colspan="5" class="py-8 text-center text-gray-500">Tidak ada data keuntungan untuk periode ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>