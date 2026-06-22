<script setup>
import { ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    anggotas: {
        type: Array,
        default: () => [],
    },
    agamas: {
        type: Array,
        default: () => [],
    },
    filterStatus: {
        type: String,
        default: null,
    },
});

const showModal = ref(false);
const editingAnggota = ref(null);

const getInitialFormData = () => ({
    nama: '',
    no_identitas: '',
    tempat_lahir: '',
    tgl_lahir: '',
    alamat: '',
    jenis_kelamin: '',
    no_telp: '',
    agama_id: '',
    pekerjaan: '',
    tgl_masuk: '',
    simpanan_pokok: 0,
    simpanan_wajib: 0,
    status: 1
});

const formData = ref(getInitialFormData());

const openAddModal = () => {
    editingAnggota.value = null;
    formData.value = getInitialFormData();
    showModal.value = true;
};

const openEditModal = (anggota) => {
    editingAnggota.value = anggota;
    formData.value = {
        nama: anggota.nama,
        no_identitas: anggota.no_identitas,
        tempat_lahir: anggota.tempat_lahir,
        tgl_lahir: anggota.tgl_lahir,
        alamat: anggota.alamat,
        jenis_kelamin: anggota.jenis_kelamin,
        no_telp: anggota.no_telp,
        agama_id: anggota.agama_id,
        pekerjaan: anggota.pekerjaan,
        tgl_masuk: anggota.tgl_masuk,
        simpanan_pokok: anggota.simpanan_pokok,
        simpanan_wajib: anggota.simpanan_wajib,
        status: anggota.status !== undefined ? anggota.status : 1
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingAnggota.value = null;
    formData.value = getInitialFormData();
};

const submitForm = () => {
    // Basic validation
    const requiredFields = [
        'nama', 'no_identitas', 'tempat_lahir', 'tgl_lahir',
        'alamat', 'jenis_kelamin', 'no_telp', 'agama_id',
        'pekerjaan', 'tgl_masuk'
    ];

    for (const field of requiredFields) {
        if (!formData.value[field] || String(formData.value[field]).trim() === '') {
            alert(`Field ${field.replace('_', ' ')} harus diisi!`);
            return;
        }
    }

    if (formData.value.simpanan_pokok === null || formData.value.simpanan_pokok === undefined || formData.value.simpanan_pokok < 0) {
        alert('Simpanan Pokok harus diisi!');
        return;
    }

    if (formData.value.simpanan_wajib === null || formData.value.simpanan_wajib === undefined || formData.value.simpanan_wajib < 0) {
        alert('Simpanan Wajib harus diisi!');
        return;
    }

    const submitCallback = () => {
        if (editingAnggota.value) {
            router.put(route('anggotas.update', editingAnggota.value.id), formData.value, {
                preserveScroll: true,
                onSuccess: () => closeModal(),
                onError: (errors) => {
                    if (errors.status === 419) {
                        alert('Sesi telah berakhir. Halaman akan di-refresh.');
                        window.location.reload();
                    } else {
                        alert('Gagal memperbarui anggota: ' + JSON.stringify(errors));
                    }
                }
            });
        } else {
            router.post(route('anggotas.store'), formData.value, {
                preserveScroll: true,
                onSuccess: () => closeModal(),
                onError: (errors) => {
                    if (errors.status === 419) {
                        alert('Sesi telah berakhir. Halaman akan di-refresh.');
                        window.location.reload();
                    } else {
                        alert('Gagal membuat anggota: ' + JSON.stringify(errors));
                    }
                }
            });
        }
    };

    submitCallback();
};

const deleteAnggota = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus anggota ini?')) {
        router.delete(route('anggotas.destroy', id), {
            preserveScroll: true,
            onError: (errors) => alert('Gagal menghapus anggota: ' + JSON.stringify(errors))
        });
    }
};

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};

