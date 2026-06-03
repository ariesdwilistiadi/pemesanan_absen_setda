<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import QrcodeVue from 'qrcode.vue';

const props = defineProps({
    rapats: {
        type: Array,
        default: () => [],
    }
});

const showQrModal = ref(false);
const currentQrUrl = ref('');
const currentRapat = ref(null);

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

const goToForm = (id) => {
    window.location.href = route('rapat.public.show', id);
};

const printQr = () => {
    const printWindow = window.open('', '_blank');
    const rapatName = currentRapat.value?.nama_kegiatan || 'Rapat';
    const rapatTime = `${currentRapat.value?.tanggal} ${currentRapat.value?.pukul} WIB`;
    const qrValue = currentQrUrl.value;
    
    // Tunggu canvas dari QrcodeVue di-render, ambil SVG-nya
    setTimeout(() => {
        const qrCanvas = document.querySelector('.qr-modal-canvas');
        let qrImage = '';
        
        if (qrCanvas && qrCanvas.tagName === 'CANVAS') {
            // Jika SVG, convert ke data URL
            qrImage = qrCanvas.toDataURL();
        } else if (qrCanvas) {
            // Jika SVG element
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
            // Fallback: generate QR via API
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
</script>

<template>
    <Head title="Daftar Kegiatan Rapat - Absensi" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-indigo-50">
        <!-- Header -->
        <div class="bg-white border-b border-gray-100 sticky top-0 z-40">
            <div class="max-w-6xl mx-auto px-4 py-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Daftar Kegiatan Rapat</h1>
                        <p class="text-gray-600 mt-1">Silakan pilih kegiatan rapat untuk mengisi daftar hadir</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="text-sm text-gray-500 bg-blue-50 px-4 py-3 rounded-lg border border-blue-100">
                            <strong>ℹ️ Info:</strong> Rapat yang ditampilkan hanya yang aktif hari ini (4 jam setelah mulai)
                        </div>
                        <a href="/rapat" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm inline-flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            Kembali ke Halaman Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-6xl mx-auto px-4 py-12">
            <div v-if="rapats.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="rapat in rapats" :key="rapat.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-shadow overflow-hidden">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-4">
                        <h3 class="text-lg font-bold text-white truncate">{{ rapat.nama_kegiatan }}</h3>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-gray-700 font-medium">{{ rapat.tanggal }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-700 font-medium">{{ rapat.pukul }} WIB</span>
                            </div>
                        </div>

                        <!-- QR Code Preview -->
                        <div class="bg-gray-50 rounded-xl p-4 flex justify-center border-2 border-dashed border-gray-200">
                            <div class="inline-block">
                                <QrcodeVue :value="route('rapat.public.show', rapat.id)" :size="120" level="H" />
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="space-y-2 pt-2">
                            <button @click="goToForm(rapat.id)" class="w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Buka Formulir Absensi
                            </button>
                            <button @click="openQrModal(rapat)" class="w-full px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-lg transition-colors text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Perbesar QR Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak Ada Kegiatan Rapat Aktif</h3>
                <p class="text-gray-600">Saat ini tidak ada kegiatan rapat yang sedang berlangsung. Silakan cek kembali nanti.</p>
            </div>
        </div>

        <!-- QR Modal -->
        <div v-if="showQrModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4 sm:p-6 backdrop-blur-sm" @click.self="closeQrModal">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full overflow-hidden flex flex-col p-6 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-2">QR Code Absensi</h3>
                <p class="text-sm text-gray-500 mb-2">{{ currentRapat?.nama_kegiatan }}</p>
                <p class="text-xs text-gray-400 mb-6">Scan dengan kamera HP Anda</p>

                <div class="flex justify-center mb-6 bg-gray-50 p-6 rounded-xl">
                    <div class="inline-block">
                        <QrcodeVue v-if="currentQrUrl" :value="currentQrUrl" :size="300" level="H" class="qr-modal-canvas" />
                    </div>
                </div>

                <div class="space-y-2">
                    <button @click="printQr" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak QR Code
                    </button>
                    <button @click="closeQrModal" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg font-bold transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
