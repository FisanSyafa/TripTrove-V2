<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
    roles: Array // Data role dari controller
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    phone_number: props.user.phone_number,
    address: props.user.address,
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};
</script>
<template>
    <Head :title="`Edit User: ${user.name}`" />
    <AdminLayout>
        <div class="mb-6">
            <Link :href="route('admin.users.index')" class="inline-flex items-center text-sm text-brand-cyan hover:text-blue-400 group">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition-transform">
                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                </svg>
                Kembali ke Daftar User
            </Link>
            <h1 class="text-3xl font-bold text-brand-cyan mt-2">Edit Pengguna</h1>
        </div>
        
        <form @submit.prevent="submit" class="max-w-xl space-y-6 bg-[#111c2e] p-8 rounded-xl shadow-lg border border-gray-700/40">
            <div>
                <InputLabel for="name" value="Nama Lengkap" class="!text-brand-cyan" />
                <TextInput id="name" type="text" class="mt-1 block w-full bg-[#0c1222] text-white border-gray-600/50" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="email" value="Email" class="!text-brand-cyan" />
                <TextInput id="email" type="email" class="mt-1 block w-full bg-[#0c1222] text-white border-gray-600/50" v-model="form.email" required />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div>
                <InputLabel for="phone_number" value="Nomor Telepon" class="!text-brand-cyan" />
                <TextInput id="phone_number" type="tel" class="mt-1 block w-full bg-[#0c1222] text-white border-gray-600/50" v-model="form.phone_number" />
                <InputError class="mt-2" :message="form.errors.phone_number" />
            </div>
             <div>
                <InputLabel for="address" value="Alamat" class="!text-brand-cyan" />
                <textarea id="address" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white" v-model="form.address" rows="3"></textarea>
                <InputError class="mt-2" :message="form.errors.address" />
            </div>
            <div>
                <InputLabel for="role" value="Role Pengguna" class="!text-brand-cyan" />
                <select id="role" v-model="form.role" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white" required>
                    <option v-for="role in roles" :key="role.value" :value="role.value">
                        {{ role.label }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>
            
            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing" class="bg-gradient-to-r from-brand-cyan to-brand-blue">Update User</PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>