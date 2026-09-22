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
    input_currency: props.package.input_currency || 'IDR',
    price: props.package.original_price !== null ? props.package.original_price : props.package.price,
    small_car_price: props.package.original_small_car_price !== null ? props.package.original_small_car_price : (props.package.small_car_price || 0),
    large_car_price: props.package.original_large_car_price !== null ? props.package.original_large_car_price : (props.package.large_car_price || 0),
    group_tickets: Array.isArray(props.package.group_tickets) 
        ? props.package.group_tickets.map(gt => ({
            name: gt.name || '',
            price: gt.original_price !== undefined && gt.original_price !== null ? gt.original_price : (gt.price || 0),
            max_persons: gt.max_persons || 4
          }))
        : [],
    discount_percent: props.package.discount_percent,
    includes_hotel: props.package.includes_hotel,
    includes_guide: props.package.includes_guide,
    includes_entrance_fee: props.package.includes_entrance_fee,
    includes_driver_vehicle: props.package.includes_driver_vehicle,
    includes_about_this_trip: props.package.includes_about_this_trip,
    cover_image: null,
    status: props.package.status,
    category: props.package.category,
    pickup_time: props.package.pickup_time || '',
    is_children_friendly: Boolean(props.package.is_children_friendly),
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

const getPreviewUrl = (file) => {
    return URL.createObjectURL(file);
};

const addGroupTicketRow = () => {
    form.group_tickets.push({
        name: '',
        price: 0,
        max_persons: 4,
    });
};

const removeGroupTicketRow = (index) => {
    form.group_tickets.splice(index, 1);
};
</script>

