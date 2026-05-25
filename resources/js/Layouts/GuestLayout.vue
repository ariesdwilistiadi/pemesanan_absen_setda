<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 px-4 py-10"
    >

        <!-- BACKGROUND -->
        <div
            class="absolute inset-0 overflow-hidden"
        >

            <div
                class="absolute top-0 left-0 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl"
            ></div>

            <div
                class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-indigo-500/20 rounded-full blur-3xl"
            ></div>

            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-cyan-500/10 rounded-full blur-3xl"
            ></div>

        </div>

        <!-- CONTENT -->
        <div class="relative z-10 w-full max-w-md">

            <!-- LOGO -->
            <div class="mb-8 text-center">

                <Link
                    href="/"
                    class="inline-flex flex-col items-center"
                >

                    <div
                        class="flex items-center justify-center w-24 h-24 rounded-3xl bg-white/10 backdrop-blur-xl border border-white/10 shadow-2xl"
                    >

                        <ApplicationLogo
                            class="h-14 w-14 fill-current text-white"
                        />

                    </div>

                    <h1
                        class="mt-5 text-3xl font-bold text-white tracking-wide"
                    >
                        Admin Panel
                    </h1>

                    <p
                        class="mt-2 text-sm text-blue-100"
                    >
                        Modern Management System
                    </p>

                </Link>

            </div>

            <!-- CARD -->
            <div
                class="rounded-[32px] border border-white/10 bg-white/10 backdrop-blur-2xl shadow-2xl overflow-hidden"
            >

                <!-- TOP -->
                <div
                    class="px-8 pt-8 pb-4"
                >

                    <h2
                        class="text-2xl font-bold text-white"
                    >
                        Welcome Back 👋
                    </h2>

                    <p
                        class="mt-2 text-sm text-blue-100"
                    >
                        Silakan login untuk melanjutkan ke dashboard.
                    </p>

                </div>

                <!-- SLOT -->
                <div
                    class="px-8 pb-8"
                >
                    <slot />
                </div>

            </div>

            <!-- FOOTER -->
            <div
                class="mt-6 text-center text-sm text-blue-200"
            >
                © 2026 Admin Dashboard. All rights reserved.
            </div>

        </div>

    </div>
</template>