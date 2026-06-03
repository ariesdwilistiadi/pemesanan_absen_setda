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

        <div v-if="status"
            class="mb-5 bg-green-50 border border-green-200 text-green-700 text-sm rounded-2xl px-4 py-3 font-medium">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">

            <div>
                <InputLabel for="email" value="Email" class="mb-2 text-sm font-bold text-gray-700" />
                <TextInput id="email" type="email" v-model="form.email" required autofocus autocomplete="username" placeholder="Masukkan email"
                    class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 py-3 px-4 transition duration-200" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex justify-between mb-2">
                    <InputLabel for="password" value="Password" class="text-sm font-bold text-gray-700" />
                    <Link v-if="canResetPassword" :href="route('password.request')"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                        Forgot Password?
                    </Link>
                </div>
                <TextInput id="password" type="password" v-model="form.password" required autocomplete="current-password" placeholder="••••••••"
                    class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 py-3 px-4 transition duration-200" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="challenge_answer" :value="`Verifikasi manusia: ${loginChallenge} = ?`"
                    class="mb-2 text-sm font-bold text-gray-700" />
                <TextInput id="challenge_answer" type="number" v-model="form.challenge_answer" required inputmode="numeric" placeholder="Jawab hasil perhitungan"
                    class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 py-3 px-4 transition duration-200" />

                <input v-model="form.website" type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />

                <p class="mt-2 text-xs text-gray-500">
                    Pemeriksaan ini membantu memastikan yang login adalah manusia, bukan bot.
                </p>

                <InputError class="mt-2" :message="form.errors.challenge_answer || form.errors.website" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <Checkbox name="remember" v-model:checked="form.remember"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <span class="text-sm text-gray-600 font-medium group-hover:text-gray-900 transition-colors">
                        Remember me
                    </span>
                </label>
            </div>

            <PrimaryButton
                class="w-full flex justify-center items-center py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md transition-all duration-300 hover:shadow-lg active:scale-95"
                :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                :disabled="form.processing">
                
                <span v-if="!form.processing">
                    Sign In
                </span>
                
                <span v-else class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Processing...
                </span>
            </PrimaryButton>

        </form>
    </GuestLayout>
</template>