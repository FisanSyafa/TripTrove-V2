<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    type: Object
});

const form = useForm({
    name: props.type.name,
    code: props.type.code,
    additional_charge: props.type.additional_charge,
});

const submit = () => {
    form.put(route('admin.vehicle-types.update', props.type.id));
};
</script>
<template>
    <Head :title="`Edit Tipe: ${type.name}`" />
    <AdminLayout>
        <h1 class="text-3xl font-bold mb-6 text-brand-cyan">Edit Tipe Kendaraan</h1>
        <form @submit.prevent="submit" class="max-w-xl space-y-6 bg-gray-800 p-8 rounded-lg shadow-lg border border-brand-border">
             <div>
                <InputLabel for="name" value="Nama Tipe" class="!text-brand-cyan" />
                <TextInput id="name" type="text" class="mt-1 block w-full bg-gray-700 text-white border-gray-600" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="code" value="Kode Unik" class="!text-brand-cyan" />
                <TextInput id="code" type="text" class="mt-1 block w-full bg-gray-700 text-white border-gray-600" v-model="form.code" required />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>
            <div>
                <InputLabel for="additional_charge" value="Biaya Tambahan (Rp)" class="!text-brand-cyan" />
                <TextInput id="additional_charge" type="number" min="0" class="mt-1 block w-full bg-gray-700 text-white border-gray-600" v-model="form.additional_charge" />
                <InputError class="mt-2" :message="form.errors.additional_charge" />
            </div>
            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing" class="bg-gradient-to-r from-brand-cyan to-brand-blue">Update</PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>