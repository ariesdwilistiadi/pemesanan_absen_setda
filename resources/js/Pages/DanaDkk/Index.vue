<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    danaDkks: {
        type: Array,
        default: () => [],
    },
    anggotas: {
        type: Array,
        default: () => [],
    },
});

const showModal = ref(false);
const searchQuery = ref('');
const formData = ref({
    id_anggota: '',
    nominal: '',
    sakit: '',
    keterangan: '',
    file_berkas: null,
    tgl_sakit: '',
    lama_sakit: '',
});

const filteredAnggotas = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    if (!query) {
        return props.anggotas;
    }
    return props.anggotas.filter((anggota) => {
        return anggota.nama.toLowerCase().includes(query) || anggota.no_anggota.toLowerCase().includes(query);
    });
});

const openAddModal = () => {
    formData.value = {
        id_anggota: '',
        nominal: '',
        sakit: '',
        keterangan: '',
        file_berkas: null,
        tgl_sakit: '',
        lama_sakit: '',
    };
    searchQuery.value = '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitForm = () => {
    if (!formData.value.id_anggota) {
        alert('Pilih anggota terlebih dahulu.');
        return;
    }
    if (!formData.value.file_berkas) {
        alert('Unggah berkas terlebih dahulu.');
        return;
    }

    const payload = new FormData();
    payload.append('id_anggota', formData.value.id_anggota);
    payload.append('nominal', formData.value.nominal);
    payload.append('sakit', formData.value.sakit);
    payload.append('keterangan', formData.value.keterangan);
    payload.append('file_berkas', formData.value.file_berkas);
    payload.append('tgl_sakit', formData.value.tgl_sakit);
    payload.append('lama_sakit', formData.value.lama_sakit);

    router.post(route('dana-dkks.store'), payload, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: (errors) => alert('Gagal menyimpan data: ' + JSON.stringify(errors)),
    });
};

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};
</script>

<template>
    <Head title="Dana DKK" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Dana DKK</h2>
                <button @click="openAddModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg">+ Tambah Dana</button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <h3 class="text-lg font-bold text-gray-900">Daftar Dana DKK</h3>
                        <p class="text-sm text-gray-500">Kelola pengajuan Dana DKK, lampirkan berkas, dan pilih anggota dari daftar.</p>
                    </div>
                    <div class="p-6 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-left">
                                    <th class="py-3 px-2 font-semibold text-gray-700">Anggota</th>
                                    <th class="py-3 px-2 font-semibold text-gray-700">Nominal</th>
                                    <th class="py-3 px-2 font-semibold text-gray-700">Sakit</th>
                                    <th class="py-3 px-2 font-semibold text-gray-700">Tanggal Sakit</th>
                                    <th class="py-3 px-2 font-semibold text-gray-700">Lama</th>
                                    <th class="py-3 px-2 font-semibold text-gray-700">Berkas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in danaDkks" :key="item.id_dana_dkk" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-2">
                                        <div class="font-semibold text-gray-900">{{ item.anggota?.nama || '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ item.anggota?.no_anggota || '-' }}</div>
                                    </td>
                                    <td class="py-3 px-2 text-gray-600">{{ formatRupiah(item.nominal) }}</td>
                                    <td class="py-3 px-2 text-gray-600">{{ item.sakit }}</td>
                                    <td class="py-3 px-2 text-gray-600">{{ item.tgl_sakit }}</td>
                                    <td class="py-3 px-2 text-gray-600">{{ item.lama_sakit }} hari</td>
                                    <td class="py-3 px-2">
                                        <a v-if="item.file_url" :href="item.file_url" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">Lihat berkas</a>
                                        <span v-else class="text-gray-400">Tidak ada</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="danaDkks.length === 0" class="text-center py-12 text-gray-500">
                            Belum ada data Dana DKK.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-3xl w-full overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900">Tambah Dana DKK</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari Anggota</label>
                        <input v-model="searchQuery" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Cari nama atau no anggota" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Anggota</label>
                        <select v-model="formData.id_anggota" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="" disabled>Pilih anggota</option>
                            <option v-for="anggota in filteredAnggotas" :key="anggota.id" :value="anggota.id">
                                {{ anggota.no_anggota }} - {{ anggota.nama }}
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal</label>
                            <input v-model="formData.nominal" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="0" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Sakit</label>
                            <input v-model="formData.tgl_sakit" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lama Sakit</label>
                            <input v-model="formData.lama_sakit" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Jumlah hari" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Berkas</label>
                            <input @change="event => formData.file_berkas = event.target.files[0]" type="file" class="w-full" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sakit</label>
                        <textarea v-model="formData.sakit" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea v-model="formData.keterangan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="closeModal" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg">Batal</button>
                    <button @click="submitForm" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg">Simpan</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
