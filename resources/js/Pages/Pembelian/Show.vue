<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    pembelian: Object,
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};

const deletePembelian = () => {
    if (confirm('Yakin ingin menghapus data pembelian ini? Stok produk yang masuk dari pembelian ini akan dikurangi.')) {
        router.delete(route('pembelian.destroy', props.pembelian.id));
    }
};
</script>

<template>
    <Head :title="`Detail Pembelian ${pembelian.nomor_transaksi}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('pembelian.index')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pembelian</h2>
                </div>
                <button @click="deletePembelian" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-md text-sm font-medium border border-red-200 transition">
                    Hapus Transaksi
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Informasi Pembelian</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">No. Transaksi</div>
                            <div class="font-bold text-gray-900">{{ pembelian.nomor_transaksi }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">Tanggal</div>
                            <div class="font-medium text-gray-900">{{ new Date(pembelian.tanggal_pembelian).toLocaleDateString('id-ID') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">Supplier</div>
                            <div class="font-medium text-gray-900">{{ pembelian.nama_supplier }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">Total Harga</div>
                            <div class="font-bold text-indigo-600 text-lg">{{ formatRupiah(pembelian.total_harga) }}</div>
                        </div>
                    </div>
                    <div v-if="pembelian.keterangan" class="bg-gray-50 p-3 rounded-lg text-sm text-gray-700">
                        <span class="font-semibold">Keterangan:</span> {{ pembelian.keterangan }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Rincian Barang</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3">Produk</th>
                                    <th class="px-4 py-3 text-center">Harga Beli</th>
                                    <th class="px-4 py-3 text-center">Qty</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in pembelian.details" :key="item.id" class="border-b">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ item.produk?.nama_barang || 'Produk Dihapus' }}</div>
                                        <div class="text-xs text-gray-500">{{ item.produk?.kode_barang }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ formatRupiah(item.harga_satuan) }}</td>
                                    <td class="px-4 py-3 text-center">{{ item.kuantitas }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-indigo-600">{{ formatRupiah(item.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>