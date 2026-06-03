<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gray-50 px-4 py-10">

        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 left-0 w-96 h-96 bg-blue-100/50 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-indigo-100/50 rounded-full blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-md">

            <div class="mb-8 text-center">
                <Link href="/" class="flex flex-col items-center justify-center hover:opacity-90 transition-opacity">
                    
                    <img 
                        src="https://kotabogor.go.id/assets/images/resources/logo-1.png" 
                        alt="Logo Kota Bogor" 
                        class="h-24 w-auto drop-shadow-md object-contain" 
                    />
                    
                    <h1 class="mt-5 text-3xl font-black text-gray-900 tracking-wide">
                        SAPA SEKDA
                    </h1>
                    
                    <p class="mt-2 text-sm text-gray-500 font-medium">
                        Pemerintah Kota Bogor
                    </p>
                </Link>
            </div>

            <div class="rounded-3xl border border-gray-100 bg-white shadow-xl overflow-hidden">

                <div class="px-8 pt-8 pb-4">
                    <h2 class="text-2xl font-bold text-gray-900">
                        Welcome Back 👋
                    </h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Silakan login untuk melanjutkan ke dashboard.
                    </p>
                </div>

                <div class="px-8 pb-8">
                    <slot />
                </div>

            </div>

            <div class="mt-8 text-center text-sm text-gray-400 font-medium">
                © 2026 Admin Dashboard. All rights reserved.
            </div>

        </div>

    </div>
</template>