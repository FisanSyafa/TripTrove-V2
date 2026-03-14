<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

// Inisialisasi form
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'driver', // Default role 'driver'
});

// Fungsi submit
const submit = () => {
    form.post(route('admin.users.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Tambah User Baru" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah User Baru (Driver/Guide)</h2>
        </template>

        <h1 class="text-3xl font-bold mb-6 text-brand-cyan">Tambah User Baru (Driver/Guide)</h1>

        <form @submit.prevent="submit" class="max-w-xl space-y-6 bg-gray-800 p-8 rounded-lg shadow-lg border border-brand-border">
            <div>
                <InputLabel for="name" value="Nama" class="!text-brand-cyan" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full bg-gray-700 text-white border-gray-600"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" class="!text-brand-cyan" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-gray-700 text-white border-gray-600"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            
            <div>
                <InputLabel for="role" value="Role" class="!text-brand-cyan" />
                <select
                    id="role"
                    class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white"
                    v-model="form.role"
                    required
                >
                    <option value="user">User Biasa</option>
                    <option value="driver">Driver</option>
                    <option value="guide">Guide</option>
                    <option value="admin">Admin</option>
                </select>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="!text-brand-cyan" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full bg-gray-700 text-white border-gray-600"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Konfirmasi Password" class="!text-brand-cyan" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full bg-gray-700 text-white border-gray-600"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing" class="bg-gradient-to-r from-brand-cyan to-brand-blue">Simpan User</PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>