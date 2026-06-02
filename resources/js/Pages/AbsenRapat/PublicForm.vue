<script setup>
import { ref, nextTick, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { VueSignaturePad } from 'vue-signature-pad';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
    rapat: Object,
    masterDinas: {
        type: Array,
        default: () => [],
    }
});

const signaturePad = ref(null);

const form = useForm({
    tipe_peserta: 'internal',
    nip: '',
    nama: '',
    id_dinas: '',
    nama_external: '',
    telp: '',
    email: '',
    signature: ''
});

onMounted(async () => {
    await nextTick();
    if (signaturePad.value) {
        signaturePad.value.resizeCanvas();
    }
});

const clearSignature = () => {
    if (signaturePad.value) {
        signaturePad.value.clearSignature();
    }
};

const handleTipePesertaChange = () => {
    form.nip = '';
    form.id_dinas = '';
    form.nama_external = '';
};

const submitForm = () => {
    const { isEmpty, data } = signaturePad.value.saveSignature();
    
    if (isEmpty) {
        alert("Silakan bubuhkan tanda tangan terlebih dahulu!");
        return;
    }
    
    if (form.tipe_peserta === 'internal' && !form.id_dinas) {
        alert("Silakan pilih Dinas Internal!");
        return;
    }
    
    form.signature = data;

    form.post(route('rapat.public.store', props.rapat.id), {
        preserveScroll: true,
        onError: (errors) => {
            console.error(errors);
            alert("Terjadi kesalahan! Periksa form Anda.");
        }
    });
};
</script>

<template>
    <Head :title="'Isi Daftar Hadir - ' + rapat.nama_kegiatan" />

    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
            
            <div class="p-6 bg-indigo-600 text-white text-center">
                <h2 class="text-xl font-bold leading-tight">{{ rapat.nama_kegiatan }}</h2>
                <p class="text-indigo-100 text-sm mt-2">
                    {{ rapat.tanggal }} &bull; {{ rapat.pukul }} WIB
                </p>
            </div>

            <!-- Form -->
            <div class="p-6 space-y-5">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Peserta <span class="text-red-500">*</span></label>
                    <select v-model="form.tipe_peserta" @change="handleTipePesertaChange" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 bg-white">
                        <option value="internal">Internal </option>
                        <option value="eksternal">Eksternal (Tamu Instansi Luar)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input v-model="form.nama" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600" required />
                </div>

                <div v-if="form.tipe_peserta === 'internal'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                    <input v-model="form.nip" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600" />
                </div>

                <div v-if="form.tipe_peserta === 'internal'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Dinas Internal <span class="text-red-500">*</span></label>
                    <v-select 
                        v-model="form.id_dinas" 
                        :options="masterDinas" 
                        :reduce="dinas => dinas.id" 
                        label="nama_dinas" 
                        placeholder="-- Ketik untuk mencari dinas --"
                        class="bg-white v-select-mobile"
                    >
                        <template #no-options>
                            Dinas tidak ditemukan.
                        </template>
                    </v-select>
                </div>

                <div v-else>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Asal Dinas / Instansi <span class="text-red-500">*</span></label>
                    <input v-model="form.nama_external" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600" required />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP / WhatsApp <span class="text-red-500">*</span></label>
                    <input v-model="form.telp" type="tel" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600" required />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input v-model="form.email" type="email" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600" required />
                </div>

                <div class="mt-4">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-700">Tanda Tangan <span class="text-red-500">*</span></label>
                        <button type="button" @click="clearSignature" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-bold transition-colors">
                            Bersihkan
                        </button>
                    </div>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 overflow-hidden touch-none relative">
                        <VueSignaturePad width="100%" height="200px" ref="signaturePad" :options="{ penColor: '#000000', backgroundColor: '#fafafa' }" />
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="submitForm" 
                    :disabled="form.processing" 
                    class="w-full mt-6 px-6 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg transition-colors disabled:opacity-50 text-lg"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Kirim Daftar Hadir' }}
                </button>

            </div>
        </div>
    </div>
</template>

<style scoped>
.touch-none {
    touch-action: none;
}

:deep(.v-select-mobile .vs__dropdown-toggle) {
    padding: 8px 0;
    border-color: #d1d5db;
    border-radius: 0.75rem; /* xl */
}
:deep(.v-select-mobile.vs--open .vs__dropdown-toggle) {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}
:deep(.v-select-mobile .vs__search) {
    font-size: 16px; /* Mencegah zoom di iOS Safari */
}
</style>