<template>
    <Head :title="`Edit Paket: ${package.name}`" />
    <AdminLayout>
        <h1 class="text-3xl font-bold mb-6 text-brand-cyan">Edit Paket: {{ package.name }}</h1>

        <form @submit.prevent="submit" class="max-w-7xl space-y-6 bg-[#111c2e] p-8 rounded-xl shadow-lg border border-gray-700/40">
            
            <div>
                <InputLabel for="name" value="Nama Paket" class="!text-brand-cyan !font-semibold" />
                <TextInput id="name" type="text" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="destination_summary" value="Ringkasan Destinasi" class="!text-brand-cyan !font-semibold" />
                <TextInput id="destination_summary" type="text" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.destination_summary" required />
                <InputError class="mt-2" :message="form.errors.destination_summary" />
            </div>

            <div>
                <InputLabel for="location_details" value="Detail Lokasi (Opsional)" class="!text-brand-cyan !font-semibold" />
                <textarea id="location_details" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white" v-model="form.location_details" rows="3"></textarea>
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <InputLabel for="input_currency" value="Mata Uang Input" class="!text-brand-cyan !font-semibold" />
                    <select id="input_currency" v-model="form.input_currency" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
                        <option value="IDR">Rupiah (IDR)</option>
                        <option value="USD">US Dollar (USD)</option>
                        <option value="MYR">Ringgit Malaysia (MYR)</option>
                        <option value="SGD">Singapore Dollar (SGD)</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.input_currency" />
                </div>
                <div>
                    <InputLabel for="duration_days" value="Durasi (Hari)" class="!text-brand-cyan !font-semibold" />
                    <TextInput id="duration_days" type="number" min="1" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.duration_days" required />
                    <InputError class="mt-2" :message="form.errors.duration_days" />
                </div>
                 <div>
                    <InputLabel for="price" :value="`Harga (${form.input_currency})`" class="!text-brand-cyan !font-semibold" />
                    <TextInput id="price" type="number" min="0" :step="form.input_currency === 'IDR' ? 1000 : 0.01" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.price" required />
                    <InputError class="mt-2" :message="form.errors.price" />
                </div>
                 <div>
                    <InputLabel for="discount_percent" value="Diskon (%)" class="!text-brand-cyan !font-semibold" />
                    <TextInput id="discount_percent" type="number" min="0" max="100" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.discount_percent" />
                    <InputError class="mt-2" :message="form.errors.discount_percent" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="small_car_price" :value="`Harga Mobil Kecil (Max 4 Penumpang) (${form.input_currency})`" class="!text-brand-cyan !font-semibold" />
                    <TextInput id="small_car_price" type="number" min="0" :step="form.input_currency === 'IDR' ? 1000 : 0.01" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.small_car_price" required />
                    <InputError class="mt-2" :message="form.errors.small_car_price" />
                </div>
                <div>
                    <InputLabel for="large_car_price" :value="`Harga Mobil Besar (5+ Penumpang) (${form.input_currency})`" class="!text-brand-cyan !font-semibold" />
                    <TextInput id="large_car_price" type="number" min="0" :step="form.input_currency === 'IDR' ? 1000 : 0.01" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.large_car_price" required />
                    <InputError class="mt-2" :message="form.errors.large_car_price" />
                </div>
            </div>

            <!-- Group Ticket Pricing (Multi-Row) -->
            <div class="p-4 bg-[#0c1222]/80 border border-gray-700/60 rounded-lg space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h4 class="font-bold text-brand-cyan text-sm flex items-center gap-2">
                            <span>🎫</span> Harga Tiket Grup / Rombongan (Opsional)
                        </h4>
                        <p class="text-xs text-gray-400 mt-1">
                            Tambahkan baris tiket grup jika paket membutuhkan tiket rombongan (contoh: Tiket Jeep max 4 orang, Tiket Pantai Timang max 3 orang).
                        </p>
                    </div>
                    <button type="button" @click="addGroupTicketRow" class="px-3 py-1.5 bg-brand-cyan/20 hover:bg-brand-cyan/30 text-brand-cyan text-xs font-bold rounded border border-brand-cyan/40 transition flex-shrink-0">
                        + Tambah Tiket Grup
                    </button>
                </div>

                <div v-if="form.group_tickets.length === 0" class="text-xs text-gray-500 italic p-3 bg-[#080d1a] rounded text-center">
                    Belum ada tiket grup. Klik "+ Tambah Tiket Grup" untuk menambahkan.
                </div>

                <div v-for="(gt, index) in form.group_tickets" :key="index" class="p-3 bg-[#080d1a] border border-gray-700/40 rounded-lg space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-400">Tiket Grup #{{ index + 1 }}</span>
                        <button type="button" @click="removeGroupTicketRow(index)" class="text-red-400 hover:text-red-300 text-xs font-bold transition">
                            ✕ Hapus
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <InputLabel :for="`gt_name_${index}`" value="Nama Tiket Grup" class="!text-brand-cyan !font-semibold !text-xs" />
                            <TextInput :id="`gt_name_${index}`" type="text" placeholder="cth: Tiket Jeep Merapi" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm text-sm" v-model="gt.name" required />
                        </div>
                        <div>
                            <InputLabel :for="`gt_price_${index}`" :value="`Harga (${form.input_currency})`" class="!text-brand-cyan !font-semibold !text-xs" />
                            <TextInput :id="`gt_price_${index}`" type="number" min="0" :step="form.input_currency === 'IDR' ? 1000 : 0.01" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm text-sm" v-model="gt.price" required />
                        </div>
                        <div>
                            <InputLabel :for="`gt_max_${index}`" value="Max Orang per Tiket" class="!text-brand-cyan !font-semibold !text-xs" />
                            <TextInput :id="`gt_max_${index}`" type="number" min="1" placeholder="cth: 4" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm text-sm" v-model="gt.max_persons" required />
                        </div>
                    </div>
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
                 <label class="flex items-center">
                    <Checkbox name="includes_about_this_trip" class="rounded border-gray-500 text-brand-blue focus:ring-brand-blue" v-model:checked="form.includes_about_this_trip" />
                    <span class="ms-2 text-sm text-gray-300">Sesuai di 'About This Trip' (As stated in About this trip)</span>
                </label>
            </div>

            <div>
                <InputLabel for="category" value="Kategori (Opsional)" class="!text-brand-cyan !font-semibold" />
                <TextInput id="category" type="text" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm" v-model="form.category" />
                <InputError class="mt-2" :message="form.errors.category" />
            </div>

            <div>
                <InputLabel for="pickup_time" value="Jam Keberangkatan (cth: 08:00)" class="!text-brand-cyan !font-semibold" />
                <input id="pickup_time" type="time" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-md shadow-sm text-white focus:outline-none focus:ring-1 focus:ring-brand-cyan p-2.5" v-model="form.pickup_time" style="color-scheme: dark;" />
                <InputError class="mt-2" :message="form.errors.pickup_time" />
            </div>

            <div class="space-y-3 pt-2">
                <label class="flex items-center">
                    <Checkbox name="is_children_friendly" class="rounded border-gray-500 text-brand-blue focus:ring-brand-blue" v-model:checked="form.is_children_friendly" />
                    <span class="ms-2 text-sm text-gray-300">Children Friendly (Paket ini cocok untuk anak-anak)</span>
                </label>
                <InputError class="mt-2" :message="form.errors.is_children_friendly" />
            </div>

            <div>
                <InputLabel for="status" value="Status" class="!text-brand-cyan !font-semibold" />
                <select id="status" v-model="form.status" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
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
                    <img v-if="form.cover_image && typeof form.cover_image === 'object'" :src="getPreviewUrl(form.cover_image)" class="mt-2 h-32 object-cover rounded shadow">
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

        <div class="mt-8 max-w-3xl bg-[#111c2e] overflow-hidden shadow-lg sm:rounded-xl border border-gray-700/40">
            <div class="p-6 bg-[#111c2e] border-b border-gray-700/40">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Galeri Foto</h3>
                
                <form @submit.prevent="submitGallery" class="mb-6">
                    <InputLabel for="gallery_image" value="Tambah Gambar Baru (Bisa pilih banyak)" class="!text-brand-cyan !font-semibold" />
                    <div class="flex items-center gap-4 mt-1">
                        <input 
                            id="gallery_image"
                            type="file" 
                            @input="galleryForm.images = Array.from($event.target.files)"  
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