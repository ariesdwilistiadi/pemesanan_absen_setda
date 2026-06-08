<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router, Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import axios from 'axios';

// Proteksi Halaman Khusus Kasir
const authUser = usePage().props.auth.user;
const userPermissions = authUser?.permissions || [];
const layout = authUser ? AuthenticatedLayout : PublicLayout;

const props = defineProps({
    produks: {
        type: Array,
        default: () => [],
    },
    ruangans: {
        type: Array,
        default: () => [],
    },
    transaksis: {
        type: Array,
        default: () => [],
    },
    initialPesertaId: {
        type: [String, Number],
        default: null
    },
    instansi: {
        type: Object,
        default: () => null
    }
});

// State untuk tab produk
const activeTab = ref('lokal'); // 'lokal' atau 'external'
const produkEksternal = ref([]);
const isLoadingExternal = ref(false);
const externalError = ref('');

// Fetch produk dari API external
const fetchProdukEksternal = async () => {
    isLoadingExternal.value = true;
    externalError.value = '';

    try {
        const response = await axios.get(route('produk-eksternal.index'));
        if (response.data.success) {
            produkEksternal.value = response.data.produks;
        } else {
            externalError.value = response.data.message || 'Gagal mengambil produk';
        }
    } catch (error) {
        console.error('Error fetch produk external:', error);
        externalError.value = 'Koneksi ke server gagal';
    } finally {
        isLoadingExternal.value = false;
    }
};

// Watch tab change - fetch external products when tab switched
watch(activeTab, (newTab) => {
    if (newTab === 'external' && produkEksternal.value.length === 0) {
        fetchProdukEksternal();
    }
});

// State for search and participant
const searchNip = ref('');
const isSearching = ref(false);
const peserta = ref(null);
const searchError = ref('');

onMounted(() => {
    if (props.initialPesertaId) {
        handleSearchPesertaById(props.initialPesertaId);
    }
});

// Search participant by ID
const handleSearchPesertaById = async (id) => {
    isSearching.value = true;
    searchError.value = '';
    peserta.value = null;

    try {
        const response = await fetch(route('kasir.cari-peserta', { id: id }));
        const result = await response.json();

        if (result.success) {
            peserta.value = result.data;
            if (peserta.value.nip) {
                searchNip.value = peserta.value.nip;
            }
        } else {
            searchError.value = result.message || 'Peserta tidak ditemukan.';
        }
    } catch (error) {
        searchError.value = 'Terjadi kesalahan saat mencari data.';
    } finally {
        isSearching.value = false;
    }
};

// Search participant by NIP
const cart = ref([]);
const idRuangan = ref('');
const nomorMeja = ref('');
const keterangan = ref('');

// State for Payment
const metodePembayaran = ref('qris');
const jumlahBayar = ref(0);

// Format currency
const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};

// Search participant by NIP
const handleSearchPeserta = async () => {
    if (!searchNip.value) return;

    isSearching.value = true;
    searchError.value = '';
    peserta.value = null;

    try {
        const response = await fetch(route('kasir.cari-peserta', { nip: searchNip.value }));
        const result = await response.json();

        if (result.success) {
            peserta.value = result.data;
        } else {
            searchError.value = result.message || 'Peserta tidak ditemukan.';
        }
    } catch (error) {
        searchError.value = 'Terjadi kesalahan saat mencari data.';
    } finally {
        isSearching.value = false;
    }
};

// Set pelanggan sebagai Tamu
const setSebagaiTamu = () => {
    searchNip.value = '';
    searchError.value = '';

    peserta.value = {
        id: null,
        nip: '-',
        nama: 'Tamu Umum'
    };
};

// Add to cart (support both local and external products)
const addToCart = (produk) => {
    // Untuk external, stok tidak ada atau unlimited
    if (produk.stok !== undefined && produk.stok <= 0) {
        alert('Stok produk habis!');
        return;
    }

    const existingItem = cart.value.find(item => item.id === produk.id && item.sumber === produk.sumber);

    if (existingItem) {
        if (produk.stok !== undefined && existingItem.jumlah >= produk.stok) {
            alert('Melebihi stok yang tersedia!');
            return;
        }
        existingItem.jumlah += 1;
    } else {
        cart.value.push({
            id: produk.id,
            kode_barang: produk.kode_barang || '',
            nama_barang: produk.nama_barang,
            harga_jual: produk.harga_jual,
            jumlah: 1,
            stok_max: produk.stok || 999,
            sumber: produk.sumber || 'lokal'
        });
    }
};

