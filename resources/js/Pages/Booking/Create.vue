<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { countries as allCountries } from '@/Constants/countries';
import { computed, ref, getCurrentInstance } from 'vue';

const props = defineProps({
    package: Object
});

// --- Helper Global ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;
const $formatCurrency = (value) => app.appContext.config.globalProperties.$formatCurrency(value);
// ---

// =======================================================
// COUNTRY CODES DATA & OPTIONS
// =======================================================
const countryCodeOptions = allCountries.map(c => ({
    value: c.dial_code,
    label: `${c.flag} ${c.dial_code}`,
    name: c.name,
    flag: c.flag
}));

const countryOptions = allCountries.map(c => ({
    value: c.name,
    label: c.name
}));


// =======================================================
// FORM DATA
// =======================================================
const form = useForm({
    guest_name: '',
    package_id: props.package.id,
    departure_date: '',
    num_adults: 1,
    num_children: 0,
    contact_email: app.appContext.config.globalProperties.$page.props.auth.user ? app.appContext.config.globalProperties.$page.props.auth.user.email : '',
    contact_phone: '',
    country_code: '+62',
    country: 'Indonesia',
    pickup_location: '',
    special_requests: '',
});

// =======================================================
// PARTICIPANT SELECTOR LOGIC
// =======================================================
const showParticipantDropdown = ref(false);

const participantDisplay = computed(() => {
    let text = `${__('Adult')} x ${form.num_adults}`;
    if (props.package.is_children_friendly && form.num_children > 0) {
        text += `, ${__('Children')} x ${form.num_children}`;
    }
    return text;
});

const totalParticipants = computed(() => form.num_adults + form.num_children);

const increaseAdult = () => { form.num_adults++; };
const decreaseAdult = () => { if (form.num_adults > 1) form.num_adults--; };
const increaseChild = () => { form.num_children++; };
const decreaseChild = () => { if (form.num_children > 0) form.num_children--; };

const toggleParticipantDropdown = () => {
    showParticipantDropdown.value = !showParticipantDropdown.value;
};

// Close dropdown when clicking outside
const closeDropdown = () => {
    showParticipantDropdown.value = false;
};

// =======================================================
// PHONE VALIDATION
// =======================================================
const phoneError = ref('');

const validatePhone = () => {
    const phone = form.contact_phone;
    if (!phone) {
        phoneError.value = '';
        return;
    }
    // Only digits allowed, 6-15 chars
    if (!/^\d{6,15}$/.test(phone)) {
        phoneError.value = __('Phone number must be 6-15 digits without spaces or special characters');
    } else {
        phoneError.value = '';
    }
};

