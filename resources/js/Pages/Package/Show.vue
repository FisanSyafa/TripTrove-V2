<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, getCurrentInstance } from 'vue';
import LazyImage from '@/Components/LazyImage.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    package: Object,
    adminWhatsappNumber: String
});

// --- Helper Global ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;
const $formatCurrency = (value) => app.appContext.config.globalProperties.$formatCurrency(value);
const page = computed(() => app.appContext.config.globalProperties.$page);
// ---

// Helper lokal
const storageUrl = (path) => {
    if (!path) return '';
    return `/storage/${path}`;
};

// --- LIGHTBOX LOGIC WITH NAVIGATION ---
const showLightbox = ref(false);
const currentLightboxIndex = ref(0);
const allImages = computed(() => {
    const images = [{ url: props.package.cover_image_url, alt: props.package.name }];
    if (props.package.galleries && props.package.galleries.length > 0) {
        props.package.galleries.forEach(img => {
            images.push({ url: img.image_url, alt: `${props.package.name} gallery` });
        });
    }
    return images;
});

const openLightbox = (imageUrl) => {
    const index = allImages.value.findIndex(img => img.url === imageUrl);
    currentLightboxIndex.value = index >= 0 ? index : 0;
    showLightbox.value = true;
};

const closeLightbox = () => {
    showLightbox.value = false;
};

const nextImage = () => {
    currentLightboxIndex.value = (currentLightboxIndex.value + 1) % allImages.value.length;
};

const prevImage = () => {
    currentLightboxIndex.value = (currentLightboxIndex.value - 1 + allImages.value.length) % allImages.value.length;
};

const currentLightboxImage = computed(() => {
    return storageUrl(allImages.value[currentLightboxIndex.value]?.url);
});

// --- BOOKING CONFIRMATION MODAL ---
const showBookingModal = ref(false);

const handleBookNowClick = () => {
    // Langsung ke halaman booking tanpa cek login
    router.visit(route('booking.create', props.package.slug));
};

const continueWithoutLogin = () => {
    showBookingModal.value = false;
    // Redirect ke route booking (akan diarahkan ke WhatsApp oleh controller)
    router.visit(route('booking.create', props.package.slug));
};

const goToLogin = () => {
    showBookingModal.value = false;
    router.visit(route('login'));
};

// --- (StarRating inlined in template for production build compatibility) ---

const averageRating = computed(() => {
    if (!props.package.reviews || props.package.reviews.length === 0) {
        return 0;
    }
    const total = props.package.reviews.reduce((acc, review) => acc + review.rating, 0);
    return (total / props.package.reviews.length).toFixed(1);
});

// --- REVIEW PAGINATION ---
const showAllReviews = ref(false);
const displayedReviews = computed(() => {
    if (showAllReviews.value || props.package.reviews.length <= 5) {
        return props.package.reviews;
    }
    return props.package.reviews.slice(0, 5);
});

// --- CONTACT WHATSAPP ---
const openWhatsApp = () => {
    const adminWANumber = props.adminWhatsappNumber || '6285122605855';
    const text = __("Hi TripTrove! I have a question about") + ` ${props.package.name}`;
    window.open(`https://wa.me/${adminWANumber}?text=${encodeURIComponent(text)}`, '_blank');
};
</script>

