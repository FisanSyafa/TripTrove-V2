<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    vehicle: Object,
    vehicleTypes: Array // Data dropdown dari controller
});

const form = useForm({
    name: props.vehicle.name,
    license_plate: props.vehicle.license_plate,
    color: props.vehicle.color,
    is_available: props.vehicle.is_available,
    vehicle_type_id: props.vehicle.vehicle_type_id,
});

const submit = () => {
    form.put(route('admin.vehicles.update', props.vehicle.id));
};
</script>
<template>
    <Head :title="`Edit Kendaraan: ${vehicle.name}`" />
    <AdminLayout>
        <h1 class="text-3xl font-bold mb-6 text-brand-cyan">Edit Kendaraan</h1>
        <form @submit.prevent="submit" class="max-w-xl space-y-6 bg-gray-800 p-8 rounded-lg shadow-lg border border-brand-border">
            <div>
                <InputLabel for="vehicle_type_id" value="Tipe Kendaraan" class="!text-brand-cyan" />
                <select id="vehicle_type_id" v-model="form.vehicle_type_id" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white" required>
                    <option value="" disabled>Pilih Tipe</option>
                    <option v-for="type in vehicleTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.vehicle_type_id" />
            </div>
            <div>
                <InputLabel for="name" value="Nama Kendaraan" class="!text-brand-cyan" />
                <TextInput id="name" type="text" class="mt-1 block w-full bg-gray-700 text-white border-gray-600" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="license_plate" value="Plat Nomor" class="!text-brand-cyan" />
                <TextInput id="license_plate" type="text" class="mt-1 block w-full bg-gray-700 text-white border-gray-600" v-model="form.license_plate" required />
                <InputError class="mt-2" :message="form.errors.license_plate" />
            </div>
            <div>
                <InputLabel for="color" value="Warna" class="!text-brand-cyan" />
                <TextInput id="color" type="text" class="mt-1 block w-full bg-gray-700 text-white border-gray-600" v-model="form.color" />
                <InputError class="mt-2" :message="form.errors.color" />
            </div>
             <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox name="is_available" class="rounded border-gray-500 text-brand-blue" v-model:checked="form.is_available" />
                    <span class="ms-2 text-sm text-gray-300">Tersedia untuk booking</span>
                </label>
            </div>
            <div class="flex items-center justify-end">
                <PrimaryButton :disabled="form.processing" class="bg-gradient-to-r from-brand-cyan to-brand-blue">Update</PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>