<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, getCurrentInstance, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    package: Object
});

// --- API Helpers ---
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;
const page = usePage();

const user = computed(() => page.props.auth.user);

const form = useForm({
    rating: 0,
    comment: '',
    guest_name: user.value ? user.value.name : '',
    guest_country: ''
});

const showCountryDropdown = ref(false);
const allCountries = [
  "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria",
  "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia",
  "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Côte d'Ivoire", "Cabo Verde",
  "Cambodia", "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo (Congo-Brazzaville)",
  "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czechia (Czech Republic)", "Democratic Republic of the Congo", "Denmark", "Djibouti",
  "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini (fmr. Swaziland)",
  "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea",
  "Guinea-Bissau", "Guyana", "Haiti", "Holy See", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland",
  "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon",
  "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali",
  "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro",
  "Morocco", "Mozambique", "Myanmar (formerly Burma)", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua",
  "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau", "Palestine State", "Panama",
  "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda",
  "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe",
  "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia",
  "South Africa", "South Korea", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria",
  "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan",
  "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America", "Uruguay", "Uzbekistan",
  "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"
];

const filteredCountries = computed(() => {
    if (!form.guest_country) return allCountries;
    return allCountries.filter(c => c.toLowerCase().includes(form.guest_country.toLowerCase()));
});

const selectCountry = (country) => {
    form.guest_country = country;
    showCountryDropdown.value = false;
};

const ratingHover = ref(0);

const submit = () => {
    form.post(route('packages.review.store', props.package.slug));
};
</script>

<template>
    <Head :title="__('Write Review')" />
    <MainLayout>
        <div class="py-12 bg-gray-50 min-h-screen pt-24">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                    <form @submit.prevent="submit" class="p-8 space-y-8">
                        
                        <div class="text-center border-b border-gray-100 pb-6">
                            <h3 class="text-2xl font-extrabold bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text mb-2">
                                {{ package.name }}
                            </h3>
                            <p class="text-sm text-gray-500 max-w-md mx-auto">
                                {{ package.destination_summary }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="guest_name" :value="__('Your Name')" class="!text-gray-700 !font-semibold mb-2" />
                                <input id="guest_name" type="text" v-model="form.guest_name"
                                    class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-xl shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-gray-900 placeholder-gray-400 p-3 transition-colors text-sm"
                                    :placeholder="__('Enter your full name')" required />
                                <InputError class="mt-2" :message="form.errors.guest_name" />
                            </div>

                            <div class="relative">
                                <InputLabel for="guest_country" :value="__('Country')" class="!text-gray-700 !font-semibold mb-2" />
                                <input id="guest_country" type="text" v-model="form.guest_country"
                                    @focus="showCountryDropdown = true"
                                    @blur="setTimeout(() => showCountryDropdown = false, 200)"
                                    class="mt-1 block w-full bg-gray-50 border-gray-300 rounded-xl shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-gray-900 placeholder-gray-400 p-3 transition-colors text-sm"
                                    :placeholder="__('Enter your country')" required autocomplete="off" />
                                <InputError class="mt-2" :message="form.errors.guest_country" />

                                <div v-if="showCountryDropdown && filteredCountries.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-xl shadow-xl max-h-60 overflow-y-auto top-[100%]">
                                    <ul tabindex="-1" role="listbox" class="py-1">
                                        <li v-for="country in filteredCountries" :key="country" 
                                            @click="selectCountry(country)" 
                                            class="text-gray-900 cursor-pointer select-none relative py-2 pl-4 pr-9 hover:bg-brand-cyan hover:text-white transition-colors duration-150 text-sm">
                                            <span class="block truncate">{{ country }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
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
                                class="w-full justify-center !py-3 !text-lg font-bold bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan shadow-lg transform hover:-translate-y-1 transition-all text-white"
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
    </MainLayout>
</template>

<style scoped>
.from-brand-cyan { --tw-gradient-from: #00d4ff; }
.to-brand-blue { --tw-gradient-to: #007bff; }
.text-brand-blue { color: #007bff; }
.text-brand-cyan { color: #00d4ff; }
</style>
