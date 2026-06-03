<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    instansi: Object,
});

const form = useForm({
    pemerintah: props.instansi.pemerintah || '',
    nama_instansi: props.instansi.nama_instansi || '',
    alamat: props.instansi.alamat || '',
    kontak: props.instansi.kontak || '',
    nama_kepala: props.instansi.nama_kepala || '',
    nip_kepala: props.instansi.nip_kepala || '',
    jabatan_kepala: props.instansi.jabatan_kepala || '',
    logo: null,
    qris_image: null,
});

const logoPreview = ref(props.instansi.logo ? '/storage/' + props.instansi.logo : null);
const qrisPreview = ref(props.instansi.qris_image ? '/storage/' + props.instansi.qris_image : null);

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    form.logo = file;

    console.log('handleLogoChange logo file:', file);

    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    } else {
        logoPreview.value = props.instansi.logo ? '/storage/' + props.instansi.logo : null;
    }
};

const handleQrisChange = (e) => {
    const file = e.target.files[0];
    form.qris_image = file;

    if (file) {
        qrisPreview.value = URL.createObjectURL(file);
    } else {
        qrisPreview.value = props.instansi.qris_image ? '/storage/' + props.instansi.qris_image : null;
    }
};

const submit = () => {
    console.log('Submitting form, logo is File?', form.logo instanceof File, 'logo:', form.logo);

    const payload = new FormData();
    payload.append('pemerintah', form.pemerintah || '');
    payload.append('nama_instansi', form.nama_instansi || '');
    payload.append('alamat', form.alamat || '');
    payload.append('kontak', form.kontak || '');
    payload.append('nama_kepala', form.nama_kepala || '');
    payload.append('nip_kepala', form.nip_kepala || '');
    payload.append('jabatan_kepala', form.jabatan_kepala || '');
    if (form.logo instanceof File) payload.append('logo', form.logo);
    if (form.qris_image instanceof File) payload.append('qris_image', form.qris_image);

    Inertia.post(route('instansi.update'), payload, {
        preserveScroll: true,
        onSuccess: () => {
            alert('Profil berhasil diperbarui!');
        }
    });
};
</script>

<template>
    <Head title="Pengaturan Profil Instansi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengaturan Profil Instansi</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash.success" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm">
                    <span class="block sm:inline font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                    <form @submit.prevent="submit" class="p-6 text-gray-900 space-y-6">
                        
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Kop Surat</h3>
                            <p class="text-sm text-gray-500">Data ini akan ditampilkan pada bagian atas dokumen cetak (seperti Daftar Hadir).</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemerintah (Baris 1)</label>
                                <input v-model="form.pemerintah" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="PEMERINTAH PROVINSI...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Instansi/Dinas (Baris 2)</label>
                                <input v-model="form.nama_instansi" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="DINAS ...">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                                <textarea v-model="form.alamat" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kontak (Telp / Email / Web)</label>
                                <input v-model="form.kontak" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="border-b pb-4 mb-4 mt-8">
                            <h3 class="text-lg font-bold text-gray-900">Tanda Tangan Pengesahan</h3>
                            <p class="text-sm text-gray-500">Data Kepala Dinas/Instansi untuk bagian tanda tangan pengesahan dokumen.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kepala</label>
                                <input v-model="form.nama_kepala" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NIP Kepala</label>
                                <input v-model="form.nip_kepala" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan (Teks di atas nama)</label>
                                <input v-model="form.jabatan_kepala" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Kepala Dinas ...">
                            </div>
                        </div>

                        <div class="border-b pb-4 mb-4 mt-8">
                            <h3 class="text-lg font-bold text-gray-900">Logo Instansi</h3>
                        </div>
                        
                        <div>
                            <div v-if="logoPreview" class="mb-4">
                                <img :src="logoPreview" alt="Logo Preview" class="h-24 object-contain">
                            </div>
                            <input type="file" @change="handleLogoChange" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div class="border-b pb-4 mb-4 mt-8">
                            <h3 class="text-lg font-bold text-gray-900">QRIS Pembayaran</h3>
                            <p class="text-sm text-gray-500">Gambar QRIS ini akan digunakan di halaman kasir untuk pembayaran.</p>
                        </div>
                        
                        <div>
                            <div v-if="qrisPreview" class="mb-4">
                                <img :src="qrisPreview" alt="QRIS Preview" class="h-48 object-contain">
                            </div>
                            <input type="file" @change="handleQrisChange" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div class="pt-6 flex justify-end">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-md transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Profil Instansi' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>