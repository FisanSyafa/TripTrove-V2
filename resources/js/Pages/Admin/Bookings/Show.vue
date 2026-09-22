<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { ref, watch, computed, nextTick } from 'vue';

const props = defineProps({
    booking: Object,
    drivers: Array,
    guides: Array,
    vehicles: Array,
});

// Form untuk update status dan assignment
const form = useForm({
    status: props.booking.status,
    assigned_driver_id: props.booking.assigned_driver_id,
    assigned_guide_id: props.booking.assigned_guide_id,
    assigned_vehicle_id: props.booking.assigned_vehicle_id,
});

const notifyForm = useForm({
    message: '',
    subject: '',
    button_text: '',
});

// Template state
const selectedTemplate = ref('');
const selectedLanguage = ref('en');

// Payment URL
const paymentUrl = computed(() => {
    return `${window.location.origin}/bookings/${props.booking.id}/payment`;
});

// Customer name
const customerName = computed(() => {
    return props.booking.user?.name || props.booking.guest_name || 'Customer';
});

// Formatted departure date
const formattedDepartureDate = computed(() => {
    if (!props.booking.departure_date) return '-';
    const date = new Date(props.booking.departure_date);
    const locales = { en: 'en-US', id: 'id-ID', ms: 'ms-MY' };
    return date.toLocaleDateString(locales[selectedLanguage.value] || 'en-US', {
        day: 'numeric', month: 'long', year: 'numeric'
    });
});

// Template messages
const templates = {
    available: {
        en: () => `Hello ${customerName.value},\n\nGreat news! Your booking *${props.booking.booking_code}* for the tour package *${props.booking.tour_package.name}* on *${formattedDepartureDate.value}* is now available.\n\nPlease proceed to complete your payment through the button below.\n\nThank you,\nTripTrove Team`,
        id: () => `Halo ${customerName.value},\n\nKabar baik! Booking Anda *${props.booking.booking_code}* untuk paket wisata *${props.booking.tour_package.name}* pada tanggal *${formattedDepartureDate.value}* sudah tersedia.\n\nSilakan lanjutkan pembayaran melalui tombol di bawah.\n\nTerima kasih,\nTim TripTrove`,
        ms: () => `Hai ${customerName.value},\n\nBerita baik! Tempahan anda *${props.booking.booking_code}* untuk pakej pelancongan *${props.booking.tour_package.name}* pada *${formattedDepartureDate.value}* kini tersedia.\n\nSila teruskan pembayaran melalui butang di bawah.\n\nTerima kasih,\nPasukan TripTrove`,
    },
    not_available: {
        en: () => `Hello ${customerName.value},\n\nThank you for your interest in our tour package *${props.booking.tour_package.name}*.\n\nUnfortunately, we are unable to accommodate your booking *${props.booking.booking_code}* for the requested date (*${formattedDepartureDate.value}*).\n\nWould you like to choose a different departure date? We'd be happy to help you find an available slot.\n\nPlease contact us to discuss alternative dates.\n\nThank you for your understanding,\nTripTrove Team`,
        id: () => `Halo ${customerName.value},\n\nTerima kasih atas minat Anda pada paket wisata *${props.booking.tour_package.name}*.\n\nSayangnya, kami belum dapat mengakomodasi booking Anda *${props.booking.booking_code}* untuk tanggal yang diminta (*${formattedDepartureDate.value}*).\n\nApakah Anda ingin memilih tanggal keberangkatan lain? Kami dengan senang hati akan membantu Anda menemukan slot yang tersedia.\n\nSilakan hubungi kami untuk mendiskusikan tanggal alternatif.\n\nTerima kasih atas pengertiannya,\nTim TripTrove`,
        ms: () => `Hai ${customerName.value},\n\nTerima kasih atas minat anda terhadap pakej pelancongan *${props.booking.tour_package.name}*.\n\nMalangnya, kami tidak dapat menampung tempahan anda *${props.booking.booking_code}* untuk tarikh yang diminta (*${formattedDepartureDate.value}*).\n\nAdakah anda ingin memilih tarikh berlepas lain? Kami dengan senang hati akan membantu anda mencari slot yang tersedia.\n\nSila hubungi kami untuk membincangkan tarikh alternatif.\n\nTerima kasih atas pemahaman anda,\nPasukan TripTrove`,
    },
};

