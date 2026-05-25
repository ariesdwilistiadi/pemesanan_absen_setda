<script setup>
import { ref, watch, computed } from 'vue';
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

const props = defineProps({
    pembayaran: {
        type: Array,
        default: () => [],
    },
    pinjaman: {
        type: Array,
        default: () => [],
    },
    anggotas: {
        type: Array,
        default: () => [],
    },
    belumBayar: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ 
            date: '', 
            month: new Date().getMonth() + 1, 
            year: new Date().getFullYear() 
        }),
    }
});

const page = usePage();
const currentUser = page.props.auth?.user?.username || page.props.auth?.user?.name || 'admin';

const isModalVisible = ref(false);
const today = new Date().toISOString().split('T')[0];

// State Parameter Filter
const filterDate = ref(props.filters.date || '');
const filterMonth = ref(props.filters.month);
const filterYear = ref(props.filters.year);

// Menggunakan useForm standar Inertia
const form = useForm({
    id_anggota: '',
    id_pinjaman: '',
    bayar: '',
    bunga: '',
    denda: 0,
    tanggal_bayar: today,
    username: currentUser,
});

// Format opsi untuk fitur ketik instan (Searchable Select)
const formattedPinjaman = computed(() => {
    return props.pinjaman.map(item => ({
        value: item.id_pinjaman,
        label: `${item.anggota?.nama || 'Tanpa Nama'} — ${item.nama || 'Reguler'} (Sisa: ${formatRupiah(item.sisa_pinjaman)})`
    }));
});

// =========================================================================
// FITUR OTOMATIS: Kalkulasi Pokok, Bunga & Denda (2% Setiap Bulan Telat)
// =========================================================================
watch([() => form.id_pinjaman, () => form.tanggal_bayar], ([newPinjamanId, newTglBayar]) => {
    if (!newPinjamanId) return;

    const selected = props.pinjaman.find(p => p.id_pinjaman === newPinjamanId);
    
    if (selected) {
        form.id_anggota = selected.id_anggota;

        const pokokPinjaman = parseFloat(selected.jumlah_pinjaman) || 0;
        const totalAngsuran = parseInt(selected.jumlah_angsuran) || 1;
        const nilaiJasa = parseFloat(selected.jasa) || 0;

        const angsuranPokok = Math.round(pokokPinjaman / totalAngsuran);
        form.bayar = angsuranPokok;
        form.bunga = Math.round((pokokPinjaman * nilaiJasa) / 100);

        if (newTglBayar && selected.tgl_pinjaman) {
            const tglBayarObj = new Date(newTglBayar);
            const tglPinjamObj = new Date(selected.tgl_pinjaman);
            
            const selisihHari = Math.floor((tglBayarObj - tglPinjamObj) / (1000 * 3600 * 24));
            
            if (selisihHari > 30) {
                const bulanTelat = Math.ceil((selisihHari - 30) / 30);
                form.denda = Math.round(((angsuranPokok * 2) / 100) * bulanTelat);
            } else {
                form.denda = 0; 
            }
        }
    }
});

const applyFilter = () => {
    router.get(route('pinjaman-bayar.index'), {
        date: filterDate.value,
        month: filterMonth.value,
        year: filterYear.value
    }, { preserveState: true, preserveScroll: true });
};

const resetDateFilter = () => {
    filterDate.value = '';
    applyFilter();
};

const triggerBayar = (itemPinjaman) => {
    form.reset();
    form.username = currentUser;
    form.tanggal_bayar = today;
    form.id_pinjaman = itemPinjaman.id_pinjaman;
    isModalVisible.value = true;
};

const openModal = () => {
    form.reset();
    form.username = currentUser;
    form.tanggal_bayar = today;
    isModalVisible.value = true;
};

const submitForm = () => {
    form.post(route('pinjaman-bayar.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalVisible.value = false;
            form.reset();
        },
    });
};

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};

const months = [
    { value: 1, label: 'Januari' }, { value: 2, label: 'Februari' }, { value: 3, label: 'Maret' },
    { value: 4, label: 'April' }, { value: 5, label: 'Mei' }, { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' }, { value: 8, label: 'Agustus' }, { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' }, { value: 11, label: 'November' }, { value: 12, label: 'Desember' },
];
</script>

