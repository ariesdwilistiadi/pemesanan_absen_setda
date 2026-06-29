<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    notulen: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatDateShort = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Detail Notulen Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('notulen.index')"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Kembali</Link>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">Detail Notulen Rapat</h2>
                </div>
                <div class="flex gap-2">
                    <a :href="route('notulen.print', notulen.id)"
                        class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak PDF
                    </a>
                    <Link :href="route('notulen.edit', notulen.id)"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto px-4">
                <!-- Info Rapat -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900">Informasi Rapat</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Nama Kegiatan</label>
                                <p class="text-gray-900 font-medium">{{ notulen.rapat?.nama_kegiatan || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal & Waktu</label>
                                <p class="text-gray-900">
                                    {{ notulen.rapat ? formatDateShort(notulen.rapat.tanggal) : '-' }}
                                    <span v-if="notulen.rapat">- {{ notulen.rapat.pukul }} WIB</span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Ruangan</label>
                                <p class="text-gray-900">{{ notulen.rapat?.ruangan?.nama_ruangan || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Mode Peserta</label>
                                <span v-if="notulen.peserta_mode === 'terlampir'"
                                    class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">
                                    Terlampir
                                </span>
                                <span v-else
                                    class="px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full font-medium">
                                    Manual
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pejabat Rapat -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900">Pejabat Rapat</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Ketua Rapat</label>
                                <p class="text-gray-900 font-medium">{{ notulen.ketua?.name || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Sekretaris</label>
                                <p class="text-gray-900 font-medium">{{ notulen.sekretaris?.name || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Pencacat / Notulis</label>
                                <p class="text-gray-900 font-medium">{{ notulen.pencacat?.name || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Isi Notulen -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900">Isi Notulen</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Pembukaan -->
                        <div>
                            <h4 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-3">Pembukaan</h4>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 min-h-[100px]">
                                <div v-if="notulen.pembukaan" v-html="notulen.pembukaan" class="prose prose-sm max-w-none"></div>
                                <p v-else class="text-gray-400 italic">-</p>
                            </div>
                        </div>

                        <!-- Pembahasan -->
                        <div>
                            <h4 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-3">Pembahasan</h4>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 min-h-[150px]">
                                <div v-if="notulen.pembahasan" v-html="notulen.pembahasan" class="prose prose-sm max-w-none"></div>
                                <p v-else class="text-gray-400 italic">-</p>
                            </div>
                        </div>

                        <!-- Peraturan -->
                        <div>
                            <h4 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-3">Peraturan</h4>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 min-h-[100px]">
                                <div v-if="notulen.peraturan" v-html="notulen.peraturan" class="prose prose-sm max-w-none"></div>
                                <p v-else class="text-gray-400 italic">-</p>
                            </div>
                        </div>

                        <!-- Penutup -->
                        <div>
                            <h4 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-3">Penutup</h4>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 min-h-[100px]">
                                <div v-if="notulen.penutup" v-html="notulen.penutup" class="prose prose-sm max-w-none"></div>
                                <p v-else class="text-gray-400 italic">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Info -->
                    <div class="p-6 border-t border-gray-100 bg-gray-50/50">
                        <p class="text-sm text-gray-500">
                            Dibuat: {{ formatDate(notulen.created_at) }}
                            <span v-if="notulen.updated_at && notulen.updated_at !== notulen.created_at">
                                | Diupdate: {{ formatDate(notulen.updated_at) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