// Subject lines
const subjects = {
    available: {
        en: 'Booking Availability Update - TripTrove',
        id: 'Pembaruan Ketersediaan Booking - TripTrove',
        ms: 'Kemas Kini Ketersediaan Tempahan - TripTrove',
    },
    not_available: {
        en: 'Update on Your Booking - TripTrove',
        id: 'Pembaruan Mengenai Booking Anda - TripTrove',
        ms: 'Kemas Kini Mengenai Tempahan Anda - TripTrove',
    },
};

// Button texts
const buttonTexts = {
    available: {
        en: 'Proceed to Payment',
        id: 'Lanjutkan ke Pembayaran',
        ms: 'Teruskan ke Pembayaran',
    },
};

// Watch template/language changes
watch([selectedTemplate, selectedLanguage], ([tmpl, lang]) => {
    if (tmpl && templates[tmpl] && templates[tmpl][lang]) {
        notifyForm.message = templates[tmpl][lang]();
        notifyForm.subject = subjects[tmpl]?.[lang] || 'Booking Availability Update - TripTrove';
        notifyForm.button_text = buttonTexts[tmpl]?.[lang] || '';
    }
});

const updateBooking = () => {
    form.put(route('admin.bookings.update', props.booking.id), {
        preserveScroll: true,
        onError: (errors) => console.error("Update Errors:", errors),
    });
};

const sendEmailNotification = () => {
    notifyForm.post(route('admin.bookings.notify-email', props.booking.id), {
        preserveScroll: true,
        onSuccess: () => {
            notifyForm.reset('message', 'subject', 'button_text');
            selectedTemplate.value = '';
        },
    });
};

const sendWATransaction = () => {
    const waBaseUrl = 'https://wa.me/';
    const phoneNumber = props.booking.contact_phone.replace(/\+/g, '');
    
    let fullMessage = notifyForm.message || `Halo ${customerName.value}, terkait booking ${props.booking.booking_code}, statusnya kini ${props.booking.status}.`;
    
    // Append payment link for WhatsApp if status is available
    if (props.booking.status === 'available') {
        const linkLabel = { en: 'Payment Link', id: 'Link Pembayaran', ms: 'Link Pembayaran' };
        const label = linkLabel[selectedLanguage.value] || 'Link Pembayaran';
        fullMessage += `\n\n${label}: ${paymentUrl.value}`;
    }

    const message = encodeURIComponent(fullMessage);
    window.open(`${waBaseUrl}${phoneNumber}?text=${message}`, '_blank');
};

