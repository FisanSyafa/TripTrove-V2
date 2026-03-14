<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, getCurrentInstance } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    booking: Object
});

// --- Helper Global ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;

const form = useForm({
    booking_id: props.booking.id,
    rating: 0,
    comment: '',
});

const ratingHover = ref(0);

const submit = () => {
    form.post(route('reviews.store'));
};
</script>

<template>
    <Head :title="__('Write Review')" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">{{ __('Write Review') }}</h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                    <form @submit.prevent="submit" class="p-8 space-y-8">
                        
                        <div class="text-center border-b border-gray-100 pb-6">
                            <h3 class="text-2xl font-extrabold bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text mb-2">
                                {{ booking.tour_package.name }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ __('Your trip on') }}: <span class="font-medium text-gray-700">{{ booking.departure_date }}</span>
                            </p>
                        </div>

                        <div>
                            <InputLabel :value="__('Rate Your Experience')" class="!text-gray-700 !font-bold !text-lg mb-2 text-center block" />
                            <div class="flex justify-center items-center space-x-2 mt-2">
                                <svg v-for="n in 5" :key="n"
                                     @click="form.rating = n"
                                     @mouseenter="ratingHover = n"
                                     @mouseleave="ratingHover = 0"
                                     class="w-10 h-10 cursor-pointer transition-transform hover:scale-110 duration-200"
                                     :class="(n <= form.rating || n <= ratingHover) ? 'text-yellow-400 drop-shadow-sm' : 'text-gray-300'" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19 10 15.27z"/>
                                </svg>
                            </div>
                            <InputError class="mt-2 text-center" :message="form.errors.rating" />
                        </div>

                        <div>
                            <InputLabel for="comment" :value="__('Share Your Experience')" class="!text-gray-700 !font-semibold mb-2" />
                            <textarea id="comment"
                                      class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-xl shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-gray-900 placeholder-gray-400 p-4 transition-colors"
                                      v-model="form.comment" rows="5"
                                      :placeholder="__('Tell us the most memorable thing...')"></textarea>
                            <InputError class="mt-2" :message="form.errors.comment" />
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                            <PrimaryButton 
                                :disabled="form.processing" 
                                class="w-full justify-center !py-3 !text-lg font-bold bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan shadow-lg transform hover:-translate-y-1 transition-all"
                                :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                            >
                                <span v-if="form.processing">{{ __('Submitting...') }}</span>
                                <span v-else>{{ __('Submit Review') }}</span>
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Definisi warna gradient jika belum ada di global CSS */
.from-brand-cyan { --tw-gradient-from: #00d4ff; }
.to-brand-blue { --tw-gradient-to: #007bff; }
.text-brand-blue { color: #007bff; }
.text-brand-cyan { color: #00d4ff; }
</style>