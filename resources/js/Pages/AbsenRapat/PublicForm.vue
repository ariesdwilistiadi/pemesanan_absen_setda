<script setup>
import { ref, nextTick, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { VueSignaturePad } from 'vue-signature-pad';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import axios from 'axios';

const props = defineProps({
    rapat: Object,
    masterDinas: {
        type: Array,
        default: () => [],
    }
});

const signaturePad = ref(null);

const isSearchingNip = ref(false);
const form = useForm({
    tipe_peserta: 'internal',
    nip: '',
    nama: '',
    jenis_kelamin: '',
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

const searchPegawai = async () => {
    if (!form.nip || form.nip.length < 5) return;
    
    isSearchingNip.value = true;
    try {
        const response = await axios.get(route('api.pegawai', form.nip));
        if (response.data && response.data.success) {
            // Menyesuaikan jika respons API dibungkus nested array/object
            let pegawai = response.data.data;
            if (Array.isArray(pegawai)) pegawai = pegawai[0];
            
            // Buka bungkusan JSON dari SIMPEG secara berlapis
            if (pegawai && pegawai.data) pegawai = pegawai.data;
            if (pegawai && pegawai.data) pegawai = pegawai.data;

            if (pegawai) {
                // Otomatis isikan form jika API mengembalikan data nama
                if (pegawai.nama || pegawai.nama_pegawai || pegawai.nama_lengkap) {
                    form.nama = (pegawai.nama || pegawai.nama_pegawai || pegawai.nama_lengkap).trim();
                }

                const email = pegawai.email || pegawai.email_pegawai || pegawai.email_personal;
                const phone = pegawai.ponsel || pegawai.telp || pegawai.telepon || pegawai.telepon_kantor || pegawai.no_hp || pegawai.nohp || pegawai.hp;
                const rawGender = pegawai.jenis_kelamin || pegawai.jk || pegawai.gender || pegawai.sex || pegawai.gender_text || pegawai.kelamin;
                const gender = rawGender ? String(rawGender).trim().toLowerCase() : '';
                const instansi = pegawai.instansi || pegawai.nama_instansi || pegawai.nama_dinas || pegawai.dinas || pegawai.unit_kerja || pegawai.nama_unit;

                if (email) form.email = email;
                if (phone) form.telp = phone;
                if (gender) {
                    if (/^(laki|male|m|pria)/.test(gender)) {
                        form.jenis_kelamin = 'laki-laki';
                    } else if (/^(perempuan|female|f|wanita)/.test(gender)) {
                        form.jenis_kelamin = 'perempuan';
                    } else {
                        form.jenis_kelamin = gender;
                    }
                }
                if (instansi) {
                    form.nama_external = typeof instansi === 'object' ? (instansi.nama_dinas || instansi.nama || instansi.label || '') : instansi;
                }

                // Jika API mengembalikan nama dinas internal, coba cocokkan dengan opsi masterDinas
                if (form.nama_external && masterDinas.length) {
                    const normalizedLabel = form.nama_external.toString().trim().toLowerCase();
                    const matched = masterDinas.find((dinas) => {
                        return [
                            dinas.nama_dinas,
                            dinas.nama,
                            dinas.label,
                        ].some(value => value && value.toString().trim().toLowerCase() === normalizedLabel);
                    });
                    if (matched) {
                        form.id_dinas = matched.id;
                    } else {
                        form.id_dinas = '';
                    }
                }
            }
        }
    } catch (error) {
        console.error("Gagal mengambil data pegawai", error);
    } finally {
        isSearchingNip.value = false;
    }
};

const submitForm = () => {
    const { isEmpty, data } = signaturePad.value.saveSignature();
    
    if (isEmpty) {
        alert("Silakan bubuhkan tanda tangan terlebih dahulu!");
        return;
    }
    
    if (form.tipe_peserta === 'internal' && !form.nama_external) {
        alert("Silakan isi nama unit kerja atau instansi internal!");
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

                <div v-if="form.tipe_peserta === 'internal'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP Pegawai <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input v-model="form.nip" @blur="searchPegawai" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600" placeholder="Ketik NIP lalu klik di luar kotak ini..." required />
                        <div v-if="isSearchingNip" class="absolute right-4 top-3.5">
                            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Nama akan terisi otomatis dari SIMPEG setelah Anda mengetikkan NIP.</p>

                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input v-model="form.nama" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600" required />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <input v-model="form.jenis_kelamin" type="text" placeholder="Laki-laki / Perempuan" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600" required />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ form.tipe_peserta === 'internal' ? 'Nama Unit Kerja / Instansi Internal' : 'Asal Dinas / Instansi' }}
                        <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.nama_external" type="text" :placeholder="form.tipe_peserta === 'internal' ? 'Contoh: Sekretariat Daerah' : 'Contoh: Dinas Kesehatan Kota'" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 bg-white" required />
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