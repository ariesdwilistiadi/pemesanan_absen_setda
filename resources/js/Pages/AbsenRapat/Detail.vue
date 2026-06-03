<script setup>
import { ref, nextTick } from 'vue';
import { router, Head, useForm, Link } from '@inertiajs/vue3';
import { VueSignaturePad } from 'vue-signature-pad';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import QrcodeVue from 'qrcode.vue';

// Import Vue Select
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import axios from 'axios';

const props = defineProps({
    rapat: Object,
    kehadiran: {
        type: Array,
        default: () => [],
    },
    masterDinas: {
        type: Array,
        default: () => [],
    }
});

const showModal = ref(false);
const showQrModal = ref(false);
const signaturePad = ref(null);

// URL Publik untuk absen
const publicUrl = ref('');

import { onMounted } from 'vue';
onMounted(() => {
    // Generate absolute URL for the QR code
    publicUrl.value = route('rapat.public.show', props.rapat.id);
    if (!publicUrl.value.startsWith('http')) {
        publicUrl.value = window.location.origin + publicUrl.value;
    }
});

const openQrModal = () => {
    showQrModal.value = true;
};

const closeQrModal = () => {
    showQrModal.value = false;
};

const printQr = () => {
    const rapatName = props.rapat?.nama_kegiatan || 'Rapat';
    const rapatTime = `${props.rapat?.tanggal} ${props.rapat?.pukul} WIB`;
    const qrValue = publicUrl.value;
    
    setTimeout(() => {
        const qrCanvas = document.querySelector('.qr-modal-canvas');
        let qrImage = '';
        
        if (qrCanvas && qrCanvas.tagName === 'CANVAS') {
            qrImage = qrCanvas.toDataURL();
        } else if (qrCanvas) {
            const svgData = new XMLSerializer().serializeToString(qrCanvas);
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            img.onload = function() {
                qrImage = canvas.toDataURL();
                openPrintWindow(qrImage, rapatName, rapatTime);
            };
            img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
        } else {
            qrImage = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(qrValue)}`;
            openPrintWindow(qrImage, rapatName, rapatTime);
        }
        
        if (qrImage && qrImage.startsWith('data:')) {
            openPrintWindow(qrImage, rapatName, rapatTime);
        }
    }, 100);
};

const openPrintWindow = (qrImage, rapatName, rapatTime) => {
    const printWindow = window.open('', '_blank');
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>QR Code - ${rapatName}</title>
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    background: white;
                }
                .container {
                    text-align: center;
                    border: 2px solid #333;
                    padding: 40px;
                    border-radius: 10px;
                    max-width: 450px;
                }
                h2 { color: #333; margin-top: 0; font-size: 24px; }
                .info { color: #666; font-size: 16px; margin: 10px 0; }
                .qr-container {
                    margin: 30px 0;
                    background: white;
                    padding: 20px;
                    border: 2px solid #ddd;
                    display: inline-block;
                }
                img { width: 280px; height: 280px; }
                .instruction { 
                    font-size: 14px; 
                    color: #333; 
                    margin-top: 20px;
                    font-weight: bold;
                }
                @media print {
                    body { padding: 0; margin: 0; }
                    .container { border: 2px solid #333; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>${rapatName}</h2>
                <div class="info">${rapatTime}</div>
                <div class="qr-container">
                    <img src="${qrImage}" alt="QR Code" />
                </div>
                <p class="instruction">📱 Scan QR Code dengan kamera HP Anda untuk mengisi daftar hadir</p>
            </div>
        </body>
        </html>
    `;
    printWindow.document.write(htmlContent);
    printWindow.document.close();
    setTimeout(() => printWindow.print(), 500);
};

// Form menggunakan useForm
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
const isSearchingNip = ref(false);

const openModal = async () => {
    form.reset();
    showModal.value = true;
    // Resize canvas setelah modal terbuka agar sesuai dengan layar HP
    await nextTick();
    if (signaturePad.value) {
        signaturePad.value.resizeCanvas();
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

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
            let pegawai = response.data.data;
            if (Array.isArray(pegawai)) pegawai = pegawai[0];
            
            if (pegawai && pegawai.data) pegawai = pegawai.data;
            if (pegawai && pegawai.data) pegawai = pegawai.data;

            if (pegawai) {
                if (pegawai.nama || pegawai.nama_pegawai || pegawai.nama_lengkap) {
                    form.nama = (pegawai.nama || pegawai.nama_pegawai || pegawai.nama_lengkap).trim();
                }
                if (pegawai.email && !form.email) form.email = pegawai.email;
            }
        }
    } catch (error) {
        console.error("Gagal mengambil data pegawai", error);
    } finally {
        isSearchingNip.value = false;
    }
};

const submitForm = () => {
    // Validasi Signature Pad
    const { isEmpty, data } = signaturePad.value.saveSignature();
    
    if (isEmpty) {
        alert("Silakan bubuhkan tanda tangan terlebih dahulu!");
        return;
    }
    
    // Cegah submit jika tipe internal tapi dinas belum dipilih
    if (form.tipe_peserta === 'internal' && !form.id_dinas) {
        alert("Silakan pilih Dinas Internal!");
        return;
    }
    
    form.signature = data;

    // Pastikan awalan rute ini sesuai dengan yang ada di web.php 
    // (Ganti 'absen.hadir.store' jika di web.php kamu pakainya 'absen')
    form.post(route('rapat.hadir.store', props.rapat.id), {
        preserveScroll: true,
        onSuccess: () => {
            alert("Data kehadiran berhasil disimpan!");
            closeModal();
        },
        onError: (errors) => {
            // Ini akan memunculkan pop-up pesan error dari Laravel
            console.error(errors);
            alert("GAGAL MENYIMPAN! Periksa error berikut:\n\n" + JSON.stringify(errors, null, 2));
        }
    });
};
</script>

<template>
    <Head :title="'Detail Rapat: ' + rapat.nama_kegiatan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <Link :href="route('rapat.index')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium mb-1 inline-block">&larr; Kembali ke Daftar Rapat</Link>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">{{ rapat.nama_kegiatan }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Tanggal: <span class="font-semibold">{{ rapat.tanggal }}</span> | Pukul: <span class="font-semibold">{{ rapat.pukul }} WIB</span></p>
                </div>
                <div class="flex gap-2">
                    <a :href="route('rapat.print', rapat.id)" target="_blank" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2.5 px-5 rounded-lg transition-colors shadow-sm text-sm whitespace-nowrap flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak
                    </a>
                    <a :href="route('rapat.public.show', rapat.id)" target="_blank" class="bg-green-100 border border-green-300 text-green-700 hover:bg-green-200 font-bold py-2.5 px-5 rounded-lg transition-colors shadow-sm text-sm whitespace-nowrap flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Buka Form Publik
                    </a>
                    <button @click="openQrModal" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2.5 px-5 rounded-lg transition-colors shadow-sm text-sm whitespace-nowrap flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        Tampilkan QR Code
                    </button>
                    <button @click="openModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-lg transition-colors shadow-sm text-sm whitespace-nowrap">
                        + Isi Daftar Hadir (Manual)
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <h3 class="text-lg font-bold text-gray-900">List Kehadiran Peserta</h3>
                    </div>

                    <div class="p-0 md:p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50">
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Tipe</th>
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Nama & Kontak</th>
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Instansi / Dinas</th>
                                        <th class="text-center py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Tanda Tangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="hadir in kehadiran" :key="hadir.id" class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3 px-4">
                                            <span v-if="hadir.tipe_peserta === 'internal'" class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">Internal</span>
                                            <span v-else class="px-2 py-1 bg-amber-100 text-amber-700 text-xs rounded-full font-medium">Eksternal</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-gray-900">{{ hadir.nama }}</div>
                                            <div class="text-xs text-gray-500">{{ hadir.telp }} | {{ hadir.email }}</div>
                                            <div v-if="hadir.nip" class="text-xs text-gray-400 mt-0.5">NIP: {{ hadir.nip }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600">
                                            {{ hadir.tipe_peserta === 'internal' ? (hadir.dinas ? hadir.dinas.nama_dinas : '-') : hadir.nama_external }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <img :src="hadir.signature" class="h-12 mx-auto object-contain bg-white border border-gray-200 p-1 rounded-md" alt="TTD" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="kehadiran.length === 0" class="text-center py-12 px-4">
                            <p class="text-gray-500 font-medium">Belum ada peserta yang mengisi daftar hadir.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4 sm:p-6 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[95vh] overflow-hidden flex flex-col">
                
                <div class="p-5 border-b border-gray-100 flex-shrink-0 bg-gray-50">
                    <h2 class="text-xl font-bold text-gray-900">Form Daftar Hadir</h2>
                    <p class="text-xs text-gray-500 mt-1">Gunakan jari untuk tanda tangan di layar HP.</p>
                </div>

                <div class="p-5 overflow-y-auto flex-grow space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Peserta <span class="text-red-500">*</span></label>
                        <select v-model="form.tipe_peserta" @change="handleTipePesertaChange" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 bg-white">
                            <option value="internal">Internal (Pegawai / Karyawan)</option>
                            <option value="eksternal">Eksternal (Tamu Instansi Luar)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="form.tipe_peserta === 'internal'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">NIP Karyawan</label>
                    <div class="relative">
                        <input v-model="form.nip" @blur="searchPegawai" type="text" placeholder="Ketik NIP dan tekan Tab" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" />
                        <div v-if="isSearchingNip" class="absolute right-3 top-3">
                            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        </div>
                    </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input v-model="form.nama" type="text" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" required />
                        </div>
                    </div>

                    <div v-if="form.tipe_peserta === 'internal'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Dinas Internal <span class="text-red-500">*</span></label>
                        <v-select 
                            v-model="form.id_dinas" 
                            :options="masterDinas" 
                            :reduce="dinas => dinas.id" 
                            label="nama_dinas" 
                            placeholder="-- Ketik untuk mencari dinas --"
                            class="bg-white"
                        >
                            <template #no-options>
                                Dinas tidak ditemukan.
                            </template>
                        </v-select>
                    </div>

                    <div v-else>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Asal Dinas / Instansi <span class="text-red-500">*</span></label>
                        <input v-model="form.nama_external" type="text" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" required />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP / WhatsApp <span class="text-red-500">*</span></label>
                            <input v-model="form.telp" type="tel" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input v-model="form.email" type="email" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" required />
                        </div>
                    </div>

                    <div class="mt-2">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-gray-700">Tanda Tangan <span class="text-red-500">*</span></label>
                            <button type="button" @click="clearSignature" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-md text-xs font-bold transition-colors border border-red-200">
                                Bersihkan (Ulangi)
                            </button>
                        </div>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 overflow-hidden touch-none relative">
                            <VueSignaturePad width="100%" height="200px" ref="signaturePad" :options="{ penColor: '#000000', backgroundColor: '#fafafa' }" />
                        </div>
                    </div>
                </div>

                <div class="p-5 border-t border-gray-100 flex justify-end space-x-3 bg-white flex-shrink-0">
                    <button type="button" @click="closeModal" class="px-5 py-2.5 text-gray-700 hover:bg-gray-100 border border-gray-300 rounded-lg font-medium transition-colors text-sm w-full md:w-auto">Batal</button>
                    <button type="button" @click="submitForm" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium shadow-sm transition-colors text-sm disabled:opacity-50 w-full md:w-auto">
                        Simpan Kehadiran
                    </button>
                </div>

            </div>
        </div>

        <!-- Modal QR Code -->
        <div v-if="showQrModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4 sm:p-6 backdrop-blur-sm" @click.self="closeQrModal">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full overflow-hidden flex flex-col p-6 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-2">QR Code Absensi</h3>
                <p class="text-sm text-gray-500 mb-6">Scan QR Code ini menggunakan HP peserta untuk mengisi daftar hadir secara mandiri.</p>
                
                <div class="flex justify-center mb-6">
                    <div class="bg-white p-4 rounded-xl border-4 border-gray-100 shadow-sm inline-block">
                        <QrcodeVue v-if="publicUrl" :value="publicUrl" :size="250" level="H" class="qr-modal-canvas" />
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 mb-6 flex items-center justify-between gap-2 overflow-hidden">
                    <span class="text-xs text-gray-600 truncate whitespace-nowrap">{{ publicUrl }}</span>
                </div>

                <div class="space-y-2">
                    <button @click="printQr" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak QR Code
                    </button>
                    <button @click="closeQrModal" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl font-bold transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Memastikan area canvas tidak ikut terscroll saat pengguna menggoreskan jari di HP */
.touch-none {
    touch-action: none;
}

/* Penyesuaian style untuk Vue-Select agar rapi mengikuti Tailwind */
:deep(.v-select .vs__dropdown-toggle) {
    padding: 6px 0;
    border-color: #d1d5db;
    border-radius: 0.5rem;
}
:deep(.v-select.vs--open .vs__dropdown-toggle) {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}
</style>