<template>
    <Head title="Manajemen Pembayaran Angsuran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Manajemen Pembayaran Angsuran</h2>
                <button 
                    @click="openModal" 
                    class="bg-[#337ab7] hover:bg-[#286090] text-white font-bold py-2 px-4 rounded transition-colors shadow-sm"
                >
                    + Catat Setoran Baru
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 space-y-8">
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Parameter Penelusuran Pembukuan</div>
                    
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-4">
                            
                            <div class="flex items-center space-x-2 border-r border-gray-200 pr-4">
                                <span class="text-xs font-bold text-gray-600">Per Tanggal:</span>
                                <input 
                                    v-model="filterDate" 
                                    @change="applyFilter" 
                                    type="date" 
                                    class="border border-gray-300 rounded-lg text-sm px-3 py-1.5 outline-none focus:ring-1 focus:ring-blue-500 bg-white" 
                                />
                                <button 
                                    v-if="filterDate" 
                                    @click="resetDateFilter" 
                                    title="Hapus filter harian" 
                                    class="text-xs text-red-500 hover:text-red-700 font-bold transition-colors"
                                >
                                    ✕ Batal
                                </button>
                            </div>

                            <div class="flex items-center space-x-2 transition-opacity duration-200" :class="{'opacity-40 pointer-events-none': filterDate}">
                                <span class="text-xs font-bold text-gray-600">Atau Bulan:</span>
                                <select v-model="filterMonth" @change="applyFilter" class="border border-gray-300 rounded-lg text-sm px-3 py-1.5 outline-none focus:ring-1 focus:ring-blue-500 bg-white">
                                    <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                                </select>
                                <input v-model="filterYear" @change="applyFilter" type="number" class="border border-gray-300 rounded-lg text-sm px-3 py-1.5 w-24 outline-none focus:ring-1 focus:ring-blue-500 bg-white" />
                            </div>

                        </div>

                        <div class="text-xs text-indigo-600 font-medium bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                            Mode: {{ filterDate ? `Harian (${filterDate})` : 'Akumulasi Bulanan' }}
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-orange-100 overflow-hidden">
                    <div class="p-5 bg-orange-50/50 border-b border-orange-100 flex justify-between items-center">
                        <h3 class="font-bold text-base text-orange-900">
                            Daftar Tunggakan Aktif
                        </h3>
                        <span class="text-xs bg-orange-100 text-orange-800 px-2.5 py-0.5 rounded-full font-bold">
                            {{ props.belumBayar.length }} Rekening Menunggu Setoran
                        </span>
                    </div>
                    
                    <div class="overflow-x-auto p-0">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50/50 text-[11px] uppercase text-gray-500 border-b">
                                <tr>
                                    <th class="py-3 px-5">Informasi Anggota</th>
                                    <th class="py-3 px-3">Keterangan Pinjaman</th>
                                    <th class="py-3 px-3 text-center">Pinjaman Pokok</th>
                                    <th class="py-3 px-3 text-center">Sisa Kewajiban</th>
                                    <th class="py-3 px-3 text-center">Tgl Pinjam</th>
                                    <th class="py-3 px-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="item in props.belumBayar" :key="item.id_pinjaman" class="hover:bg-orange-50/20">
                                    <td class="py-3 px-5">
                                        <div class="font-bold text-gray-900">{{ item.anggota?.nama || '-' }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ item.anggota?.no_anggota || '-' }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-gray-600 font-medium">{{ item.nama || 'Pinjaman Reguler' }}</td>
                                    <td class="py-3 px-3 text-center text-gray-600">{{ formatRupiah(item.jumlah_pinjaman) }}</td>
                                    <td class="py-3 px-3 text-center font-bold text-red-600">{{ formatRupiah(item.sisa_pinjaman) }}</td>
                                    <td class="py-3 px-3 text-center text-xs text-gray-500">{{ item.tgl_pinjaman }}</td>
                                    <td class="py-3 px-5 text-right">
                                        <button 
                                            @click="triggerBayar(item)"
                                            class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-3 py-1 rounded text-xs transition-colors shadow-sm"
                                        >
                                            Bayar / Tagih
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="props.belumBayar.length === 0">
                                    <td colspan="6" class="text-center py-8 text-gray-400 text-xs italic">
                                        Tidak terdeteksi adanya tunggakan setoran untuk rentang parameter ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <h3 class="text-lg font-bold text-gray-900">Riwayat Setoran Masuk</h3>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50/80 text-[11px] uppercase text-gray-500 border-b border-gray-100">
                                <tr>
                                    <th class="py-3 px-5 font-bold">Anggota</th>
                                    <th class="py-3 px-3 font-bold">Pinjaman</th>
                                    <th class="py-3 px-3 font-bold text-right">Setoran Pokok</th>
                                    <th class="py-3 px-3 font-bold text-right">Bunga/Jasa</th>
                                    <th class="py-3 px-3 font-bold text-right">Denda</th>
                                    <th class="py-3 px-3 font-bold text-center">Tanggal Bayar</th>
                                    <th class="py-3 px-3 font-bold text-center">Petugas</th>
                                    <th class="py-3 px-5 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="item in props.pembayaran" :key="item.id_pinjaman_bayar" class="hover:bg-blue-50/20">
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-gray-900">{{ item.anggota?.nama || '-' }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ item.anggota?.no_anggota || '-' }}</div>
                                    </td>
                                    <td class="py-4 px-3 text-gray-600">{{ item.pinjaman?.nama || '-' }}</td>
                                    <td class="py-4 px-3 text-right font-semibold text-gray-800">{{ formatRupiah(item.bayar) }}</td>
                                    <td class="py-4 px-3 text-right text-gray-600">{{ formatRupiah(item.bunga) }}</td>
                                    <td class="py-4 px-3 text-right text-orange-500">{{ formatRupiah(item.denda) }}</td>
                                    <td class="py-4 px-3 text-center text-xs text-gray-500">{{ item.tanggal_bayar }}</td>
                                    <td class="py-4 px-3 text-center font-mono text-[10px] text-gray-400 uppercase">{{ item.username }}</td>
                                    <td class="py-4 px-5 text-center">
                                        <a 
                                            :href="route('pinjaman-bayar.print', item.id_pinjaman_bayar)" 
                                            target="_blank"
                                            class="bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-bold border border-gray-300 transition-colors inline-block"
                                        >
                                            🖨️ Cetak
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="props.pembayaran.length === 0">
                                    <td colspan="8" class="text-center py-12 text-gray-400 italic">
                                        Belum ada pembukuan setoran yang tercatat pada kueri ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <div 
            v-if="isModalVisible" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 transition-all"
        >
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden border border-gray-100">
                
                <div class="bg-[#337ab7] px-8 py-5 flex justify-between items-center text-white">
                    <h3 class="text-base font-bold tracking-tight">Formulir Penerimaan Setoran</h3>
                    <button @click="isModalVisible = false" class="text-white/80 hover:text-white font-bold text-lg transition-colors">✕</button>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="p-8 max-h-[70vh] overflow-y-auto space-y-4 text-sm">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pilih Rekening Pinjaman</label>
                                <Multiselect 
                                    v-model="form.id_pinjaman" 
                                    :options="formattedPinjaman"
                                    :searchable="true"
                                    placeholder="🔍 Ketik nama anggota atau keterangan transaksi..."
                                    class="w-full custom-multiselect"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pemilik Rekening (Anggota)</label>
                                <select v-model="form.id_anggota" disabled class="w-full bg-gray-100 border border-gray-200 rounded-xl px-4 py-3 text-gray-500 cursor-not-allowed outline-none">
                                    <option value="" disabled>Terpetakan otomatis...</option>
                                    <option v-for="anggota in props.anggotas" :key="anggota.id_anggota" :value="anggota.id_anggota">
                                        {{ anggota.no_anggota }} - {{ anggota.nama }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tanggal Pembukuan</label>
                                <input v-model="form.tanggal_bayar" type="date" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all" />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Setoran Pokok (Rp)</label>
                                <input v-model="form.bayar" type="number" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all" placeholder="0" />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pendapatan Jasa / Bunga (Rp)</label>
                                <input v-model="form.bunga" type="number" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all" placeholder="0" />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pendapatan Denda (Rp)</label>
                                <input v-model="form.denda" type="number" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all" placeholder="0" />
                            </div>

                            <div class="md:col-span-2 bg-gray-50 p-4 rounded-2xl border border-dashed border-gray-200">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Otoritas Validasi</label>
                                <input v-model="form.username" type="text" readonly class="w-full bg-transparent font-mono text-sm text-gray-600 outline-none cursor-not-allowed border-0 p-0" />
                            </div>

                        </div>

                    </div>

                    <div class="bg-gray-50 px-8 py-5 flex gap-3 justify-end">
                        <button 
                            type="button" 
                            @click="isModalVisible = false" 
                            class="px-6 py-2.5 text-gray-500 font-bold hover:text-gray-700 transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-8 py-2.5 bg-[#337ab7] hover:bg-[#286090] text-white font-bold rounded-xl shadow-lg shadow-blue-100 transition-all disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
/* Kustomisasi Tampilan Pustaka Multiselect agar Pas dengan Form Input Tailwind */
:deep(.custom-multiselect) {
    --ms-border-color: #e5e7eb;
    --ms-border-color-active: #3b82f6;
    --ms-border-width: 1px;
    --ms-radius: 0.75rem;
    --ms-py: 0.75rem;
    --ms-px: 1rem;
    --ms-bg: #f9fafb;
    --ms-ring-color: rgba(59, 130, 246, 0.2);
}
</style>