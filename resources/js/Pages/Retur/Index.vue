<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { debounce } from 'lodash-es';

const props = defineProps({
    returs: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
    router.get(
        route('retur.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
}, 300));

const deleteRetur = (id) => {
    if (confirm('Yakin ingin menghapus data retur ini? Stok produk akan dikembalikan seperti sebelum retur dilakukan.')) {
        router.delete(route('retur.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Retur Barang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Retur Barang</h2>
                <Link :href="route('retur.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition shadow-sm">
                    + Tambah Retur
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash.success" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <div v-if="$page.props.flash.error || Object.keys($page.props.errors).length > 0" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ $page.props.flash.error || 'Terjadi kesalahan. Silakan periksa kembali.' }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Daftar Transaksi Retur</h3>
                            <div class="w-full md:w-1/3 relative">
                                <input 
                                    v-model="search" 
                                    type="text" 
                                    placeholder="Cari No. Transaksi / Pihak Terkait..." 
                                    class="w-full pl-10 pr-4 py-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3">Tanggal</th>
                                        <th class="px-4 py-3">No. Transaksi</th>
                                        <th class="px-4 py-3">Jenis Retur</th>
                                        <th class="px-4 py-3">Pihak Terkait</th>
                                        <th class="px-4 py-3 text-center">Jml Item</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in returs.data" :key="item.id" class="border-b hover:bg-gray-50/50">
                                        <td class="px-4 py-3 text-gray-600">{{ new Date(item.tanggal).toLocaleDateString('id-ID') }}</td>
                                        <td class="px-4 py-3 font-mono font-medium text-indigo-600">{{ item.nomor_transaksi }}</td>
                                        <td class="px-4 py-3">
                                            <span :class="item.jenis_retur === 'Masuk' ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800'" class="px-2 py-1 rounded text-xs font-bold uppercase">
                                                {{ item.jenis_retur }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-semibold">{{ item.pihak_terkait }}</td>
                                        <td class="px-4 py-3 text-center">{{ item.details ? item.details.length : 0 }} item</td>
                                        <td class="px-4 py-3 text-center space-x-2 whitespace-nowrap">
                                            <Link :href="route('retur.show', item.id)" class="text-blue-600 hover:text-blue-900 bg-blue-50 px-2 py-1 rounded text-xs font-medium border border-blue-200">
                                                Detail
                                            </Link>
                                            <button @click="deleteRetur(item.id)" class="text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded text-xs font-medium border border-red-200">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="returs.data.length === 0">
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada data retur.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="returs.links && returs.links.length > 3" class="mt-6">
                            <div class="flex flex-wrap -mb-1">
                                <template v-for="(link, p) in returs.links" :key="p">
                                    <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="link.label" />
                                    <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-indigo-50 text-indigo-700 border-indigo-300 font-semibold': link.active }" :href="link.url" v-html="link.label" />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>