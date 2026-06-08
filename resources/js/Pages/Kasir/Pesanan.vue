<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    transaksis: {
        type: Array,
        default: () => [],
    }
});

// State untuk real-time
const isConnected = ref(false);
const lastCheckedId = ref(0);
const newOrderCount = ref(0);
const showNewOrderBadge = ref(false);
const audioContext = ref(null);
const notificationPermission = ref('default');

// Request browser notification permission
const requestNotificationPermission = async () => {
    if (!('Notification' in window)) {
        console.log('Browser tidak mendukung notifications');
        return;
    }

    if (Notification.permission === 'granted') {
        notificationPermission.value = 'granted';
        return;
    }

    if (Notification.permission !== 'denied') {
        const permission = await Notification.requestPermission();
        notificationPermission.value = permission;
    }
};

// Show browser notification
const showBrowserNotification = (title, body) => {
    if (Notification.permission === 'granted') {
        const notification = new Notification(title, {
            body: body,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'pesanan-baru',
            requireInteraction: true,
            vibrate: [200, 100, 200],
            sound: '/notification.mp3'
        });

        // Click notification to focus window
        notification.onclick = () => {
            window.focus();
            notification.close();
        };

        // Auto close after 10 seconds
        setTimeout(() => notification.close(), 10000);
    }
};

// Audio notification sound
const playNotificationSound = () => {
    try {
        // Create audio context if not exists
        if (!audioContext.value) {
            audioContext.value = new (window.AudioContext || window.webkitAudioContext)();
        }

        const ctx = audioContext.value;
        const oscillator = ctx.createOscillator();
        const gainNode = ctx.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);

        // Ding-dong sound pattern
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(880, ctx.currentTime); // A5
        gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);

        oscillator.start(ctx.currentTime);
        oscillator.stop(ctx.currentTime + 0.15);

        // Second tone (ding)
        setTimeout(() => {
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(1318.51, ctx.currentTime); // E6
            gain2.gain.setValueAtTime(0.3, ctx.currentTime);
            gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
            osc2.start(ctx.currentTime);
            osc2.stop(ctx.currentTime + 0.3);
        }, 200);

    } catch (e) {
        console.warn('Audio not supported:', e);
    }
};

// Polling interval
let pollingInterval = null;
const POLLING_INTERVAL = 3000; // 3 detik

// Start polling for new orders
const startPolling = () => {
    if (pollingInterval) return;

    isConnected.value = true;
    console.log('🔄 Started polling for new orders...');

    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(
                route('kasir.cek-pesanan') + '?last_id=' + lastCheckedId.value,
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            if (response.ok) {
                const data = await response.json();

                if (data.transaksis && data.transaksis.length > 0) {
                    // Ada pesanan baru!
                    newOrderCount.value += data.transaksis.length;
                    showNewOrderBadge.value = true;

                    // Play notification sound
                    playNotificationSound();

                    // Browser notification
                    const latestOrder = data.transaksis[0];
                    showBrowserNotification(
                        '🔔 Pesanan Baru!',
                        `${latestOrder.nama} - Meja: ${latestOrder.nomor_meja || 'Takeaway'}`
                    );

                    // Vibrate if supported (mobile)
                    if (navigator.vibrate) {
                        navigator.vibrate([200, 100, 200]);
                    }

                    // Update last checked ID
                    const maxId = Math.max(...data.transaksis.map(t => t.id));
                    if (maxId > lastCheckedId.value) {
                        lastCheckedId.value = maxId;
                    }

                    console.log('🔔 New orders detected:', data.count);
                }
            }
        } catch (error) {
            console.warn('Polling error:', error);
        }
    }, POLLING_INTERVAL);
};

// Stop polling
const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
    isConnected.value = false;
    console.log('⏹️ Stopped polling');
};

// Reload page to get new orders
const reloadOrders = () => {
    newOrderCount.value = 0;
    showNewOrderBadge.value = false;
    router.reload({ only: ['transaksis'], preserveScroll: true });
};

// Initialize
onMounted(() => {
    if (props.transaksis.length > 0) {
        lastCheckedId.value = Math.max(...props.transaksis.map(t => t.id));
    }
    // Request browser notification permission
    requestNotificationPermission();
    startPolling();
});

onUnmounted(() => {
    stopPolling();
});

// Format currency
const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};

// Filter orders by status
const pendingOrders = computed(() => props.transaksis.filter(t => t.status === 'pending' || !t.status));
const processOrders = computed(() => props.transaksis.filter(t => t.status === 'diproses'));
const completedOrders = computed(() => props.transaksis.filter(t => t.status === 'selesai'));

