<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, getCurrentInstance, onMounted, onUnmounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import LazyImage from '@/Components/LazyImage.vue';

// --- HELPER GLOBAL ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;
const $formatCurrency = (value) => app.appContext.config.globalProperties.$formatCurrency(value);

// === PROPS ===
const props = defineProps({
    popularPackages: Array,
    announcement: Object,
    filterOptions: Object,
    adminWhatsappNumber: String
});

// --- FORM PENCARIAN (HOME) ---
const homeSearchForm = useForm({
    search: '',
    category: null,
    duration: null, 
    min_price: '',
    max_price: '',
});

const submitHomeSearch = () => {
    homeSearchForm.get(route('packages.all'));
};

const aboutSectionRef = ref(null);
const isAboutVisible = ref(false);

// --- HERO SLIDESHOW LOGIC ---
const activePackageIndex = ref(0);
let slideshowInterval = null;

onMounted(() => {
    const observer = new IntersectionObserver(
        ([entry]) => {
            isAboutVisible.value = entry.isIntersecting;
        },
        { threshold: 0.3 }
    );

    if (aboutSectionRef.value) {
        observer.observe(aboutSectionRef.value);
    }

    if (props.popularPackages && props.popularPackages.length > 0) {
        slideshowInterval = setInterval(() => {
            activePackageIndex.value = (activePackageIndex.value + 1) % props.popularPackages.length;
        }, 4000);
    }
});

onUnmounted(() => {
    if (slideshowInterval) clearInterval(slideshowInterval);
});

// --- HELPER LAINNYA ---
const getAverageRating = (pkg) => {
    if (!pkg.reviews || pkg.reviews.length === 0) return 0;
    const total = pkg.reviews.reduce((acc, review) => acc + review.rating, 0);
    const avg = total / pkg.reviews.length;
    return Number(avg.toFixed(1));
};

const showWaBubble = ref(true); 
const openFloatingWhatsApp = () => {
    const adminWANumber = props.adminWhatsappNumber || '6285122605855';
    const text = __("Hi TripTrove! I would like to ask about tour packages.");
    const encodedText = encodeURIComponent(text);
    window.open(`https://wa.me/${adminWANumber}?text=${encodedText}`, '_blank');
};

const faqs = ref([
    { question: 'How do I book a tour package?', answer: 'You can book a package by clicking "Book Now" on the package details page.', active: false },
    { question: 'Can I change my departure date?', answer: 'Schedule changes are possible subject to terms and conditions.', active: false },
    { question: 'Is the price all-inclusive?', answer: 'Generally, accommodation, tours, and some meals are included.', active: false },
    { question: 'Is traveling with TripTrove safe?', answer: 'Your safety is our priority.', active: false }
]);
const toggleFaq = (index) => { faqs.value[index].active = !faqs.value[index].active; };
const form = useForm({ name: '', email: '', phone_number: '', subject: '', message: '' });

const submitContact = () => {
    const adminWANumber = props.adminWhatsappNumber || '6285122605855';
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            if (confirm(__("Your message has been saved! Send via WhatsApp?"))) {
                 let text = `*${__("Hi TripTrove!")}*\n\n*${__("Name")}:* ${form.name}\n*${__("Subject")}:* ${form.subject}\n\n*${__("Message")}:*\n${form.message}`;
                 window.open(`https://wa.me/${adminWANumber}?text=${encodeURIComponent(text)}`, '_blank');
            }
        },
        onError: (errors) => console.error(errors)
    });
};
const features = ref([
    { title: 'Global Destinations', description: 'Explore 100+ curated destinations worldwide designed for your ultimate comfort.' },
    { title: 'Best Price Guarantee', description: 'We offer competitive pricing and the best value without compromising on quality.' },
    { title: 'Safe & Trusted', description: 'Your safety is our top priority with verified partners and 24/7 support.' },
    { title: 'Professional Guides', description: 'Our experienced and friendly guides are ready to make your trip unforgettable.' }
]);
</script>

