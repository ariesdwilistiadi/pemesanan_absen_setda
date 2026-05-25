<script setup>
import { ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});

const showModal = ref(false);
const editingUser = ref(null);
const formData = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const openAddModal = () => {
    editingUser.value = null;
    formData.value = { name: '', email: '', password: '', password_confirmation: '' };
    showModal.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    formData.value = { name: user.name, email: user.email, password: '', password_confirmation: '' };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingUser.value = null;
    formData.value = { name: '', email: '', password: '', password_confirmation: '' };
};

const submitForm = () => {
    if (!formData.value.name.trim() || !formData.value.email.trim()) {
        alert('Nama dan email tidak boleh kosong!');
        return;
    }

    if (!editingUser.value && (!formData.value.password || formData.value.password !== formData.value.password_confirmation)) {
        alert('Password dan konfirmasi password harus diisi dan sama!');
        return;
    }

    if (editingUser.value) {
        router.put(route('users.update', editingUser.value.id), formData.value, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => alert('Gagal memperbarui user: ' + JSON.stringify(errors))
        });
    } else {
        router.post(route('users.store'), formData.value, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => alert('Gagal membuat user: ' + JSON.stringify(errors))
        });
    }
};

const deleteUser = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        router.delete(route('users.destroy', id), {
            preserveScroll: true,
            onError: (errors) => alert('Gagal menghapus user: ' + JSON.stringify(errors))
        });
    }
};
</script>

<template>
    <Head title="Manajemen User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Manajemen User</h2>

                <button
                    @click="openAddModal"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-sm"
                >
                    + Tambah User
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-6xl mx-auto px-4">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <h3 class="text-lg font-bold text-gray-900">Daftar User</h3>
                        <p class="text-sm text-gray-500">Kelola semua user yang terdaftar di sistem.</p>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Nama</th>
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Email</th>
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Bergabung</th>
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users" :key="user.id" class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-4 px-4">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold mr-3">
                                                    {{ user.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-900">{{ user.name }}</p>
                                                    <p v-if="user.id === $page.props.auth.user.id" class="text-xs text-indigo-600 font-medium">Akun Anda</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-gray-600">{{ user.email }}</td>
                                        <td class="py-4 px-4 text-gray-600 text-sm">
                                            {{ new Date(user.created_at).toLocaleDateString('id-ID') }}
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex space-x-2">
                                                <button
                                                    @click="openEditModal(user)"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Edit"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-4l7-7m0 0l-7 7" />
                                                    </svg>
                                                </button>
                                                <button
                                                    v-if="user.id !== $page.props.auth.user.id"
                                                    @click="deleteUser(user.id)"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
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

                        <div v-if="users.length === 0" class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 text-gray-300 mb-4">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">Belum ada data user.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6 overflow-hidden">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    {{ editingUser ? 'Edit User' : 'Tambah User Baru' }}
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input
                            v-model="formData.name"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600"
                            placeholder="Masukkan nama lengkap"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            v-model="formData.email"
                            type="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600"
                            placeholder="Masukkan email"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password {{ editingUser ? '(Kosongkan jika tidak ingin mengubah)' : '' }}
                        </label>
                        <input
                            v-model="formData.password"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600"
                            placeholder="Masukkan password"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input
                            v-model="formData.password_confirmation"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600"
                            placeholder="Konfirmasi password"
                        />
                    </div>
                </div>

                <div class="mt-6 flex space-x-3 justify-end">
                    <button @click="closeModal" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button @click="submitForm" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
                        {{ editingUser ? 'Simpan' : 'Tambah' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>