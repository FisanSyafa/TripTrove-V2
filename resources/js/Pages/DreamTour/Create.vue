<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, getCurrentInstance } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import Modal from '@/Components/Modal.vue';
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
    destinations: [''],
    additional_info: '',
    attachments: [],
});

const fileError = ref('');
const handleFileChange = (e) => {
    fileError.value = '';
    const files = Array.from(e.target.files);
    let totalSize = 0;
    files.forEach(file => {
        totalSize += file.size;
    });

    if (totalSize > 3 * 1024 * 1024) {
        fileError.value = __('Total file size exceeds 3MB.');
        e.target.value = ''; // reset
        form.attachments = [];
    } else {
        form.attachments = files;
    }
};

const getFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

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

const showSuccessPopup = ref(false);
const whatsappUrl = ref('');

const submit = () => {
    form.post(route('dream-tour.store'), {
        onSuccess: (page) => {
            whatsappUrl.value = page.props.flash.whatsapp_url;
            showSuccessPopup.value = true;
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
                                    <InputLabel for="name" class="!font-semibold">
                                        {{ __('Full Name') }} <span class="text-red-500">*</span>
                                    </InputLabel>
                                    <TextInput id="name" type="text" v-model="form.name" class="mt-1 block w-full" required />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>
                                <div>
                                    <InputLabel for="email" class="!font-semibold">
                                        {{ __('Email Address') }} <span class="text-red-500">*</span>
                                    </InputLabel>
                                    <TextInput id="email" type="email" v-model="form.email" class="mt-1 block w-full" required />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>
                                <div class="md:col-span-2">
                                    <InputLabel for="phone" class="!font-semibold">
                                        {{ __('Phone Number') }} <span class="text-red-500">*</span>
                                    </InputLabel>
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
                            <h3 class="text-xl font-bold text-gray-900 mb-2 flex items-center">
                                <span class="bg-brand-blue text-white w-8 h-8 flex items-center justify-center rounded-lg mr-3 text-sm shadow-md">2</span>
                                {{ __('Where do you want to go?') }} <span class="text-red-500 ml-1.5">*</span>
                            </h3>
                            <p class="text-sm text-gray-500 mb-6 font-medium">
                                {{ __('You can either enter your destinations manually below, or upload your list destination in the next section.') }}
                            </p>
                            <div class="space-y-4">
                                <div v-for="(dest, index) in form.destinations" :key="index" class="flex gap-2">
                                    <div class="flex-grow">
                                        <TextInput 
                                            v-model="form.destinations[index]" 
                                            :placeholder="__('Enter destination (e.g. Borobudur Temple, Uluwatu)')" 
                                            class="w-full"
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

                        <!-- Section 3: Document Upload -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                <span class="bg-brand-blue text-white w-8 h-8 flex items-center justify-center rounded-lg mr-3 text-sm shadow-md">3</span>
                                {{ __('Upload Your List Itinerary / Destinations') }} <span class="text-red-500 ml-1.5">*</span>
                            </h3>
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 hover:bg-gray-50 transition-colors">
                                <input 
                                    type="file" 
                                    multiple 
                                    accept=".jpg,.jpeg,.png,.heic,.heif,.pdf,.xls,.xlsx"
                                    @change="handleFileChange"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-blue file:text-white hover:file:bg-brand-cyan transition-colors"
                                />
                                <p class="text-xs text-gray-400 mt-2">{{ __('Supported formats: .jpg, .jpeg, .png, .heic, .heif, .pdf, .xls, .xlsx. Max total size: 3MB.') }}</p>
                                
                                <InputError class="mt-2" :message="fileError || form.errors.attachments" />
                                
                                <div v-if="form.attachments.length > 0" class="mt-4 space-y-2">
                                    <h4 class="text-sm font-semibold text-gray-700">{{ __('Selected Files:') }}</h4>
                                    <ul class="text-sm text-gray-600 space-y-1 bg-gray-100 p-3 rounded-lg overflow-hidden">
                                        <li v-for="(file, index) in form.attachments" :key="index" class="flex justify-between items-center">
                                            <span class="truncate pr-4 flex-1" :title="file.name">📄 {{ file.name }}</span>
                                            <span class="text-gray-400 text-xs shrink-0 font-medium">{{ getFileSize(file.size) }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Participants & Date -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                    <span class="bg-brand-blue text-white w-8 h-8 flex items-center justify-center rounded-lg mr-3 text-sm shadow-md">4</span>
                                    {{ __('Departure Date') }} <span class="text-red-500 ml-1.5">*</span>
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
                                    <span class="bg-brand-blue text-white w-8 h-8 flex items-center justify-center rounded-lg mr-3 text-sm shadow-md">5</span>
                                    {{ __('Participants') }} <span class="text-red-500 ml-1.5">*</span>
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
                                                <div>
                                                    <span class="font-semibold text-gray-900">{{ __('Adult') }}</span>
                                                    <p class="text-xs text-gray-500">{{ __('Age 4+') }}</p>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <button type="button" @click="decreaseAdult" class="w-8 h-8 rounded-full border border-gray-300 text-gray-700 flex items-center justify-center disabled:opacity-30 hover:bg-gray-50" :disabled="form.num_adults <= 1">−</button>
                                                    <span class="font-bold w-6 text-center text-gray-900">{{ form.num_adults }}</span>
                                                    <button type="button" @click="increaseAdult" class="w-8 h-8 rounded-full border border-brand-blue text-brand-blue flex items-center justify-center hover:bg-blue-50">+</button>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                                                <div>
                                                    <span class="font-semibold text-gray-900">{{ __('Children') }}</span>
                                                    <p class="text-xs text-gray-500">{{ __('Age 0-3') }}</p>
                                                </div>
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

                        <!-- Section 6: Additional Info -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                <span class="bg-brand-blue text-white w-8 h-8 flex items-center justify-center rounded-lg mr-3 text-sm shadow-md">6</span>
                                {{ __('Additional Information') }}
                            </h3>
                            <textarea 
                                v-model="form.additional_info" 
                                class="w-full bg-white border border-gray-300 rounded-xl shadow-sm focus:ring-brand-blue focus:border-brand-blue p-4" 
                                rows="4"
                                :placeholder="__('Any special needs?')"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.additional_info" />
                        </div>

                        <div class="pt-6">
                            <PrimaryButton 
                                type="submit" 
                                class="w-full justify-center !py-4 !text-lg font-bold bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan shadow-lg shadow-blue-200" 
                                :disabled="form.processing || !!fileError"
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

    <!-- Success Modal -->
    <Modal :show="showSuccessPopup" @close="showSuccessPopup = false">
        <div class="p-8 text-center bg-white rounded-3xl overflow-hidden relative">
            <!-- Decorative circle -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-green-50 rounded-full z-0"></div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <h2 class="text-3xl font-extrabold text-gray-900 mb-4">{{ __('Request Submitted') }}</h2>
                <p class="text-gray-600 mb-8 max-w-sm mx-auto leading-relaxed">
                    {{ __('Thank you for your interest! Our team will contact you shortly to discuss your dream tour request.') }}
                </p>
                
                <div class="flex flex-col gap-3">
                    <a 
                        v-if="whatsappUrl"
                        :href="whatsappUrl" 
                        target="_blank"
                        class="w-full inline-flex justify-center items-center gap-2 px-6 py-4 bg-[#25D366] hover:bg-[#128C7E] text-white font-bold rounded-xl transition-all shadow-lg shadow-green-100 text-lg"
                    >
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.937 3.659 1.431 5.63 1.432h.006c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        {{ __('Chat via WhatsApp') }}
                    </a>
                    <Link 
                        :href="route('home')" 
                        class="w-full inline-flex justify-center px-6 py-4 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold rounded-xl transition-all border border-gray-200"
                    >
                        {{ __('Back to Home') }}
                    </Link>
                </div>
            </div>
        </div>
    </Modal>
</template>
