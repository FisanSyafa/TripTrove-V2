<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, getCurrentInstance } from 'vue';

const props = defineProps({
    booking: Object,
    clientKey: String,
    snapToken: String,
});

// --- Helper Global untuk Bahasa & Mata Uang ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;
const $formatCurrency = (value) => app.appContext.config.globalProperties.$formatCurrency(value);
// ----------------------------------------------

// Load script Midtrans Snap saat halaman dibuka
onMounted(() => {
    const script = document.createElement('script');
    // Pastikan URL ini sesuai environment (Sandbox/Production)
    script.src = 'https://app.sandbox.midtrans.com/snap/snap.js'; 
    script.setAttribute('data-client-key', props.clientKey);
    document.head.appendChild(script);
});

const pay = () => {
    console.log('Starting payment with token:', props.snapToken);
    window.snap.pay(props.snapToken, {
        onSuccess: function(result){
            console.log('Payment success:', result);
            // Kirim ke backend untuk update status dan kirim email
            router.post(route('payment.success', props.booking.id), {
                transaction_id: result.transaction_id || 'TXN-' + Date.now(),
                payment_type: result.payment_type || 'midtrans',
            }, {
                onSuccess: () => {
                    console.log('Backend updated successfully');
                },
                onError: (errors) => {
                    console.error('Backend error:', errors);
                }
            });
        },
        onPending: function(result){
            console.log('Payment pending:', result);
            alert(__('Waiting for your payment!'));
        },
        onError: function(result){
            console.log('Payment error:', result);
            alert(__('Payment failed!'));
        },
        onClose: function(){
            console.log('Payment popup closed');
        }
    });
};
</script>

<template>
    <MainLayout>
        <Head :title="__('Payment Confirmation')" />

        <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                
                <div class="text-center mb-10">
                    <h1 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text mb-3">
                        {{ __('Payment Confirmation') }}
                    </h1>
                    <p class="text-gray-600 text-lg">
                        {{ __('Please complete your payment to secure your adventure.') }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden relative">
                    
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-brand-cyan/10 rounded-full blur-2xl"></div>

                    <div class="p-8 relative z-10">
                        
                        <div class="flex flex-col md:flex-row justify-between items-center border-b border-gray-100 pb-6 mb-6 gap-4">
                            <div class="text-center md:text-left">
                                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">{{ __('Booking Code') }}</h3>
                                <p class="text-2xl font-mono font-bold text-gray-800">{{ booking.booking_code }}</p>
                            </div>
                            <div class="px-4 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold shadow-sm">
                                {{ __('Waiting for Payment') }}
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-6 mb-8 border border-gray-100">
                            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <span class="mr-2">📦</span> {{ __('Booking Details') }}
                            </h4>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-sm md:text-base">
                                    <span class="text-gray-600">{{ __('Tour Package') }}</span>
                                    <span class="font-bold text-gray-900 text-right">{{ booking.tour_package.name }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm md:text-base">
                                    <span class="text-gray-600">{{ __('Participants') }}</span>
                                    <span class="font-bold text-gray-900">{{ booking.num_participants }} {{ __('People') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm md:text-base" v-if="booking.tour_package.duration_days">
                                    <span class="text-gray-600">{{ __('Duration') }}</span>
                                    <span class="font-bold text-gray-900">{{ booking.tour_package.duration_days }} {{ __('Days') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-center justify-center mb-8 bg-blue-50/50 p-6 rounded-xl border border-blue-100/50">
                            <p class="text-gray-500 font-medium mb-1">{{ __('Total Amount to Pay') }}</p>
                            <h2 class="text-4xl md:text-5xl font-extrabold text-brand-blue tracking-tight">
                                {{ $formatCurrency(booking.total_price) }}
                            </h2>
                        </div>

                        <button 
                            @click="pay" 
                            class="w-full group relative flex justify-center py-4 px-4 border border-transparent text-lg font-bold rounded-xl text-white bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200"
                        >
                            <span class="flex items-center">
                                {{ __('Pay Now') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
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
/* Gradient definitions matching theme */
.from-brand-cyan { --tw-gradient-from: #00d4ff; }
.to-brand-blue { --tw-gradient-to: #007bff; }
.text-brand-blue { color: #007bff; }
.bg-brand-blue { background-color: #007bff; }
</style>