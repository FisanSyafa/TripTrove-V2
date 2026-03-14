<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    code: '',
    additional_charge: 0,
});

const submit = () => {
    form.post(route('admin.vehicle-types.store'));
};
</script>
<template>
    <Head title="Tambah Tipe Kendaraan" />
    <AdminLayout>
        <h1 class="text-3xl font-bold mb-6 text-brand-cyan">Tambah Tipe Kendaraan</h1>
        <form @submit.prevent="submit" class="max-w-xl space-y-6 bg-[#111c2e] p-8 rounded-xl shadow-lg border border-gray-700/40">
            <div>
                <InputLabel for="name" value="Nama Tipe (cth: Multi Purpose Vehicle)" class="!text-brand-cyan" />
                <TextInput id="name" type="text" class="mt-1 block w-full bg-[#0c1222] text-white border-gray-600/50" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="code" value="Kode Unik (cth: MVP)" class="!text-brand-cyan" />
                <TextInput id="code" type="text" class="mt-1 block w-full bg-[#0c1222] text-white border-gray-600/50" v-model="form.code" required />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>
            <div>
                <InputLabel for="additional_charge" value="Biaya Tambahan (Rp)" class="!text-brand-cyan" />
                <TextInput id="additional_charge" type="number" min="0" class="mt-1 block w-full bg-[#0c1222] text-white border-gray-600/50" v-model="form.additional_charge" />
                <InputError class="mt-2" :message="form.errors.additional_charge" />
            </div>
            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing" class="bg-gradient-to-r from-brand-cyan to-brand-blue">Simpan</PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>