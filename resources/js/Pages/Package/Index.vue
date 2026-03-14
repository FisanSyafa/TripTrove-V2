<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import LazyImage from '@/Components/LazyImage.vue';
import { getCurrentInstance, ref } from 'vue'; // Tambahkan ref

const props = defineProps({
    packages: Object,
    filters: Object,
    filterOptions: Object,
});

// --- State untuk Mobile Filter Toggle ---
const showMobileFilter = ref(false);

// --- Helper Global ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;
const $formatCurrency = (value) => app.appContext.config.globalProperties.$formatCurrency(value);

// --- Helper Rating ---
const getAverageRating = (pkg) => {
    if (!pkg.reviews || pkg.reviews.length === 0) return 0;
    const total = pkg.reviews.reduce((acc, review) => acc + review.rating, 0);
    const avg = total / pkg.reviews.length;
    return Number(avg.toFixed(1));
};

const toBoolean = (value) => {
    return [true, 'true', 1, '1'].includes(value);
};

const filterForm = useForm({
    search: props.filters.search || '',
    category: props.filters.category || null,
    min_price: props.filters.min_price || '',
    max_price: props.filters.max_price || '',
    duration: props.filters.duration || null,
    includes_hotel: toBoolean(props.filters.includes_hotel),
    includes_guide: toBoolean(props.filters.includes_guide),
    includes_driver_vehicle: toBoolean(props.filters.includes_driver_vehicle),
    sortBy: props.filters.sortBy || null,
});

const applyFilters = () => {
    filterForm
        .transform((data) => ({
            ...data,
            includes_hotel: data.includes_hotel ? 1 : 0,
            includes_guide: data.includes_guide ? 1 : 0,
            includes_driver_vehicle: data.includes_driver_vehicle ? 1 : 0,
        }))
        .get(route('packages.all'), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                // Opsional: Tutup filter di mobile setelah apply
                // showMobileFilter.value = false; 
            }
        });
};

// PERBAIKAN: Reset manual agar benar-benar kosong, bukan kembali ke nilai awal props
const resetFilters = () => {
    filterForm.search = '';
    filterForm.category = null;
    filterForm.min_price = '';
    filterForm.max_price = '';
    filterForm.duration = null;
    filterForm.includes_hotel = false;
    filterForm.includes_guide = false;
    filterForm.includes_driver_vehicle = false;
    filterForm.sortBy = null;
    
    applyFilters();
};

const storageUrl = (path) => {
    return `/storage/${path}`;
};
</script>

