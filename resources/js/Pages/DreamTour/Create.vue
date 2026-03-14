<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, getCurrentInstance } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { countries as allCountries } from '@/Constants/countries';

// --- Helper Global ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;

const countryCodeOptions = allCountries.map(c => ({
    value: c.dial_code,
    label: `${c.flag} ${c.dial_code}`,
    name: c.name,
    flag: c.flag
}));

const form = useForm({
    name: app.appContext.config.globalProperties.$page.props.auth.user ? app.appContext.config.globalProperties.$page.props.auth.user.name : '',
    email: app.appContext.config.globalProperties.$page.props.auth.user ? app.appContext.config.globalProperties.$page.props.auth.user.email : '',
    phone: '',
    country_code: '+62',
    departure_date: '',
    num_adults: 1,
    num_children: 0,
    destinations: [''],
    additional_info: '',
});

const addDestination = () => {
    form.destinations.push('');
};

const removeDestination = (index) => {
    if (form.destinations.length > 1) {
        form.destinations.splice(index, 1);
    }
};

// Participant selector logic
const showParticipantDropdown = ref(false);
const participantDisplay = computed(() => {
    let text = `${__('Adult')} x ${form.num_adults}`;
    if (form.num_children > 0) {
        text += `, ${__('Children')} x ${form.num_children}`;
    }
    return text;
});

const increaseAdult = () => { form.num_adults++; };
const decreaseAdult = () => { if (form.num_adults > 1) form.num_adults--; };
const increaseChild = () => { form.num_children++; };
const decreaseChild = () => { if (form.num_children > 0) form.num_children--; };

const submit = () => {
    form.post(route('dream-tour.store'), {
        onSuccess: (page) => {
            if (page.props.flash.whatsapp_url) {
                window.location.href = page.props.flash.whatsapp_url;
            }
        },
    });
};
</script>

