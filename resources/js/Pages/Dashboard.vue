<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                Dashboard Overview
            </h2>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-3xl p-8 shadow-lg shadow-indigo-200 text-white">
                <h1 class="text-3xl font-extrabold mb-2">Selamat Datang, {{ $page.props.auth.user.name }}! 👋</h1>
                <p class="text-indigo-100 text-lg max-w-2xl">
                    Ini adalah tampilan dashboard baru Anda. Menu sidebar di sebelah kiri juga sudah siap untuk memuat data menu secara dinamis dari database Anda.
                </p>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-4 rounded-full bg-blue-50 text-blue-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Pengguna</h3>
                            <p class="text-3xl font-black text-gray-900 mt-1">1,234</p>
                        </div>
                    </div>
                </div>
                
                <!-- Card 2 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-4 rounded-full bg-emerald-50 text-emerald-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pendapatan</h3>
                            <p class="text-3xl font-black text-gray-900 mt-1">Rp 45.2M</p>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-4 rounded-full bg-purple-50 text-purple-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Aktivitas</h3>
                            <p class="text-3xl font-black text-gray-900 mt-1">892</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 min-h-[400px]">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Aktivitas Terbaru</h3>
                    <button class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Lihat Semua &rarr;</button>
                </div>
                <div class="flex flex-col items-center justify-center h-64 text-center border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50">
                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    <p class="text-gray-500 font-medium">Belum ada data aktivitas untuk ditampilkan.</p>
                    <p class="text-sm text-gray-400 mt-1">Data akan muncul di sini saat aplikasi mulai digunakan.</p>
                </div>
            </div>
            
        </div>
    </AuthenticatedLayout>
</template>
