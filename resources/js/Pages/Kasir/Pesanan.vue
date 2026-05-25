<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    transaksis: {
        type: Array,
        default: () => [],
    }
});

// Format currency
const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};

// Filter orders by status
const pendingOrders = computed(() => props.transaksis.filter(t => t.status === 'pending' || !t.status));
const processOrders = computed(() => props.transaksis.filter(t => t.status === 'diproses'));
const completedOrders = computed(() => props.transaksis.filter(t => t.status === 'selesai'));

// Update status function
const updateStatus = (id, newStatus) => {
    let confirmMsg = `Ubah status pesanan menjadi ${newStatus.toUpperCase()}?`;
    if (newStatus === 'batal') {
        confirmMsg = 'Apakah Anda yakin ingin membatalkan pesanan ini? Stok akan dikembalikan.';
    }

    if (confirm(confirmMsg)) {
        router.patch(route('kasir.update-status', id), { status: newStatus }, {
            preserveScroll: true,
            onError: (errors) => alert('Gagal update status: ' + JSON.stringify(errors))
        });
    }
};

const deleteTransaksi = (id) => {
    if (confirm('Hapus transaksi secara permanen?')) {
        router.delete(route('kasir.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Daftar Pesanan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Daftar Pesanan (Dapur / Kasir)</h2>
                <button @click="router.get(route('kasir.index'))" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg font-medium shadow-sm transition-colors text-sm">
                    Kembali ke POS Kasir
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- KANBAN: PENDING -->
                    <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-200 h-[calc(100vh-12rem)] flex flex-col">
                        <div class="flex items-center justify-between mb-4 px-2">
                            <h3 class="font-black text-lg text-slate-800 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                                PESANAN BARU
                            </h3>
                            <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ pendingOrders.length }}</span>
                        </div>

                        <div class="overflow-y-auto pr-2 space-y-4 flex-grow">
                            <div v-for="trx in pendingOrders" :key="trx.id" class="bg-white rounded-xl shadow-sm border border-red-100 p-4 hover:shadow-md transition-shadow relative overflow-hidden group">
                                <div class="absolute top-0 left-0 w-1.5 h-full bg-red-500"></div>
                                
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="text-xs font-mono text-slate-400 mb-1">{{ trx.no_transaksi }}</div>
                                        <div class="font-bold text-slate-900">{{ trx.nama }}</div>
                                        <div class="text-xs font-semibold text-indigo-600 mt-1">Meja: {{ trx.nomor_meja || 'Takeaway' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500">{{ new Date(trx.tanggal_transaksi).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) }}</div>
                                    </div>
                                </div>

                                <div class="bg-slate-50 rounded-lg p-3 mb-3 text-sm">
                                    <ul class="space-y-1">
                                        <li v-for="item in trx.details" :key="item.id" class="flex justify-between">
                                            <span class="text-slate-700"><span class="font-bold mr-1">{{ item.jumlah }}x</span> {{ item.produk?.nama_barang || 'Produk Dihapus' }}</span>
                                        </li>
                                    </ul>
                                    <div v-if="trx.keterangan" class="mt-2 pt-2 border-t border-slate-200 text-xs italic text-slate-500">
                                        "{{ trx.keterangan }}"
                                    </div>
                                </div>

                                <div class="flex gap-2 mt-4">
                                    <button @click="updateStatus(trx.id, 'diproses')" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-lg text-sm transition-colors shadow-sm">
                                        Proses Pesanan
                                    </button>
                                    <button @click="updateStatus(trx.id, 'batal')" class="px-3 bg-white border border-red-200 text-red-600 hover:bg-red-50 font-bold py-2 rounded-lg text-sm transition-colors" title="Batalkan Pesanan">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div v-if="pendingOrders.length === 0" class="text-center py-10 text-slate-400 text-sm font-medium border-2 border-dashed border-slate-200 rounded-xl">
                                Belum ada pesanan baru.
                            </div>
                        </div>
                    </div>

                    <!-- KANBAN: DIPROSES -->
                    <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-200 h-[calc(100vh-12rem)] flex flex-col">
                        <div class="flex items-center justify-between mb-4 px-2">
                            <h3 class="font-black text-lg text-slate-800 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.6)]"></span>
                                SEDANG DIPROSES
                            </h3>
                            <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ processOrders.length }}</span>
                        </div>

                        <div class="overflow-y-auto pr-2 space-y-4 flex-grow">
                            <div v-for="trx in processOrders" :key="trx.id" class="bg-white rounded-xl shadow-sm border border-amber-200 p-4 hover:shadow-md transition-shadow relative overflow-hidden group">
                                <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-400"></div>
                                
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="text-xs font-mono text-slate-400 mb-1">{{ trx.no_transaksi }}</div>
                                        <div class="font-bold text-slate-900">{{ trx.nama }}</div>
                                        <div class="text-xs font-semibold text-indigo-600 mt-1">Meja: {{ trx.nomor_meja || 'Takeaway' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500">{{ new Date(trx.tanggal_transaksi).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) }}</div>
                                    </div>
                                </div>

                                <div class="bg-slate-50 rounded-lg p-3 mb-3 text-sm border border-slate-100">
                                    <ul class="space-y-1">
                                        <li v-for="item in trx.details" :key="item.id" class="flex justify-between">
                                            <span class="text-slate-700"><span class="font-bold mr-1">{{ item.jumlah }}x</span> {{ item.produk?.nama_barang || 'Produk Dihapus' }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="flex gap-2 mt-4">
                                    <button @click="updateStatus(trx.id, 'selesai')" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 rounded-lg text-sm transition-colors shadow-sm">
                                        Selesai & Sajikan
                                    </button>
                                </div>
                            </div>

                            <div v-if="processOrders.length === 0" class="text-center py-10 text-slate-400 text-sm font-medium border-2 border-dashed border-slate-200 rounded-xl">
                                Tidak ada pesanan diproses.
                            </div>
                        </div>
                    </div>

                    <!-- KANBAN: SELESAI -->
                    <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-200 h-[calc(100vh-12rem)] flex flex-col">
                        <div class="flex items-center justify-between mb-4 px-2">
                            <h3 class="font-black text-lg text-slate-800 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                                SELESAI
                            </h3>
                            <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ completedOrders.length }}</span>
                        </div>

                        <div class="overflow-y-auto pr-2 space-y-4 flex-grow">
                            <div v-for="trx in completedOrders" :key="trx.id" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-4 relative overflow-hidden opacity-75 hover:opacity-100 transition-opacity">
                                <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>
                                
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="text-[11px] font-mono text-slate-400 mb-0.5">{{ trx.no_transaksi }}</div>
                                        <div class="font-bold text-slate-700 text-sm">{{ trx.nama }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-emerald-600">{{ formatRupiah(trx.total_harga) }}</div>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500 flex justify-between items-center border-t border-slate-100 pt-2 mt-2">
                                    <span>{{ trx.total_item }} items</span>
                                    <button @click="deleteTransaksi(trx.id)" class="text-slate-400 hover:text-red-500" title="Hapus Riwayat">
                                        Hapus
                                    </button>
                                </div>
                            </div>

                            <div v-if="completedOrders.length === 0" class="text-center py-10 text-slate-400 text-sm font-medium border-2 border-dashed border-slate-200 rounded-xl">
                                Belum ada pesanan selesai.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
