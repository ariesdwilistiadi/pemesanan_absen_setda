<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
<template>
    <GuestLayout>
        <Head title="Register" />

        <form
            @submit.prevent="submit"
            class="space-y-6"
        >

            <!-- HEADER -->
            <div class="text-center mb-8">

                <h1
                    class="text-3xl font-bold text-white"
                >
                   Daftar Akun
                </h1>

                <p
                    class="mt-2 text-blue-100"
                >
                    Daftar untuk mulai menggunakan dashboard
                </p>

            </div>

            <!-- NAME -->
            <div>

                <InputLabel
                    for="name"
                    value="Nama Lengkap"
                    class="mb-2 block text-sm font-semibold text-blue-50"
                />

                <div class="relative">

                    <TextInput
                        id="name"
                        type="text"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Masukkan nama lengkap"
                        class="w-full rounded-2xl border border-white/10 bg-white/10 backdrop-blur-xl px-5 py-4 text-white placeholder:text-blue-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-500"
                    />

                </div>

                <InputError
                    class="mt-2 text-red-300"
                    :message="form.errors.name"
                />

            </div>

            <!-- EMAIL -->
            <div>

                <InputLabel
                    for="email"
                    value="Email"
                    class="mb-2 block text-sm font-semibold text-blue-50"
                />

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="contoh@email.com"
                    class="w-full rounded-2xl border border-white/10 bg-white/10 backdrop-blur-xl px-5 py-4 text-white placeholder:text-blue-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-500"
                />

                <InputError
                    class="mt-2 text-red-300"
                    :message="form.errors.email"
                />

            </div>

            <!-- PASSWORD -->
            <div>

                <InputLabel
                    for="password"
                    value="Password"
                    class="mb-2 block text-sm font-semibold text-blue-50"
                />

                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Masukkan password"
                    class="w-full rounded-2xl border border-white/10 bg-white/10 backdrop-blur-xl px-5 py-4 text-white placeholder:text-blue-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-500"
                />

                <InputError
                    class="mt-2 text-red-300"
                    :message="form.errors.password"
                />

            </div>

            <!-- CONFIRM PASSWORD -->
            <div>

                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                    class="mb-2 block text-sm font-semibold text-blue-50"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Konfirmasi password"
                    class="w-full rounded-2xl border border-white/10 bg-white/10 backdrop-blur-xl px-5 py-4 text-white placeholder:text-blue-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-500"
                />

                <InputError
                    class="mt-2 text-red-300"
                    :message="form.errors.password_confirmation"
                />

            </div>

            <!-- ACTION -->
            <div class="pt-2">

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="group relative w-full overflow-hidden rounded-2xl bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 px-6 py-4 font-bold text-white shadow-2xl transition-all duration-300 hover:scale-[1.02] hover:shadow-blue-500/40 disabled:opacity-50"
                >

                    <span
                        class="absolute inset-0 bg-white/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    ></span>

                    <span class="relative">

                        {{
                            form.processing
                                ? 'Processing...'
                                : 'Create Account'
                        }}

                    </span>

                </button>

            </div>

            <!-- LOGIN -->
            <div class="text-center pt-2">

                <p class="text-sm text-blue-100">

                    Sudah punya akun?

                    <Link
                        :href="route('login')"
                        class="font-semibold text-white hover:text-blue-200 transition-colors"
                    >
                        Login sekarang
                    </Link>

                </p>

            </div>

        </form>
    </GuestLayout>
</template>