<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    booking: Object, // Booking ini sudah di-load dengan relasi
    drivers: Array, // Data driver dari controller
    guides: Array, // Data guide dari controller
    vehicles: Array, // Data vehicle dari controller
});

// Form untuk update status dan assignment
const form = useForm({
    status: props.booking.status,
    assigned_driver_id: props.booking.assigned_driver_id,
    assigned_guide_id: props.booking.assigned_guide_id,
    assigned_vehicle_id: props.booking.assigned_vehicle_id,
});

const updateBooking = () => {
    form.put(route('admin.bookings.update', props.booking.id), {
        preserveScroll: true,
        onError: (errors) => console.error("Update Errors:", errors)
    });
};

// =======================================================
// TAMBAHAN: Helper Functions (Agar template lebih bersih)
// =======================================================
const formatCurrency = (value) => {
    if (typeof value !== 'number') {
        value = Number(value) || 0;
    }
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

const formatDateTime = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const storageUrl = (path) => {
    if (!path) return '#';
    // Pastikan path tidak diawali dengan '/'
    if (path.startsWith('/')) {
        path = path.substring(1);
    }
    return `/storage/${path}`;
};
// =======================================================

</script>
<template>
    <Head :title="`Booking ${booking.booking_code}`" />
    <AdminLayout>
         <Link :href="route('admin.bookings.index')" class="inline-flex items-center text-sm text-brand-cyan hover:text-blue-400 mb-6 group">
           <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition-transform">
             <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
           </svg>
           Kembali ke Daftar Booking
         </Link>

        <div v-if="$page.props.flash?.message" class="bg-green-600 border border-green-700 text-white p-4 rounded-lg mb-6 shadow">
            {{ $page.props.flash.message }}
        </div>
         <div v-if="$page.props.flash?.error" class="bg-red-600 border border-red-700 text-white p-4 rounded-lg mb-6 shadow">
            {{ $page.props.flash.error }}
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                
                <div 
                    v-if="booking.payments.length > 0" 
                    class="bg-gray-800 rounded-lg shadow-lg p-6"
                    :class="{ 
                        'border-2 border-yellow-500': booking.status === 'waiting_confirmation',
                        'border border-brand-border': booking.status !== 'waiting_confirmation'
                    }"
                >
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-700 pb-2 flex justify-between items-center">
                        Data Pembayaran
                        <span 
                            v-if="booking.status === 'waiting_confirmation'"
                            class="px-3 py-1 text-xs font-bold rounded-full uppercase bg-yellow-500/80 text-yellow-100 border border-yellow-600"
                        >
                            Perlu Konfirmasi
                        </span>
                    </h2>
                    
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div class="sm:col-span-1">
                            <dt class="font-semibold text-gray-400">Metode:</dt>
                            <dd class="text-white">{{ booking.payments[0].payment_method }}</dd>
                        </div>
                         <div class="sm:col-span-1">
                            <dt class="font-semibold text-gray-400">Jumlah Transfer:</dt>
                            <dd class="text-white font-medium">{{ formatCurrency(booking.payments[0].amount) }}</dd>
                        </div>
                         <div class="sm:col-span-2">
                            <dt class="font-semibold text-gray-400">Tanggal Upload:</dt>
                            <dd class="text-white">{{ formatDateTime(booking.payments[0].paid_at) }}</dd>
                        </div>
                         <div class="sm:col-span-2 mt-4">
                            <a 
                                :href="storageUrl(booking.payments[0].payment_proof_url)" 
                                target="_blank" 
                                class="inline-block px-5 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-md"
                            >
                                Lihat Bukti Transfer
                            </a>
                        </div>
                    </dl>
                </div>
                <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-700 pb-2">Detail Booking</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div class="sm:col-span-1">
                            <dt class="font-semibold text-gray-400">Kode Booking:</dt>
                            <dd class="font-mono text-white">{{ booking.booking_code }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="font-semibold text-gray-400">Status:</dt>
                            <dd>
                                 <span class="px-3 py-1 text-xs font-bold rounded-full uppercase"
                                       :class="{
                                           'bg-yellow-500/80 text-yellow-100 border border-yellow-600': booking.status === 'pending',
                                           'bg-cyan-500/80 text-cyan-100 border border-cyan-600': booking.status === 'waiting_confirmation', // <-- BARU
                                           'bg-blue-500/80 text-blue-100 border border-blue-600': booking.status === 'paid',
                                           'bg-green-500/80 text-green-100 border border-green-600': booking.status === 'confirmed',
                                           'bg-red-500/80 text-red-100 border border-red-600': booking.status === 'cancelled',
                                           'bg-gray-500/80 text-gray-100 border border-gray-600': booking.status === 'completed',
                                       }">
                                    {{ booking.status.replace('_', ' ') }}
                                 </span>
                            </dd>
                        </div>
                         <div class="sm:col-span-2">
                             <dt class="font-semibold text-gray-400">Paket Wisata:</dt>
                            <dd class="text-white">{{ booking.tour_package.name }}</dd>
                         </div>
                        <div class="sm:col-span-1">
                            <dt class="font-semibold text-gray-400">Pelanggan:</dt>
                            <dd class="text-white">{{ booking.user.name }} ({{ booking.user.email }})</dd>
                        </div>
                         <div class="sm:col-span-1">
                            <dt class="font-semibold text-gray-400">Tanggal Pesan:</dt>
                            <dd class="text-white">{{ formatDate(booking.created_at) }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="font-semibold text-gray-400">Tgl Berangkat:</dt>
                            <dd class="text-white">{{ formatDate(booking.departure_date) }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="font-semibold text-gray-400">Jumlah Peserta:</dt>
                            <dd class="text-white">{{ booking.num_participants }} orang</dd>
                        </div>
                         <div class="sm:col-span-1">
                             <dt class="font-semibold text-gray-400">Harga Paket (saat booking):</dt>
                            <dd class="text-white">{{ formatCurrency(booking.package_price_at_booking) }}</dd>
                         </div>
                         <div class="sm:col-span-1">
                             <dt class="font-semibold text-gray-400">Diskon (saat booking):</dt>
                            <dd class="text-white">{{ booking.discount_at_booking }}%</dd>
                         </div>
                         <div class="sm:col-span-2">
                             <dt class="font-semibold text-gray-400">Total Harga:</dt>
                            <dd class="text-brand-cyan font-bold text-lg">{{ formatCurrency(booking.total_price) }}</dd>
                         </div>
                         <div v-if="booking.special_requests" class="sm:col-span-2">
                             <dt class="font-semibold text-gray-400">Permintaan Khusus:</dt>
                            <dd class="text-white whitespace-pre-line">{{ booking.special_requests }}</dd>
                         </div>
                    </dl>
                </div>

                <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-700 pb-2">Data Penumpang</h2>
                    <table class="w-full text-left text-sm text-gray-300">
                        <thead class="border-b border-gray-600 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="py-2">Nama Lengkap</th>
                                <th class="py-2">Tanggal Lahir</th>
                            </tr>
                        </thead>
                        <tbody>
                             <tr v-if="!booking.passengers || booking.passengers.length === 0">
                                 <td colspan="2" class="py-2 text-center text-gray-500 italic">Data penumpang tidak ditemukan.</td>
                             </tr>
                            <tr v-for="passenger in booking.passengers" :key="passenger.id" class="border-b border-gray-700 last:border-b-0">
                                <td class="py-2 text-white">{{ passenger.full_name }}</td>
                                <td class="py-2">{{ formatDate(passenger.date_of_birth) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                 <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-700 pb-2">Detail Assignment</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                             <dt class="font-semibold text-gray-400">Driver Ditugaskan:</dt>
                             <dd class="text-white">{{ booking.driver?.name ?? 'Belum ditugaskan' }}</dd>
                        </div>
                         <div>
                             <dt class="font-semibold text-gray-400">Guide Ditugaskan:</dt>
                             <dd class="text-white">{{ booking.guide?.name ?? 'Belum ditugaskan' }}</dd>
                        </div>
                         <div>
                             <dt class="font-semibold text-gray-400">Kendaraan Ditugaskan:</dt>
                             <dd class="text-white">{{ booking.vehicle ? `${booking.vehicle.name} (${booking.vehicle.license_plate})` : 'Belum ditugaskan' }}</dd>
                        </div>
                    </dl>
                 </div>

            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border sticky top-28">
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-700 pb-2">Update Booking</h2>
                    <form @submit.prevent="updateBooking" class="space-y-4">
                        <div>
                            <InputLabel for="status" value="Status Booking" class="!text-brand-cyan !font-semibold" />
                            <select id="status" v-model="form.status" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
                                <option value="pending">Pending</option>
                                <option value="waiting_confirmation">Waiting Confirmation</option> <option value="paid">Paid</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>

                         <div>
                            <InputLabel for="assigned_driver_id" value="Assign Driver" class="!text-brand-cyan !font-semibold" />
                            <select id="assigned_driver_id" v-model="form.assigned_driver_id" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
                                <option :value="null">-- Belum Ditugaskan --</option>
                                <option v-for="driver in drivers" :key="driver.id" :value="driver.id">{{ driver.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.assigned_driver_id" />
                        </div>

                         <div>
                            <InputLabel for="assigned_guide_id" value="Assign Guide" class="!text-brand-cyan !font-semibold" />
                            <select id="assigned_guide_id" v-model="form.assigned_guide_id" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
                                <option :value="null">-- Belum Ditugaskan --</option>
                                <option v-for="guide in guides" :key="guide.id" :value="guide.id">{{ guide.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.assigned_guide_id" />
                        </div>

                        <div>
                            <InputLabel for="assigned_vehicle_id" value="Assign Vehicle" class="!text-brand-cyan !font-semibold" />
                            <select id="assigned_vehicle_id" v-model="form.assigned_vehicle_id" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
                                <option :value="null">-- Belum Ditugaskan --</option>
                                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">{{ vehicle.name }} ({{ vehicle.license_plate }})</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.assigned_vehicle_id" />
                        </div>


                        <PrimaryButton type="submit" class="w-full justify-center !py-3 !text-base bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan focus:ring-offset-gray-800" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Update Booking
                        </PrimaryButton>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>