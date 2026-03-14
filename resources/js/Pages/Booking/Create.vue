<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
// Helper untuk terjemahan
import { getCurrentInstance } from 'vue';

const props = defineProps({
    package: Object
});

// --- Helper Global ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;
const $formatCurrency = (value) => app.appContext.config.globalProperties.$formatCurrency(value);
// ---

// =======================================================
// LOGIKA FORM
// =======================================================
const form = useForm({
    package_id: props.package.id,
    departure_date: '',
    num_participants: 1, 
    contact_email: app.appContext.config.globalProperties.$page.props.auth.user.email || '', 
    contact_phone: app.appContext.config.globalProperties.$page.props.auth.user.phone_number || '', 
    special_requests: '',
});

const submit = () => {
    form.post(route('booking.store'), {
         onError: (errors) => console.error("Booking Errors:", errors)
    });
};
</script>

<template>
    <Head :title="`${__('Booking')} ${package.name}`" />
    <MainLayout>
        <div class="min-h-screen bg-gray-50 py-12 px-6">
            <div class="container mx-auto max-w-4xl">
                
                <h1 class="text-4xl font-extrabold mb-8 bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text border-b border-gray-200 pb-4">
                    {{ __('Booking Form') }}
                </h1>

                <div class="bg-white p-8 rounded-2xl mb-8 shadow-lg border border-gray-100 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 z-0"></div>
                    
                    <div class="relative z-10">
                        <h2 class="text-2xl font-bold text-gray-900">{{ package.name }}</h2>
                        <div class="flex flex-col md:flex-row justify-between items-baseline mt-4">
                            <div class="flex items-center text-gray-600 mb-2 md:mb-0">
                                <span class="mr-2 text-xl">⏳</span> 
                                <span class="font-medium">{{ package.duration_days }} {{ __('Days') }}</span>
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

                <form @submit.prevent="submit" class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-8">
                    
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-600 w-8 h-8 flex items-center justify-center rounded-full mr-3 text-sm">1</span>
                            {{ __('Trip Details') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-11">
                            <div>
                                <InputLabel for="departure_date" :value="__('Departure Date')" class="!text-gray-700 !font-semibold" />
                                <TextInput id="departure_date" type="date" :min="new Date().toISOString().split('T')[0]" 
                                    class="mt-1 block w-full bg-white border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-gray-900 rounded-lg shadow-sm" 
                                    v-model="form.departure_date" required />
                                <InputError class="mt-2" :message="form.errors.departure_date" />
                            </div>
                            <div>
                                <InputLabel for="num_participants" :value="__('Number of People')" class="!text-gray-700 !font-semibold" />
                                <TextInput id="num_participants" type="number" min="1" 
                                    class="mt-1 block w-full bg-white border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-gray-900 rounded-lg shadow-sm" 
                                    v-model.number="form.num_participants" required />
                                <InputError class="mt-2" :message="form.errors.num_participants" />
                            </div>
                        </div>
                    </div>

                    <div>
                         <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-600 w-8 h-8 flex items-center justify-center rounded-full mr-3 text-sm">2</span>
                            {{ __('Contact Information (Can choose one)') }}
                        </h3>
                        
                        <div class="pl-11">
                            <p class="text-sm text-gray-500 mb-6 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                ℹ️ {{ __("We will use this contact information to send booking confirmations and updates regarding your order.") }}
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="contact_email" :value="__('Email')" class="!text-gray-700 !font-semibold" />
                                    <TextInput id="contact_email" type="email" 
                                        class="mt-1 block w-full bg-white border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-gray-900 rounded-lg shadow-sm" 
                                        v-model="form.contact_email" required />
                                    <InputError class="mt-2" :message="form.errors.contact_email" />
                                </div>
                                <div>
                                    <InputLabel for="contact_phone" :value="__('WhatsApp Number')" class="!text-gray-700 !font-semibold" />
                                    <TextInput id="contact_phone" type="tel" 
                                        class="mt-1 block w-full bg-white border-gray-300 focus:border-brand-blue focus:ring-brand-blue text-gray-900 rounded-lg shadow-sm" 
                                        v-model="form.contact_phone" required placeholder="628123456789" />
                                    <InputError class="mt-2" :message="form.errors.contact_phone" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-600 w-8 h-8 flex items-center justify-center rounded-full mr-3 text-sm">3</span>
                            {{ __('Special Requests (Optional)') }}
                        </h3>
                        <div class="pl-11">
                            <textarea id="special_requests" 
                                class="mt-1 block w-full bg-white border-gray-300 rounded-lg shadow-sm focus:border-brand-blue focus:ring-brand-blue text-gray-900" 
                                v-model="form.special_requests" rows="3" 
                                :placeholder="__('Example: Halal food preferences, non-smoking room, etc.')"></textarea>
                            <InputError class="mt-2" :message="form.errors.special_requests" />
                        </div>
                    </div>

                    <div class="mt-8 border-t border-gray-200 pt-8">
                        <PrimaryButton type="submit" class="w-full justify-center !py-4 !text-lg font-bold bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1" :class="{ 'opacity-70 cursor-not-allowed': form.processing }" :disabled="form.processing">
                            <span v-if="form.processing">{{ __('Processing...') }}</span>
                            <span v-else>{{ __('Submit Order') }}</span>
                        </PrimaryButton>
                        <p v-if="form.hasErrors" class="text-red-600 text-sm text-center mt-4 font-medium">
                            {{ __('There are errors in your submission. Please check again.') }}
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
/* Definisi warna gradient jika belum ada di global CSS */
.from-brand-cyan { --tw-gradient-from: #00d4ff; }
.to-brand-blue { --tw-gradient-to: #007bff; }
.text-brand-blue { color: #007bff; }
.bg-brand-blue { background-color: #007bff; }
</style>