// =======================================================
// Helper Functions
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
    if (path.startsWith('/')) {
        path = path.substring(1);
    }
    return `/storage/${path}`;
};
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
                    class="bg-[#111c2e] rounded-xl shadow-lg p-6"
                    :class="{ 
                        'border-2 border-yellow-500': booking.status === 'waiting_confirmation',
                        'border border-brand-border': booking.status !== 'waiting_confirmation'
                    }"
                >
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-700 pb-2 flex justify-between items-center">
                        Data Pembayaran
                        <span 
                            v-if="booking.status === 'waiting_confirmation'"
                            class="px-3 py-1 text-xs font-bold rounded-full uppercase bg-yellow-500/20 text-yellow-400 border border-yellow-500/30"
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
                <div class="bg-[#111c2e] rounded-xl shadow-lg p-6 border border-gray-700/40">
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-600/50 pb-2">Detail Booking</h2>
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
                                           'bg-brand-cyan/80 text-white border border-brand-cyan': booking.status === 'available',
                                           'bg-red-500/80 text-white border border-red-600': booking.status === 'not_available',
                                           'bg-cyan-500/80 text-cyan-100 border border-cyan-600': booking.status === 'waiting_confirmation',
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
                            <dd class="text-white">
                                {{ booking.user ? booking.user.name : booking.guest_name }} 
                                ({{ booking.user ? booking.user.email : booking.contact_email }})
                            </dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="font-semibold text-gray-400">Kontak:</dt>
                            <dd class="text-white">
                                {{ booking.contact_phone }} 
                                <span v-if="booking.country">({{ booking.country }})</span>
                            </dd>
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
                         <template v-if="booking.group_tickets && booking.group_tickets.length > 0">
                             <div v-for="(gt, idx) in booking.group_tickets" :key="idx" class="sm:col-span-1">
                                 <dt class="font-semibold text-gray-400">{{ gt.name || 'Tiket Grup' }} (saat booking):</dt>
                                 <dd class="text-white">{{ gt.count }}x Tiket (Max {{ gt.max_persons }} pax) - {{ formatCurrency(gt.total) }}</dd>
                             </div>
                         </template>
                         <div v-else-if="Number(booking.group_ticket_total) > 0" class="sm:col-span-1">
                             <dt class="font-semibold text-gray-400">Tiket Grup (saat booking):</dt>
                            <dd class="text-white">{{ formatCurrency(booking.group_ticket_total) }}</dd>
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

                <div class="bg-[#111c2e] rounded-xl shadow-lg p-6 border border-gray-700/40">
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-600/50 pb-2">Data Penumpang</h2>
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

                 <div class="bg-[#111c2e] rounded-xl shadow-lg p-6 border border-gray-700/40">
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-600/50 pb-2">Detail Assignment</h2>
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
                <!-- Update Booking -->
                <div class="bg-[#111c2e] rounded-xl shadow-lg p-6 border border-gray-700/40">
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-600/50 pb-2">Update Booking</h2>
                    <form @submit.prevent="updateBooking" class="space-y-4">
                        <div>
                            <InputLabel for="status" value="Status Booking" class="!text-brand-cyan !font-semibold" />
                            <select id="status" v-model="form.status" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
                                <option value="pending">Pending</option>
                                <option value="available">Available</option>
                                <option value="not_available">Not Available</option>
                                <option value="waiting_confirmation">Waiting Confirmation</option>
                                <option value="paid">Paid</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>

                         <div>
                            <InputLabel for="assigned_driver_id" value="Assign Driver" class="!text-brand-cyan !font-semibold" />
                            <select id="assigned_driver_id" v-model="form.assigned_driver_id" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
                                <option :value="null">-- Belum Ditugaskan --</option>
                                <option v-for="driver in drivers" :key="driver.id" :value="driver.id">{{ driver.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.assigned_driver_id" />
                        </div>

                         <div>
                            <InputLabel for="assigned_guide_id" value="Assign Guide" class="!text-brand-cyan !font-semibold" />
                            <select id="assigned_guide_id" v-model="form.assigned_guide_id" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
                                <option :value="null">-- Belum Ditugaskan --</option>
                                <option v-for="guide in guides" :key="guide.id" :value="guide.id">{{ guide.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.assigned_guide_id" />
                        </div>

                        <div>
                            <InputLabel for="assigned_vehicle_id" value="Assign Vehicle" class="!text-brand-cyan !font-semibold" />
                            <select id="assigned_vehicle_id" v-model="form.assigned_vehicle_id" class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white">
                                <option :value="null">-- Belum Ditugaskan --</option>
                                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">{{ vehicle.name }} ({{ vehicle.license_plate }})</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.assigned_vehicle_id" />
                        </div>

                        <PrimaryButton 
                            type="submit" 
                            class="w-full justify-center"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Update Booking' }}
                        </PrimaryButton>
                    </form>
                </div>

                <!-- Notify User Section -->
                <div class="bg-[#111c2e] rounded-xl shadow-lg p-6 border border-gray-700/40">
                    <h2 class="text-2xl font-bold mb-4 text-brand-cyan border-b border-gray-600/50 pb-2">Hubungi Pengguna</h2>
                    <div class="space-y-4">
                        <!-- Template Selector -->
                        <div>
                            <InputLabel value="Pilih Template" class="!text-brand-cyan !font-semibold" />
                            <select 
                                v-model="selectedTemplate" 
                                class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white text-sm"
                            >
                                <option value="">-- Pilih Template --</option>
                                <option value="available">✅ Available (Tersedia)</option>
                                <option value="not_available">❌ Not Available (Tidak Tersedia)</option>
                            </select>
                        </div>

                        <!-- Language Selector -->
                        <div v-if="selectedTemplate">
                            <InputLabel value="Bahasa Pesan" class="!text-brand-cyan !font-semibold" />
                            <div class="flex gap-2 mt-1">
                                <button 
                                    @click="selectedLanguage = 'en'" 
                                    :class="selectedLanguage === 'en' ? 'bg-brand-cyan text-gray-900' : 'bg-[#0c1222] text-gray-400 border-gray-600/50'"
                                    class="flex-1 px-3 py-2 rounded-md text-sm font-bold border transition-colors"
                                >
                                    🇬🇧 EN
                                </button>
                                <button 
                                    @click="selectedLanguage = 'id'" 
                                    :class="selectedLanguage === 'id' ? 'bg-brand-cyan text-gray-900' : 'bg-[#0c1222] text-gray-400 border-gray-600/50'"
                                    class="flex-1 px-3 py-2 rounded-md text-sm font-bold border transition-colors"
                                >
                                    🇮🇩 ID
                                </button>
                                <button 
                                    @click="selectedLanguage = 'ms'" 
                                    :class="selectedLanguage === 'ms' ? 'bg-brand-cyan text-gray-900' : 'bg-[#0c1222] text-gray-400 border-gray-600/50'"
                                    class="flex-1 px-3 py-2 rounded-md text-sm font-bold border transition-colors"
                                >
                                    🇲🇾 MS
                                </button>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="notify_subject" value="Subjek Email" class="!text-brand-cyan !font-semibold" />
                            <input 
                                id="notify_subject" 
                                v-model="notifyForm.subject" 
                                type="text"
                                class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white text-sm"
                                placeholder="Subjek email..."
                            />
                        </div>

                        <div v-if="selectedTemplate === 'available'">
                            <InputLabel for="notify_button_text" value="Teks Tombol Pembayaran" class="!text-brand-cyan !font-semibold" />
                            <input 
                                id="notify_button_text" 
                                v-model="notifyForm.button_text" 
                                type="text"
                                class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white text-sm"
                                placeholder="Teks tombol..."
                            />
                        </div>

                        <div>
                            <InputLabel for="notify_message" value="Pesan" class="!text-brand-cyan !font-semibold" />
                            <textarea 
                                id="notify_message" 
                                v-model="notifyForm.message" 
                                rows="8" 
                                class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white text-sm"
                                placeholder="Pilih template di atas atau tulis pesan kustom..."
                            ></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <PrimaryButton 
                                type="button" 
                                @click="sendEmailNotification" 
                                class="justify-center !bg-blue-600 hover:!bg-blue-700"
                                :disabled="notifyForm.processing || !notifyForm.message"
                            >
                                <svg v-if="notifyForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ notifyForm.processing ? 'Mengirim...' : 'Kirim Email' }}
                            </PrimaryButton>
                            <PrimaryButton 
                                type="button" 
                                @click="sendWATransaction" 
                                class="justify-center !bg-green-600 hover:!bg-green-700"
                                :disabled="!notifyForm.message"
                            >
                                Kirim WA
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>