// Update status function
const updateStatus = (id, newStatus) => {
    let confirmMsg = `Ubah status pesanan menjadi ${newStatus.toUpperCase()}?`;
    if (newStatus === 'batal') {
        confirmMsg = 'Apakah Anda yakin ingin membatalkan pesanan ini? Stok akan dikembalikan.';
    }

    if (confirm(confirmMsg)) {
        router.patch(route('kasir.update-status', id), { status: newStatus }, {
            preserveScroll: true,
            onSuccess: () => {
                // Update lastCheckedId if needed
                if (newStatus === 'selesai') {
                    const trx = props.transaksis.find(t => t.id === id);
                    if (trx && trx.id > lastCheckedId.value) {
                        lastCheckedId.value = trx.id;
                    }
                }
            },
            onError: (errors) => alert('Gagal update status: ' + JSON.stringify(errors))
        });
    }
};

const deleteTransaksi = (id) => {
    if (confirm('Hapus transaksi secara permanen?')) {
        router.delete(route('kasir.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Daftar Pesanan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">Daftar Pesanan (Dapur / Kasir)</h2>
                    <!-- Connection Status Badge -->
                    <div class="flex items-center gap-4">
                    <!-- Connection Status Badge -->
                    <div class="flex items-center gap-2">
                        <span
                            class="px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5"
                            :class="isConnected ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                        >
                            <span
                                class="w-2 h-2 rounded-full"
                                :class="isConnected ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"
                            ></span>
                            {{ isConnected ? 'Live' : 'Offline' }}
                        </span>
                        <!-- Enable Notification Button -->
                        <button
                            v-if="notificationPermission !== 'granted'"
                            @click="requestNotificationPermission"
                            class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 hover:bg-blue-200 flex items-center gap-1.5"
                            title="Aktifkan notifikasi browser"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Aktifkan Notifikasi
                        </button>
                    </div>
                </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- New Order Badge -->
                    <button
                        v-if="showNewOrderBadge"
                        @click="reloadOrders"
                        class="relative bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg animate-pulse flex items-center gap-2 transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span>{{ newOrderCount }} Pesanan Baru!</span>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-white text-red-600 rounded-full text-xs font-bold flex items-center justify-center">
                            {{ newOrderCount }}
                        </span>
                    </button>
                    <button @click="router.get(route('kasir.index'))" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg font-medium shadow-sm transition-colors text-sm">
                        Kembali ke POS Kasir
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- KANBAN: PENDING -->
                    <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-200 h-[calc(100vh-12rem)] flex flex-col">
                        <div class="flex items-center justify-between mb-4 px-2">
                            <h3 class="font-black text-lg text-slate-800 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.6)] animate-pulse"></span>
                                PESANAN BARU
                            </h3>
                            <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ pendingOrders.length }}</span>
                        </div>

                        <div class="overflow-y-auto pr-2 space-y-4 flex-grow">
                            <div v-for="trx in pendingOrders" :key="trx.id" class="bg-white rounded-xl shadow-sm border border-red-200 p-4 hover:shadow-md transition-shadow relative overflow-hidden group">
                                <!-- Pulse animation for new orders -->
                                <div class="absolute inset-0 bg-red-500/5 animate-ping rounded-xl" v-if="trx.created_at && isNewOrder(trx.created_at)"></div>

                                <div class="absolute top-0 left-0 w-1.5 h-full bg-red-500"></div>

                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="text-xs font-mono text-slate-400 mb-1">{{ trx.no_transaksi }}</div>
                                        <div class="font-bold text-slate-900">{{ trx.nama }}</div>
                                        <div class="text-xs font-semibold text-indigo-600 mt-1">Meja: {{ trx.nomor_meja || 'Takeaway' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500">{{ formatTime(trx.tanggal_transaksi) }}</div>
                                    </div>
                                </div>

                                <div class="bg-slate-50 rounded-lg p-3 mb-3 text-sm">
                                    <ul class="space-y-1">
                                        <li v-for="item in trx.details" :key="item.id" class="flex justify-between">
                                            <span class="text-slate-700"><span class="font-bold mr-1">{{ item.jumlah }}x</span> {{ item.produk?.nama_barang || 'Produk Dihapus' }}</span>
                                        </li>
                                    </ul>
                                    <div v-if="trx.keterangan" class="mt-2 pt-2 border-t border-slate-200 text-xs italic text-slate-500">
                                        "{{ trx.keterangan }}"
                                    </div>
                                </div>

                                <div class="flex gap-2 mt-4">
                                    <button @click="updateStatus(trx.id, 'diproses')" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-lg text-sm transition-colors shadow-sm">
                                        Proses Pesanan
                                    </button>
                                    <button @click="updateStatus(trx.id, 'batal')" class="px-3 bg-white border border-red-200 text-red-600 hover:bg-red-50 font-bold py-2 rounded-lg text-sm transition-colors" title="Batalkan Pesanan">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div v-if="pendingOrders.length === 0" class="text-center py-10 text-slate-400 text-sm font-medium border-2 border-dashed border-slate-200 rounded-xl">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span>Belum ada pesanan baru.<br/><span class="text-xs">Akan otomatis muncul saat ada pesanan masuk...</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KANBAN: DIPROSES -->
                    <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-200 h-[calc(100vh-12rem)] flex flex-col">
                        <div class="flex items-center justify-between mb-4 px-2">
                            <h3 class="font-black text-lg text-slate-800 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.6)]"></span>
                                SEDANG DIPROSES
                            </h3>
                            <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ processOrders.length }}</span>
                        </div>

                        <div class="overflow-y-auto pr-2 space-y-4 flex-grow">
                            <div v-for="trx in processOrders" :key="trx.id" class="bg-white rounded-xl shadow-sm border border-amber-200 p-4 hover:shadow-md transition-shadow relative overflow-hidden group">
                                <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-400"></div>

                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="text-xs font-mono text-slate-400 mb-1">{{ trx.no_transaksi }}</div>
                                        <div class="font-bold text-slate-900">{{ trx.nama }}</div>
                                        <div class="text-xs font-semibold text-indigo-600 mt-1">Meja: {{ trx.nomor_meja || 'Takeaway' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500">{{ formatTime(trx.tanggal_transaksi) }}</div>
                                    </div>
                                </div>

                                <div class="bg-slate-50 rounded-lg p-3 mb-3 text-sm border border-slate-100">
                                    <ul class="space-y-1">
                                        <li v-for="item in trx.details" :key="item.id" class="flex justify-between">
                                            <span class="text-slate-700"><span class="font-bold mr-1">{{ item.jumlah }}x</span> {{ item.produk?.nama_barang || 'Produk Dihapus' }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="flex gap-2 mt-4">
                                    <button @click="updateStatus(trx.id, 'selesai')" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 rounded-lg text-sm transition-colors shadow-sm">
                                        Selesai & Sajikan
                                    </button>
                                </div>
                            </div>

                            <div v-if="processOrders.length === 0" class="text-center py-10 text-slate-400 text-sm font-medium border-2 border-dashed border-slate-200 rounded-xl">
                                Tidak ada pesanan diproses.
                            </div>
                        </div>
                    </div>

                    <!-- KANBAN: SELESAI -->
                    <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-200 h-[calc(100vh-12rem)] flex flex-col">
                        <div class="flex items-center justify-between mb-4 px-2">
                            <h3 class="font-black text-lg text-slate-800 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                                SELESAI
                            </h3>
                            <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ completedOrders.length }}</span>
                        </div>

                        <div class="overflow-y-auto pr-2 space-y-4 flex-grow">
                            <div v-for="trx in completedOrders" :key="trx.id" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-4 relative overflow-hidden opacity-75 hover:opacity-100 transition-opacity">
                                <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>

                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="text-[11px] font-mono text-slate-400 mb-0.5">{{ trx.no_transaksi }}</div>
                                        <div class="font-bold text-slate-700 text-sm">{{ trx.nama }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-emerald-600">{{ formatRupiah(trx.total_harga) }}</div>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500 flex justify-between items-center border-t border-slate-100 pt-2 mt-2">
                                    <span>{{ trx.total_item }} items</span>
                                    <button @click="deleteTransaksi(trx.id)" class="text-slate-400 hover:text-red-500" title="Hapus Riwayat">
                                        Hapus
                                    </button>
                                </div>
                            </div>

                            <div v-if="completedOrders.length === 0" class="text-center py-10 text-slate-400 text-sm font-medium border-2 border-dashed border-slate-200 rounded-xl">
                                Belum ada pesanan selesai.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
// Helper functions di luar setup
function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function isNewOrder(createdAt) {
    // Order dianggap baru jika kurang dari 30 detik yang lalu
    const created = new Date(createdAt).getTime();
    const now = Date.now();
    return (now - created) < 30000;
}
</script>

<style scoped>
/* Animation for new order pulse */
@keyframes new-order-pulse {
    0%, 100% {
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
}

.animate-new-order {
    animation: new-order-pulse 1.5s ease-in-out infinite;
}

/* Badge animation */
@keyframes badge-bounce {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.animate-badge-bounce {
    animation: badge-bounce 0.5s ease-in-out infinite;
}
</style>