// Filter handlers
const filterStatus = (status) => {
    if (status === props.filterStatus) {
        // If clicking the same filter, remove it (show all)
        router.get(route('anggotas.index'), {}, {
            preserveScroll: true,
        });
    } else {
        router.get(route('anggotas.index'), { status: status }, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Manajemen Anggota" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Manajemen Anggota</h2>

                <button
                    @click="openAddModal"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm"
                >
                    + Tambah Anggota
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Daftar Anggota</h3>
                                <p class="text-sm text-gray-500">Kelola semua data anggota di sistem.</p>
                            </div>

                            <!-- Filter Buttons -->
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500 mr-2">Filter:</span>
                                <button
                                    @click="filterStatus('active')"
                                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                                    :class="filterStatus === 'active' ? 'bg-green-500 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'"
                                >
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        Aktif
                                    </span>
                                </button>
                                <button
                                    @click="filterStatus('inactive')"
                                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                                    :class="filterStatus === 'inactive' ? 'bg-red-500 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'"
                                >
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                        Non-Aktif
                                    </span>
                                </button>
                                <button
                                    @click="filterStatus(null)"
                                    v-if="filterStatus"
                                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                                >
                                    Tampilkan Semua
                                </button>
                            </div>
                        </div>

                        <!-- Summary Stats -->
                        <div class="mt-4 flex flex-wrap gap-4">
                            <div class="bg-green-50 text-green-700 px-3 py-1.5 rounded-full text-sm font-medium">
                                <span class="text-green-500">●</span> Aktif: {{ anggotas.filter(a => a.status === 1).length }}
                            </div>
                            <div class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">
                                <span class="text-gray-400">●</span> Non-Aktif: {{ anggotas.filter(a => a.status === 0).length }}
                            </div>
                            <div class="bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-full text-sm font-medium">
                                Total: {{ anggotas.length }}
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">No. Anggota</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Nama Lengkap</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Identitas</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Kontak</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Agama</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Simpanan</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Status</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-700">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="anggota in anggotas" :key="anggota.id" class="border-b border-gray-100 hover:bg-gray-50" :class="{'bg-red-50/30': anggota.status === 0}">
                                        <td class="py-3 px-2">
                                            <span class="font-mono text-gray-600">{{ anggota.no_anggota || 'N/A' }}</span>
                                        </td>
                                        <td class="py-3 px-2">
                                            <div class="font-semibold text-gray-900" :class="{'line-through text-gray-400': anggota.status === 0}">{{ anggota.nama }}</div>
                                            <div class="text-xs text-gray-500 capitalize">{{ anggota.jenis_kelamin }}</div>
                                        </td>
                                        <td class="py-3 px-2 text-gray-600">{{ anggota.no_identitas }}</td>
                                        <td class="py-3 px-2">
                                            <div class="text-gray-900">{{ anggota.no_telp }}</div>
                                            <div class="text-xs text-gray-500 truncate w-32" :title="anggota.alamat">{{ anggota.alamat }}</div>
                                        </td>
                                        <td class="py-3 px-2 text-gray-600">
                                            {{ anggota.agama ? anggota.agama.nama : '-' }}
                                        </td>
                                        <td class="py-3 px-2">
                                            <div class="text-xs"><span class="text-gray-500">Pokok:</span> {{ formatRupiah(anggota.simpanan_pokok) }}</div>
                                            <div class="text-xs"><span class="text-gray-500">Wajib:</span> {{ formatRupiah(anggota.simpanan_wajib) }}</div>
                                        </td>
                                        <td class="py-3 px-2">
                                            <span v-if="anggota.status === 1" class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Aktif</span>
                                            <span v-else class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">Non-Aktif</span>
                                        </td>
                                        <td class="py-3 px-2">
                                            <div class="flex space-x-2">
                                                <button
                                                    @click="openEditModal(anggota)"
                                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Edit"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-4l7-7m0 0l-7 7" />
                                                    </svg>
                                                </button>
                                                <button
                                                    @click="deleteAnggota(anggota.id)"
                                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Hapus"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="anggotas.length === 0" class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 text-gray-300 mb-4">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">Belum ada data anggota.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex-shrink-0">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ editingAnggota ? 'Edit Anggota' : 'Tambah Anggota Baru' }}
                    </h2>
                    <p v-if="editingAnggota" class="text-sm text-gray-500 mt-1">No. Anggota: <span class="font-mono font-medium">{{ editingAnggota.no_anggota }}</span></p>
                    <p v-else class="text-sm text-gray-500 mt-1">No. Anggota akan digenerate otomatis.</p>
                </div>

                <div class="p-6 overflow-y-auto flex-grow">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kolom 1 -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input v-model="formData.nama" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" placeholder="Masukkan nama lengkap" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Identitas (KTP/SIM) <span class="text-red-500">*</span></label>
                                <input v-model="formData.no_identitas" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" placeholder="Masukkan no identitas" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir <span class="text-red-500">*</span></label>
                                    <input v-model="formData.tempat_lahir" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" placeholder="Tempat lahir" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input v-model="formData.tgl_lahir" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                                <select v-model="formData.jenis_kelamin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600">
                                    <option value="" disabled>Pilih Jenis Kelamin</option>
                                    <option value="laki-laki">Laki-laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Agama <span class="text-red-500">*</span></label>
                                <select v-model="formData.agama_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600">
                                    <option value="" disabled>Pilih Agama</option>
                                    <option v-for="agama in agamas" :key="agama.id" :value="agama.id">
                                        {{ agama.nama }}
                                    </option>
                                </select>
                            </div>
                            
                            <div v-if="editingAnggota">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Keanggotaan <span class="text-red-500">*</span></label>
                                <select v-model="formData.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600">
                                    <option :value="1">Aktif</option>
                                    <option :value="0">Non-Aktif</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kolom 2 -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP/Telepon <span class="text-red-500">*</span></label>
                                <input v-model="formData.no_telp" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" placeholder="08xxxxxxxxxx" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan <span class="text-red-500">*</span></label>
                                <input v-model="formData.pekerjaan" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" placeholder="Pekerjaan" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                                <textarea v-model="formData.alamat" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" placeholder="Alamat lengkap"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Masuk <span class="text-red-500">*</span></label>
                                <input v-model="formData.tgl_masuk" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Simpanan Pokok <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                                        <input v-model="formData.simpanan_pokok" type="number" class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Simpanan Wajib <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                                        <input v-model="formData.simpanan_wajib" type="number" class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100 flex-shrink-0 flex justify-end space-x-3 bg-gray-50">
                    <button @click="closeModal" class="px-5 py-2.5 text-gray-700 hover:bg-gray-200 bg-white border border-gray-300 rounded-lg font-medium transition-colors">Batal</button>
                    <button @click="submitForm" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium shadow-sm transition-colors">
                        {{ editingAnggota ? 'Simpan Perubahan' : 'Tambah Anggota' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
