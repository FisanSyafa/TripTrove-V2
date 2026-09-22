<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { computed, getCurrentInstance, ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    bookings: Object, 
    adminWhatsappNumber: String,
});

// --- Helper Global ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;
const $formatCurrency = (value) => app.appContext.config.globalProperties.$formatCurrency(value);
const page = computed(() => app.appContext.config.globalProperties.$page);

// Helper formatDate
const formatDate = (dateString) => {
    const locale = page.value.props.locale || 'en'; 
    let localeCode = 'en-US'; 
    if (locale === 'id') localeCode = 'id-ID';
    if (locale === 'ms') localeCode = 'ms-MY';
    
    return new Date(dateString).toLocaleDateString(localeCode, {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

// Status Class
const getStatusClass = (status) => {
    switch (status) {
        case 'pending': return 'bg-orange-100 text-orange-700 border border-orange-200';
        case 'available': return 'bg-brand-cyan/20 text-brand-blue border border-brand-cyan/30';
        case 'not_available': return 'bg-red-100 text-red-700 border border-red-200';
        case 'waiting_confirmation': return 'bg-blue-100 text-blue-700 border border-blue-200';
        case 'confirmed': return 'bg-green-100 text-green-700 border border-green-200';
        case 'cancelled': return 'bg-red-100 text-red-700 border border-red-200';
        case 'completed': return 'bg-gray-100 text-gray-700 border border-gray-200';
        default: return 'bg-gray-100 text-gray-600';
    }
};

const translateStatus = (status) => {
    const statusKey = status.replace('_', ' '); 
    const capitalized = statusKey.charAt(0).toUpperCase() + statusKey.slice(1);
    return __(capitalized); 
}

const showSuccessModal = ref(false);
const modalMessage = ref('');
const modalTitle = ref('');
const modalType = ref('success'); // 'success' | 'availability'
const isCheckingAvailability = ref(false);
const selectedBooking = ref(null);

// Participant display helper
const getParticipantDisplay = (booking) => {
    const adults = booking.num_adults || booking.num_participants || 1;
    const children = booking.num_children || 0;
    let text = adults + ' ' + __('Adult');
    if (children > 0) {
        text += ' & ' + children + ' ' + __('Children');
    }
    return text;
};

const paymentWhatsappUrl = computed(() => {
    if (!props.adminWhatsappNumber || !selectedBooking.value) return '#';
    
    const header = __('Payment Confirmation');
    const intro = __('Hello TripTrove, I would like to pay for my booking:');
    const labelCode = __('Booking Code');
    const labelPackage = __('Package');
    const labelDate = __('Departure Date');
    const labelParticipants = __('Participants');
    const labelAmount = __('Total Amount');
    const footer = __('Please let me know the payment instructions. Thank you!');

    const b = selectedBooking.value;
    const dateStr = b.departure_date 
        ? new Date(b.departure_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) 
        : '-';

    const message = `*${header}*\n\n${intro}\n- ${labelCode}: ${b.booking_code}\n- ${labelPackage}: ${b.tour_package?.name || '-'}\n- ${labelDate}: ${dateStr}\n- ${labelParticipants}: ${getParticipantDisplay(b)}\n- ${labelAmount}: ${$formatCurrency(b.total_price)}\n\n${footer}`;
    
    return `https://wa.me/${props.adminWhatsappNumber}?text=${encodeURIComponent(message)}`;
});

const getAvailabilityWhatsappUrl = (booking) => {
    if (!props.adminWhatsappNumber) return '#';
    
    const header = __('Availability Check');
    const intro = __('Hello TripTrove, I would like to check the availability for my booking:');
    const labelCode = __('Booking Code');
    const labelPackage = __('Package');
    const labelDate = __('Departure Date');
    const labelParticipants = __('Participants');
    const footer = __('Please let me know if this date is available. Thank you!');

    const dateStr = booking.departure_date 
        ? new Date(booking.departure_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) 
        : '-';

    const message = `*${header}*\n\n${intro}\n- ${labelCode}: ${booking.booking_code}\n- ${labelPackage}: ${booking.tour_package?.name || '-'}\n- ${labelDate}: ${dateStr}\n- ${labelParticipants}: ${getParticipantDisplay(booking)}\n\n${footer}`;
    
    return `https://wa.me/${props.adminWhatsappNumber}?text=${encodeURIComponent(message)}`;
};

const checkAvailability = (booking) => {
    // Notify backend and then open WA
    axios.post(route('booking.request-availability', booking.id))
        .finally(() => {
            window.open(getAvailabilityWhatsappUrl(booking), '_blank');
        });
};

const closeModal = () => {
    showSuccessModal.value = false;
    router.reload({ preserveScroll: true });
};

// Handle Pay Now (manual) - Only opens WhatsApp in a new tab
const handlePayNowManual = (booking) => {
    selectedBooking.value = booking;
    window.open(paymentWhatsappUrl.value, '_blank');
}
</script>

<template>
    <Head :title="__('Dashboard')" />

    <MainLayout>
        <div class="min-h-screen bg-gray-50">
            
            <div class="bg-white border-b border-gray-200 pt-10 pb-8 px-6 shadow-sm mb-8">
                <div class="container mx-auto max-w-7xl">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        
                        <div>
                            <h1 class="text-3xl font-extrabold bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text mb-2">
                                {{ __('Welcome back') }}, {{ $page.props.auth.user.name }}!
                            </h1>
                            <p class="text-gray-600">{{ __('Here is the history of your exciting journeys.') }}</p>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <Link 
                                :href="route('profile.edit')" 
                                class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-cyan focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                {{ __('Profile') }}
                            </Link>

                            <Link 
                                :href="route('logout')" 
                                method="post" 
                                as="button" 
                                class="inline-flex items-center px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg font-semibold text-xs text-red-600 uppercase tracking-widest shadow-sm hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                                </svg>
                                {{ __('Log Out') }}
                            </Link>

                            <Link 
                                :href="route('packages.all')"
                                class="inline-flex items-center justify-center px-5 py-2.5 
                                       bg-gradient-to-r from-brand-cyan to-brand-blue text-white 
                                       font-bold text-xs uppercase tracking-widest rounded-lg shadow-md 
                                       hover:from-brand-blue hover:to-brand-cyan hover:shadow-lg 
                                       transition-all transform hover:-translate-y-0.5"
                            >
                                <span class="mr-2 text-base">🌍</span> {{ __('Explore') }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container mx-auto max-w-7xl px-6 pb-12">
                
                <div v-if="$page.props.flash?.message" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    <p>{{ $page.props.flash.message }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 relative">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-30 pointer-events-none"></div>

                    <div class="p-8 text-gray-900 relative z-10">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <span class="bg-brand-cyan/10 p-2 rounded-lg mr-3 text-brand-blue">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                            </span>
                            {{ __('Booking List') }}
                        </h3>
                        
                        <div v-if="bookings.data.length === 0" class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-300">
                            <div class="text-5xl mb-3">🎫</div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __("You don't have any booking history yet.") }}</h3>
                            <p class="text-gray-500 mb-6">{{ __("Start your journey by exploring our packages.") }}</p>
                            <Link :href="route('packages.all')" class="text-brand-cyan font-bold hover:underline">{{ __('Browse Packages') }} &rarr;</Link>
                        </div>

                        <div v-else class="space-y-5">
                            <div v-for="booking in bookings.data" :key="booking.id" 
                                 class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm hover:shadow-md hover:border-brand-cyan/50 transition-all duration-300 flex flex-col md:flex-row justify-between items-start md:items-center group relative overflow-hidden">
                                
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-brand-cyan to-brand-blue opacity-0 group-hover:opacity-100 transition-opacity"></div>

                                <div class="flex-1 mb-4 md:mb-0 pl-2">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="text-xs font-mono bg-gray-100 border border-gray-200 px-2 py-0.5 rounded text-gray-600">
                                            #{{ booking.booking_code }}
                                        </span>
                                        <span 
                                            class="text-xs font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wide"
                                            :class="getStatusClass(booking.status)"
                                        >
                                            {{ translateStatus(booking.status) }}
                                        </span>
                                    </div>
                                    
                                    <Link :href="route('packages.show', booking.tour_package.slug)" class="text-xl font-bold text-gray-900 hover:text-brand-blue transition-colors">
                                        {{ booking.tour_package.name }}
                                    </Link>
                                    
                                    <div class="text-sm text-gray-600 mt-1 flex items-center">
                                        <span class="mr-1">🗓️</span> 
                                        {{ __('Departure') }}: <span class="font-medium ml-1 text-gray-800">{{ formatDate(booking.departure_date) }}</span>
                                    </div>
                                    
                                    <div class="text-lg font-extrabold text-brand-blue mt-2">
                                        {{ $formatCurrency(booking.total_price) }}
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <div v-if="booking.car_type" class="flex items-center gap-2 text-xs text-brand-blue font-semibold bg-blue-50 w-fit px-2 py-1 rounded">
                                            <span>🚗 {{ booking.car_type === 'small' ? __('Small Car') : __('Large Car') }}</span>
                                            <span class="text-gray-400 font-normal">({{ $formatCurrency(booking.car_price) }})</span>
                                        </div>
                                        <template v-if="booking.group_tickets && booking.group_tickets.length > 0">
                                            <div v-for="(gt, idx) in booking.group_tickets" :key="idx" class="flex items-center gap-2 text-xs text-purple-700 font-semibold bg-purple-50 w-fit px-2 py-1 rounded">
                                                <span>🎫 {{ gt.name || __('Group Ticket') }} ({{ gt.count }}x)</span>
                                                <span class="text-gray-400 font-normal">({{ $formatCurrency(gt.total) }})</span>
                                            </div>
                                        </template>
                                        <div v-else-if="Number(booking.group_ticket_total) > 0" class="flex items-center gap-2 text-xs text-purple-700 font-semibold bg-purple-50 w-fit px-2 py-1 rounded">
                                            <span>🎫 {{ __('Group Ticket') }}</span>
                                            <span class="text-gray-400 font-normal">({{ $formatCurrency(booking.group_ticket_total) }})</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-start md:items-end space-y-3 min-w-[150px]">
                                    <!-- Pending: Show Checking Availability Message + Contact Button -->
                                    <template v-if="booking.status === 'pending'">
                                        <div class="bg-orange-50 p-4 rounded-lg border border-orange-100 w-full mb-2">
                                            <p class="text-sm font-bold text-orange-800 mb-1 flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                {{ __('Checking availability...') }}
                                            </p>
                                            <p class="text-xs text-orange-700">
                                                {{ __('We are checking the availability for this tour package on your selected date.') }}
                                            </p>
                                        </div>
                                        <button 
                                            @click="checkAvailability(booking)"
                                            class="w-full text-center px-4 py-2 bg-orange-500 text-white rounded-lg shadow hover:bg-orange-600 font-bold text-xs transition-all flex items-center justify-center gap-2"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('Check Availability') }}
                                        </button>
                                    </template>

                                    <!-- Available: Show Pay Now (Disabled) & Pay Now (Manual) -->
                                    <template v-else-if="booking.status === 'available'">
                                        <button 
                                            disabled
                                            class="w-full text-center px-4 py-2 bg-gray-300 text-gray-500 rounded-lg font-bold text-xs cursor-not-allowed mb-2"
                                            :title="__('Pay with payment gateway is coming soon')"
                                        >
                                            {{ __('Pay with payment gateway is coming soon') }}
                                        </button>
                                        
                                        <div class="w-full">
                                            <button 
                                                @click="handlePayNowManual(booking)"
                                                class="w-full text-center px-5 py-2.5 bg-gradient-to-r from-brand-cyan to-brand-blue text-white rounded-lg shadow hover:from-brand-blue hover:to-brand-cyan font-bold text-sm transform hover:-translate-y-0.5 transition-all"
                                            >
                                                {{ __('Pay Now') }}
                                            </button>
                                            <p class="mt-1 text-[10px] text-center text-gray-500 italic leading-tight">
                                                {{ __('Chat with us for payment and we will provide payment method options') }}
                                            </p>
                                        </div>
                                    </template>

                                    <span v-else-if="booking.status === 'waiting_confirmation'" class="text-sm text-gray-500 flex items-center bg-gray-50 px-3 py-1 rounded-full border border-gray-200">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ __('Processing...') }}
                                    </span>

                                    <!-- Print Invoice Button for paid bookings -->
                                    <template v-if="['waiting_confirmation', 'confirmed', 'completed'].includes(booking.status)">
                                        <a 
                                            :href="route('booking.invoice', booking.id)"
                                            target="_blank"
                                            class="w-full text-center px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow hover:from-blue-600 hover:to-blue-700 font-bold text-sm transform hover:-translate-y-0.5 transition-all flex items-center justify-center"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            {{ __('Print Invoice') }}
                                        </a>
                                    </template>

                                    <span v-if="booking.status === 'confirmed'" class="text-sm text-green-600 font-bold flex items-center bg-green-50 px-3 py-1 rounded-full border border-green-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        {{ __('Paid') }}
                                    </span>
                                    
                                    <Link
                                        v-else-if="booking.status === 'completed' && !booking.review" 
                                        :href="route('reviews.create', booking.id)"
                                        class="w-full text-center px-5 py-2 bg-gradient-to-r from-yellow-400 to-yellow-500 text-white rounded-lg shadow hover:from-yellow-500 hover:to-yellow-600 font-bold text-sm transform hover:-translate-y-0.5 transition-all"
                                    >
                                        {{ __('Give Review') }}
                                    </Link>

                                    <span v-else-if="booking.status === 'completed' && booking.review" class="text-sm text-gray-400 italic flex items-center">
                                        <span class="text-yellow-400 mr-1">★</span> {{ __('Review Sent') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 border-t border-gray-100 pt-4">
                            <Pagination :links="bookings.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <!-- Success Modal -->
    <Modal :show="showSuccessModal" @close="closeModal">
            <div class="p-6 sm:p-8">
                <div class="flex items-center justify-center w-16 h-16 rounded-full mb-6 mx-auto bg-green-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <h3 class="text-2xl font-bold text-gray-900 text-center mb-2">{{ modalTitle }}</h3>
                <p class="text-gray-600 text-center mb-8">{{ modalMessage }}</p>
                
                <div class="flex justify-center">
                    <button 
                        @click="closeModal"
                        class="px-10 py-3 bg-brand-blue text-white font-bold rounded-xl hover:bg-brand-blue/90 transition-all font-sans"
                    >
                        {{ __('OK') }}
                    </button>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<style scoped>
.from-brand-cyan { --tw-gradient-from: #00d4ff; }
.to-brand-blue { --tw-gradient-to: #007bff; }
.text-brand-blue { color: #007bff; }
.text-brand-cyan { color: #00d4ff; }
</style>