// Remove from cart
const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

// Decrease qty
const decreaseQty = (index) => {
    if (cart.value[index].jumlah > 1) {
        cart.value[index].jumlah -= 1;
    } else {
        removeFromCart(index);
    }
};

// Increase qty
const increaseQty = (index) => {
    const item = cart.value[index];
    if (item.stok_max && item.jumlah < item.stok_max) {
        item.jumlah += 1;
    } else if (!item.stok_max) {
        item.jumlah += 1;
    } else {
        alert('Melebihi stok yang tersedia!');
    }
};

// Filtered Products
const searchProduk = ref('');
const filteredProduksLokal = computed(() => {
    if (!searchProduk.value) {
        return props.produks;
    }
    const searchTerm = searchProduk.value.toLowerCase();
    return props.produks.filter(produk =>
        (produk.nama_barang && String(produk.nama_barang).toLowerCase().includes(searchTerm)) ||
        (produk.kode_barang && String(produk.kode_barang).toLowerCase().includes(searchTerm))
    );
});

const filteredProduksExternal = computed(() => {
    if (!searchProduk.value) {
        return produkEksternal.value;
    }
    const searchTerm = searchProduk.value.toLowerCase();
    return produkEksternal.value.filter(produk =>
        (produk.nama_barang && String(produk.nama_barang).toLowerCase().includes(searchTerm)) ||
        (produk.kode_barang && String(produk.kode_barang).toLowerCase().includes(searchTerm))
    );
});

// Current products based on active tab
const currentProduks = computed(() => {
    return activeTab.value === 'lokal' ? filteredProduksLokal.value : filteredProduksExternal.value;
});

// Calculate total
const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => total + (item.harga_jual * item.jumlah), 0);
});

// Calculate total items
const cartTotalItems = computed(() => {
    return cart.value.reduce((total, item) => total + item.jumlah, 0);
});

// Calculate kembalian
const kembalian = computed(() => {
    if (metodePembayaran.value === 'qris') return 0;
    const diff = jumlahBayar.value - cartTotal.value;
    return diff > 0 ? diff : 0;
});

// Process Checkout
const processCheckout = () => {
    if (!peserta.value) {
        alert('Silakan cari peserta terlebih dahulu sebelum memproses transaksi.');
        return;
    }

    if (cart.value.length === 0) {
        alert('Keranjang belanja kosong.');
        return;
    }

    if (confirm(`Proses transaksi untuk ${peserta.value.nama} dengan total ${formatRupiah(cartTotal.value)}?`)) {
        router.post(route('kasir.store'), {
            id_absen_rapats: peserta.value.id,
            nip: peserta.value.nip,
            nama: peserta.value.nama,
            id_ruangan: idRuangan.value || null,
            nomor_meja: nomorMeja.value,
            keterangan: keterangan.value,
            metode_pembayaran: 'qris',
            jumlah_bayar: cartTotal.value,
            cart: cart.value
        }, {
            preserveScroll: true,
            onSuccess: (page) => {
                const printId = page.props.flash?.print_id;
                if (printId) {
                    window.open(route('kasir.print', printId), '_blank', 'width=400,height=600');
                }

                let successMsg = '';
                if (metodePembayaran.value === 'qris') {
                    successMsg = 'Pemesanan sudah masuk, selamat menunggu';
                } else {
                    successMsg = 'Transaksi Berhasil!';
                    if (metodePembayaran.value === 'cash') {
                        successMsg += `\nKembalian: ${formatRupiah(kembalian.value)}`;
                    }
                }
                alert(successMsg);

                // Reset form
                cart.value = [];
                peserta.value = null;
                searchNip.value = '';
                idRuangan.value = '';
                nomorMeja.value = '';
                keterangan.value = '';
                metodePembayaran.value = 'cash';
                jumlahBayar.value = 0;
            },
            onError: (errors) => {
                alert('Gagal memproses transaksi: \n' + JSON.stringify(errors, null, 2));
            }
        });
    }
};