// =======================================================
// SUBMIT
// =======================================================
const submit = () => {
    validatePhone();
    if (phoneError.value) return;
    
    form.post(route('booking.store'), {
        onSuccess: () => {
            // Redirect akan otomatis dihandle oleh Inertia
            console.log('Booking created successfully');
        },
        onError: (errors) => {
            console.error("Booking Errors:", errors);
            // Scroll to top to show errors
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
};
</script>

<template>
    <Head :title="`${__('Booking')} ${package.name}`" />
    <MainLayout>
        <div class="min-h-screen bg-gray-50 py-12 px-6" @click="closeDropdown">
            <div class="container mx-auto max-w-4xl">
                
                <h1 class="text-4xl font-extrabold mb-8 bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text border-b border-gray-200 pb-4">
                    {{ __('Booking Form') }}
                </h1>

                <!-- Package Summary Card -->
                <div class="bg-white p-6 md:p-8 rounded-2xl mb-8 shadow-lg border border-gray-100 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 z-0"></div>
                    
                    <div class="relative z-10">
                        <h2 class="text-2xl font-bold text-gray-900">{{ package.name }}</h2>
                        <div class="flex flex-col md:flex-row justify-between items-baseline mt-4">
                            <div class="flex flex-wrap items-center gap-4 text-gray-600 mb-2 md:mb-0">
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-brand-blue"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" /></svg>
                                    <span class="font-medium">{{ package.duration_days }} {{ __('Days') }}</span>
                                </span>
                                <span v-if="package.pickup_time" class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-green-500"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" /></svg>
                                    <span class="font-medium">{{ __('Pickup') }}: {{ package.pickup_time }}</span>
                                </span>
                                <span v-if="package.category" class="flex items-center gap-1 px-2 py-0.5 bg-blue-50 rounded-full text-sm text-brand-blue font-medium">
                                    {{ package.category }}
                                </span>
                            </div>
                            
                            <div class="text-right">
                                <span v-if="package.discount_percent > 0" class="text-sm text-gray-400 line-through mr-2 font-medium">
                                    {{ $formatCurrency(package.price) }}
                                </span>
                                <span class="text-3xl text-brand-blue font-extrabold">
                                    {{ $formatCurrency(package.price * (1 - (package.discount_percent / 100))) }}
                                </span>
                                <span class="text-sm text-gray-500 font-medium"> / {{ __('person') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 space-y-8">
                    
                    <!-- Section 1: Trip Details -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-600 w-8 h-8 flex items-center justify-center rounded-full mr-3 text-sm">1</span>
                            {{ __('Trip Details') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-11">
                            <div>
                                <InputLabel for="departure_date" class="!text-gray-700 !font-semibold">
                                    {{ __('Departure Date') }} <span class="text-red-500">*</span>
                                </InputLabel>
                                <TextInput id="departure_date" type="date" :min="new Date().toISOString().split('T')[0]" 
                                    class="mt-1 block w-full bg-white border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-gray-900 rounded-lg shadow-sm" 
                                    v-model="form.departure_date" required />
                                <InputError class="mt-2" :message="form.errors.departure_date" />
                            </div>
                            
                            <!-- Participant Selector -->
                            <div class="relative">
                                <InputLabel for="participants" class="!text-gray-700 !font-semibold">
                                    {{ __('Number of Participants') }} <span class="text-red-500">*</span>
                                </InputLabel>
                                <div 
                                    id="participants"
                                    @click.stop="toggleParticipantDropdown"
                                    class="mt-1 flex items-center justify-between w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg shadow-sm cursor-pointer hover:border-brand-blue transition-colors text-gray-900"
                                >
                                    <span>{{ participantDisplay }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': showParticipantDropdown }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                                
                                <!-- Dropdown Panel -->
                                <transition 
                                    enter-active-class="transition ease-out duration-200" 
                                    enter-from-class="opacity-0 translate-y-1" 
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition ease-in duration-150" 
                                    leave-from-class="opacity-100 translate-y-0" 
                                    leave-to-class="opacity-0 translate-y-1"
                                >
                                    <div v-if="showParticipantDropdown" @click.stop class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl p-5 space-y-4">
                                        <!-- Adult -->
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ __('Adult') }}</p>
                                                <p class="text-xs text-gray-500">{{ __('Age 12+') }}</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button type="button" @click="decreaseAdult" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-30 transition-colors" :disabled="form.num_adults <= 1">−</button>
                                                <span class="font-bold text-gray-900 w-6 text-center">{{ form.num_adults }}</span>
                                                <button type="button" @click="increaseAdult" class="w-8 h-8 rounded-full border border-brand-blue text-brand-blue flex items-center justify-center hover:bg-blue-50 transition-colors">+</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Children (only if children friendly) -->
                                        <div v-if="package.is_children_friendly" class="flex items-center justify-between border-t border-gray-100 pt-4">
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ __('Children') }}</p>
                                                <p class="text-xs text-gray-500">{{ __('Age 2-11') }}</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button type="button" @click="decreaseChild" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-30 transition-colors" :disabled="form.num_children <= 0">−</button>
                                                <span class="font-bold text-gray-900 w-6 text-center">{{ form.num_children }}</span>
                                                <button type="button" @click="increaseChild" class="w-8 h-8 rounded-full border border-brand-blue text-brand-blue flex items-center justify-center hover:bg-blue-50 transition-colors">+</button>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right pt-2 border-t border-gray-100">
                                            <span class="text-sm text-gray-500">{{ __('Total') }}: </span>
                                            <span class="font-bold text-brand-blue">{{ totalParticipants }} {{ __('People') }}</span>
                                        </div>
                                    </div>
                                </transition>
                                
                                <InputError class="mt-2" :message="form.errors.num_adults" />
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Contact Information -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-600 w-8 h-8 flex items-center justify-center rounded-full mr-3 text-sm">2</span>
                            {{ __('Contact Information') }}
                        </h3>
                        
                        <div class="pl-11 space-y-6">
                            <p class="text-sm text-gray-500 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                ℹ️ {{ __("We will use this contact information to send booking confirmations and updates regarding your order.") }}
                            </p>
                            
                            <!-- Guest Name (if not logged in) -->
                            <div v-if="!$page.props.auth.user">
                                <InputLabel for="guest_name" class="!text-gray-700 !font-semibold">
                                    {{ __('Full Name') }} <span class="text-red-500">*</span>
                                </InputLabel>
                                <TextInput id="guest_name" type="text" 
                                    class="mt-1 block w-full bg-white border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-gray-900 rounded-lg shadow-sm" 
                                    v-model="form.guest_name" required />
                                <InputError class="mt-2" :message="form.errors.guest_name" />
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <InputLabel for="contact_email" class="!text-gray-700 !font-semibold">
                                    {{ __('Email') }} <span class="text-red-500">*</span>
                                </InputLabel>
                                <TextInput id="contact_email" type="email" 
                                    class="mt-1 block w-full bg-white border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-gray-900 rounded-lg shadow-sm" 
                                    v-model="form.contact_email" required />
                                <InputError class="mt-2" :message="form.errors.contact_email" />
                            </div>

                            <!-- Phone with Country Code -->
                            <div>
                                <InputLabel for="contact_phone" class="!text-gray-700 !font-semibold">
                                    {{ __('Phone Number') }} <span class="text-red-500">*</span>
                                </InputLabel>
                                <div class="mt-1 flex flex-col sm:flex-row gap-3 sm:gap-0">
                                    <div class="w-full sm:w-32">
                                        <SearchableSelect
                                            v-model="form.country_code"
                                            :options="countryCodeOptions"
                                            value-field="value"
                                            label-field="label"
                                            :search-placeholder="__('Search...')"
                                            class="sm:!rounded-r-none"
                                        >
                                            <template #option="{ option }">
                                                <div class="flex items-center gap-2">
                                                    <span>{{ option.flag }}</span>
                                                    <span>{{ option.value }}</span>
                                                    <span class="text-xs text-gray-400 truncate ml-auto">{{ option.name }}</span>
                                                </div>
                                            </template>
                                        </SearchableSelect>
                                    </div>
                                    <TextInput 
                                        id="contact_phone" 
                                        type="tel"
                                        class="w-full sm:flex-1 bg-white border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-gray-900 rounded-lg sm:rounded-r-lg shadow-sm sm:!rounded-l-none sm:border-l-0" 
                                        v-model="form.contact_phone" 
                                        required 
                                        @blur="validatePhone"
                                        @input="form.contact_phone = form.contact_phone.replace(/\D/g, '')"
                                    />
                                </div>
                                <p class="text-xs text-gray-400 mt-1">{{ __('Enter without country code') }}</p>
                                <InputError class="mt-1" :message="form.errors.contact_phone" />
                                <p v-if="phoneError" class="text-red-500 text-xs mt-1">{{ phoneError }}</p>
                            </div>

                            <!-- Country -->
                            <div>
                                <InputLabel for="country" class="!text-gray-700 !font-semibold">
                                    {{ __('Country') }} <span class="text-red-500">*</span>
                                </InputLabel>
                                <SearchableSelect
                                    v-model="form.country"
                                    :options="countryOptions"
                                    value-field="value"
                                    label-field="label"
                                    :placeholder="__('Select Country')"
                                    :search-placeholder="__('Search country...')"
                                    class="mt-1"
                                />
                                <InputError class="mt-2" :message="form.errors.country" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 3: Pickup Location -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-600 w-8 h-8 flex items-center justify-center rounded-full mr-3 text-sm">3</span>
                            {{ __('Pickup Location') }}
                        </h3>
                        <div class="pl-11">
                            <InputLabel for="pickup_location" class="!text-gray-700 !font-semibold">
                                {{ __('Pickup Location') }} <span class="text-red-500">*</span>
                            </InputLabel>
                            <TextInput id="pickup_location" type="text" 
                                class="mt-1 block w-full bg-white border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-gray-900 rounded-lg shadow-sm" 
                                v-model="form.pickup_location"
                                required />
                            <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ __("If unsure, enter '-' and TripTrove will assist you") }}
                            </p>
                            <InputError class="mt-2" :message="form.errors.pickup_location" />
                        </div>
                    </div>

                    <!-- Section 4: Special Requests -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-600 w-8 h-8 flex items-center justify-center rounded-full mr-3 text-sm">4</span>
                            {{ __('Special Requests') }} <span class="text-gray-400 text-sm font-normal ml-2">({{ __('Optional') }})</span>
                        </h3>
                        <div class="pl-11">
                            <textarea id="special_requests" 
                                class="mt-1 block w-full bg-white border-gray-300 rounded-lg shadow-sm focus:border-brand-blue focus:ring-brand-blue text-gray-900" 
                                v-model="form.special_requests" rows="3" 
                                ></textarea>
                            <InputError class="mt-2" :message="form.errors.special_requests" />
                        </div>
                    </div>

                    <!-- Price Summary -->
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-6 rounded-xl border border-blue-100">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">{{ participantDisplay }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $formatCurrency(package.price * (1 - (package.discount_percent / 100))) }} × {{ totalParticipants }} {{ __('People') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ __('Estimated Total') }}</p>
                                <p class="text-2xl font-extrabold text-brand-blue">
                                    {{ $formatCurrency(package.price * (1 - (package.discount_percent / 100)) * totalParticipants) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="mt-8 border-t border-gray-200 pt-8">
                        <PrimaryButton type="submit" class="w-full justify-center !py-4 !text-lg font-bold bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1" :class="{ 'opacity-70 cursor-not-allowed': form.processing }" :disabled="form.processing">
                            <span v-if="form.processing">{{ __('Processing...') }}</span>
                            <span v-else>{{ __('Submit Order') }}</span>
                        </PrimaryButton>
                        <p v-if="form.hasErrors" class="text-red-600 text-sm text-center mt-4 font-medium">
                            {{ __('There are errors in your submission. Please check again.') }}
                        </p>
                        <p v-if="form.errors.error" class="text-red-600 text-sm text-center mt-4 font-medium bg-red-50 p-3 rounded-lg">
                            {{ form.errors.error }}
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>