<script setup>
import { ref } from 'vue';
import { router, Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import QrcodeVue from 'qrcode.vue';

const props = defineProps({
    rapats: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ tanggal: '' }),
    },
    canViewAll: {
        type: Boolean,
        default: false,
    },
});

const filterTanggal = ref(props.filters.tanggal || '');

// State Modal
const showModal = ref(false);
const isEdit = ref(false);

// Form menggunakan useForm dari Inertia
const form = useForm({
    id: null,
    nama_kegiatan: '',
    tanggal: '',
    pukul: '',
});

const showQrModal = ref(false);
const currentQrUrl = ref('');
const currentRapat = ref(null);

const showPublicQrModal = ref(false);
const publicListUrl = ref('');

const openPublicQr = () => {
    let url = route('rapat.public.list');
    if (!url.startsWith('http')) {
        url = window.location.origin + url;
    }
    publicListUrl.value = url;
    showPublicQrModal.value = true;
};

const closePublicQr = () => {
    showPublicQrModal.value = false;
};

const printPublicQr = () => {
    const title = 'Daftar Kegiatan Rapat (Publik)';
    const desc = 'Scan untuk melihat daftar rapat hari ini';
    const qrValue = publicListUrl.value;
    
    setTimeout(() => {
        const qrCanvas = document.querySelector('.public-qr-modal-canvas');
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
                openPrintWindow(qrImage, title, desc);
            };
            img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
        } else {
            qrImage = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(qrValue)}`;
            openPrintWindow(qrImage, title, desc);
        }
        
        if (qrImage && qrImage.startsWith('data:')) {
            openPrintWindow(qrImage, title, desc);
        }
    }, 100);
};

// Fungsi Filter Tanggal
const handleFilter = () => {
    router.get(route('rapat.index'), { tanggal: filterTanggal.value }, { preserveState: true, replace: true });
};

const openQrModal = (rapat) => {
    let url = route('rapat.public.show', rapat.id);
    if (!url.startsWith('http')) {
        url = window.location.origin + url;
    }

    currentQrUrl.value = url;
    currentRapat.value = rapat;
    showQrModal.value = true;
};

const closeQrModal = () => {
    showQrModal.value = false;
    currentQrUrl.value = '';
    currentRapat.value = null;
};

const printQr = () => {
    const rapatName = currentRapat.value?.nama_kegiatan || 'Rapat';
    const rapatTime = `${currentRapat.value?.tanggal} ${currentRapat.value?.pukul} WIB`;
    const qrValue = currentQrUrl.value;
    
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

// Buka Modal Buat
const openCreateModal = () => {
    isEdit.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

// Buka Modal Edit
const openEditModal = (rapat) => {
    isEdit.value = true;
    form.clearErrors();
    form.id = rapat.id;
    form.nama_kegiatan = rapat.nama_kegiatan;
    form.tanggal = rapat.tanggal;
    form.pukul = rapat.pukul;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

// Submit Form (Buat atau Edit)
const submitForm = () => {
    if (isEdit.value) {
        form.put(route('rapat.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('rapat.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

// Fungsi ke Halaman Detail
const goToDetail = (id) => {
    router.get(route('rapat.show', id));
};
</script>

<template>
    <Head title="Daftar Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Manajemen Daftar Rapat</h2>
                <div class="flex gap-2">
                    <button @click="openPublicQr" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        QR Code Publik
                    </button>
                    <a :href="route('rapat.public.list')" target="_blank" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Lihat Halaman Publik
                    </a>
                    <button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm">
                        + Buat Rapat Baru
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Daftar Kegiatan Rapat</h3>
                            <p class="text-sm text-gray-500">Kelola jadwal rapat dan lihat daftar hadir peserta.</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Filter Tanggal:</label>
                            <input type="date" v-model="filterTanggal" @change="handleFilter" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-600" />
                            <button v-if="filterTanggal" @click="filterTanggal = ''; handleFilter()" class="text-sm text-red-500 hover:text-red-700 font-medium ml-2">
                                Reset
                            </button>
                        </div>
                    </div>
                    <div class="px-6 pb-4">
                        <p class="text-sm text-gray-500">
                            <span v-if="canViewAll">Menampilkan semua rapat. Gunakan filter tanggal untuk mencari rapat tertentu.</span>
                            <span v-else>Menampilkan rapat hari ini saja. Rapat yang telah berlalu tidak ditampilkan lagi tanpa filter.</span>
                        </p>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">No</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Nama Kegiatan</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Tanggal</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Pukul</th>
                                        <th class="text-center py-3 px-2 font-semibold text-gray-700">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(rapat, index) in rapats" :key="rapat.id" class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3 px-2 text-gray-600">{{ index + 1 }}</td>
                                        <td class="py-3 px-2 font-semibold text-gray-900">{{ rapat.nama_kegiatan }}</td>
                                        <td class="py-3 px-2 text-gray-600">{{ rapat.tanggal }}</td>
                                        <td class="py-3 px-2 text-gray-600 font-mono">{{ rapat.pukul }} WIB</td>
                                        <td class="py-3 px-2 flex justify-center gap-2">
                                            <button @click="openEditModal(rapat)" class="px-3 py-1.5 bg-amber-100 text-amber-700 hover:bg-amber-200 rounded-md text-xs font-bold transition-colors">
                                                Edit
                                            </button>
                                            <button @click="goToDetail(rapat.id)" class="px-3 py-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md text-xs font-bold transition-colors">
                                                Lihat Detail
                                            </button>
                                            <button @click="openQrModal(rapat)" class="px-3 py-1.5 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded-md text-xs font-bold transition-colors">
                                                QR Code
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="rapats.length === 0" class="text-center py-12">
                            <p class="text-gray-500 font-medium">Belum ada jadwal rapat yang ditemukan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900">{{ isEdit ? 'Edit Data Rapat' : 'Buat Rapat Baru' }}</h2>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kegiatan <span class="text-red-500">*</span></label>
                            <input v-model="form.nama_kegiatan" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" required />
                            <span v-if="form.errors.nama_kegiatan" class="text-xs text-red-500">{{ form.errors.nama_kegiatan }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                <input v-model="form.tanggal" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" required />
                                <span v-if="form.errors.tanggal" class="text-xs text-red-500">{{ form.errors.tanggal }}</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pukul <span class="text-red-500">*</span></label>
                                <input v-model="form.pukul" type="time" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" required />
                                <span v-if="form.errors.pukul" class="text-xs text-red-500">{{ form.errors.pukul }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-100 flex justify-end space-x-3 bg-gray-50">
                        <button type="button" @click="closeModal" class="px-5 py-2.5 text-gray-700 hover:bg-gray-200 bg-white border border-gray-300 rounded-lg font-medium transition-colors text-sm">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium shadow-sm transition-colors text-sm disabled:opacity-50">
                            {{ isEdit ? 'Simpan Perubahan' : 'Buat Rapat' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showQrModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4 sm:p-6 backdrop-blur-sm" @click.self="closeQrModal">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full overflow-hidden flex flex-col p-6 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-2">QR Code Absensi</h3>
                <p class="text-sm text-gray-500 mb-6">Scan QR Code ini menggunakan HP peserta untuk mengisi daftar hadir secara mandiri.</p>

                <div class="flex justify-center mb-6">
                    <div class="bg-white p-4 rounded-xl border-4 border-gray-100 shadow-sm inline-block">
                        <QrcodeVue v-if="currentQrUrl" :value="currentQrUrl" :size="250" level="H" class="qr-modal-canvas" />
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 mb-6 flex items-center justify-between gap-2 overflow-hidden">
                    <span class="text-xs text-gray-600 truncate whitespace-nowrap">{{ currentQrUrl }}</span>
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

        <!-- Modal QR Code Halaman Publik -->
        <div v-if="showPublicQrModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4 sm:p-6 backdrop-blur-sm" @click.self="closePublicQr">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full overflow-hidden flex flex-col p-6 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-2">QR Code Halaman Publik</h3>
                <p class="text-sm text-gray-500 mb-6">Scan QR Code ini untuk melihat daftar semua rapat aktif hari ini.</p>

                <div class="flex justify-center mb-6">
                    <div class="bg-white p-4 rounded-xl border-4 border-gray-100 shadow-sm inline-block">
                        <QrcodeVue v-if="publicListUrl" :value="publicListUrl" :size="250" level="H" class="public-qr-modal-canvas" />
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 mb-6 flex items-center justify-between gap-2 overflow-hidden">
                    <span class="text-xs text-gray-600 truncate whitespace-nowrap">{{ publicListUrl }}</span>
                </div>

                <div class="space-y-2">
                    <button @click="printPublicQr" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak QR Code
                    </button>
                    <button @click="closePublicQr" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl font-bold transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