<template>
    <Head :title="__('All Tour Packages')" />
    <MainLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="bg-white border-b border-gray-200 pt-8 pb-8 px-4 md:px-6 text-center shadow-sm">
                <h1 class="text-3xl md:text-4xl pb-2 font-extrabold mb-2 bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text">
                    {{ __('Explore All Tour Packages') }}
                </h1>
                <p class="text-sm md:text-lg text-gray-600 font-medium max-w-2xl mx-auto">
                    {{ __('Find your next adventure from our complete collection.') }}
                </p>
            </div>

            <div class="container mx-auto px-4 md:px-6 py-6 md:py-8">
                
                <div class="md:hidden mb-4">
                    <button 
                        @click="showMobileFilter = !showMobileFilter"
                        class="w-full flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 font-bold py-3 rounded-xl shadow-sm hover:bg-gray-50 transition-all"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span v-if="!showMobileFilter">{{ __('Show Filters') }}</span>
                        <span v-else>{{ __('Hide Filters') }}</span>
                    </button>
                </div>

                <div
                    class="relative bg-white/40 p-5 md:p-6 rounded-2xl shadow-[0_18px_45px_rgba(15,23,42,0.12)]
                           border border-white/70 mb-8 md:mb-10 transition-all duration-300
                           hover:bg-white/60 backdrop-blur-xl overflow-hidden"
                    :class="{ 'hidden md:block': !showMobileFilter }" 
                >
                    <div class="pointer-events-none absolute -top-10 -left-6 w-32 h-32 bg-gradient-to-br from-cyan-400/15 to-blue-400/10 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-16 right-0 w-40 h-40 bg-gradient-to-tr from-blue-500/15 to-cyan-400/10 blur-3xl"></div>

                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-4 border-b border-white/60 pb-2">
                            <h3 class="text-base md:text-lg font-bold text-gray-800 flex items-center">
                                <span class="bg-brand-cyan/10 text-brand-cyan p-1.5 rounded-lg mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                </span>
                                {{ __('Filter Packages') }}
                            </h3>
                            <button
                                @click="resetFilters"
                                class="text-xs md:text-sm text-red-500 hover:text-red-700 font-semibold hover:underline"
                            >
                                {{ __('Reset Filter') }}
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 text-black">
                            <!-- Search has been moved below filter panel -->

                            <div>
                                <InputLabel for="category" :value="__('Category')" class="!text-gray-700 !font-semibold mb-1" />
                                <select
                                    id="category"
                                    v-model="filterForm.category"
                                    class="block w-full bg-white/60 border-white/70 backdrop-blur-sm
                                           rounded-lg focus:ring-brand-cyan focus:border-brand-cyan text-gray-900 text-sm md:text-base"
                                >
                                    <option :value="null">{{ __('All Categories') }}</option>
                                    <option
                                        v-for="cat in filterOptions.categories"
                                        :key="cat"
                                        :value="cat"
                                    >
                                        {{ cat }}
                                    </option>
                                </select>
                            </div>

                            <div class="text-black">
                                <InputLabel :value="__('Price Range')" class="!text-gray-700 !font-semibold mb-1" />
                                <div class="flex items-center space-x-2">
                                    <TextInput
                                        type="number"
                                        v-model="filterForm.min_price"
                                        :placeholder="__('Min')"
                                        class="w-1/2 bg-white/60 border-white/70 backdrop-blur-sm
                                               rounded-lg focus:ring-brand-cyan focus:border-brand-cyan text-sm md:text-base"
                                    />
                                    <span class="text-gray-400">-</span>
                                    <TextInput
                                        type="number"
                                        v-model="filterForm.max_price"
                                        :placeholder="__('Max')"
                                        class="w-1/2 bg-white/60 border-white/70 backdrop-blur-sm
                                               rounded-lg focus:ring-brand-cyan focus:border-brand-cyan text-sm md:text-base"
                                    />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="duration" :value="__('Duration')" class="!text-gray-700 !font-semibold mb-1" />
                                <select
                                    id="duration"
                                    v-model="filterForm.duration"
                                    class="block w-full bg-white/60 border-white/70 backdrop-blur-sm
                                           rounded-lg focus:ring-brand-cyan focus:border-brand-cyan text-gray-900 text-sm md:text-base"
                                >
                                    <option :value="null">{{ __('All Durations') }}</option>
                                    <option value="1-3">{{ __('1 - 3 Days') }}</option>
                                    <option value="4-6">{{ __('4 - 6 Days') }}</option>
                                    <option value="7+">{{ __('7+ Days') }}</option>
                                </select>
                            </div>
                        </div>

                        <div
                            class="mt-6 flex flex-col md:flex-row justify-between items-center border-t border-white/60 pt-4 gap-4"
                        >
                            <div class="flex flex-wrap gap-4 items-center w-full md:w-auto">
                                <span class="text-sm font-semibold text-gray-700 mr-2">{{ __('Includes:') }}</span>
                                <label class="flex items-center cursor-pointer hover:text-brand-blue transition">
                                    <input
                                        type="checkbox"
                                        v-model="filterForm.includes_hotel"
                                        class="mr-2 text-brand-cyan rounded focus:ring-brand-cyan border-gray-300"
                                    />
                                    <span class="text-gray-600 text-sm">{{ __('Hotel') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-brand-blue transition">
                                    <input
                                        type="checkbox"
                                        v-model="filterForm.includes_guide"
                                        class="mr-2 text-brand-cyan rounded focus:ring-brand-cyan border-gray-300"
                                    />
                                    <span class="text-gray-600 text-sm">{{ __('Guide') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-brand-blue transition">
                                    <input
                                        type="checkbox"
                                        v-model="filterForm.includes_driver_vehicle"
                                        class="mr-2 text-brand-cyan rounded focus:ring-brand-cyan border-gray-300"
                                    />
                                    <span class="text-gray-600 text-sm">{{ __('Transport') }}</span>
                                </label>
                            </div>

                            <div class="w-full md:w-auto min-w-[200px]">
                                <PrimaryButton
                                    @click="applyFilters"
                                    class="w-full justify-center !py-3 bg-gradient-to-r from-brand-cyan to-brand-blue
                                           hover:shadow-lg transform hover:-translate-y-0.5 transition-all"
                                    :disabled="filterForm.processing"
                                >
                                    {{ __('Apply Filters') }}
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEARCH BAR & DREAM TOUR BUTTON -->
                <div class="mb-8 md:mb-10">
                    <!-- Desktop: Side by side -->
                    <div class="hidden md:flex gap-4 items-center">
                        <!-- Search Bar (Left) -->
                        <div class="relative flex-1 group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 group-focus-within:text-brand-blue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                v-model="filterForm.search"
                                @keyup.enter="applyFilters"
                                :placeholder="__('Search your dream tour package...')"
                                class="block w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl shadow-sm
                                       focus:ring-4 focus:ring-brand-blue/10 focus:border-brand-blue text-gray-900 
                                       placeholder-gray-400 transition-all font-medium"
                            />
                            <div class="absolute inset-y-0 right-2 flex items-center">
                                <button 
                                    @click="applyFilters"
                                    class="bg-brand-blue text-white px-6 py-2 rounded-xl font-bold hover:bg-brand-cyan transition-all shadow-md active:scale-95"
                                >
                                    {{ __('Search') }}
                                </button>
                            </div>
                        </div>

                        <!-- Dream Tour Button (Right) -->
                        <div class="shrink-0">
                            <Link 
                                :href="route('dream-tour.create')"
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-cyan to-brand-blue text-white px-6 py-4 rounded-2xl font-bold text-base shadow-xl shadow-brand-blue/30 hover:shadow-brand-blue/50 hover:-translate-y-1 transition-all active:scale-95 whitespace-nowrap"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                {{ __('Create Your Dream Trip') }}
                            </Link>
                        </div>
                    </div>

                    <!-- Mobile: Stacked with OR divider -->
                    <div class="md:hidden space-y-4">
                        <!-- Search Bar -->
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-brand-blue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                v-model="filterForm.search"
                                @keyup.enter="applyFilters"
                                :placeholder="__('Search your dream tour package...')"
                                class="block w-full pl-11 pr-20 py-3 bg-white border border-gray-200 rounded-xl shadow-sm
                                       focus:ring-4 focus:ring-brand-blue/10 focus:border-brand-blue text-gray-900 
                                       placeholder-gray-400 transition-all font-medium text-sm"
                            />
                            <div class="absolute inset-y-0 right-2 flex items-center">
                                <button 
                                    @click="applyFilters"
                                    class="bg-brand-blue text-white px-4 py-1.5 rounded-lg font-bold hover:bg-brand-cyan transition-all shadow-md active:scale-95 text-sm"
                                >
                                    {{ __('Search') }}
                                </button>
                            </div>
                        </div>

                        <!-- OR Divider -->
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-px bg-gray-300"></div>
                            <span class="text-gray-500 font-semibold text-xs uppercase tracking-wider">{{ __('or') }}</span>
                            <div class="flex-1 h-px bg-gray-300"></div>
                        </div>

                        <!-- Dream Tour Button -->
                        <Link 
                            :href="route('dream-tour.create')"
                            class="flex items-center justify-center gap-2 bg-gradient-to-r from-brand-cyan to-brand-blue text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg shadow-brand-blue/30 hover:shadow-brand-blue/50 active:scale-95 transition-all w-full"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            {{ __('Create Your Dream Trip') }}
                        </Link>
                    </div>
                </div>

                <!-- DREAM TOUR CTA - REMOVED (functionality moved above) -->

                <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                    <h2 class="text-lg md:text-xl font-bold text-gray-800">
                        {{ __('Showing') }}
                        <span class="text-brand-blue text-xl md:text-2xl mx-1">{{ packages.total }}</span>
                        {{ __('packages') }}
                    </h2>

                    <select
                        v-model="filterForm.sortBy"
                        @change="applyFilters"
                        class="w-full sm:w-auto bg-white/60 border-white/70 backdrop-blur-sm rounded-xl px-4 py-2 text-sm
                               focus:ring-brand-cyan focus:border-brand-cyan text-gray-700 shadow-sm
                               hover:border-brand-cyan cursor-pointer"
                    >
                        <option :value="null">{{ __('Sort by (Latest)') }}</option>
                        <option value="price_asc">{{ __('Price (Lowest)') }}</option>
                        <option value="price_desc">{{ __('Price (Highest)') }}</option>
                        <option value="duration_asc">{{ __('Duration (Shortest)') }}</option>
                    </select>
                </div>

                <div
                    v-if="packages.data.length > 0"
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-8"
                >
                    <div
                        v-for="pkg in packages.data"
                        :key="pkg.id"
                        class="relative bg-white/40 rounded-xl md:rounded-2xl shadow-[0_10px_20px_rgba(15,23,42,0.08)] md:shadow-[0_18px_45px_rgba(15,23,42,0.12)]
                               overflow-hidden border border-white/70 hover:shadow-xl hover:border-white
                               transform transition-all duration-300 hover:-translate-y-2 backdrop-blur-xl flex flex-col"
                    >
                        <div class="pointer-events-none absolute -top-10 left-6 w-20 h-20 md:w-28 md:h-28 bg-gradient-to-br from-cyan-400/15 to-blue-400/10 blur-3xl"></div>
                        <div class="pointer-events-none absolute -bottom-16 right-0 w-24 h-24 md:w-36 md:h-36 bg-gradient-to-tr from-blue-500/15 to-cyan-300/10 blur-3xl"></div>

                        <Link
                            :href="route('packages.show', pkg.slug)"
                            class="block relative z-10 flex flex-col h-full"
                        >
                            <div class="relative h-36 md:h-52 shrink-0 overflow-hidden">
                                <LazyImage
                                    :src="storageUrl(pkg.cover_image_url)"
                                    :alt="pkg.name"
                                    aspect-ratio="56.25%"
                                    img-class="w-full h-full object-cover"
                                />
                                <span
                                    v-if="pkg.discount_percent > 0"
                                    class="absolute top-2 right-2 md:top-3 md:right-3 bg-orange-500 text-white text-[10px] md:text-xs font-bold px-1.5 py-0.5 md:px-2 md:py-1 rounded-full shadow-sm backdrop-blur-sm z-10"
                                >
                                    -{{ pkg.discount_percent }}%
                                </span>
                            </div>

                            <div class="p-3 md:p-5 flex flex-col flex-grow relative">
                                <h3
                                    class="text-sm md:text-lg font-bold mb-1 truncate text-gray-900 group-hover:text-brand-blue transition-colors"
                                    :title="pkg.name"
                                >
                                    {{ pkg.name }}
                                </h3>

                                <div class="flex items-center mb-2 md:mb-3">
                                    <svg
                                        v-for="n in 5"
                                        :key="n"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-3 w-3 md:h-4 md:w-4"
                                        :class="n <= Math.round(getAverageRating(pkg)) ? 'text-yellow-400' : 'text-gray-300'"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                        />
                                    </svg>
                                    <span class="ml-1 text-xs md:text-sm font-bold text-gray-800">
                                        {{ getAverageRating(pkg) }}
                                    </span>
                                    <span class="ml-0.5 md:ml-1 text-[10px] md:text-xs text-gray-500">
                                        ({{ pkg.reviews ? pkg.reviews.length : 0 }})
                                    </span>
                                </div>

                                <p class="text-xs md:text-sm text-gray-600 mb-0.5 md:mb-1 flex items-center truncate">
                                    <span class="mr-1 text-brand-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 md:w-4 md:h-4">
                                            <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    {{ pkg.destination_summary }}
                                </p>

                                <p class="text-xs md:text-sm text-gray-600 mb-2 md:mb-4 flex items-center">
                                    <span class="mr-1 text-brand-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 md:w-4 md:h-4">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    {{ pkg.duration_days }} {{ __('Days') }}
                                </p>

                                <div class="mt-auto text-right border-t border-white/60 pt-2 md:pt-3">
                                    <div
                                        v-if="pkg.discount_percent > 0"
                                        class="text-[10px] md:text-xs text-gray-400 line-through font-medium"
                                    >
                                        {{ $formatCurrency(pkg.price) }}
                                    </div>
                                    <div class="flex justify-end items-baseline flex-wrap">
                                        <span class="text-sm md:text-lg font-extrabold text-brand-blue">
                                            {{ $formatCurrency(pkg.price * (1 - (pkg.discount_percent / 100))) }}
                                        </span>
                                        <span class="text-[10px] md:text-xs text-gray-500 font-medium ml-1">
                                            /{{ __('person') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div
                    v-else
                    class="text-center py-20 bg-white/60 rounded-2xl shadow-sm border border-white/70 backdrop-blur-xl"
                >
                    <div class="text-6xl mb-4">🔍</div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ __('Oops! Not Found') }}</h2>
                    <p class="text-gray-500 mt-2">
                        {{ __('Try changing your search keywords or filters.') }}
                    </p>
                    <button
                        @click="resetFilters"
                        class="mt-6 text-brand-cyan font-bold hover:underline"
                    >
                        {{ __('Clear All Filters') }}
                    </button>
                </div>

                <Pagination class="mt-12 justify-center" :links="packages.links" />
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.from-brand-cyan { --tw-gradient-from: #00d4ff; }
.to-brand-blue { --tw-gradient-to: #007bff; }
.text-brand-blue { color: #007bff; }
.text-brand-cyan { color: #00d4ff; }
.bg-brand-cyan { background-color: #00d4ff; }
</style>