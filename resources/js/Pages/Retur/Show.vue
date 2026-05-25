<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    retur: Object,
});

const deleteRetur = () => {
    if (confirm('Yakin ingin menghapus data retur ini? Perubahan stok akan dikembalikan.')) {
        router.delete(route('retur.destroy', props.retur.id));
    }
};
</script>

<template>
    <Head :title="`Detail Retur ${retur.nomor_transaksi}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('retur.index')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Transaksi Retur</h2>
                </div>
                <button @click="deleteRetur" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-md text-sm font-medium border border-red-200 transition">
                    Hapus & Kembalikan Stok
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Informasi Retur</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">No. Transaksi</div>
                            <div class="font-bold text-gray-900">{{ retur.nomor_transaksi }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">Tanggal</div>
                            <div class="font-medium text-gray-900">{{ new Date(retur.tanggal).toLocaleDateString('id-ID') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">Jenis Retur</div>
                            <div>
                                <span :class="retur.jenis_retur === 'Masuk' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800'" class="px-2 py-0.5 rounded text-xs font-bold uppercase">
                                    {{ retur.jenis_retur }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">Pihak Terkait</div>
                            <div class="font-medium text-gray-900">{{ retur.pihak_terkait }}</div>
                        </div>
                    </div>
                    <div v-if="retur.keterangan" class="bg-gray-50 p-3 rounded-lg text-sm text-gray-700">
                        <span class="font-semibold">Keterangan Umum:</span> {{ retur.keterangan }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Rincian Barang Retur</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3">Produk</th>
                                    <th class="px-4 py-3 text-center">Kuantitas</th>
                                    <th class="px-4 py-3">Keterangan Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in retur.details" :key="item.id" class="border-b">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ item.produk?.nama_barang || 'Produk Dihapus' }}</div>
                                        <div class="text-xs text-gray-500">{{ item.produk?.kode_barang }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-indigo-600">{{ item.kuantitas }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ item.keterangan || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>