<template>
    <Head :title="__('Request Your Dream Tour')" />
    <MainLayout>
        <div class="min-h-screen bg-gray-50 py-12 px-6" @click="showParticipantDropdown = false">
            <div class="container mx-auto max-w-4xl">
                <div class="text-center mb-10">
                    <h1 class="text-4xl font-extrabold mb-4 bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text">
                        {{ __('Request Your Dream Tour') }}
                    </h1>
                    <p class="text-gray-600 max-w-2xl mx-auto">
                        {{ __('Tell us where you want to go, and we will create a personalized itinerary just for you.') }}
                    </p>
                </div>

                <form @submit.prevent="submit" class="bg-white p-6 md:p-10 rounded-3xl shadow-xl border border-gray-100 space-y-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-blue-50/50 rounded-full -mr-20 -mt-20 z-0"></div>
                    
                    <div class="relative z-10 space-y-8">
                        <!-- Section 1: Contact Info -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                <span class="bg-brand-blue text-white w-8 h-8 flex items-center justify-center rounded-lg mr-3 text-sm shadow-md">1</span>
                                {{ __('Your Contact Information') }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="name" :value="__('Full Name')" class="!font-semibold" />
                                    <TextInput id="name" type="text" v-model="form.name" class="mt-1 block w-full" required />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>
                                <div>
                                    <InputLabel for="email" :value="__('Email Address')" class="!font-semibold" />
                                    <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" required />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>
                                <div class="md:col-span-2">
                                    <InputLabel for="phone" :value="__('Phone Number')" class="!font-semibold" />
                                    <div class="mt-1 flex flex-col sm:flex-row gap-3 sm:gap-0">
                                        <div class="w-full sm:w-32">
                                            <SearchableSelect
                                                v-model="form.country_code"
                                                :options="countryCodeOptions"
                                                value-field="value"
                                                label-field="label"
                                                class="sm:!rounded-r-none"
                                            />
                                        </div>
                                        <TextInput 
                                            id="phone" 
                                            type="tel" 
                                            v-model="form.phone" 
                                            class="w-full sm:flex-1 sm:!rounded-l-none sm:border-l-0" 
                                            @input="form.phone = form.phone.replace(/\D/g, '')"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.phone" />
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Destinations -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                <span class="bg-brand-blue text-white w-8 h-8 flex items-center justify-center rounded-lg mr-3 text-sm shadow-md">2</span>
                                {{ __('Where do you want to go?') }}
                            </h3>
                            <div class="space-y-4">
                                <div v-for="(dest, index) in form.destinations" :key="index" class="flex gap-2">
                                    <div class="flex-grow">
                                        <TextInput 
                                            v-model="form.destinations[index]" 
                                            :placeholder="__('Enter destination (e.g. Borobudur Temple, Uluwatu)')" 
                                            class="w-full"
                                            required
                                        />
                                    </div>
                                    <button 
                                        v-if="form.destinations.length > 1"
                                        type="button" 
                                        @click="removeDestination(index)"
                                        class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                                <button 
                                    type="button" 
                                    @click="addDestination"
                                    class="flex items-center gap-2 text-brand-blue font-bold hover:text-brand-cyan transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    {{ __('Add Destination') }}
                                </button>
                                <InputError class="mt-2" :message="form.errors.destinations" />
                            </div>
                        </div>

                        <!-- Section 3: Participants & Date -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                    <span class="bg-brand-blue text-white w-8 h-8 flex items-center justify-center rounded-lg mr-3 text-sm shadow-md">3</span>
                                    {{ __('Departure Date') }}
                                </h3>
                                <TextInput 
                                    type="date" 
                                    v-model="form.departure_date" 
                                    :min="new Date().toISOString().split('T')[0]"
                                    class="w-full" 
                                />
                                <InputError class="mt-2" :message="form.errors.departure_date" />
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                    <span class="bg-brand-blue text-white w-8 h-8 flex items-center justify-center rounded-lg mr-3 text-sm shadow-md">4</span>
                                    {{ __('Participants') }}
                                </h3>
                                <div class="relative">
                                    <div 
                                        @click.stop="showParticipantDropdown = !showParticipantDropdown"
                                        class="flex items-center justify-between w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg shadow-sm cursor-pointer hover:border-brand-blue transition-colors"
                                    >
                                        <span class="text-gray-900 font-medium">{{ participantDisplay }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" :class="{ 'rotate-180': showParticipantDropdown }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                    <transition 
                                        enter-active-class="transition ease-out duration-200" 
                                        enter-from-class="opacity-0 translate-y-1" 
                                        enter-to-class="opacity-100 translate-y-0"
                                        leave-active-class="transition ease-in duration-150" 
                                        leave-from-class="opacity-100 translate-y-0" 
                                        leave-to-class="opacity-0 translate-y-1"
                                    >
                                        <div v-if="showParticipantDropdown" @click.stop class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl p-5 space-y-4">
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-gray-900">{{ __('Adult') }}</span>
                                                <div class="flex items-center gap-3">
                                                    <button type="button" @click="decreaseAdult" class="w-8 h-8 rounded-full border border-gray-300 text-gray-700 flex items-center justify-center disabled:opacity-30 hover:bg-gray-50" :disabled="form.num_adults <= 1">−</button>
                                                    <span class="font-bold w-6 text-center text-gray-900">{{ form.num_adults }}</span>
                                                    <button type="button" @click="increaseAdult" class="w-8 h-8 rounded-full border border-brand-blue text-brand-blue flex items-center justify-center hover:bg-blue-50">+</button>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                                                <span class="font-semibold text-gray-900">{{ __('Children') }}</span>
                                                <div class="flex items-center gap-3">
                                                    <button type="button" @click="decreaseChild" class="w-8 h-8 rounded-full border border-gray-300 text-gray-700 flex items-center justify-center disabled:opacity-30 hover:bg-gray-50" :disabled="form.num_children <= 0">−</button>
                                                    <span class="font-bold w-6 text-center text-gray-900">{{ form.num_children }}</span>
                                                    <button type="button" @click="increaseChild" class="w-8 h-8 rounded-full border border-brand-blue text-brand-blue flex items-center justify-center hover:bg-blue-50">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Additional Info -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                <span class="bg-brand-blue text-white w-8 h-8 flex items-center justify-center rounded-lg mr-3 text-sm shadow-md">5</span>
                                {{ __('Additional Information') }}
                            </h3>
                            <textarea 
                                v-model="form.additional_info" 
                                class="w-full bg-white border border-gray-300 rounded-xl shadow-sm focus:ring-brand-blue focus:border-brand-blue p-4" 
                                rows="4"
                                :placeholder="__('Any special needs? (e.g. food preferences, hotel class, specific interests)')"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.additional_info" />
                        </div>

                        <div class="pt-6">
                            <PrimaryButton 
                                type="submit" 
                                class="w-full justify-center !py-4 !text-lg font-bold bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan shadow-lg shadow-blue-200" 
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">{{ __('Processing...') }}</span>
                                <span v-else>{{ __('Submit Request') }}</span>
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>
