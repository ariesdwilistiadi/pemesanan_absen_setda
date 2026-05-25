<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    stokOpname: Object,
});

const deleteOpname = () => {
    if (confirm('Yakin ingin menghapus data stok opname ini? Stok akan dikembalikan ke angka sebelum penyesuaian (sistem).')) {
        router.delete(route('stok-opname.destroy', props.stokOpname.id));
    }
};
</script>

<template>
    <Head :title="`Detail Stok Opname ${stokOpname.nomor_transaksi}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('stok-opname.index')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Hasil Stok Opname</h2>
                </div>
                <button @click="deleteOpname" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-md text-sm font-medium border border-red-200 transition">
                    Hapus Data & Kembalikan Stok
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Informasi Stok Opname</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">No. Transaksi</div>
                            <div class="font-bold text-gray-900">{{ stokOpname.nomor_transaksi }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">Tanggal</div>
                            <div class="font-medium text-gray-900">{{ new Date(stokOpname.tanggal).toLocaleDateString('id-ID') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium mb-1">Penanggung Jawab</div>
                            <div class="font-medium text-gray-900">{{ stokOpname.penanggung_jawab }}</div>
                        </div>
                    </div>
                    <div v-if="stokOpname.keterangan" class="bg-gray-50 p-3 rounded-lg text-sm text-gray-700">
                        <span class="font-semibold">Keterangan Umum:</span> {{ stokOpname.keterangan }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Rincian Perubahan Stok</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse border border-gray-200">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 border-r border-gray-200">Produk</th>
                                    <th class="px-4 py-3 text-center border-r border-gray-200">Stok Sistem (Lama)</th>
                                    <th class="px-4 py-3 text-center border-r border-gray-200 font-bold">Stok Fisik (Baru)</th>
                                    <th class="px-4 py-3 text-center border-r border-gray-200">Selisih</th>
                                    <th class="px-4 py-3 border-r border-gray-200">Catatan Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in stokOpname.details" :key="item.id" class="border-b">
                                    <td class="px-4 py-3 border-r border-gray-200">
                                        <div class="font-medium text-gray-900">{{ item.produk?.nama_barang || 'Produk Dihapus' }}</div>
                                        <div class="text-xs text-gray-500">{{ item.produk?.kode_barang }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center border-r border-gray-200">{{ item.stok_sistem }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-indigo-600 border-r border-gray-200">{{ item.stok_fisik }}</td>
                                    <td class="px-4 py-3 text-center font-bold border-r border-gray-200" :class="item.selisih < 0 ? 'text-red-500' : (item.selisih > 0 ? 'text-emerald-500' : 'text-gray-500')">
                                        <span v-if="item.selisih > 0">+</span>{{ item.selisih }}
                                    </td>
                                    <td class="px-4 py-3 border-r border-gray-200 text-gray-600">{{ item.keterangan || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>