<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    settings: Object
});

const form = useForm({
    small_car_price: props.settings.small_car_price,
    large_car_price: props.settings.large_car_price,
});

const submit = () => {
    form.post(route('admin.trip-settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handling if needed
        }
    });
};
</script>

<template>
    <Head title="Trip Settings" />
    <AdminLayout>
        <h1 class="text-3xl font-bold mb-6 text-brand-cyan">Trip Settings</h1>

        <div v-if="$page.props.flash.message" class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400">
            {{ $page.props.flash.message }}
        </div>

        <div class="max-w-3xl bg-[#111c2e] p-8 rounded-xl shadow-lg border border-gray-700/40">
            <h2 class="text-xl font-semibold mb-6 text-white border-b border-gray-700 pb-2">Car Pricing (Transportation Charge)</h2>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="small_car_price" value="Small Car Price (Max 4 Passengers)" class="!text-brand-cyan !font-semibold" />
                        <div class="mt-1 flex items-center">
                            <span class="bg-[#0c1222] border border-r-0 border-gray-600/50 px-3 py-2 rounded-l-md text-gray-400">Rp</span>
                            <TextInput 
                                id="small_car_price" 
                                type="number" 
                                class="block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-r-md shadow-sm" 
                                v-model="form.small_car_price" 
                                required 
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 italic text-white flex items-center mb-1">
                            {{ __('This price will be added to bookings with 1-4 participants.') }}
                        </p>
                        <InputError class="mt-2" :message="form.errors.small_car_price" />
                    </div>

                    <div>
                        <InputLabel for="large_car_price" value="Large Car Price (5+ Passengers)" class="!text-brand-cyan !font-semibold" />
                        <div class="mt-1 flex items-center">
                            <span class="bg-[#0c1222] border border-r-0 border-gray-600/50 px-3 py-2 rounded-l-md text-gray-400">Rp</span>
                            <TextInput 
                                id="large_car_price" 
                                type="number" 
                                class="block w-full bg-[#0c1222] border-gray-600/50 focus:border-brand-cyan focus:ring-brand-cyan rounded-r-md shadow-sm" 
                                v-model="form.large_car_price" 
                                required 
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 italic text-white flex items-center mb-1">
                            {{ __('This price will be added to bookings with 5 or more participants.') }}
                        </p>
                        <InputError class="mt-2" :message="form.errors.large_car_price" />
                    </div>
                </div>

                <div class="flex items-center justify-end mt-8 border-t border-gray-700 pt-6">
                    <PrimaryButton 
                        class="bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan transition-all" 
                        :disabled="form.processing"
                    >
                        Save Settings
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