<template>
    <MainLayout>
        <Head :title="package.name" />
        
        <div class="min-h-screen bg-gray-50 py-12 px-6">
            <div class="container mx-auto">
            
                <div class="relative mb-6 shadow-xl rounded-2xl overflow-hidden bg-white group">
                    <button @click="openLightbox(package.cover_image_url)" class="w-full block overflow-hidden">
                        <LazyImage 
                            :src="storageUrl(package.cover_image_url)"
                            :alt="package.name"
                            aspect-ratio="56.25%"
                            img-class="w-full h-[300px] md:h-[500px] object-cover transition-transform duration-700 group-hover:scale-105"
                        />
                    </button>
                    <span v-if="package.discount_percent > 0" class="absolute top-4 right-4 bg-orange-500 text-white text-sm font-bold px-4 py-1 rounded-full shadow-lg animate-pulse z-10">
                        -{{ package.discount_percent }}%
                    </span>
                </div>

                <div v-if="package.galleries && package.galleries.length > 0" class="mb-10">
                    <div class="flex overflow-x-auto space-x-4 p-4 rounded-xl bg-white border border-gray-200 shadow-sm">
                        
                        <button @click="openLightbox(package.cover_image_url)" 
                                class="flex-shrink-0 w-32 h-24 rounded-lg overflow-hidden transition-transform duration-300 hover:scale-105 border-2 border-brand-cyan shadow-md ring-2 ring-offset-2 ring-brand-cyan/20">
                            <LazyImage 
                                :src="storageUrl(package.cover_image_url)"
                                :alt="package.name"
                                aspect-ratio="75%"
                                img-class="w-full h-full object-cover"
                            />
                        </button>

                        <div v-for="image in package.galleries" :key="image.id" class="flex-shrink-0">
                            <button @click="openLightbox(image.image_url)" class="w-32 h-24 rounded-lg overflow-hidden transition-transform duration-300 hover:scale-105 border border-gray-200 hover:border-brand-cyan hover:shadow-md">
                                <LazyImage 
                                    :src="storageUrl(image.image_url)" 
                                    :alt="`${package.name} gallery image ${image.id}`"
                                    aspect-ratio="75%"
                                    img-class="w-full h-full object-cover"
                                />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-8">
                        <div>
                            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl pb-2.5 font-extrabold mb-3 md:mb-4 bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text leading-tight">
                                {{ package.name }}
                            </h1>

                            <div class="flex flex-wrap gap-x-3 md:gap-x-6 gap-y-2 text-gray-600 font-medium text-xs sm:text-sm md:text-base">
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-brand-blue"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
                                    {{ package.destination_summary }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-brand-blue"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" /></svg>
                                    {{ package.duration_days }} {{ __('Days') }}
                                </span>
                                <span v-if="package.pickup_time" class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-green-500"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" /></svg>
                                    {{ __('Pickup') }}: {{ package.pickup_time }}
                                </span>
                                <span v-if="package.category" class="flex items-center gap-1 px-2 py-0.5 bg-blue-50 rounded-full text-brand-blue">{{ package.category }}</span>
                            </div>
                        </div>

                        <!-- BOOK NOW SECTION - MOBILE ONLY (Above Reviews) -->
                        <div class="lg:hidden bg-white p-6 rounded-2xl shadow-xl border border-gray-100 relative overflow-hidden">
                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-brand-cyan/10 rounded-full blur-xl"></div>

                            <div class="mb-6 relative z-10">
                                <p class="text-base sm:text-lg text-gray-400 line-through font-medium" v-if="package.discount_percent > 0">
                                    {{ $formatCurrency(package.price) }}
                                </p>
                                <div class="flex items-baseline">
                                    <p class="text-3xl sm:text-4xl font-extrabold text-brand-blue">
                                        {{ $formatCurrency(package.price * (1 - (package.discount_percent / 100))) }}
                                    </p>
                                    <p class="text-xs sm:text-sm text-gray-500 ml-2 font-medium">/ {{ __('person') }}</p>
                                </div>
                            </div>

                            <hr class="border-gray-100 mb-6">

                            <div class="space-y-3 mb-8">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">{{ __('Duration') }}</span>
                                    <span class="font-bold text-gray-900">{{ package.duration_days }} {{ __('Day') }}</span>
                                </div>
                            </div>

                            <button 
                                @click="handleBookNowClick"
                                class="block w-full text-center px-6 py-4 bg-gradient-to-r from-brand-cyan to-brand-blue 
                                    text-white text-lg font-bold rounded-xl shadow-lg shadow-blue-500/30
                                    hover:from-brand-blue hover:to-brand-cyan hover:shadow-blue-500/50
                                    transition-all duration-300 transform hover:-translate-y-1"
                            >
                                {{ __('Book Now') }}
                            </button>
                            
                            <p class="text-center text-xs text-gray-400 mt-4">
                                *{{ __('Terms & Conditions apply') }}
                            </p>
                        </div>

                        <div class="bg-white p-4 sm:p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100">
                            <h3 class="text-lg sm:text-xl md:text-2xl font-bold mb-3 md:mb-4 text-gray-900">{{ __('About This Trip') }}</h3>
                            <div class="prose prose-sm sm:prose md:prose-lg max-w-none text-gray-700 leading-relaxed text-justify">
                                <div v-html="package.description"></div>
                                <p v-if="package.location_details" class="mt-4 text-gray-600 bg-gray-50 p-4 rounded-lg border-l-4 border-brand-cyan">
                                <strong>{{ __('Location Details') }}:</strong> {{ package.location_details }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-white p-4 sm:p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100">
                            <h3 class="text-lg sm:text-xl md:text-2xl font-bold mb-4 md:mb-6 text-gray-900 flex items-center">
                                {{ __('Facilities Included') }}
                            </h3>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-3 text-sm md:text-base text-gray-700">
                                <li v-if="package.includes_hotel" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <span class="text-green-500 text-xl">✓</span> <span>{{ __('Hotel Accommodation') }}</span>
                                </li>
                                <li v-if="package.includes_guide" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <span class="text-green-500 text-xl">✓</span> <span>{{ __('Professional Tour Guide') }}</span>
                                </li>
                                <li v-if="package.includes_entrance_fee" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <span class="text-green-500 text-xl">✓</span> <span>{{ __('Attraction Entrance Fees') }}</span>
                                </li>
                                <li v-if="package.includes_driver_vehicle" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <span class="text-green-500 text-xl">✓</span> <span>{{ __('Transportation (Driver & Vehicle)') }}</span>
                                </li>
                            </ul>
                            <p v-if="!package.includes_hotel && !package.includes_guide && !package.includes_entrance_fee && !package.includes_driver_vehicle" class="text-gray-500 italic mt-2">
                                {{ __('Contact us for facility details.') }}
                            </p>
                        </div>
                        
                        <div class="mt-8 md:mt-12">
                             <h3 class="text-lg sm:text-xl md:text-2xl font-bold mb-4 md:mb-6 text-gray-900 border-b border-gray-200 pb-3 md:pb-4">
                                {{ __('Customer Reviews') }} <span class="text-gray-500 text-base md:text-lg font-normal">({{ package.reviews.length }})</span>
                            </h3>

                            <div v-if="package.reviews.length > 0" class="flex items-center gap-4 md:gap-6 bg-white p-4 md:p-6 rounded-2xl shadow-sm border border-gray-100 mb-6 md:mb-8">
                                <div class="text-4xl sm:text-5xl font-extrabold text-brand-blue">{{ averageRating }}</div>
                                <div class="border-l border-gray-200 pl-4 md:pl-6">
                                    <div class="flex items-center">
                                        <svg v-for="n in 5" :key="n" class="w-4 h-4 sm:w-5 sm:h-5" :class="n <= averageRating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19 10 15.27z"/>
                                        </svg>
                                    </div>
                                    <div class="text-xs sm:text-sm text-gray-500 mt-1">{{ __('Average of') }} {{ package.reviews.length }} {{ __('reviews') }}</div>
                                </div>
                            </div>

                            <div v-if="package.reviews.length > 0" class="space-y-4">
                                <div v-for="review in displayedReviews" :key="review.id" class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                                    <div class="flex gap-3 sm:gap-4">
                                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-brand-cyan to-brand-blue text-white flex-shrink-0 flex items-center justify-center font-bold text-lg sm:text-xl shadow-md">
                                            {{ review.user.name.charAt(0) }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <h4 class="font-bold text-gray-900 text-base sm:text-lg">{{ review.user.name }}</h4>
                                                    <p class="text-xs text-gray-500">{{ new Date(review.review_date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric'}) }}</p>
                                                </div>
                                                <div class="flex items-center">
                                                    <svg v-for="n in 5" :key="n" class="w-4 h-4 sm:w-5 sm:h-5" :class="n <= review.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19 10 15.27z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-sm sm:text-base text-gray-700 italic bg-gray-50 p-3 rounded-lg">"{{ review.comment }}"</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- View All Button -->
                                <div v-if="package.reviews.length > 5 && !showAllReviews" class="text-center pt-4">
                                    <button 
                                        @click="showAllReviews = true"
                                        class="inline-flex items-center gap-2 px-6 py-3 bg-white border-2 border-brand-blue text-brand-blue font-bold rounded-xl hover:bg-brand-blue hover:text-white transition-all duration-300 shadow-sm hover:shadow-md"
                                    >
                                        {{ __('View All Reviews') }} ({{ package.reviews.length }})
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Show Less Button -->
                                <div v-if="showAllReviews && package.reviews.length > 5" class="text-center pt-4">
                                    <button 
                                        @click="showAllReviews = false"
                                        class="inline-flex items-center gap-2 px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all duration-300"
                                    >
                                        {{ __('Show Less') }}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div v-else class="text-center bg-white p-8 rounded-2xl border border-dashed border-gray-300">
                                <p class="text-gray-500 text-lg">{{ __('No reviews for this package yet.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-24 h-fit">
                        <!-- DESKTOP ONLY -->
                        <div class="hidden lg:block bg-white p-6 rounded-2xl shadow-xl border border-gray-100 relative overflow-hidden">
                            
                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-brand-cyan/10 rounded-full blur-xl"></div>

                            <div class="mb-6 relative z-10">
                                <p class="text-base sm:text-lg text-gray-400 line-through font-medium" v-if="package.discount_percent > 0">
                                    {{ $formatCurrency(package.price) }}
                                </p>
                                <div class="flex items-baseline">
                                    <p class="text-3xl sm:text-4xl font-extrabold text-brand-blue">
                                        {{ $formatCurrency(package.price * (1 - (package.discount_percent / 100))) }}
                                    </p>
                                    <p class="text-xs sm:text-sm text-gray-500 ml-2 font-medium">/ {{ __('person') }}</p>
                                </div>
                            </div>

                            <hr class="border-gray-100 mb-6">

                            <div class="space-y-3 mb-8">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">{{ __('Duration') }}</span>
                                    <span class="font-bold text-gray-900">{{ package.duration_days }} {{ __('Day') }}</span>
                                </div>
                            </div>

                            <button 
                                @click="handleBookNowClick"
                                class="block w-full text-center px-6 py-4 bg-gradient-to-r from-brand-cyan to-brand-blue 
                                    text-white text-lg font-bold rounded-xl shadow-lg shadow-blue-500/30
                                    hover:from-brand-blue hover:to-brand-cyan hover:shadow-blue-500/50
                                    transition-all duration-300 transform hover:-translate-y-1"
                            >
                                {{ __('Book Now') }}
                            </button>
                            
                            <p class="text-center text-xs text-gray-400 mt-4">
                                *{{ __('Terms & Conditions apply') }}
                            </p>
                        </div>

                        <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100 text-center">
                            <h4 class="font-bold text-blue-800 mb-2">{{ __('Need Help?') }}</h4>
                            <p class="text-sm text-blue-600 mb-4">{{ __('Contact our support team if you have any questions.') }}</p>
                            <button 
                                @click="openWhatsApp"
                                class="inline-flex items-center gap-2 text-sm font-bold text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg transition-all shadow-md hover:shadow-lg"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                                {{ __('Contact Us') }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showLightbox" @click="closeLightbox" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4 cursor-pointer">
                
                <button @click.stop="closeLightbox" class="absolute top-6 right-6 z-50 text-white bg-white/10 hover:bg-white/20 rounded-full p-3 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                
                <!-- Previous Button -->
                <button 
                    v-if="allImages.length > 1"
                    @click.stop="prevImage" 
                    class="absolute left-4 md:left-8 z-50 text-white bg-white/10 hover:bg-white/20 rounded-full p-3 transition-all"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Next Button -->
                <button 
                    v-if="allImages.length > 1"
                    @click.stop="nextImage" 
                    class="absolute right-4 md:right-8 z-50 text-white bg-white/10 hover:bg-white/20 rounded-full p-3 transition-all"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                
                <div @click.stop class="relative">
                    <img :src="currentLightboxImage" class="max-w-[95vw] max-h-[90vh] rounded-lg shadow-2xl border-4 border-white">
                    <!-- Image Counter -->
                    <div v-if="allImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/70 text-white px-4 py-2 rounded-full text-sm font-medium">
                        {{ currentLightboxIndex + 1 }} / {{ allImages.length }}
                    </div>
                </div>
            </div>
        </transition>

        <!-- Booking Confirmation Modal -->
        <Modal :show="showBookingModal" @close="showBookingModal = false" max-width="2xl">
            <div class="p-8">
                <!-- Header -->
                <div class="text-center mb-6">
                    <div class="mx-auto w-16 h-16 bg-gradient-to-br from-brand-cyan to-brand-blue rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">
                        {{ __('Book Without Login?') }}
                    </h3>
                    <p class="text-sm sm:text-base text-gray-600">
                        {{ __('You can book via WhatsApp, but you will miss these benefits:') }}
                    </p>
                </div>

                <!-- Benefits of Login -->
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-6 mb-6 border border-blue-100">
                    <h4 class="font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-brand-blue mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                        {{ __('Benefits of Login:') }}
                    </h4>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">{{ __('Track your booking status in real-time') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">{{ __('Pay online with Midtrans (Credit Card, E-Wallet, Bank Transfer)') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">{{ __('View and print your booking invoice anytime') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">{{ __('Access your complete booking history') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">{{ __('Receive email notifications for booking updates') }}</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">{{ __('Leave reviews and share your experience') }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button
                        @click="goToLogin"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-brand-cyan to-brand-blue text-white font-bold rounded-lg
                            hover:from-brand-blue hover:to-brand-cyan transition-all duration-300 shadow-lg hover:shadow-xl
                            transform hover:-translate-y-0.5"
                    >
                        {{ __('Login / Register') }}
                    </button>
                    <button
                        @click="continueWithoutLogin"
                        class="flex-1 px-6 py-3 bg-white text-gray-700 font-semibold rounded-lg border-2 border-gray-300
                            hover:bg-gray-50 hover:border-gray-400 transition-all duration-300"
                    >
                        {{ __('Continue via WhatsApp') }}
                    </button>
                </div>

                <!-- Note -->
                <p class="text-center text-xs text-gray-500 mt-4">
                    {{ __('Booking via WhatsApp requires manual confirmation from our team') }}
                </p>
            </div>
        </Modal>
    </MainLayout>
</template>

<style scoped>
/* Menggunakan gradasi yang sama dengan homepage */
.from-brand-cyan { --tw-gradient-from: #00d4ff; }
.to-brand-blue { --tw-gradient-to: #007bff; }
.text-brand-blue { color: #007bff; }
.bg-brand-cyan { background-color: #00d4ff; }
.border-brand-cyan { border-color: #00d4ff; }

/* Perbaikan typography untuk Light Mode */
.prose strong {
    color: #111827; /* Gray-900 */
}
.prose h1, .prose h2, .prose h3 {
    color: #1f2937; /* Gray-800 */
}
</style>