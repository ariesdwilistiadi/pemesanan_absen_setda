<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    notulens: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

const handleSearch = () => {
    router.get(route('notulen.index'), { search: search.value }, { preserveState: true });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Notulen Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">Notulen Rapat</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola notulen rapat</p>
                </div>
                <div class="flex gap-2 items-center">
                    <Link :href="route('notulen.create')"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-lg transition-colors shadow-sm text-sm whitespace-nowrap flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Notulen
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4">
                <!-- Filter -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input v-model="search" @keyup.enter="handleSearch" type="text"
                                placeholder="Cari nama kegiatan..."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent" />
                        </div>
                        <button @click="handleSearch"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-lg transition-colors">
                            Cari
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">No</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Rapat</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Pencacat</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Mode Peserta</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Tanggal</th>
                                    <th class="text-center py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(notulen, index) in notulens.data" :key="notulen.id"
                                    class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-600">{{ index + 1 }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-gray-900">
                                            {{ notulen.rapat?.nama_kegiatan || '-' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ notulen.rapat ? formatDate(notulen.rapat.tanggal) : '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ notulen.pencacat?.name || '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span v-if="notulen.peserta_mode === 'terlampir'"
                                            class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">
                                            Terlampir
                                        </span>
                                        <span v-else
                                            class="px-2 py-1 bg-amber-100 text-amber-700 text-xs rounded-full font-medium">
                                            Manual
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ formatDate(notulen.created_at) }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <Link :href="route('notulen.show', notulen.id)"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                title="Lihat">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </Link>
                                            <Link :href="route('notulen.edit', notulen.id)"
                                                class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                                                title="Edit">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </Link>
                                            <a :href="route('notulen.print', notulen.id)"
                                                class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                                title="Cetak PDF">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </a>
                                            <button @click="router.delete(route('notulen.destroy', notulen.id))"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Hapus"
                                                onclick="return confirm('Yakin ingin menghapus notulen ini?')">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="notulens.data.length === 0">
                                    <td colspan="6" class="py-12 px-4 text-center text-gray-500">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg class="w-16 h-16 text-gray-300" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="font-medium">Belum ada notulen rapat</p>
                                            <Link :href="route('notulen.create')"
                                                class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                                + Tambah Notulen Baru
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="notulens.data.length > 0" class="p-4 border-t border-gray-100">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                Menampilkan {{ notulens.from || 0 }} - {{ notulens.to || 0 }} dari
                                {{ notulens.total }} data
                            </div>
                            <div class="flex gap-1">
                                <component v-for="link in notulens.links" :key="link.label" :is="link.url ? 'Link' : 'span'"
                                    :href="link.url" :disabled="!link.url"
                                    :class="[
                                        'px-3 py-1.5 text-sm rounded-lg',
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : link.url
                                                ? 'text-gray-700 hover:bg-gray-100'
                                                : 'text-gray-400 cursor-not-allowed'
                                    ]" v-html="link.label" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
