<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue';

// Definisikan form dengan semua field baru
const form = useForm({
    name: '',
    destination_summary: '',
    location_details: '',
    description: '',
    duration_days: 1,
    price: 0,
    discount_percent: 0,
    includes_hotel: false,
    includes_guide: false,
    includes_entrance_fee: false,
    includes_driver_vehicle: false,
    cover_image: null,
    status: 'draft', // Default status
    category: '',
});

// Fungsi submit
const submit = () => {
    form.post(route('admin.packages.store'), {
        forceFormData: true, // Diperlukan karena ada upload file
        onError: (errors) => {
            console.error("Validation Errors:", errors); // Log error jika ada
        }
    });
};
</script>

<template>
    <Head title="Tambah Paket" />
    <AdminLayout>
        <h1 class="text-3xl font-bold mb-6 text-brand-cyan">Tambah Paket Wisata Baru</h1>

        <form @submit.prevent="submit" class="max-w-3xl space-y-6 bg-gray-800 p-8 rounded-lg shadow-lg border border-brand-border">

            <div>
                <InputLabel for="name" value="Nama Paket" class="!text-brand-cyan !font-semibold" />
                <TextInput id="name" type="text" class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="destination_summary" value="Ringkasan Destinasi (cth: Yogyakarta & Magelang)" class="!text-brand-cyan !font-semibold" />
                <TextInput id="destination_summary" type="text" class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.destination_summary" required />
                <InputError class="mt-2" :message="form.errors.destination_summary" />
            </div>

            <div>
                <InputLabel for="location_details" value="Detail Lokasi (Opsional)" class="!text-brand-cyan !font-semibold" />
                <textarea id="location_details" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white" v-model="form.location_details" rows="3"></textarea>
                <InputError class="mt-2" :message="form.errors.location_details" />
            </div>

            <div>
                <InputLabel for="description" value="Deskripsi Lengkap" class="!text-brand-cyan !font-semibold" />
                <TiptapEditor 
                    id="description" 
                    v-model="form.description" 
                    class="mt-1"
                    :form="form" 
                    field-name="description"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel for="duration_days" value="Durasi (Hari)" class="!text-brand-cyan !font-semibold" />
                    <TextInput id="duration_days" type="number" min="1" class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.duration_days" required />
                    <InputError class="mt-2" :message="form.errors.duration_days" />
                </div>
                 <div>
                    <InputLabel for="price" value="Harga (Rp)" class="!text-brand-cyan !font-semibold" />
                    <TextInput id="price" type="number" min="0" step="1000" class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.price" required />
                    <InputError class="mt-2" :message="form.errors.price" />
                </div>
                 <div>
                    <InputLabel for="discount_percent" value="Diskon (%)" class="!text-brand-cyan !font-semibold" />
                    <TextInput id="discount_percent" type="number" min="0" max="100" class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.discount_percent" />
                    <InputError class="mt-2" :message="form.errors.discount_percent" />
                </div>
            </div>

            <div class="space-y-3 pt-2">
                <InputLabel value="Fasilitas Termasuk" class="!text-brand-cyan !font-semibold mb-2" />
                <label class="flex items-center">
                    <Checkbox name="includes_hotel" class="rounded border-gray-500 text-brand-blue focus:ring-brand-blue" v-model:checked="form.includes_hotel" />
                    <span class="ms-2 text-sm text-gray-300">Hotel</span>
                </label>
                 <label class="flex items-center">
                    <Checkbox name="includes_guide" class="rounded border-gray-500 text-brand-blue focus:ring-brand-blue" v-model:checked="form.includes_guide" />
                    <span class="ms-2 text-sm text-gray-300">Pemandu Wisata</span>
                </label>
                 <label class="flex items-center">
                    <Checkbox name="includes_entrance_fee" class="rounded border-gray-500 text-brand-blue focus:ring-brand-blue" v-model:checked="form.includes_entrance_fee" />
                    <span class="ms-2 text-sm text-gray-300">Tiket Masuk Wisata</span>
                </label>
                 <label class="flex items-center">
                    <Checkbox name="includes_driver_vehicle" class="rounded border-gray-500 text-brand-blue focus:ring-brand-blue" v-model:checked="form.includes_driver_vehicle" />
                    <span class="ms-2 text-sm text-gray-300">Driver & Kendaraan</span>
                </label>
                 <InputError class="mt-2" :message="form.errors.includes_hotel" />
                 <InputError class="mt-2" :message="form.errors.includes_guide" />
                 <InputError class="mt-2" :message="form.errors.includes_entrance_fee" />
                 <InputError class="mt-2" :message="form.errors.includes_driver_vehicle" />
            </div>

            <div>
                <InputLabel for="category" value="Kategori (Opsional, cth: Short Trip, Honeymoon)" class="!text-brand-cyan !font-semibold" />
                <TextInput id="category" type="text" class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.category" />
                <InputError class="mt-2" :message="form.errors.category" />
            </div>

            <div>
                <InputLabel for="status" value="Status" class="!text-brand-cyan !font-semibold" />
                <select id="status" v-model="form.status" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
                <InputError class="mt-2" :message="form.errors.status" />
            </div>

            <div>
                <InputLabel for="cover_image" value="Gambar Sampul" class="!text-brand-cyan !font-semibold" />
                <input id="cover_image" type="file" @input="form.cover_image = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-cyan file:text-brand-dark hover:file:bg-brand-blue cursor-pointer"/>
                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full mt-2 h-2 rounded [&::-webkit-progress-bar]:rounded [&::-webkit-progress-value]:rounded [&::-webkit-progress-bar]:bg-gray-700 [&::-webkit-progress-value]:bg-brand-cyan [&::-moz-progress-bar]:bg-brand-cyan">
                    {{ form.progress.percentage }}%
                </progress>
                <InputError class="mt-2" :message="form.errors.cover_image" />
            </div>

            <div class="flex items-center justify-end mt-6">
                <PrimaryButton class="ms-4 bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan focus:ring-offset-gray-800" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Simpan Paket
                </PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>