<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { useInactivityTimeout } from '@/Composables/useInactivityTimeout';

// Jalankan fitur auto logout (akan logout otomatis jika 15 menit tidak ada aktivitas)
useInactivityTimeout();

const showingNavigationDropdown = ref(false);
const openMenus = ref([]);

const toggleMenu = (id) => {
    const index = openMenus.value.indexOf(id);
    if (index > -1) {
        openMenus.value.splice(index, 1);
    } else {
        openMenus.value.push(id);
    }
};

// Helper agar layout tidak CRASH/BLANK jika admin menginput nama route yang salah/belum ada
const getRouteUrl = (routeName) => {
    if (!routeName) return '#';
    if (routeName.startsWith('http') || routeName.startsWith('/') || routeName.startsWith('#')) return routeName;
    try {
        return route(routeName);
    } catch (error) {
        console.warn(`Route "${routeName}" belum dibuat di web.php.`);
        return '#';
    }
};

const isRouteCurrent = (routeName) => {
    if (!routeName) return false;
    try {
        return route().current(routeName);
    } catch (error) {
        return false;
    }
};
</script>
<template>
    <div class="flex h-screen bg-slate-100 overflow-hidden">

        <!-- SIDEBAR -->
        <aside
            class="hidden sm:flex w-72 flex-col bg-gradient-to-b from-slate-900 via-blue-800 to-slate-900 text-white shadow-2xl shrink-0"
        >

            <!-- LOGO -->
            <div
                class="h-20 flex items-center px-6 border-b border-white/10"
            >
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-4"
                >

                    <div
                        class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center"
                    >
                        <ApplicationLogo
                            class="h-8 w-8 fill-current text-white"
                        />
                    </div>

                    <div>
                        <div class="font-bold text-lg tracking-wide">
                            Admin Panel
                        </div>

                        <div class="text-xs text-emerald-200">
                            Management System
                        </div>
                    </div>

                </Link>
            </div>

            <!-- MENU -->
            <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-3">

                <template
                    v-for="menu in $page.props.menus"
                    :key="menu.id"
                >

                    <!-- SINGLE -->
                    <Link
                        v-if="!menu.children || menu.children.length === 0"
                        :href="getRouteUrl(menu.route)"
                        :class="[
                            isRouteCurrent(menu.route)
                                ? 'bg-white text-blue-800 shadow-lg'
                                : 'text-emerald-100 hover:bg-white/10 hover:text-white',
                            'group flex items-center rounded-2xl px-4 py-3 font-medium transition-all duration-200'
                        ]"
                    >

                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center mr-3"
                            :class="
                                isRouteCurrent(menu.route)
                                    ? 'bg-emerald-100'
                                    : 'bg-white/10 group-hover:bg-white/20'
                            "
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 7l9-4 9 4M4 10h16v10H4V10z"
                                />
                            </svg>
                        </div>

                        <span class="truncate">
                            {{ menu.name }}
                        </span>

                    </Link>

                    <!-- GROUP -->
                    <div
                        v-else
                        class="space-y-2"
                    >

                        <button
                            @click="toggleMenu(menu.id)"
                            class="w-full flex items-center justify-between px-4 py-3 text-sm font-bold text-emerald-100 uppercase tracking-wider hover:bg-white/5 rounded-2xl transition-colors cursor-pointer"
                        >

                            <span>
                                {{ menu.name }}
                            </span>

                            <div class="flex items-center gap-2">
                                <span class="bg-white/10 text-xs px-2 py-1 rounded-lg">{{ menu.children.length }}</span>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="openMenus.includes(menu.id) ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </button>

                        <div v-show="openMenus.includes(menu.id)" class="space-y-1 pl-3 border-l border-white/10">

                            <Link
                                v-for="child in menu.children"
                                :key="child.id"
                                :href="getRouteUrl(child.route)"
                                :class="[
                                    isRouteCurrent(child.route)
                                        ? 'bg-white text-blue-800 shadow-md'
                                        : 'text-emerald-100 hover:bg-white/10 hover:text-white',
                                    'flex items-center rounded-xl px-4 py-3 text-sm font-medium transition-all duration-200'
                                ]"
                            >

                                <div
                                    class="w-2 h-2 rounded-full mr-3"
                                    :class="
                                        isRouteCurrent(child.route)
                                            ? 'bg-emerald-600'
                                            : 'bg-emerald-300'
                                    "
                                ></div>

                                <span class="truncate">
                                    {{ child.name }}
                                </span>

                            </Link>

                        </div>
                    </div>

                </template>

            </nav>

            <!-- USER -->
            <div
                class="p-4 border-t border-white/10"
            >

                <div
                    class="flex items-center gap-3 rounded-2xl bg-white/10 p-4 backdrop-blur-md"
                >

                    <div
                        class="w-12 h-12 rounded-2xl bg-white text-emerald-700 flex items-center justify-center font-bold text-lg"
                    >
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>

                    <div class="min-w-0">

                        <div class="font-semibold truncate">
                            {{ $page.props.auth.user.name }}
                        </div>

                        <div class="text-sm text-emerald-200 truncate">
                            {{ $page.props.auth.user.email }}
                        </div>

                    </div>

                </div>

            </div>

        </aside>

        <!-- CONTENT -->
        <div class="flex flex-1 flex-col overflow-hidden">

            <!-- TOPBAR -->
            <header
                class="h-20 bg-white/80 backdrop-blur-xl border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 shadow-sm"
            >

                <div class="flex items-center gap-4">

                    <!-- MOBILE -->
                    <button
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="sm:hidden w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center"
                    >
                        <svg
                            class="w-6 h-6"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>

                    <div>

                        <h1 class="text-2xl font-bold text-slate-800">
                            Dashboard
                        </h1>

                        <p class="text-sm text-slate-500">
                            Welcome back 👋
                        </p>

                    </div>

                </div>

                <!-- PROFILE -->
                <Dropdown align="right" width="56">

                    <template #trigger>

                        <button
                            class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-2 hover:shadow-md transition-all"
                        >

                            <div
                                class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold"
                            >
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>

                            <div class="hidden md:block text-left">

                                <div class="font-semibold text-slate-800">
                                    {{ $page.props.auth.user.name }}
                                </div>

                                <div class="text-sm text-slate-500">
                                    Administrator
                                </div>

                            </div>

                            <svg
                                class="w-5 h-5 text-slate-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>

                        </button>

                    </template>

                    <template #content>

                        <DropdownLink :href="route('profile.edit')">
                            Profile
                        </DropdownLink>

                        <DropdownLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                        >
                            Logout
                        </DropdownLink>

                    </template>

                </Dropdown>

            </header>

            <!-- MOBILE MENU -->
            <div
                v-if="showingNavigationDropdown"
                class="sm:hidden bg-slate-900 text-white overflow-y-auto"
            >

                <div class="p-4 space-y-2">

                    <template
                        v-for="menu in $page.props.menus"
                        :key="menu.id"
                    >

                        <ResponsiveNavLink
                            v-if="!menu.children || menu.children.length === 0"
                            :href="getRouteUrl(menu.route)"
                            :active="isRouteCurrent(menu.route)"
                        >
                            {{ menu.name }}
                        </ResponsiveNavLink>

                        <div v-else class="space-y-1">

                            <button
                                @click="toggleMenu(menu.id)"
                                class="w-full flex items-center justify-between px-3 py-2 text-sm font-bold text-emerald-200 uppercase"
                            >
                                <span>{{ menu.name }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="openMenus.includes(menu.id) ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <div v-show="openMenus.includes(menu.id)" class="pl-4 space-y-1">

                                <ResponsiveNavLink
                                    v-for="child in menu.children"
                                    :key="child.id"
                                    :href="getRouteUrl(child.route)"
                                    :active="isRouteCurrent(child.route)"
                                >
                                    {{ child.name }}
                                </ResponsiveNavLink>

                            </div>

                        </div>

                    </template>

                </div>

            </div>

            <!-- MAIN -->
            <div class="flex-1 overflow-y-auto bg-slate-100">

                <header
                    v-if="$slots.header"
                    class="px-4 sm:px-8 py-6"
                >
                    <slot name="header" />
                </header>

                <main class="px-4 sm:px-8 pb-8">
                    <slot />
                </main>

            </div>

        </div>
    </div>
</template>