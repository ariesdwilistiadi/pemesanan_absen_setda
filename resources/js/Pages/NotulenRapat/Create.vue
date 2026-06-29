<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { router, Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
    rapats: Array,
    users: Array,
    selectedRapat: Object,
});

const isQuillLoaded = ref(false);
let quillInstances = {};

// Load Quill CSS and JS via CDN
onMounted(() => {
    // Load Quill CSS
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css';
    document.head.appendChild(link);

    // Load Quill JS
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.min.js';
    script.onload = () => {
        isQuillLoaded.value = true;
        initQuill();
    };
    document.head.appendChild(script);
});

const initQuill = async () => {
    await nextTick();

    const fields = ['pembukaan', 'pembahasan', 'peraturan', 'penutup'];

    fields.forEach(field => {
        const editorContainer = document.querySelector(`#${field}-editor`);
        if (editorContainer && window.Quill) {
            quillInstances[field] = new window.Quill(`#${field}-editor`, {
                theme: 'snow',
                placeholder: `Masukkan teks ${field}...`,
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link'],
                        ['clean']
                    ]
                }
            });
        }
    });
};

onUnmounted(() => {
    quillInstances = {};
});

const form = useForm({
    absen_rapat_id: props.selectedRapat?.id || '',
    ketua_id: '',
    sekretaris_id: '',
    pencacat_id: '',
    pembukaan: '',
    pembahasan: '',
    peraturan: '',
    penutup: '',
    peserta_mode: 'terlampir',
});

const submit = () => {
    // Get content from Quill editors
    const fields = ['pembukaan', 'pembahasan', 'peraturan', 'penutup'];
    fields.forEach(field => {
        if (quillInstances[field]) {
            form[field] = quillInstances[field].root.innerHTML;
        }
    });

    form.post(route('notulen.store'), {
        onSuccess: () => {
            alert('Notulen berhasil disimpan!');
        },
        onError: (errors) => {
            console.error(errors);
            alert('Gagal menyimpan: ' + Object.values(errors).join(', '));
        }
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Tambah Notulen Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('notulen.index')"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Kembali</Link>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Tambah Notulen Rapat</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto px-4">
                <!-- Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                    <p class="text-sm text-blue-800">
                        <strong>💡 Tips:</strong> Field teks menggunakan Quill Editor. Anda bisa formatting teks, membuat daftar, menambahkan link, dll.
                    </p>
                </div>

                <form @submit.prevent="submit">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Header Info -->
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-900">Informasi Notulen</h3>
                        </div>

                        <div class="p-6 space-y-6">
                            <!-- Pilih Rapat -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Pilih Rapat <span class="text-red-500">*</span>
                                </label>
                                <v-select v-model="form.absen_rapat_id" :options="rapats" :reduce="rapat => rapat.id"
                                    label="nama_kegiatan" placeholder="-- Pilih Rapat --">
                                    <template #option="{ nama_kegiatan, tanggal, pukul }">
                                        <div>
                                            <strong>{{ nama_kegiatan }}</strong>
                                            <div class="text-xs text-gray-500">{{ formatDate(tanggal) }} - {{ pukul }} WIB</div>
                                        </div>
                                    </template>
                                </v-select>
                                <p v-if="form.errors.absen_rapat_id" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.absen_rapat_id }}
                                </p>
                            </div>

                            <!-- Pejabat Rapat -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ketua Rapat</label>
                                    <v-select v-model="form.ketua_id" :options="users" :reduce="user => user.id"
                                        label="name" placeholder="-- Pilih Ketua --" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sekretaris</label>
                                    <v-select v-model="form.sekretaris_id" :options="users" :reduce="user => user.id"
                                        label="name" placeholder="-- Pilih Sekretaris --" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencacat / Notulis</label>
                                    <v-select v-model="form.pencacat_id" :options="users" :reduce="user => user.id"
                                        label="name" placeholder="-- Pilih Pencacat --" />
                                </div>
                            </div>

                            <!-- Mode Peserta -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mode Peserta <span
                                        class="text-red-500">*</span></label>
                                <div class="flex gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="form.peserta_mode" value="terlampir"
                                            class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-600" />
                                        <span class="text-sm text-gray-700">Terlampir</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="form.peserta_mode" value="manual"
                                            class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-600" />
                                        <span class="text-sm text-gray-700">Manual</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Isi Notulen dengan Quill Editor -->
                        <div class="p-6 border-t border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Isi Notulen</h3>

                            <!-- Loading indicator -->
                            <div v-if="!isQuillLoaded" class="flex items-center justify-center py-12">
                                <div class="flex items-center gap-3 text-gray-500">
                                    <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span>Memuat editor...</span>
                                </div>
                            </div>

                            <div v-else class="space-y-6">
                                <!-- Pembukaan -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Pembukaan
                                    </label>
                                    <div id="pembukaan-editor" class="quill-editor-container"></div>
                                </div>

                                <!-- Pembahasan -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Pembahasan
                                    </label>
                                    <div id="pembahasan-editor" class="quill-editor-container"></div>
                                </div>

                                <!-- Peraturan -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Peraturan
                                    </label>
                                    <div id="peraturan-editor" class="quill-editor-container"></div>
                                </div>

                                <!-- Penutup -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Penutup
                                    </label>
                                    <div id="penutup-editor" class="quill-editor-container"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                            <Link :href="route('notulen.index')"
                                class="px-5 py-2.5 text-gray-700 hover:bg-gray-100 border border-gray-300 rounded-lg font-medium transition-colors">
                                Batal
                            </Link>
                            <button type="submit" :disabled="form.processing || !isQuillLoaded"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Notulen' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.v-select .vs__dropdown-toggle) {
    padding: 8px 0;
    border-color: #d1d5db;
    border-radius: 0.5rem;
}

:deep(.v-select.vs--open .vs__dropdown-toggle) {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

/* Quill Editor Container */
.quill-editor-container {
    min-height: 150px;
    background: white;
}

:deep(.ql-toolbar) {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    border-color: #d1d5db !important;
    background: #f9fafb;
}

:deep(.ql-container) {
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    border-color: #d1d5db !important;
    font-family: inherit;
    font-size: 14px;
}

:deep(.ql-editor) {
    min-height: 120px;
}

:deep(.ql-editor.ql-blank::before) {
    color: #9ca3af;
    font-style: normal;
}
</style>
