<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { getCurrentInstance, computed } from 'vue';

const props = defineProps({
    booking: Object,
    whatsappUrl: String,
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

const contactTripTrove = () => {
    window.open(props.whatsappUrl, '_blank');
};
</script>

<template>
    <MainLayout>
        <Head :title="__('Payment Successful')" />

        <div class="min-h-screen bg-gray-50 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                
                <div class="text-center mb-8 sm:mb-10">
                    <!-- Success Icon -->
                    <div class="mx-auto flex items-center justify-center h-16 w-16 sm:h-20 sm:w-20 rounded-full bg-green-100 mb-4">
                        <svg class="h-8 w-8 sm:h-10 sm:w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-green-600 to-green-500 text-transparent bg-clip-text mb-3">
                        {{ __('Payment Successful!') }}
                    </h1>
                    <p class="text-gray-600 text-base sm:text-lg">
                        {{ __('Your payment has been processed successfully. Please contact us to confirm your booking details.') }}
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden relative">
                    
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-green-500/10 rounded-full blur-2xl"></div>

                    <div class="p-5 sm:p-8 relative z-10">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-center border-b border-gray-100 pb-6 mb-6 gap-4">
                            <div class="text-center sm:text-left">
                                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">{{ __('Booking Code') }}</h3>
                                <p class="text-xl sm:text-2xl font-mono font-bold text-gray-800">{{ booking.booking_code }}</p>
                            </div>
                            <div class="px-4 py-1.5 bg-green-100 text-green-800 rounded-full text-sm font-bold shadow-sm">
                                {{ __('Payment Confirmed') }}
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

                        <div class="flex flex-col items-center justify-center mb-8 bg-green-50/50 p-4 sm:p-6 rounded-xl border border-green-100/50">
                            <p class="text-gray-500 font-medium mb-1">{{ __('Amount Paid') }}</p>
                            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-green-600 tracking-tight">
                                {{ $formatCurrency(booking.total_price) }}
                            </h2>
                            <div class="flex items-center mt-2 text-green-600">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="font-semibold">{{ __('Paid Successfully') }}</span>
                            </div>
                        </div>

                        <!-- Next Steps Section -->
                        <div class="bg-blue-50 rounded-xl p-4 sm:p-6 mb-6 border border-blue-100">
                            <h4 class="text-lg font-bold text-blue-900 mb-3 flex items-center">
                                <span class="mr-2">📋</span> {{ __('Next Steps') }}
                            </h4>
                            <div class="space-y-2 text-sm text-blue-800">
                                <p class="flex items-start">
                                    <span class="mr-2 mt-0.5">1.</span>
                                    {{ __('Your payment has been confirmed and booking is being processed') }}
                                </p>
                                <p class="flex items-start">
                                    <span class="mr-2 mt-0.5">2.</span>
                                    {{ __('Click the button below to contact our team via WhatsApp') }}
                                </p>
                                <p class="flex items-start">
                                    <span class="mr-2 mt-0.5">3.</span>
                                    {{ __('Our team will provide detailed trip information and pickup details') }}
                                </p>
                                <p class="flex items-start">
                                    <span class="mr-2 mt-0.5">4.</span>
                                    {{ __('You will receive email confirmation with booking details') }}
                                </p>
                            </div>
                        </div>

                        <!-- Contact TripTrove Button -->
                        <button 
                            @click="contactTripTrove" 
                            class="w-full group relative flex justify-center py-3.5 sm:py-4 px-4 border border-transparent text-base sm:text-lg font-bold rounded-xl text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200"
                        >
                            <span class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                </svg>
                                {{ __('Contact TripTrove Team') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </span>
                        </button>

                        <p class="mt-4 text-center text-xs text-gray-400">
                            {{ __('Our team will respond within 24 hours to confirm your booking details.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <Link :href="route('home')" class="text-sm font-medium text-gray-500 hover:text-green-600 transition-colors">
                        &larr; {{ __('Back to Home') }}
                    </Link>
                </div>

            </div>
        </div>
    </MainLayout>
</template>