// Delete transaction (for kasir admin)
const deleteTransaksi = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini? Stok produk akan dikembalikan.')) {
        router.delete(route('kasir.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Kasir & Pemesanan" />

    <component :is="layout">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Kasir & Pemesanan Produk</h2>
                <button v-if="authUser" @click="router.get(route('kasir.pesanan'))" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg font-medium shadow-sm transition-colors text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Lihat Daftar Pesanan (Dapur)
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <div class="lg:col-span-8 space-y-6">

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Data Pemesan</h3>
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-grow flex gap-2">
                                    <input
                                        v-model="searchNip"
                                        @keyup.enter="handleSearchPeserta"
                                        type="text"
                                        autofocus
                                        placeholder="Masukkan NIP peserta..."
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                    <button
                                        @click="handleSearchPeserta"
                                        :disabled="isSearching"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors disabled:opacity-50"
                                    >
                                        {{ isSearching ? 'Mencari...' : 'Cari' }}
                                    </button>
                                </div>

                                <button
                                    @click="setSebagaiTamu"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium transition-colors whitespace-nowrap shadow-sm"
                                >
                                    + Pesan sbg Tamu
                                </button>
                            </div>

                            <div v-if="searchError" class="mt-3 text-red-500 text-sm">
                                {{ searchError }}
                            </div>

                            <div v-if="peserta" class="mt-4 p-4 bg-green-50 rounded-lg border border-green-100 flex items-start justify-between">
                                <div class="w-full mr-4">
                                    <div class="text-sm text-green-800 font-semibold mb-1">
                                        {{ peserta.id ? 'Peserta Ditemukan' : 'Mode Tamu' }}
                                    </div>

                                    <input v-if="!peserta.id" v-model="peserta.nama" type="text" class="w-full mt-1 px-3 py-1.5 border border-green-300 rounded-md text-sm focus:ring-green-500 bg-white" placeholder="Ketik nama tamu (opsional)">

                                    <div v-else class="text-lg font-bold text-gray-900">{{ peserta.nama }}</div>

                                    <div v-if="peserta.id" class="text-sm text-gray-600 mt-1">NIP: {{ peserta.nip || '-' }} | Email: {{ peserta.email || '-' }}</div>
                                </div>
                                <button @click="peserta = null; searchNip = ''" class="text-gray-400 hover:text-gray-600 mt-1">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                       <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
							<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4">

                                <!-- Tab Buttons -->
                                <div class="flex items-center gap-2">
                                    <h3 class="text-2xl font-black text-slate-900 mr-4">Katalog Produk</h3>
                                    <div class="flex bg-gray-100 rounded-xl p-1">
                                        <button
                                            @click="activeTab = 'lokal'"
                                            class="px-4 py-2 rounded-lg font-semibold text-sm transition-all"
                                            :class="activeTab === 'lokal' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                                        >
                                            🏪 Menu Sekda
                                        </button>
                                        <button
                                            @click="activeTab = 'external'"
                                            class="px-4 py-2 rounded-lg font-semibold text-sm transition-all flex items-center gap-2"
                                            :class="activeTab === 'external' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                                        >
                                            🛒 Menu Koperasi Dharma Wanita
                                        </button>
                                    </div>
                                </div>

                                <div class="w-full md:w-64">
                                    <div class="relative">
                                        <input
                                            v-model="searchProduk"
                                            type="text"
                                            placeholder="Cari produk..."
                                            class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-10 text-sm"
                                        >
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <button
                                            v-if="searchProduk"
                                            @click="searchProduk = ''"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
							</div>

                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm text-slate-400 font-medium">
                                    {{ activeTab === 'lokal' ? filteredProduksLokal.length : filteredProduksExternal.length }} Produk Tersedia
                                </span>
                                <span v-if="activeTab === 'external'" class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                                    🔄 Data dari Server External
                                </span>
                            </div>


							<!-- Loading State for External -->
							<div v-if="activeTab === 'external' && isLoadingExternal" class="text-center py-20">
								<div class="animate-spin w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full mx-auto mb-4"></div>
								<p class="text-slate-500 font-medium">Memuat produk dari server...</p>
							</div>

							<!-- Error State for External -->
							<div v-else-if="activeTab === 'external' && externalError" class="text-center py-20 bg-red-50 rounded-xl">
								<p class="text-red-500 font-medium mb-2">{{ externalError }}</p>
								<button @click="fetchProdukEksternal" class="px-4 py-2 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600">
									Coba Lagi
								</button>
							</div>

							<!-- Products Grid -->
							<div v-else class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-6">
                                <div
                                    v-for="produk in currentProduks"
                                    :key="produk.id + '-' + (produk.sumber || 'lokal')"
                                    @click="addToCart(produk)"
                                    class="group bg-white rounded-2xl overflow-hidden border border-slate-100 hover:border-indigo-500 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col relative"
                                    :class="{'opacity-75 grayscale-[30%] cursor-not-allowed': produk.stok !== undefined && produk.stok <= 0}"
                                >
                                    <!-- Badge Sumber -->
                                    <div class="absolute top-3 left-3 z-20">
                                        <span
                                            v-if="produk.sumber === 'external'"
                                            class="bg-emerald-500 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-sm"
                                        >
                                            EXTERNAL
                                        </span>
                                        <span
                                            v-else
                                            class="bg-indigo-500 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-sm"
                                        >
                                            LOKAL
                                        </span>
                                    </div>

                                    <!-- IMAGE CONTAINER -->
                                    <div class="relative w-full aspect-square bg-slate-50 overflow-hidden">
                                        <img
                                            v-if="produk.gambar"
                                            :src="produk.sumber === 'external' ? produk.gambar : '/storage/' + produk.gambar"
                                            :alt="produk.nama_barang"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        >
                                        <div
                                            v-else
                                            class="w-full h-full flex flex-col items-center justify-center text-slate-300"
                                        >
                                            <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-xs font-medium">No Image</span>
                                        </div>

                                        <!-- Overlay Stok Habis -->
                                        <div v-if="produk.stok !== undefined && produk.stok <= 0" class="absolute inset-0 bg-white/40 backdrop-blur-[2px] flex items-center justify-center z-10">
                                            <span class="bg-red-500 text-white font-bold px-4 py-1.5 rounded-full text-sm shadow-lg tracking-wider">
                                                HABIS
                                            </span>
                                        </div>

                                        <!-- Badge Stok Terbatas -->
                                        <div v-else-if="produk.stok !== undefined && produk.stok > 0 && produk.stok <= 5" class="absolute top-3 right-3 z-10">
                                            <span class="bg-orange-500/90 backdrop-blur-sm text-white text-[10px] uppercase font-bold px-2.5 py-1 rounded-full shadow-sm">
                                                Sisa {{ produk.stok }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- CONTENT CONTAINER -->
                                    <div class="p-4 flex flex-col flex-grow">
                                        <!-- Kode & Stok -->
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider bg-indigo-50 px-2 py-0.5 rounded-md">
                                                {{ produk.kode_barang || '-' }}
                                            </span>
                                            <span v-if="produk.stok !== undefined" class="text-xs font-semibold text-slate-500">
                                                Stok: {{ produk.stok }}
                                            </span>
                                        </div>

                                        <!-- Nama Produk -->
                                        <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2 mb-3 flex-grow group-hover:text-indigo-600 transition-colors">
                                            {{ produk.nama_barang }}
                                        </h3>

                                        <!-- Harga & Button -->
                                        <div class="flex items-end justify-between mt-auto">
                                            <div class="flex flex-col">
                                                <span class="text-[11px] text-slate-400 font-medium mb-0.5">{{ produk.satuan || 'pcs' }}</span>
                                                <span class="text-base font-black text-slate-900 leading-none">
                                                    {{ formatRupiah(produk.harga_jual) }}
                                                </span>
                                            </div>

                                            <button
                                                :disabled="produk.stok !== undefined && produk.stok <= 0"
                                                class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-indigo-600 text-slate-600 group-hover:text-white flex items-center justify-center transition-all duration-300"
                                            >
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
							</div>

							<!-- Empty State for External (Produk Belum Tersedia) -->
							<div v-if="activeTab === 'external' && !isLoadingExternal && !externalError && filteredProduksExternal.length === 0" class="text-center py-20">
								<div class="mb-4">
									<svg class="w-16 h-16 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
									</svg>
								</div>
								<p class="text-slate-500 font-semibold text-lg mb-2">Daftar menu belum tersedia</p>
								<p class="text-slate-400 text-sm">Menu dari server external belum ada untuk saat ini.</p>
							</div>

							<!-- Empty State for Local -->
							<div v-else-if="activeTab === 'lokal' && !isLoadingExternal && filteredProduksLokal.length === 0" class="text-center py-20 text-slate-400">
								<p class="font-bold">Belum ada produk tersedia.</p>
							</div>
						</div>

                    </div>

                    <div class="lg:col-span-4">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                            <h3 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-4 mb-4">Keranjang Pesanan</h3>

                            <div v-if="!peserta" class="bg-yellow-50 text-yellow-800 p-3 rounded-lg text-sm mb-4 border border-yellow-200">
                                ⚠️ Silakan cari NIP atau pilih mode <b>Tamu</b> terlebih dahulu.
                            </div>

                            <div v-if="peserta" class="mb-4 text-sm bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <span class="text-gray-500">Pemesan: </span><span class="font-bold text-gray-900">{{ peserta.nama }}</span>
                            </div>

                            <div class="space-y-4 mb-6 max-h-[400px] overflow-y-auto pr-2">
                                <div v-if="cart.length === 0" class="text-center py-8 text-gray-400 text-sm">
                                    Keranjang masih kosong.<br>Klik produk untuk menambahkan.
                                </div>

                                <div v-for="(item, index) in cart" :key="index" class="flex justify-between items-start border-b border-gray-50 pb-3">
                                    <div class="flex-grow">
                                        <div class="font-semibold text-gray-900 text-sm">{{ item.nama_barang }}</div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-indigo-600 text-sm">{{ formatRupiah(item.harga_jual) }}</span>
                                            <span v-if="item.sumber === 'external'" class="text-[10px] bg-emerald-100 text-emerald-700 px-1 rounded">EXT</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button @click="decreaseQty(index)" class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center hover:bg-gray-200 text-gray-600">-</button>
                                        <span class="text-sm font-medium w-6 text-center">{{ item.jumlah }}</span>
                                        <button @click="increaseQty(index)" class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center hover:bg-gray-200 text-gray-600">+</button>
                                        <button @click="removeFromCart(index)" class="w-6 h-6 rounded text-red-500 flex items-center justify-center hover:bg-red-50 ml-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 mb-6 pt-4 border-t border-gray-100">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Ruangan</label>
                                    <select v-model="idRuangan" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- Pilih Ruangan --</option>
                                        <option v-for="ruangan in ruangans" :key="ruangan.id" :value="ruangan.id">
                                            {{ ruangan.nama_ruangan }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Meja</label>
                                    <input v-model="nomorMeja" type="text" placeholder="Cth: Meja 12" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Keterangan / Catatan</label>
                                    <textarea v-model="keterangan" rows="2" placeholder="Catatan pesanan..." class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4 mb-4 border border-gray-100">
                                <div class="flex justify-between text-sm mb-2 text-gray-600">
                                    <span>Total Item</span>
                                    <span>{{ cartTotalItems }} Pcs</span>
                                </div>
                                <div class="flex justify-between font-bold text-lg text-gray-900 border-t border-gray-200 pt-2 mt-2">
                                    <span>Total Harga</span>
                                    <span class="text-indigo-600">{{ formatRupiah(cartTotal) }}</span>
                                </div>
                            </div>

                            <!-- Payment Section -->
                            <div class="space-y-4 mb-6 pt-4 border-t border-gray-100" v-if="cart.length > 0">
                                <div>
                                    <label class="block text-sm font-bold text-gray-900 mb-2">Metode Pembayaran</label>
                                    <div class="grid grid-cols-1 gap-3">
                                        <button
                                            class="bg-indigo-50 border-indigo-500 text-indigo-700 border-2 rounded-xl py-3 font-semibold flex items-center justify-center gap-2 w-full cursor-default"
                                        >
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                            QRIS
                                        </button>
                                    </div>
                                </div>

                                <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100 text-center">
                                    <p class="text-sm text-indigo-800 font-medium mb-3">Arahkan customer untuk scan QRIS sebesar <b>{{ formatRupiah(cartTotal) }}</b>.</p>
                                    <div v-if="instansi?.qris_image" class="flex justify-center">
                                        <img :src="'/storage/' + instansi.qris_image" alt="QRIS" class="w-48 h-48 object-contain rounded-lg border-2 border-indigo-200">
                                    </div>
                                    <div v-else class="text-xs text-red-500 bg-red-50 p-2 rounded-lg border border-red-100">
                                        Gambar QRIS belum diunggah oleh Admin. Transaksi tetap dapat diproses.
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="processCheckout"
                                :disabled="!peserta || cart.length === 0"
                                class="w-full py-3 rounded-xl font-bold text-white transition-colors shadow-sm"
                                :class="peserta && cart.length > 0 ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400 cursor-not-allowed'"
                            >
                                Proses Transaksi
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </component>
</template>