<template>
    <MainLayout>
        <section class="relative min-h-screen flex items-center overflow-hidden bg-[url('/images/main-photo.jpg')] bg-cover bg-center bg-no-repeat">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900 via-gray-900/80 to-gray-900/40 z-0"></div>
            
            <div class="container mx-auto px-6 relative z-10 pt-20 pb-20"> 
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <div class="lg:col-span-7 flex flex-col items-center lg:items-start text-center lg:text-left">
                        <div v-if="announcement" class="mb-6 animate-slideDown">
                            <div class="inline-flex items-center gap-3 py-2 px-4 pr-5 rounded-full 
                                            bg-white/10 backdrop-blur-md border border-white/20 shadow-lg
                                            group hover:bg-white/20 transition-all duration-300 cursor-default">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-cyan opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-cyan"></span>
                                </span>
                                <span class="text-brand-cyan font-bold text-xs tracking-wider uppercase border-r border-white/20 pr-3 mr-1">
                                    Update
                                </span>
                                <span class="text-white text-sm font-medium truncate max-w-[200px] sm:max-w-xs">
                                    {{ announcement.message }}
                                </span>
                            </div>
                        </div>

                        <h1 class="text-5xl sm:text-6xl md:text-7xl font-extrabold leading-tight text-white mb-6 drop-shadow-lg">
                            {{ __('Explore The World') }} <br />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-cyan to-brand-blue">
                                {{ __('With TripTrove') }}
                            </span>
                        </h1>

                        <p class="text-lg text-gray-300 mb-10 max-w-xl leading-relaxed">
                            {{ __('Discover unforgettable adventures in your dream destinations. We curate the best experiences just for you.') }}
                        </p>

                        <div class="w-full max-w-2xl bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-3xl shadow-2xl animate-slideUp animation-delay-200">
                            <form @submit.prevent="submitHomeSearch" class="flex flex-col md:flex-row gap-3">
                                
                                <div class="relative flex-grow group/input">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within/input:text-brand-cyan transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <TextInput 
                                        id="search" 
                                        type="text" 
                                        v-model="homeSearchForm.search" 
                                        :placeholder="__('Where do you want to go?')" 
                                        class="pl-11 w-full bg-gray-900/50 border-white/30 rounded-xl py-3
                                               focus:bg-gray-900/80 focus:ring-2 focus:ring-brand-cyan focus:border-transparent 
                                               text-white placeholder-gray-400 shadow-inner backdrop-blur-sm transition-all" 
                                    />
                                </div>

                                <div class="relative md:w-1/3">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                        </svg>
                                    </div>
                                    <select 
                                        id="category" 
                                        v-model="homeSearchForm.category" 
                                        class="w-full pl-10 bg-gray-900/50 border-white/30 rounded-xl py-3
                                               focus:bg-gray-900/80 focus:ring-2 focus:ring-brand-cyan focus:border-transparent 
                                               text-white shadow-inner backdrop-blur-sm transition-all appearance-none cursor-pointer"
                                    >
                                        <option :value="null" class="text-gray-900">{{ __('Category') }}</option>
                                        <option v-for="cat in filterOptions?.categories" :key="cat" :value="cat" class="text-gray-900">{{ cat }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>

                                <div>
                                    <PrimaryButton 
                                        class="w-full md:w-auto h-full justify-center py-3 px-8 rounded-xl font-bold text-base 
                                               bg-gradient-to-r from-brand-cyan to-brand-blue text-white 
                                               shadow-[0_0_20px_rgba(0,212,255,0.3)] border border-white/20
                                               hover:shadow-[0_0_30px_rgba(0,212,255,0.5)] hover:scale-105 hover:brightness-110
                                               active:scale-95 transition-all duration-300" 
                                        :class="{ 'opacity-75': homeSearchForm.processing }" 
                                        :disabled="homeSearchForm.processing"
                                    >
                                        {{ __('Search') }}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                        </div>

                    <div class="lg:col-span-5 hidden lg:flex justify-center relative">
                         <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] bg-brand-blue/20 blur-[80px] rounded-full pointer-events-none"></div>

                        <div v-if="popularPackages && popularPackages.length > 0" 
                             class="relative w-[380px] h-[500px] bg-gray-900 rounded-[2.5rem] border-[6px] border-gray-800/50 shadow-2xl overflow-hidden transform rotate-3 hover:rotate-0 transition-all duration-500 group">
                            <div class="absolute inset-0">
                                <transition-group name="fade">
                                    <div v-for="(pkg, index) in popularPackages" 
                                         :key="pkg.id"
                                         v-show="activePackageIndex === index"
                                         class="absolute inset-0 w-full h-full"
                                    >
                                        <LazyImage 
                                            :src="pkg.cover_image_url ? `/storage/${pkg.cover_image_url}` : '/images/placeholder.jpg'"
                                            :alt="pkg.name"
                                            aspect-ratio="100%"
                                            img-class="w-full h-full object-cover"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-80"></div>
                                        <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                                            <div class="flex justify-between items-end mb-2">
                                                <span class="px-3 py-1 bg-brand-cyan text-gray-900 text-xs font-bold rounded-lg uppercase tracking-wider">Popular</span>
                                                <div class="flex text-yellow-400 text-sm">★★★★★ <span class="text-white/70 ml-1 text-xs">({{ pkg.reviews.length }})</span></div>
                                            </div>
                                            <h3 class="text-2xl font-bold mb-1 leading-tight line-clamp-1">{{ pkg.name }}</h3>
                                            <p class="text-gray-300 text-sm mb-4 line-clamp-1 flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                                                {{ pkg.destination_summary }}
                                            </p>
                                            <div class="flex items-center justify-between border-t border-white/20 pt-4">
                                                <div>
                                                    <span class="text-xs text-gray-400 block">Starting from</span>
                                                    <span class="text-xl font-bold text-brand-cyan">{{ $formatCurrency(pkg.price * (1 - (pkg.discount_percent / 100))) }}</span>
                                                </div>
                                                <Link :href="route('packages.show', pkg.slug)" class="w-10 h-10 rounded-full bg-white text-gray-900 flex items-center justify-center hover:bg-brand-cyan transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </transition-group>
                            </div>
                            <div class="absolute top-0 left-0 h-1 bg-brand-cyan/50 transition-all duration-[4000ms] ease-linear w-full opacity-50"></div>
                        </div>
                        <div v-else class="w-[320px] h-[450px] bg-white/10 rounded-[2rem] border border-white/20 flex items-center justify-center text-white/50">No Packages</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="packages" class="py-8 px-4 md:py-12 md:px-6 bg-gray-50 border-b border-gray-200 relative overflow-hidden">
             <div class="container mx-auto max-w-7xl relative z-10">
                <h2 class="section-title-light text-2xl pb-2 md:text-4xl">{{ __('Popular Tour Packages') }}</h2>
                <p class="text-center text-gray-700 mb-8 md:mb-10 -mt-4 md:-mt-8 text-sm md:text-base font-medium">
                    {{ __('The most chosen tour package by our customers.') }}
                </p>

                <div v-if="popularPackages && popularPackages.length > 0" 
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-6 xl:gap-8">
                    <Link
                        v-for="pkg in popularPackages"
                        :key="pkg.id"
                        :href="route('packages.show', pkg.slug)"
                        class="destination-card block rounded-xl md:rounded-2xl overflow-hidden
                            bg-white/40 border border-white/70 backdrop-blur-xl
                            shadow-[0_10px_20px_rgba(15,23,42,0.08)] md:shadow-[0_18px_45px_rgba(15,23,42,0.12)]
                            transition-all duration-300 transform hover:-translate-y-1 md:hover:-translate-y-2
                            hover:bg-white/60 hover:border-white
                            relative group flex flex-col"
                    >

                        <div class="absolute top-20 left-0 w-16 h-16 md:w-24 md:h-24 bg-gradient-to-r from-cyan-400/15 to-blue-400/10 rounded-full blur-xl md:blur-2xl -ml-4 z-0 pointer-events-none"></div>
                        <div class="absolute bottom-0 right-0 w-20 h-20 md:w-32 md:h-32 bg-gradient-to-r from-cyan-400/10 to-blue-500/15 rounded-full blur-xl md:blur-2xl -mr-6 -mb-6 z-0 pointer-events-none"></div>

                        <div class="relative h-36 md:h-52 shrink-0 z-10 overflow-hidden">
                            <LazyImage 
                                :src="pkg.cover_image_url ? `/storage/${pkg.cover_image_url}` : 'https://picsum.photos/seed/placeholder/400/300'" 
                                :alt="pkg.name"
                                aspect-ratio="56.25%"
                                img-class="w-full h-full object-cover"
                            />
                            <span 
                                v-if="pkg.discount_percent > 0" 
                                class="absolute top-2 right-2 md:top-3 md:right-3 bg-orange-500 text-white text-[10px] md:text-xs font-bold px-1.5 py-0.5 md:px-2 md:py-1 rounded-full shadow-sm z-10">
                                -{{ pkg.discount_percent }}%
                            </span>
                        </div>
                        
                        <div class="p-3 md:p-5 flex flex-col flex-grow relative z-10">
                            <h3 class="text-sm md:text-lg font-bold mb-1 truncate text-gray-900 leading-tight" :title="pkg.name">
                                {{ pkg.name }}
                            </h3>

                            <div class="flex items-center mb-2 md:mb-3">
                                <svg v-for="n in 5" :key="n" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-4 md:w-4" :class="n <= Math.round(getAverageRating(pkg)) ? 'text-yellow-400' : 'text-gray-300'" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="ml-1 text-xs md:text-sm font-bold text-gray-800">{{ getAverageRating(pkg) }}</span>
                                <span class="ml-0.5 md:ml-1 text-[10px] md:text-xs text-gray-500">({{ pkg.reviews ? pkg.reviews.length : 0 }})</span>
                            </div>

                            <p class="text-xs md:text-sm text-gray-600 mb-0.5 md:mb-1 flex items-center truncate">
                                <span class="mr-1 text-brand-blue"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 md:w-4 md:h-4"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg></span> 
                                {{ pkg.destination_summary }}
                            </p>
                            <p class="text-xs md:text-sm text-gray-600 mb-2 md:mb-4 flex items-center">
                                <span class="mr-1 text-brand-blue"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 md:w-4 md:h-4"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" /></svg></span> 
                                {{ pkg.duration_days }} {{ __('Days') }}
                            </p>

                            <div class="mt-auto text-right border-t border-gray-100/70 pt-2 md:pt-3">
                                <div v-if="pkg.discount_percent > 0" class="text-[10px] md:text-xs text-gray-400 line-through font-medium">{{ $formatCurrency(pkg.price) }}</div>
                                <div class="flex justify-end items-baseline flex-wrap">
                                    <span class="text-sm md:text-lg font-extrabold text-brand-blue">{{ $formatCurrency(pkg.price * (1 - (pkg.discount_percent / 100))) }}</span>
                                    <span class="text-[10px] md:text-xs text-gray-500 font-medium ml-1">/{{ __('pax') }}</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
                
                <div v-else>
                    <p class="text-center text-gray-600 font-medium">{{ __('No tour packages are available at this moment.') }}</p>
                </div>

                <div class="text-center mt-10 md:mt-14">
                    <Link :href="route('packages.all')" class="cta-btn-blue inline-flex items-center gap-3 rounded-full border border-brand-cyan bg-brand-cyan text-white backdrop-blur-xl px-8 py-3 md:px-10 md:py-4 text-sm md:text-lg font-bold tracking-wide shadow-lg shadow-brand-blue/30 hover:bg-blue-600 hover:border-blue-600 hover:-translate-y-1 transition-all duration-300">
                        {{ __('Explore All Tour Packages') }}
                    </Link>
                </div>
            </div>
        </section>

        <section id="features" class="py-12 px-4 md:py-20 md:px-6 bg-white relative">
            <div class="container mx-auto max-w-7xl">
                <div class="text-center mb-10 md:mb-16 max-w-3xl mx-auto">
                    <h2 class="text-2xl md:text-4xl pb-2 font-extrabold mb-4 md:mb-6 bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text inline-block">{{ __('Why Choose Us?') }}</h2>
                    <p class="text-gray-600 text-sm md:text-lg px-4">{{ __('We go the extra mile to ensure your journey is not just a trip, but a lifetime memory.') }}</p>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8">
                    <div v-for="(feature, index) in features" :key="index" class="group relative p-6 md:p-8 bg-gray-50 rounded-3xl border border-gray-100 hover:bg-white hover:border-brand-cyan/30 hover:shadow-2xl hover:shadow-brand-cyan/10 transition-all duration-500 hover:-translate-y-2 overflow-hidden">
                        <div class="absolute -right-4 -top-6 text-8xl md:text-[8rem] font-black text-gray-200/50 group-hover:text-brand-cyan/5 group-hover:scale-110 transition-all duration-500 select-none leading-none z-0">{{ (index + 1).toString().padStart(2, '0') }}</div>
                        <div class="w-12 h-1 bg-gradient-to-r from-brand-cyan to-brand-blue rounded-full mb-6 relative z-10"></div>
                        <div class="relative z-10">
                            <h3 class="text-lg md:text-xl font-bold mb-2 md:mb-3 text-gray-900 group-hover:text-brand-blue transition-colors duration-300">{{ __(feature.title) }}</h3>
                            <p class="text-gray-600 leading-relaxed text-sm group-hover:text-gray-700">{{ __(feature.description) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" ref="aboutSectionRef" class="py-12 px-4 md:py-24 md:px-6 bg-gray-50 relative overflow-hidden">
             <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
                <div class="absolute top-10 right-[-5%] w-[300px] md:w-[600px] h-[300px] md:h-[600px] bg-blue-100/20 rounded-full blur-3xl opacity-60"></div>
                <div class="absolute bottom-10 left-[-5%] w-[300px] md:w-[600px] h-[300px] md:h-[600px] bg-cyan-100/20 rounded-full blur-3xl opacity-60"></div>
            </div>
            <div class="container mx-auto max-w-6xl relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-16 items-center">
                    <div class="flex justify-center items-center order-2 md:order-1 relative h-64 md:h-80">
                        <div class="absolute inset-0 bg-gradient-to-tr from-brand-cyan/30 to-brand-blue/20 rounded-full blur-3xl transition-all duration-1000 ease-in-out transform" :class="isAboutVisible ? 'opacity-100 scale-110' : 'opacity-0 scale-75'"></div>
                        <div class="absolute transition-all duration-[1200ms] ease-[cubic-bezier(0.34,1.56,0.64,1)] bg-gradient-to-br from-gray-800 via-gray-900 to-black border border-white/10 p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] shadow-2xl z-0" :class="isAboutVisible ? 'scale-100 opacity-100 -rotate-12 -translate-x-10 md:-translate-x-16 -translate-y-4 md:-translate-y-6' : 'scale-90 opacity-0 rotate-0 translate-x-0 translate-y-0'">
                             <div class="w-32 md:w-64 flex items-center justify-center"><img src="/images/logo_triptrove_putih.png" alt="TripTrove White" class="w-full h-auto drop-shadow-[0_0_15px_rgba(255,255,255,0.5)]"></div>
                        </div>
                        <div class="relative transition-all duration-[1200ms] ease-[cubic-bezier(0.34,1.56,0.64,1)] bg-white/40 backdrop-blur-2xl border border-white/60 p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] shadow-[0_18px_45px_rgba(15,23,42,0.1)] z-10" :class="isAboutVisible ? 'rotate-6 translate-x-4 md:translate-x-8 translate-y-2 bg-white/70 border-white' : 'rotate-0 translate-x-0 translate-y-0'">
                            <div class="w-32 md:w-64 flex items-center justify-center"><img src="/images/logo_triptrove_hitam.png" alt="TripTrove Black" class="w-full h-auto drop-shadow-xl"></div>
                        </div>
                    </div>
                    <div class="text-center md:text-left order-1 md:order-2 transition-all duration-1000 ease-out delay-100" :class="isAboutVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
                         <div class="relative rounded-3xl border border-white/60 bg-white/50 backdrop-blur-xl p-6 md:p-12 shadow-sm transition-all duration-500 hover:shadow-lg hover:bg-white/60">
                            <div class="absolute -top-10 -right-10 w-32 md:w-40 h-32 md:h-40 bg-gradient-to-b from-cyan-400/10 to-transparent rounded-full blur-2xl pointer-events-none"></div>
                            <div class="absolute -bottom-10 -left-10 w-32 md:w-40 h-32 md:h-40 bg-gradient-to-t from-blue-600/10 to-transparent rounded-full blur-2xl pointer-events-none"></div>
                            <h2 class="text-2xl md:text-4xl pb-2 font-extrabold mb-4 md:mb-6 bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text inline-block">{{ __('About TripTrove') }}</h2>
                            <div class="space-y-4 md:space-y-6 text-sm md:text-lg text-gray-600 leading-relaxed font-medium relative z-10">
                                <p>{{ __("TripTrove isn't just a name; it's a promise. \"Trove\" means a store of valuable or delightful things. We are your personal treasure chest for the world's most amazing travel experiences.") }}</p>
                                <div class="h-px w-16 md:w-24 bg-gradient-to-r from-brand-cyan/50 to-transparent mx-auto md:mx-0 my-3 md:my-4"></div>
                                <p>{{ __("That's why our logo is a diamond; a precious gem encapsulating millions of spectacular sights and authentic experiences. We handle the details, so you can focus on creating memories.") }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="py-12 px-4 md:px-6 bg-white relative overflow-hidden">
             <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
                <div class="absolute top-[10%] left-[-5%] w-64 md:w-96 h-64 md:h-96 bg-blue-100/20 rounded-full blur-3xl opacity-50"></div>
                <div class="absolute bottom-[10%] right-[-5%] w-64 md:w-96 h-64 md:h-96 bg-cyan-100/20 rounded-full blur-3xl opacity-50"></div>
            </div>
            <div class="container mx-auto max-w-3xl relative z-10">
                <h2 class="section-title-light pb-2 text-2xl md:text-4xl">{{ __('Frequently Asked Questions') }}</h2>
                <div class="space-y-4 md:space-y-6">
                    <div v-for="(faq, index) in faqs" :key="index" class="group relative rounded-2xl border border-white/70 bg-white/60 backdrop-blur-xl shadow-[0_8px_30px_rgba(0,0,0,0.04)] overflow-hidden transition-all duration-300 hover:shadow-[0_20px_50px_rgba(0,119,255,0.15)] hover:border-white hover:-translate-y-1">
                        <div class="absolute -top-10 -left-6 w-24 h-24 bg-gradient-to-r from-cyan-400/15 to-blue-500/10 rounded-full blur-2xl transition-all duration-500 group-hover:scale-150"></div>
                        <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-gradient-to-r from-blue-600/10 to-cyan-400/15 rounded-full blur-2xl transition-all duration-500 group-hover:scale-150"></div>
                        <div class="relative z-10">
                            <div @click="toggleFaq(index)" class="faq-question-glass p-4 md:p-6">
                                <span class="text-gray-800 font-bold text-sm md:text-lg">{{ __(faq.question) }}</span>
                                <span class="flex items-center justify-center w-6 h-6 md:w-8 md:h-8 rounded-full bg-white/50 border border-white/60 shadow-sm transition-all duration-300 shrink-0 ml-2" :class="{'rotate-45 bg-blue-50 text-blue-600 border-blue-200': faq.active, 'text-gray-400': !faq.active}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </span>
                            </div>
                            <div class="faq-answer-glass" :class="{'active': faq.active}">
                                <div class="pb-4 px-4 md:pb-6 md:px-6 text-sm md:text-base text-gray-600 leading-relaxed border-t border-gray-100/60 pt-4">{{ __(faq.answer) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="py-12 px-4 md:py-20 md:px-6 bg-gray-50 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
                <div class="absolute top-1/4 left-[-10%] w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-blue-100/20 rounded-full blur-3xl opacity-60"></div>
                <div class="absolute bottom-1/4 right-[-10%] w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-cyan-100/20 rounded-full blur-3xl opacity-60"></div>
            </div>
            <div class="container mx-auto max-w-2xl relative z-10">
                <h2 class="section-title-light text-2xl md:text-4xl">{{ __('Get In Touch') }}</h2>
                <p class="text-center text-gray-600 mb-8 md:mb-12 -mt-4 md:-mt-6 text-sm md:text-lg">{{ __("Have questions? Need help planning your trip? We're here for you.") }}</p>
                <div v-if="$page.props.flash?.message" class="bg-green-100 border border-green-200 text-green-700 p-4 rounded-lg mb-6 shadow">{{ $page.props.flash.message }}</div>
                <div class="relative rounded-3xl border border-white/70 bg-white/60 backdrop-blur-xl shadow-[0_18px_45px_rgba(15,23,42,0.12)] p-6 md:p-12 overflow-hidden transition-all duration-500 group hover:shadow-[0_20px_50px_rgba(0,119,255,0.15)] hover:border-white">
                    <div class="absolute -top-24 -left-24 w-48 md:w-64 h-48 md:h-64 bg-gradient-to-r from-cyan-400/15 to-blue-500/10 rounded-full blur-3xl pointer-events-none transition-transform duration-700 group-hover:scale-110"></div>
                    <div class="absolute -bottom-24 -right-24 w-48 md:w-64 h-48 md:h-64 bg-gradient-to-r from-blue-600/10 to-cyan-400/15 rounded-full blur-3xl pointer-events-none transition-transform duration-700 group-hover:scale-110"></div>
                    <form @submit.prevent="submitContact" class="relative z-10 space-y-4 md:space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                            <div><InputLabel for="name" :value="__('Your Name')" class="!text-brand-blue !font-bold mb-1 md:mb-2 text-sm md:text-base" /><TextInput id="name" type="text" class="mt-1 block w-full bg-white/50 border-gray-200 text-gray-900 focus:border-brand-cyan focus:ring-brand-cyan rounded-xl text-sm md:text-base" v-model="form.name" required placeholder="John Doe" /><InputError class="mt-2" :message="form.errors.name" /></div>
                            <div><InputLabel for="email" :value="__('Your Email')" class="!text-brand-blue !font-bold mb-1 md:mb-2 text-sm md:text-base" /><TextInput id="email" type="email" class="mt-1 block w-full bg-white/50 border-gray-200 text-gray-900 focus:border-brand-cyan focus:ring-brand-cyan rounded-xl text-sm md:text-base" v-model="form.email" required placeholder="name@example.com" /><InputError class="mt-2" :message="form.errors.email" /></div>
                        </div>
                        <div><InputLabel for="subject" :value="__('Subject')" class="!text-brand-blue !font-bold mb-1 md:mb-2 text-sm md:text-base" /><TextInput id="subject" type="text" class="mt-1 block w-full bg-white/50 border-gray-200 text-gray-900 focus:border-brand-cyan focus:ring-brand-cyan rounded-xl text-sm md:text-base" v-model="form.subject" required placeholder="I want to book a tour..." /><InputError class="mt-2" :message="form.errors.subject" /></div>
                        <div><InputLabel for="message" :value="__('Your Message')" class="!text-brand-blue !font-bold mb-1 md:mb-2 text-sm md:text-base" /><textarea id="message" class="mt-1 block w-full bg-white/50 border-gray-200 rounded-xl shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-gray-900 text-sm md:text-base" v-model="form.message" rows="5" required placeholder="Tell us more about your plan..."></textarea><InputError class="mt-2" :message="form.errors.message" /></div>
                        <div class="flex items-center justify-end pt-2"><PrimaryButton class="w-full md:w-auto justify-center py-2 md:py-3 px-6 md:px-8 bg-gradient-to-r from-brand-cyan to-brand-blue hover:shadow-lg hover:-translate-y-0.5 transition-all rounded-xl text-sm md:text-base" :class="{ 'opacity-75': form.processing }" :disabled="form.processing">{{ __('Send Message') }}</PrimaryButton></div>
                    </form>
                </div>
            </div>
        </section>

        <div class="fixed bottom-6 right-4 md:bottom-8 md:right-6 z-50 flex flex-col items-end space-y-2 group animate-slideUp">
            <div v-if="showWaBubble" class="relative mb-2 mr-2">
                 <div class="bg-white text-gray-800 text-xs md:text-sm font-medium py-2 px-4 md:py-3 md:px-5 rounded-2xl shadow-xl border border-gray-100 w-48 md:w-56 relative animate-float-slight">
                    <button @click="showWaBubble = false" class="absolute top-1 right-2 text-gray-400 hover:text-gray-600 text-xs font-bold">✕</button>
                    <p>{{ __('Need help? Chat with us!') }} 👋</p>
                    <div class="absolute -bottom-2 right-6 w-4 h-4 bg-white border-b border-r border-gray-100 transform rotate-45"></div>
                 </div>
            </div>
            <button @click="openFloatingWhatsApp" class="bg-[#25D366] hover:bg-[#20bd5a] text-white p-3 md:p-4 rounded-full shadow-2xl transition-all transform hover:scale-110 active:scale-95 flex items-center justify-center w-12 h-12 md:w-16 md:h-16 border-2 md:border-4 border-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 md:w-8 md:h-8" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg>
            </button>
        </div>

    </MainLayout>
</template>

<style scoped>
@keyframes float-slight { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
.animate-float-slight { animation: float-slight 3s ease-in-out infinite; }
.animate-slowZoom { animation: slowZoom 20s ease-in-out infinite alternate; }
@keyframes slowZoom { from { transform: scale(1.0); } to { transform: scale(1.1); } }
@keyframes shimmer { 100% { transform: translateX(100%); } }
.animate-shimmer { animation: shimmer 2s infinite; }
.section-title-light { text-align: center; font-weight: 700; margin-bottom: 1.5rem; background: linear-gradient(135deg, #00d4ff, #007bff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
@media (min-width: 768px) { .section-title-light { margin-bottom: 2rem; } }
.feature-card-light { background: #FFFFFF; padding: 2rem; border-radius: 20px; border: 1px solid #E5E7EB; transition: all 0.3s; text-align: center; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
.feature-card-light:hover { transform: translateY(-10px); border-color: #00d4ff; box-shadow: 0 10px 40px rgba(0, 212, 255, 0.15); }
.feature-icon-light { font-size: 3rem; margin-bottom: 1rem; }
.faq-question-glass { cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s; background: transparent; }
.faq-question-glass:hover span.text-gray-800 { color: #007bff; }
.faq-answer-glass { max-height: 0; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0; }
.faq-answer-glass.active { max-height: 500px; opacity: 1; }
.contact-form-light { background: #FFFFFF; padding: 3rem; border-radius: 20px; border: 1px solid #E5E7EB; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
.cta-btn-light { display: inline-block; background-color: #F97316; color: white; font-weight: 700; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(249, 115, 22, 0.3), 0 4px 6px -2px rgba(249, 115, 22, 0.1); transition: all 0.3s; }
.cta-btn-light:hover { background-color: #EA580C; transform: scale(1.05); box-shadow: 0 20px 25px -5px rgba(249, 115, 22, 0.4), 0 10px 10px -5px rgba(249, 115, 22, 0.2); }
.glass-card-clean { background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(4px) saturate(180%); -webkit-backdrop-filter: blur(16px) saturate(180%); border: 1px solid rgba(255, 255, 255, 0.4); border-top: 1px solid rgba(255, 255, 255, 0.6); border-left: 1px solid rgba(255, 255, 255, 0.6); box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15), 0 2px 8px 0 rgba(31, 38, 135, 0.1), inset 0 1px 0 0 rgba(255, 255, 255, 0.3); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; z-index: 20; }
.glass-card-clean:hover { transform: translateY(-8px); background: rgba(255, 255, 255, 0.3); box-shadow: 0 16px 48px 0 rgba(31, 38, 135, 0.2), 0 4px 16px 0 rgba(31, 38, 135, 0.15), inset 0 1px 0 0 rgba(255, 255, 255, 0.4); border-color: rgba(255, 255, 255, 0.6); }
@keyframes float { 0% { transform: translate(0, 0) scale(1) rotate(0deg); } 33% { transform: translate(20px, -20px) scale(1.05) rotate(5deg); } 66% { transform: translate(-20px, 20px) scale(0.95) rotate(-5deg); } 100% { transform: translate(0, 0) scale(1) rotate(0deg); } }
.animate-float { animation: float 15s ease-in-out infinite; }
.animation-delay-0 { animation-delay: 0s; }
.animation-delay-200 { animation-delay: 3s; }
.animation-delay-400 { animation-delay: 6s; }
.animation-delay-600 { animation-delay: 9s; }
.animation-delay-800 { animation-delay: 12s; }
@keyframes slideUp { from { opacity: 0; transform: translateY(50px); } to { opacity: 1; transform: translateY(0); } }
.animate-slideUp { animation: slideUp 1s ease both; }
.animation-delay-200 { animation-delay: 0.2s; }
.animation-delay-400 { animation-delay: 0.4s; }
@keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
.animate-slideDown { animation: slideDown 1s ease-out forwards; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.8s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.cta-btn-blue {
    display: inline-block;
    transition: all 0.3s;
}
</style>