<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, getCurrentInstance, computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    booking: Object,
    clientKey: String,
    snapToken: String,
    snapScript: String,
    adminWhatsappNumber: String,
    auth: Object,
});

const showSuccessModal = ref(false);
const modalMessage = ref('');
const modalTitle = ref('');
const modalType = ref('success'); // 'success' | 'availability'
const isCheckingAvailability = ref(false);

// --- Helper Global untuk Bahasa & Mata Uang ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;
const $formatCurrency = (value) => app.appContext.config.globalProperties.$formatCurrency(value);
// ----------------------------------------------

// Computed: participant display text
const participantDisplay = computed(() => {
    const adults = props.booking.num_adults || 0;
    const children = props.booking.num_children || 0;
    let text = adults + ' ' + __('Adult');
    if (children > 0) {
        text += ' & ' + children + ' ' + __('Children');
    }
    return text;
});

const carTypeLabel = computed(() => {
    if (!props.booking.car_type) return null;
    return props.booking.car_type === 'small' ? __('Small Car') : __('Large Car');
});

// WhatsApp URL for availability confirmation
const availabilityWhatsappUrl = computed(() => {
    if (!props.adminWhatsappNumber) return '#';
    
    const header = __('Availability Check');
    const intro = __('Hello TripTrove, I would like to check the availability for my booking:');
    const labelCode = __('Booking Code');
    const labelPackage = __('Package');
    const labelDate = __('Departure Date');
    const labelParticipants = __('Participants');
    const footer = __('Please let me know if this date is available. Thank you!');

    const dateStr = props.booking.departure_date 
        ? new Date(props.booking.departure_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) 
        : '-';

    const message = `*${header}*\n\n${intro}\n- ${labelCode}: ${props.booking.booking_code}\n- ${labelPackage}: ${props.booking.tour_package?.name || '-'}\n- ${labelDate}: ${dateStr}\n- ${labelParticipants}: ${participantDisplay.value}\n\n${footer}`;
    
    return `https://wa.me/${props.adminWhatsappNumber}?text=${encodeURIComponent(message)}`;
});

const paymentWhatsappUrl = computed(() => {
    if (!props.adminWhatsappNumber) return '#';
    
    const header = __('Payment Confirmation');
    const intro = __('Hello TripTrove, I would like to pay for my booking:');
    const labelCode = __('Booking Code');
    const labelPackage = __('Package');
    const labelDate = __('Departure Date');
    const labelParticipants = __('Participants');
    const labelAmount = __('Total Amount');
    const footer = __('Please let me know the payment instructions. Thank you!');

    const dateStr = props.booking.departure_date 
        ? new Date(props.booking.departure_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) 
        : '-';

    const message = `*${header}*\n\n${intro}\n- ${labelCode}: ${props.booking.booking_code}\n- ${labelPackage}: ${props.booking.tour_package?.name || '-'}\n- ${labelDate}: ${dateStr}\n- ${labelParticipants}: ${participantDisplay.value}\n- ${labelAmount}: ${$formatCurrency(props.booking.total_price)}\n\n${footer}`;
    
    return `https://wa.me/${props.adminWhatsappNumber}?text=${encodeURIComponent(message)}`;
});

// Load script Midtrans Snap saat halaman dibuka
onMounted(() => {
    const script = document.createElement('script');
    script.src = props.snapScript; 
    script.setAttribute('data-client-key', props.clientKey);
    document.head.appendChild(script);
});

const pay = () => {
    window.snap.pay(props.snapToken, {
        onSuccess: function(result){
            router.post(route('payment.success', props.booking.id), {
                transaction_id: result.transaction_id || 'TXN-' + Date.now(),
                payment_type: result.payment_type || 'midtrans',
            });
        },
        onPending: function(result){
            alert(__('Waiting for your payment!'));
        },
        onError: function(result){
            alert(__('Payment failed!'));
        },
        onClose: function(){
            console.log('Payment popup closed');
        }
    });
};

const checkAvailability = () => {
    isCheckingAvailability.value = true;
    axios.post(route('booking.request-availability', props.booking.id))
        .then(response => {
            if (response.data.success) {
                modalType.value = 'availability';
                modalTitle.value = __('Availability Request Sent!');
                modalMessage.value = __('Your availability request has been sent successfully. TripTrove will contact you as soon as possible regarding the availability of your selected date.');
                showSuccessModal.value = true;
            }
        })
        .catch(error => {
            alert(__('An error occurred. Please try again.'));
        })
        .finally(() => {
            isCheckingAvailability.value = false;
        });
};

