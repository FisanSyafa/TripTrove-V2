<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue'; // Kita masih butuh ini untuk field lain
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue'; // Import Tiptap

// Terima props 'package' dari controller
const props = defineProps({
    package: Object
});

// Isi form dengan data yang ada
const form = useForm({
    _method: 'PUT',
    name: props.package.name,
    destination_summary: props.package.destination_summary,
    location_details: props.package.location_details,
    description: props.package.description,
    duration_days: props.package.duration_days,
    price: props.package.price,
    discount_percent: props.package.discount_percent,
    includes_hotel: props.package.includes_hotel,
    includes_guide: props.package.includes_guide,
    includes_entrance_fee: props.package.includes_entrance_fee,
    includes_driver_vehicle: props.package.includes_driver_vehicle,
    cover_image: null,
    status: props.package.status,
    category: props.package.category,
});

const galleryForm = useForm({
    images: [], 
});

const submitGallery = () => {
  galleryForm.post(
    route('admin.packages.gallery.store', { package: props.package.slug }),
    {
      forceFormData: true,
      onSuccess: () => galleryForm.reset('images'),
    }
  );
};

// Fungsi submit form utama
const submit = () => {
    form.post(route('admin.packages.update', props.package.slug), { 
        forceFormData: true,
        onError: (errors) => {
            console.error("Validation Errors:", errors);
        }
    });
};
</script>

<template>
    <Head :title="`Edit Paket: ${package.name}`" />
    <AdminLayout>
        <h1 class="text-3xl font-bold mb-6 text-brand-cyan">Edit Paket: {{ package.name }}</h1>

        <form @submit.prevent="submit" class="max-w-7xl space-y-6 bg-gray-800 p-8 rounded-lg shadow-lg border border-brand-border">
            
            <div>
                <InputLabel for="name" value="Nama Paket" class="!text-brand-cyan !font-semibold" />
                <TextInput id="name" type="text" class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="destination_summary" value="Ringkasan Destinasi" class="!text-brand-cyan !font-semibold" />
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
            </div>

            <div>
                <InputLabel for="category" value="Kategori (Opsional)" class="!text-brand-cyan !font-semibold" />
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
                <InputLabel for="cover_image" value="Ganti Gambar Sampul (Opsional)" class="!text-brand-cyan !font-semibold" />
                <input id="cover_image" type="file" @input="form.cover_image = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-cyan file:text-brand-dark hover:file:bg-brand-blue cursor-pointer"/>
                
                <progress 
                    v-if="form.progress" 
                    :value="form.progress ? form.progress.percentage : 0" 
                    max="100" 
                    class="w-full mt-2 h-2 rounded [&::-webkit-progress-bar]:rounded [&::-webkit-progress-value]:rounded [&::-webkit-progress-bar]:bg-gray-700 [&::-webkit-progress-value]:bg-brand-cyan [&::-moz-progress-bar]:bg-brand-cyan"
                >
                    {{ form.progress ? form.progress.percentage : 0 }}%
                </progress>
                <InputError class="mt-2" :message="form.errors.cover_image" />
                
                <div class="mt-4">
                    <p class="text-sm text-gray-400">Preview Gambar:</p>
                    <img v-if="form.cover_image && typeof form.cover_image === 'object'" :src="URL.createObjectURL(form.cover_image)" class="mt-2 h-32 object-cover rounded shadow">
                    <img v-else-if="package.cover_image_url" :src="`/storage/${package.cover_image_url}`" class="mt-2 h-32 object-cover rounded shadow">
                    <p v-else class="mt-2 text-sm text-gray-500">Tidak ada gambar.</p>
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <PrimaryButton class="ms-4 bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan focus:ring-offset-gray-800" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Update Paket
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-8 max-w-3xl bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-brand-border">
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Galeri Foto</h3>
                
                <form @submit.prevent="submitGallery" class="mb-6">
                    <InputLabel for="gallery_image" value="Tambah Gambar Baru (Bisa pilih banyak)" class="!text-brand-cyan !font-semibold" />
                    <div class="flex items-center gap-4 mt-1">
                        <input 
                            id="gallery_image"
                            type="file" 
                            @input="galleryForm.images = $event.target.files"  
                            class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-cyan file:text-brand-dark hover:file:bg-brand-cyan/20 cursor-pointer"
                            required
                            multiple 
                        />
                        <PrimaryButton :disabled="galleryForm.processing" class="bg-gradient-to-r from-brand-cyan to-brand-blue">Upload</PrimaryButton>
                    </div>
                    <InputError class="mt-2" :message="galleryForm.errors.images || (galleryForm.errors && galleryForm.errors['images.0'])" />
                    
                    <progress 
                        v-if="galleryForm.progress" 
                        :value="galleryForm.progress ? galleryForm.progress.percentage : 0" 
                        max="100" 
                        class="w-full mt-2 h-2 rounded [&::-webkit-progress-bar]:rounded [&::-webkit-progress-value]:rounded [&::-webkit-progress-bar]:bg-gray-700 [&::-webkit-progress-value]:bg-brand-cyan [&::-moz-progress-bar]:bg-brand-cyan"
                    >
                        {{ galleryForm.progress ? galleryForm.progress.percentage : 0 }}%
                    </progress>
                </form>

                <div v-if="package.galleries && package.galleries.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div v-for="image in package.galleries" :key="image.id" class="relative group">
                        <img :src="`/storage/${image.image_url}`" class="w-full h-32 object-cover rounded-lg shadow-md">
                        <Link 
                            :href="route('admin.packages.gallery.destroy', image.id)" 
                            method="delete" 
                            as="button"
                            preserve-scroll
                            class="absolute top-1 right-1 p-1.5 bg-red-600 rounded-full text-white opacity-0 group-hover:opacity-100 transition-opacity"
                            aria-label="Hapus gambar"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </Link>
                    </div>
                </div>
                <div v-else>
                    <p class="text-gray-500 dark:text-gray-400 italic">Belum ada gambar di galeri.</p>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>