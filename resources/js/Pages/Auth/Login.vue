<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    loginChallenge: String,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
    challenge_answer: '',
    website: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password', 'challenge_answer', 'website'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Login" />

        <div
            class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 via-white to-emerald-200 px-4 py-10">

            <!-- Background Blur -->
            <div
                class="absolute top-0 left-0 w-72 h-72 bg-green-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse">
            </div>

            <div
                class="absolute bottom-0 right-0 w-72 h-72 bg-emerald-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse">
            </div>

            <!-- Card -->
            <div
                class="relative w-full max-w-md backdrop-blur-xl bg-white/70 border border-white/30 shadow-2xl rounded-3xl p-8">

                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <div
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-10 h-10 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5.121 17.804A9 9 0 1118.364 4.56M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-black text-gray-800">
                        Welcome Back
                    </h1>

                    <p class="mt-2 text-gray-500 text-sm">
                        Login untuk melanjutkan ke dashboard
                    </p>
                </div>

                <!-- Alert -->
                <div v-if="status"
                    class="mb-5 bg-green-100 border border-green-200 text-green-700 text-sm rounded-2xl px-4 py-3">
                    {{ status }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email -->
                    <div>
                        <InputLabel for="email"
                            value="Email"
                            class="mb-2 text-sm font-semibold text-gray-700" />

                        <TextInput id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Masukkan email"
                            class="w-full rounded-2xl border-0 bg-white/80 shadow-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-green-500 py-3 px-4 transition duration-300" />

                        <InputError class="mt-2"
                            :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex justify-between mb-2">
                            <InputLabel for="password"
                                value="Password"
                                class="text-sm font-semibold text-gray-700" />

                            <Link v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-green-600 hover:text-green-700 font-medium">
                            Forgot Password?
                            </Link>
                        </div>

                        <TextInput id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full rounded-2xl border-0 bg-white/80 shadow-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-green-500 py-3 px-4 transition duration-300" />

                        <InputError class="mt-2"
                            :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="challenge_answer"
                            :value="`Verifikasi manusia: ${loginChallenge} = ?`"
                            class="mb-2 text-sm font-semibold text-gray-700" />

                        <TextInput id="challenge_answer"
                            type="number"
                            v-model="form.challenge_answer"
                            required
                            inputmode="numeric"
                            placeholder="Jawab hasil perhitungan"
                            class="w-full rounded-2xl border-0 bg-white/80 shadow-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-green-500 py-3 px-4 transition duration-300" />

                        <input
                            v-model="form.website"
                            type="text"
                            name="website"
                            tabindex="-1"
                            autocomplete="off"
                            class="hidden"
                            aria-hidden="true"
                        />

                        <p class="mt-2 text-xs text-gray-500">
                            Pemeriksaan ini membantu memastikan yang login adalah manusia, bukan bot.
                        </p>

                        <InputError class="mt-2"
                            :message="form.errors.challenge_answer || form.errors.website" />
                    </div>

                    <!-- Remember -->
                    <div class="flex items-center justify-between">

                        <label class="flex items-center gap-2 cursor-pointer">
                            <Checkbox name="remember"
                                v-model:checked="form.remember"
                                class="rounded border-gray-300 text-green-600 focus:ring-green-500" />

                            <span class="text-sm text-gray-600">
                                Remember me
                            </span>
                        </label>
                    </div>

                    <!-- Button -->
                    <PrimaryButton
                        class="w-full flex justify-center items-center py-3.5 rounded-2xl bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold shadow-lg transition-all duration-300 hover:scale-[1.02]"
                        :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                        :disabled="form.processing">

                        <span v-if="!form.processing">
                            Sign In
                        </span>

                        <span v-else class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4">
                                </circle>

                                <path class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>

                            Processing...
                        </span>

                    </PrimaryButton>

                </form>

            </div>

        </div>

    </GuestLayout>
</template>
