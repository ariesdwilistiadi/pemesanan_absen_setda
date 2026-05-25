<script setup>
import { ref, computed } from 'vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

const props = defineProps({
    pinjaman: {
        type: Array,
        default: () => [],
    },
    anggotas: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const currentUser = page.props.auth?.user?.username || page.props.auth?.user?.name || 'admin';

// ==================== STATE MODAL & HISTORY ====================
const isModalVisible = ref(false);
const selectedId = ref(null);

const toggleHistory = (id) => {
    selectedId.value = selectedId.value === id ? null : id;
};

// ==================== DATA MAPPING (SEARCHABLE SELECT) ====================
// Memetakan senarai anggota ke format pustaka Multiselect (value & label)
const formattedAnggotas = computed(() => {
    return props.anggotas.map(anggota => ({
        value: anggota.id_anggota,
        label: `${anggota.no_anggota} — ${anggota.nama}`
    }));
});

// ==================== FORM HANDLING (INERTIA USEFORM) ====================
// Menggunakan useForm resmi agar pesan galat dari Laravel terikat secara otomatis
const form = useForm({
    id_anggota: null,
    kategori: 1, // Diubah ke integer 1 agar lolos validasi "The kategori field must be an integer."
    jumlah_pinjaman: '',
    jasa: '',
    jumlah_angsuran: 10,
    jangka_waktu: '', // Menggunakan input date agar lolos validasi "must be a valid date."
    tgl_pinjaman: '',
    nama: '',
    id_jenis_pinjaman: 0, // Diberi nilai baku agar lolos validasi "is required."
    username: currentUser,
});

const openModal = () => {
    form.reset();
    form.clearErrors();
    form.username = currentUser;
    isModalVisible.value = true;
};

const submitForm = () => {
    form.post(route('pinjaman.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalVisible.value = false;
            form.reset();
        },
    });
};

// ==================== FORMATTER ====================
const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const getKategoriLabel = (val) => {
    return val == 1 ? 'Pinjaman Uang' : 'Lainnya';
};
</script>

<template>
    <Head title="Data Pinjaman" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800">
                    Data Pinjaman
                </h2>

                <button
                    @click="openModal"
                    class="bg-[#337ab7] hover:bg-[#286090] text-white font-bold px-4 py-2 rounded transition-colors shadow-sm"
                >
                    + Tambah Pinjaman
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 space-y-6">

                <div v-if="page.props.flash?.success" class="p-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200">
                    {{ page.props.flash.success }}
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

                    <div class="p-6 border-b bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-lg text-gray-900">
                            Daftar Pinjaman
                        </h3>

                        <span class="text-xs text-indigo-500 font-medium italic animate-pulse">
                            *Klik baris untuk melihat riwayat angsuran & sisa utang
                        </span>
                    </div>

                    <div class="overflow-x-auto p-0">
                        <table class="w-full text-sm">

                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-100 uppercase text-[11px] tracking-wider text-gray-500">
                                    <th class="py-3 px-6 text-left font-bold">Anggota</th>
                                    <th class="py-3 px-4 text-center font-bold">Kategori</th>
                                    <th class="py-3 px-4 text-center font-bold">Pokok</th>
                                    <th class="py-3 px-4 text-center font-bold">Sisa Pinjaman</th>
                                    <th class="py-3 px-4 text-center font-bold">Tenor</th>
                                    <th class="py-3 px-4 text-center font-bold">Tanggal</th>
                                    <th class="py-3 px-4 text-center font-bold">Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                <template
                                    v-for="item in props.pinjaman"
                                    :key="item.id_pinjaman"
                                >

                                    <tr
                                        @click="toggleHistory(item.id_pinjaman)"
                                        class="border-b border-gray-50 hover:bg-indigo-50/40 cursor-pointer transition-colors duration-150"
                                        :class="{'bg-indigo-50/30': selectedId === item.id_pinjaman}"
                                    >

                                        <td class="py-4 px-6">
                                            <div class="font-bold text-gray-900">
                                                {{ item.anggota?.nama || '-' }}
                                            </div>

                                            <div class="text-[10px] font-mono text-gray-400 uppercase">
                                                {{ item.anggota?.no_anggota || '-' }}
                                            </div>
                                        </td>

                                        <td class="text-center text-gray-600">
                                            {{ getKategoriLabel(item.kategori) }}
                                        </td>

                                        <td class="text-center text-gray-600">
                                            {{ formatRupiah(item.jumlah_pinjaman) }}
                                        </td>

                                        <td
                                            class="text-center font-bold"
                                            :class="item.sisa_pinjaman > 0 ? 'text-red-500' : 'text-green-600'"
                                        >
                                            {{ formatRupiah(item.sisa_pinjaman) }}
                                        </td>

                                        <td class="text-center text-gray-600">
                                            {{ item.jumlah_angsuran }}x
                                        </td>

                                        <td class="text-center text-xs text-gray-500">
                                            {{ item.tgl_pinjaman }}
                                        </td>

                                        <td class="text-center">
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                                :class="item.sisa_pinjaman <= 0 ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'"
                                            >
                                                {{ item.sisa_pinjaman <= 0 ? 'Lunas' : 'Aktif' }}
                                            </span>
                                        </td>

                                    </tr>

                                    <tr v-if="selectedId === item.id_pinjaman">

                                        <td colspan="7" class="p-0 border-b border-indigo-100">

                                            <div class="bg-gray-50/60 p-6 overflow-hidden animate-slide-down">

                                                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-4">

                                                    <div class="flex justify-between items-center border-b border-dashed border-gray-100 pb-3">
                                                        <div>
                                                            <h4 class="font-bold text-indigo-900 text-xs uppercase tracking-widest">
                                                                Riwayat Angsuran
                                                            </h4>
                                                        </div>

                                                        <div class="text-right">
                                                            <div class="text-[10px] text-gray-400 block uppercase">
                                                                Total Telah Dibayar
                                                            </div>

                                                            <div class="text-sm font-bold text-green-600">
                                                                {{ formatRupiah(item.total_dibayar) }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <table class="w-full text-[11px]">
                                                        <thead>
                                                            <tr class="text-gray-400 text-left border-b border-gray-50 uppercase tracking-tighter">
                                                                <th class="pb-2">Tanggal Bayar</th>
                                                                <th class="pb-2">Angsuran Pokok</th>
                                                                <th class="pb-2">Bunga/Jasa</th>
                                                                <th class="pb-2 text-center">Petugas</th>
                                                            </tr>
                                                        </thead>

                                                        <tbody class="divide-y divide-gray-50">
                                                            <tr
                                                                v-for="bayar in item.angsuran"
                                                                :key="bayar.id_pinjaman_bayar"
                                                                class="hover:bg-gray-50/50"
                                                            >
                                                                <td class="py-2 text-gray-600">{{ bayar.tanggal_bayar }}</td>
                                                                <td class="py-2 font-semibold text-gray-800">{{ formatRupiah(bayar.bayar) }}</td>
                                                                <td class="py-2 text-orange-500 font-medium">{{ formatRupiah(bayar.bunga) }}</td>
                                                                <td class="py-2 text-center font-mono text-gray-400 uppercase text-[9px]">{{ bayar.username }}</td>
                                                            </tr>

                                                            <tr v-if="item.angsuran?.length === 0">
                                                                <td colspan="4" class="text-center py-6 text-gray-400 italic">
                                                                    Belum ada catatan pembayaran angsuran.
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                </div>

                                            </div>

                                        </td>

                                    </tr>

                                </template>

                            </tbody>

                        </table>

                        <div v-if="props.pinjaman.length === 0" class="text-center py-12 text-gray-400">
                            Belum ada data transaksi pinjaman
                        </div>

                    </div>

                </div>

            </div>
        </div>

        <div
            v-if="isModalVisible"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        >

            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden border border-gray-100">

                <div class="flex justify-between items-center bg-[#337ab7] text-white px-8 py-5">
                    <div>
                        <h3 class="text-base font-bold tracking-tight">Formulir Pinjaman Baru</h3>
                    </div>

                    <button @click="isModalVisible = false" class="text-white/80 hover:text-white font-bold text-lg transition-colors">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="p-8 max-h-[70vh] overflow-y-auto space-y-4 text-sm">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                    Cari & Pilih Anggota
                                </label>

                                <Multiselect
                                    v-model="form.id_anggota"
                                    :options="formattedAnggotas"
                                    :searchable="true"
                                    placeholder="🔍 Ketik nama atau nomor anggota..."
                                    class="w-full custom-multiselect"
                                    :class="{'has-error': form.errors.id_anggota}"
                                />
                                <span v-if="form.errors.id_anggota" class="text-red-500 text-[10px] block mt-1">
                                    {{ form.errors.id_anggota }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                    Kategori
                                </label>

                                <select
                                    v-model="form.kategori"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all"
                                >
                                    <option :value="1">Pinjaman Uang</option>
                                </select>
                                <span v-if="form.errors.kategori" class="text-red-500 text-[10px] block mt-1">{{ form.errors.kategori }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                    Kode Jenis Pinjaman
                                </label>

                                <input
                                    v-model="form.id_jenis_pinjaman"
                                    type="number"
                                    min="0"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all"
                                />
                                <span v-if="form.errors.id_jenis_pinjaman" class="text-red-500 text-[10px] block mt-1">{{ form.errors.id_jenis_pinjaman }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                    Jumlah Pinjaman Pokok (Rp)
                                </label>

                                <input
                                    v-model="form.jumlah_pinjaman"
                                    type="number"
                                    placeholder="0"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all"
                                />
                                <span v-if="form.errors.jumlah_pinjaman" class="text-red-500 text-[10px] block mt-1">{{ form.errors.jumlah_pinjaman }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                    Jasa / Bunga (Rp)
                                </label>

                                <input
                                    v-model="form.jasa"
                                    type="number"
                                    placeholder="0"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all"
                                />
                                <span v-if="form.errors.jasa" class="text-red-500 text-[10px] block mt-1">{{ form.errors.jasa }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                    Tenor Angsuran (x)
                                </label>

                                <input
                                    v-model="form.jumlah_angsuran"
                                    type="number"
                                    placeholder="10"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all"
                                />
                                <span v-if="form.errors.jumlah_angsuran" class="text-red-500 text-[10px] block mt-1">{{ form.errors.jumlah_angsuran }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                    Tanggal Pencairan
                                </label>

                                <input
                                    v-model="form.tgl_pinjaman"
                                    type="date"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all"
                                />
                                <span v-if="form.errors.tgl_pinjaman" class="text-red-500 text-[10px] block mt-1">{{ form.errors.tgl_pinjaman }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                    Target Tanggal Lunas
                                </label>

                                <input
                                    v-model="form.jangka_waktu"
                                    type="date"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all"
                                />
                                <span v-if="form.errors.jangka_waktu" class="text-red-500 text-[10px] block mt-1">{{ form.errors.jangka_waktu }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                    Nama / Keterangan
                                </label>

                                <input
                                    v-model="form.nama"
                                    type="text"
                                    placeholder="Catatan pinjaman..."
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all"
                                />
                                <span v-if="form.errors.nama" class="text-red-500 text-[10px] block mt-1">{{ form.errors.nama }}</span>
                            </div>

                            <div class="md:col-span-2 bg-gray-50 p-4 rounded-2xl border border-dashed border-gray-200">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">
                                    Otoritas Pencatat
                                </label>

                                <input
                                    v-model="form.username"
                                    type="text"
                                    readonly
                                    class="w-full bg-transparent font-mono text-sm text-gray-600 outline-none cursor-not-allowed border-0 p-0"
                                />
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
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slide-down {
    animation: slideDown 0.3s ease-out forwards;
}

/* Kustomisasi Tampilan Pustaka Multiselect agar Senada dengan Desain Tailwind Anda */
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

:deep(.custom-multiselect.has-error) {
    --ms-border-color: #ef4444;
}
</style>