// Handle Pay Now (manual WhatsApp contact)
const openManualPaymentWhatsapp = () => {
    window.open(paymentWhatsappUrl.value, '_blank');
};

const closeModal = () => {
    showSuccessModal.value = false;
    if (props.auth?.user) {
        router.visit(route('dashboard'));
    } else {
        router.visit(route('home'));
    }
};
</script>

<template>
    <MainLayout>
        <Head :title="__('Payment Confirmation')" />

        <div class="min-h-screen bg-gray-50 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                
                <div class="text-center mb-8 sm:mb-10">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text mb-3">
                        {{ __('Payment Confirmation') }}
                    </h1>
                    <p class="text-gray-600 text-base sm:text-lg">
                        {{ __('Please complete your payment to secure your adventure.') }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden relative">
                    
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-brand-cyan/10 rounded-full blur-2xl"></div>

                    <div class="p-5 sm:p-8 relative z-10">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-center border-b border-gray-100 pb-6 mb-6 gap-4">
                            <div class="text-center sm:text-left">
                                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">{{ __('Booking Code') }}</h3>
                                <p class="text-xl sm:text-2xl font-mono font-bold text-gray-800">{{ booking.booking_code }}</p>
                            </div>
                            <div class="px-4 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold shadow-sm">
                                {{ __('Waiting for Payment') }}
                            </div>
                        </div>
                        
                        <!-- Availability Message for Pending (At Top) -->
                        <div v-if="booking.status === 'pending'" class="mb-8 p-6 bg-orange-50 rounded-2xl border-2 border-dashed border-orange-200 text-center">
                            <p class="text-orange-800 font-bold mb-3">{{ __('Checking availability...') }}</p>
                            <p class="text-sm text-orange-700 mb-6">
                                {{ __('We are checking the availability for this tour package on your selected date. Please click the button below to notify us that you are waiting for this booking.') }}
                            </p>
                            <button 
                                @click="checkAvailability" 
                                :disabled="isCheckingAvailability"
                                class="inline-flex items-center px-6 py-3 bg-orange-500 text-white font-bold rounded-xl shadow-md hover:bg-orange-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg v-if="isCheckingAvailability" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ isCheckingAvailability ? __('Sending...') : __('Check Availability') }}
                            </button>
                            
                            <div class="mt-4 pt-4 border-t border-orange-100">
                                <Link 
                                    :href="auth.user ? route('dashboard') : route('home')" 
                                    class="text-sm font-bold text-orange-700 hover:text-orange-900 transition-colors uppercase tracking-wider flex items-center justify-center p-2"
                                >
                                    &larr; {{ auth.user ? __('Back to Dashboard') : __('Back to Home') }}
                                </Link>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4 sm:p-6 mb-8 border border-gray-100">
                            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <span class="mr-2">📦</span> {{ __('Booking Details') }}
                            </h4>
                            
                            <div class="space-y-3">
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center text-sm md:text-base gap-1">
                                    <span class="text-gray-600">{{ __('Tour Package') }}</span>
                                    <span class="font-bold text-gray-900 sm:text-right">{{ booking.tour_package.name }}</span>
                                </div>
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center text-sm md:text-base gap-1">
                                    <span class="text-gray-600">{{ __('Participants') }}</span>
                                    <span class="font-bold text-gray-900">{{ participantDisplay }}</span>
                                </div>
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center text-sm md:text-base gap-1" v-if="booking.departure_date">
                                    <span class="text-gray-600">{{ __('Departure Date') }}</span>
                                    <span class="font-bold text-gray-900">{{ new Date(booking.departure_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
                                </div>
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center text-sm md:text-base gap-1" v-if="booking.tour_package.pickup_time">
                                    <span class="text-gray-600">{{ __('Pickup') }}</span>
                                    <span class="font-bold text-gray-900">{{ booking.tour_package.pickup_time }}</span>
                                </div>
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center text-sm md:text-base gap-1" v-if="booking.tour_package.duration_days">
                                    <span class="text-gray-600">{{ __('Duration') }}</span>
                                    <span class="font-bold text-gray-900">{{ booking.tour_package.duration_days }} {{ __('Days') }}</span>
                                </div>
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center text-sm md:text-base gap-1 border-t border-gray-100 pt-2 mt-2" v-if="booking.car_type">
                                    <span class="text-gray-600">{{ __('Transportation') }} ({{ carTypeLabel }})</span>
                                    <span class="font-bold text-gray-900">{{ $formatCurrency(booking.car_price) }}</span>
                                </div>
                                <template v-if="booking.group_tickets && booking.group_tickets.length > 0">
                                    <div v-for="(gt, idx) in booking.group_tickets" :key="idx" class="flex flex-col sm:flex-row justify-between sm:items-center text-sm md:text-base gap-1 border-t border-gray-100 pt-2 mt-2">
                                        <span class="text-gray-600">{{ gt.name || __('Group Ticket') }} ({{ gt.count }} {{ __('Ticket(s)') }})</span>
                                        <span class="font-bold text-gray-900">{{ $formatCurrency(gt.total) }}</span>
                                    </div>
                                </template>
                                <div v-else-if="Number(booking.group_ticket_total) > 0" class="flex flex-col sm:flex-row justify-between sm:items-center text-sm md:text-base gap-1 border-t border-gray-100 pt-2 mt-2">
                                    <span class="text-gray-600">{{ __('Group Ticket') }}</span>
                                    <span class="font-bold text-gray-900">{{ $formatCurrency(booking.group_ticket_total) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-center justify-center mb-8 bg-blue-50/50 p-4 sm:p-6 rounded-xl border border-blue-100/50">
                            <p class="text-gray-500 font-medium mb-1">{{ __('Total Amount to Pay') }}</p>
                            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-brand-blue tracking-tight">
                                {{ $formatCurrency(booking.total_price) }}
                            </h2>
                        </div>

                        <!-- Action Buttons for Available -->
                        <template v-if="booking.status === 'available'">
                            <!-- Pay Now Button (Disabled) -->
                            <button 
                                disabled
                                class="w-full group relative flex justify-center py-3.5 sm:py-4 px-4 border border-transparent text-base sm:text-lg font-bold rounded-xl text-white bg-gray-400 cursor-not-allowed shadow-none"
                            >
                                <span class="flex items-center">
                                    {{ __('Pay with payment gateway is coming soon') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </span>
                            </button>

                             <!-- Pay Now (via manual) Button -->
                             <div class="mt-4">
                                 <button 
                                     @click="openManualPaymentWhatsapp" 
                                     class="w-full group relative flex justify-center py-3.5 sm:py-4 px-4 bg-green-500 hover:bg-green-600 font-bold rounded-xl text-white shadow-md hover:shadow-lg transition-all duration-200"
                                 >
                                     <span class="flex items-center">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                         </svg>
                                         {{ __('Pay Now') }}
                                     </span>
                                 </button>
                                <p class="mt-2 text-center text-xs text-gray-500 italic">
                                    {{ __('Chat with us for payment and we will provide payment method options') }}
                                </p>
                            </div>
                        </template>

                        <p class="mt-4 text-center text-xs text-gray-400">
                            {{ __('Coming soon for secure payment processed by Midtrans. Supports Visa, Mastercard, QRIS, and Bank Transfer.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <Link 
                        :href="auth.user ? route('dashboard') : route('home')" 
                        class="text-sm font-medium text-gray-500 hover:text-brand-blue transition-colors"
                    >
                        &larr; {{ auth.user ? __('Back to Dashboard') : __('Back to Home') }}
                    </Link>
                </div>

            </div>
        </div>

        <!-- Success / Availability Modal -->
        <Modal :show="showSuccessModal" @close="closeModal">
            <div class="p-6 sm:p-8">
                <div class="flex items-center justify-center w-16 h-16 rounded-full mb-6 mx-auto" :class="modalType === 'availability' ? 'bg-orange-100' : 'bg-green-100'">
                    <svg v-if="modalType === 'availability'" xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <h3 class="text-2xl font-bold text-gray-900 text-center mb-2">{{ modalTitle }}</h3>
                <p class="text-gray-600 text-center mb-8">{{ modalMessage }}</p>
                
                <!-- WhatsApp button for availability modal -->
                <div v-if="modalType === 'availability'" class="space-y-3">
                    <a 
                        :href="availabilityWhatsappUrl" 
                        target="_blank"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-green-500 text-white font-bold rounded-xl shadow-md hover:bg-green-600 transition-all"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        {{ __('Confirm via WhatsApp') }}
                    </a>
                    <button 
                        @click="closeModal"
                        class="w-full py-3 px-4 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-all uppercase tracking-wide text-sm"
                    >
                        {{ __('Exit') }}
                    </button>
                </div>

                <!-- Simple OK button for other modals -->
                <div v-else class="flex justify-center">
                    <PrimaryButton @click="closeModal" class="!px-10">
                        {{ __('OK') }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<style scoped>
.from-brand-cyan { --tw-gradient-from: #00d4ff; }
.to-brand-blue { --tw-gradient-to: #007bff; }
.text-brand-blue { color: #007bff; }
.bg-brand-blue { background-color: #007bff; }
</style>