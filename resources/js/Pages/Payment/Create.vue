<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, getCurrentInstance, computed } from 'vue';

const props = defineProps({
    booking: Object,
    clientKey: String,
    snapToken: String,
    snapScript: String,
    adminWhatsappNumber: String,
});

// --- Helper Global untuk Bahasa & Mata Uang ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;
const $formatCurrency = (value) => app.appContext.config.globalProperties.$formatCurrency(value);
// ----------------------------------------------

// Computed: participant display text
const participantDisplay = computed(() => {
    const adults = props.booking.num_adults || props.booking.num_participants || 1;
    const children = props.booking.num_children || 0;
    let text = adults + ' ' + __('Adult');
    if (children > 0) {
        text += ' & ' + children + ' ' + __('Children');
    }
    return text;
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

const payOnArrival = () => {
    axios.post(route('booking.pay-on-arrival', props.booking.id))
        .then(response => {
            if (response.data.success && response.data.whatsapp_url) {
                window.location.href = response.data.whatsapp_url;
            }
        })
        .catch(error => {
            alert(__('An error occurred. Please try again.'));
        });
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
                            </div>
                        </div>

                        <div class="flex flex-col items-center justify-center mb-8 bg-blue-50/50 p-4 sm:p-6 rounded-xl border border-blue-100/50">
                            <p class="text-gray-500 font-medium mb-1">{{ __('Total Amount to Pay') }}</p>
                            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-brand-blue tracking-tight">
                                {{ $formatCurrency(booking.total_price) }}
                            </h2>
                        </div>

                        <!-- Pay Now Button -->
                        <button 
                            @click="pay" 
                            class="w-full group relative flex justify-center py-3.5 sm:py-4 px-4 border border-transparent text-base sm:text-lg font-bold rounded-xl text-white bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200"
                        >
                            <span class="flex items-center">
                                {{ __('Pay Now') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </span>
                        </button>

                        <!-- Pay on Arrival Button -->
                        <button 
                            @click="payOnArrival" 
                            class="w-full mt-3 group relative flex justify-center py-3.5 sm:py-4 px-4 border-2 border-gray-300 text-base sm:text-lg font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 shadow-sm hover:shadow transition-all duration-200"
                        >
                            <span class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                </svg>
                                {{ __('Pay on Arrival') }}
                            </span>
                        </button>

                        <p class="mt-4 text-center text-xs text-gray-400">
                            {{ __('Secure payment processed by Midtrans. Supports Visa, Mastercard, QRIS, and Bank Transfer.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <Link :href="route('home')" class="text-sm font-medium text-gray-500 hover:text-brand-blue transition-colors">
                        &larr; {{ __('Back to Home') }}
                    </Link>
                </div>

            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.from-brand-cyan { --tw-gradient-from: #00d4ff; }
.to-brand-blue { --tw-gradient-to: #007bff; }
.text-brand-blue { color: #007bff; }
.bg-brand-blue { background-color: #007bff; }
</style>