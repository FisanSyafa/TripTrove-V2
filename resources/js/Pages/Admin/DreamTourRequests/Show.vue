<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    request: Object,
});

const statusForm = useForm({
    status: props.request.status,
    admin_price: props.request.admin_price || '',
    price_currency: props.request.price_currency || 'IDR',
});

const notifyForm = useForm({
    message: '',
    subject: '',
});

// Template state
const selectedTemplate = ref('');
const selectedLanguage = ref('en');

const updateStatus = () => {
    statusForm.put(route('admin.dream-tour-requests.update', props.request.id), {
        preserveScroll: true,
    });
};

// Price formatting helper
const formatPrice = (amount, currency) => {
    if (!amount) return '-';
    const locales = { IDR: 'id-ID', USD: 'en-US', MYR: 'ms-MY' };
    return new Intl.NumberFormat(locales[currency] || 'en-US', { 
        style: 'currency', 
        currency: currency || 'IDR',
        minimumFractionDigits: 0 
    }).format(amount);
};

// Formatted price in selected language's currency
const formattedPrice = computed(() => {
    const currencyMap = { en: 'USD', id: 'IDR', ms: 'MYR' };
    const currency = currencyMap[selectedLanguage.value] || statusForm.price_currency;
    return formatPrice(statusForm.admin_price, currency);
});

// Destinations text
const destinationsText = computed(() => {
    if (!props.request.destinations) return '-';
    return props.request.destinations.join(', ');
});

// Formatted departure date 
const formattedDepartureDate = computed(() => {
    if (!props.request.departure_date) return '-';
    const date = new Date(props.request.departure_date);
    const locales = { en: 'en-US', id: 'id-ID', ms: 'ms-MY' };
    return date.toLocaleDateString(locales[selectedLanguage.value] || 'en-US', {
        day: 'numeric', month: 'long', year: 'numeric'
    });
});

// Template messages
const templates = {
    completed: {
        en: () => `Hello ${props.request.name},\n\nGreat news! Your dream tour request to *${destinationsText.value}* has been confirmed.\n\nHere are the details:\n📅 Departure Date: ${formattedDepartureDate.value}\n👥 Participants: ${props.request.num_adults} adults${props.request.num_children > 0 ? `, ${props.request.num_children} children` : ''}\n🏝️ Destinations: ${destinationsText.value}\n💰 Total Price: ${formattedPrice.value}\n\nPlease proceed with the payment to confirm your booking.\n\nThank you for choosing TripTrove!\nTripTrove Team`,
        id: () => `Halo ${props.request.name},\n\nKabar baik! Permintaan tur impian Anda ke *${destinationsText.value}* telah dikonfirmasi.\n\nBerikut detailnya:\n📅 Tanggal Keberangkatan: ${formattedDepartureDate.value}\n👥 Peserta: ${props.request.num_adults} dewasa${props.request.num_children > 0 ? `, ${props.request.num_children} anak-anak` : ''}\n🏝️ Destinasi: ${destinationsText.value}\n💰 Total Harga: ${formattedPrice.value}\n\nSilakan lanjutkan pembayaran untuk mengonfirmasi pemesanan Anda.\n\nTerima kasih telah memilih TripTrove!\nTim TripTrove`,
        ms: () => `Hai ${props.request.name},\n\nBerita baik! Permintaan pelancongan impian anda ke *${destinationsText.value}* telah disahkan.\n\nBerikut butirannya:\n📅 Tarikh Berlepas: ${formattedDepartureDate.value}\n👥 Peserta: ${props.request.num_adults} dewasa${props.request.num_children > 0 ? `, ${props.request.num_children} kanak-kanak` : ''}\n🏝️ Destinasi: ${destinationsText.value}\n💰 Jumlah Harga: ${formattedPrice.value}\n\nSila teruskan pembayaran untuk mengesahkan tempahan anda.\n\nTerima kasih kerana memilih TripTrove!\nPasukan TripTrove`,
    },
    cancelled: {
        en: () => `Hello ${props.request.name},\n\nThank you for your interest in a custom tour to *${destinationsText.value}*.\n\nUnfortunately, we are unable to accommodate your request at this time. This may be due to limited availability or logistical constraints for the requested dates and destinations.\n\nWould you like to explore alternative dates or destinations? We'd be happy to help you plan another trip.\n\nPlease feel free to contact us for further assistance.\n\nThank you for your understanding,\nTripTrove Team`,
        id: () => `Halo ${props.request.name},\n\nTerima kasih atas minat Anda pada tur kustom ke *${destinationsText.value}*.\n\nSayangnya, kami belum dapat mengakomodasi permintaan Anda saat ini. Hal ini mungkin disebabkan oleh ketersediaan terbatas atau kendala logistik untuk tanggal dan destinasi yang diminta.\n\nApakah Anda ingin menjelajahi tanggal atau destinasi alternatif? Kami dengan senang hati akan membantu Anda merencanakan perjalanan lainnya.\n\nSilakan hubungi kami untuk bantuan lebih lanjut.\n\nTerima kasih atas pengertiannya,\nTim TripTrove`,
        ms: () => `Hai ${props.request.name},\n\nTerima kasih atas minat anda terhadap pelancongan khas ke *${destinationsText.value}*.\n\nMalangnya, kami tidak dapat menampung permintaan anda pada masa ini. Ini mungkin disebabkan oleh ketersediaan terhad atau kekangan logistik untuk tarikh dan destinasi yang diminta.\n\nAdakah anda ingin meneroka tarikh atau destinasi alternatif? Kami dengan senang hati akan membantu anda merancang perjalanan lain.\n\nSila hubungi kami untuk bantuan lanjut.\n\nTerima kasih atas pemahaman anda,\nPasukan TripTrove`,
    },
};

// Subject lines
const subjects = {
    completed: {
        en: 'Your Dream Tour Request Has Been Confirmed! - TripTrove',
        id: 'Permintaan Tur Impian Anda Telah Dikonfirmasi! - TripTrove',
        ms: 'Permintaan Pelancongan Impian Anda Telah Disahkan! - TripTrove',
    },
    cancelled: {
        en: 'Update on Your Dream Tour Request - TripTrove',
        id: 'Pembaruan Permintaan Tur Impian Anda - TripTrove',
        ms: 'Kemas Kini Permintaan Pelancongan Impian Anda - TripTrove',
    },
};

// Watch template/language changes
watch([selectedTemplate, selectedLanguage], ([tmpl, lang]) => {
    if (tmpl && templates[tmpl] && templates[tmpl][lang]) {
        notifyForm.message = templates[tmpl][lang]();
        notifyForm.subject = subjects[tmpl]?.[lang] || 'Dream Tour Request Update - TripTrove';
    }
});

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
        contacted: 'bg-blue-500/20 text-blue-400 border-blue-500/30',
        completed: 'bg-green-500/20 text-green-400 border-green-500/30',
        cancelled: 'bg-red-500/20 text-red-400 border-red-500/30',
    };
    return colors[status] || colors.pending;
};

const formatDate = (date) => {
    if (!date) return __('Not specified');
    return new Date(date).toLocaleDateString('id-ID', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

const sendEmailNotification = () => {
    notifyForm.post(route('admin.dream-tour-requests.notify-email', props.request.id), {
        preserveScroll: true,
        onSuccess: () => {
            notifyForm.reset('message', 'subject');
            selectedTemplate.value = '';
        },
    });
};

const openWhatsApp = () => {
    const phone = props.request.country_code + props.request.phone;
    const message = notifyForm.message || `Halo ${props.request.name}, terima kasih atas permintaan tur impian Anda ke ${destinationsText.value}. Tim TripTrove siap membantu Anda!`;
    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
};
</script>

<template>
    <Head :title="__('Dream Tour Request Details')" />
    <AdminLayout>
        <div class="mb-6">
            <Link 
                :href="route('admin.dream-tour-requests.index')"
                class="inline-flex items-center gap-2 text-brand-cyan hover:text-brand-blue transition-colors mb-4"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to List') }}
            </Link>
            <h1 class="text-3xl font-bold text-brand-cyan">{{ __('Dream Tour Request Details') }}</h1>
        </div>

        <div v-if="$page.props.flash?.message" class="bg-green-600 text-white p-4 rounded-lg mb-4">
            {{ $page.props.flash.message }}
        </div>
        <div v-if="$page.props.flash?.error" class="bg-red-600 text-white p-4 rounded-lg mb-4">
            {{ $page.props.flash.error }}
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Request Information -->
                <div class="bg-[#111c2e] rounded-xl shadow-lg border border-gray-700/40 p-6">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('Request Information') }}
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">{{ __('Submitted On') }}</p>
                            <p class="text-white font-medium">{{ formatDate(request.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-1">{{ __('Request Status') }}</p>
                            <span 
                                :class="getStatusColor(request.status)"
                                class="inline-block px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border"
                            >
                                {{ __(request.status.charAt(0).toUpperCase() + request.status.slice(1)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-[#111c2e] rounded-xl shadow-lg border border-gray-700/40 p-6">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ __('Customer Information') }}
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">{{ __('Name') }}</p>
                            <p class="text-white font-medium">{{ request.name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-1">{{ __('Email') }}</p>
                            <p class="text-white font-medium">{{ request.email }}</p>
                        </div>
                        <div v-if="request.phone">
                            <p class="text-gray-400 text-sm mb-1">{{ __('Phone') }}</p>
                            <p class="text-white font-medium">{{ request.country_code }} {{ request.phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Trip Preferences -->
                <div class="bg-[#111c2e] rounded-xl shadow-lg border border-gray-700/40 p-6">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ __('Trip Preferences') }}
                    </h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-400 text-sm mb-1">{{ __('Number of Adults') }}</p>
                                <p class="text-white font-medium">{{ request.num_adults }} {{ __('adults') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-sm mb-1">{{ __('Number of Children') }}</p>
                                <p class="text-white font-medium">{{ request.num_children }} {{ __('children') }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-1">{{ __('Preferred Departure Date') }}</p>
                            <p class="text-white font-medium">{{ formatDate(request.departure_date) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-2">{{ __('Requested Destinations') }}</p>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="(dest, index) in request.destinations" 
                                    :key="index"
                                    class="px-3 py-1.5 bg-brand-blue/20 text-brand-cyan rounded-lg border border-brand-blue/30 text-sm font-medium"
                                >
                                    {{ dest }}
                                </span>
                            </div>
                        </div>
                        <div v-if="request.additional_info">
                            <p class="text-gray-400 text-sm mb-1">{{ __('Additional Notes') }}</p>
                            <p class="text-white bg-gray-800/50 p-3 rounded-lg">{{ request.additional_info }}</p>
                        </div>
                        <div v-else>
                            <p class="text-gray-500 italic text-sm">{{ __('No additional notes provided.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <div v-if="request.attachments && request.attachments.length > 0" class="bg-[#111c2e] rounded-xl shadow-lg border border-gray-700/40 p-6">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        {{ __('Attachments') }}
                    </h2>
                    <ul class="space-y-2">
                        <li v-for="(attachment, index) in request.attachments" :key="index" class="flex items-center gap-3 bg-gray-800/50 p-3 rounded-lg border border-gray-700/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <a :href="`/storage/${attachment}`" target="_blank" class="text-brand-cyan hover:underline hover:text-brand-blue flex-1 truncate text-sm">
                                {{ attachment.split('/').pop() }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Sidebar Actions -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Update Status & Price -->
                <div class="bg-[#111c2e] rounded-xl shadow-lg border border-gray-700/40 p-6">
                    <h3 class="text-lg font-bold text-white mb-4">{{ __('Update Status') }}</h3>
                    <form @submit.prevent="updateStatus" class="space-y-4">
                        <div>
                            <label class="text-brand-cyan text-sm font-semibold">Status</label>
                            <select 
                                v-model="statusForm.status"
                                class="mt-1 w-full bg-gray-800/50 border-gray-600/50 rounded-lg px-4 py-2.5 text-white focus:ring-brand-cyan focus:border-brand-cyan"
                            >
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="contacted">{{ __('Contacted') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                                <option value="cancelled">{{ __('Cancelled') }}</option>
                            </select>
                        </div>
                        
                        <!-- Price Input -->
                        <div>
                            <label class="text-brand-cyan text-sm font-semibold">{{ __('Package Price') }}</label>
                            <div class="flex gap-2 mt-1">
                                <select 
                                    v-model="statusForm.price_currency"
                                    class="w-24 bg-gray-800/50 border-gray-600/50 rounded-lg px-2 py-2.5 text-white focus:ring-brand-cyan focus:border-brand-cyan text-sm"
                                >
                                    <option value="IDR">IDR</option>
                                    <option value="USD">USD</option>
                                    <option value="MYR">MYR</option>
                                </select>
                                <input 
                                    type="number" 
                                    v-model="statusForm.admin_price" 
                                    placeholder="0"
                                    step="0.01"
                                    min="0"
                                    class="flex-1 bg-gray-800/50 border-gray-600/50 rounded-lg px-4 py-2.5 text-white focus:ring-brand-cyan focus:border-brand-cyan"
                                />
                            </div>
                            <p v-if="statusForm.admin_price" class="text-xs text-gray-400 mt-1">
                                {{ formatPrice(statusForm.admin_price, statusForm.price_currency) }}
                            </p>
                        </div>

                        <button 
                            type="submit"
                            :disabled="statusForm.processing"
                            class="w-full bg-brand-cyan text-gray-900 font-bold py-2.5 rounded-lg hover:bg-brand-blue transition-colors disabled:opacity-50"
                        >
                            {{ statusForm.processing ? __('Processing...') : __('Save Changes') }}
                        </button>
                    </form>
                </div>

                <!-- Contact Actions with Templates -->
                <div class="bg-[#111c2e] rounded-xl shadow-lg border border-gray-700/40 p-6">
                    <h3 class="text-lg font-bold text-white mb-4">{{ __('Contact Customer') }}</h3>
                    <div class="space-y-4">
                        <!-- Template Selector -->
                        <div>
                            <label class="text-brand-cyan text-sm font-semibold">{{ __('Message Template') }}</label>
                            <select 
                                v-model="selectedTemplate" 
                                class="mt-1 block w-full bg-gray-800/50 border-gray-600/50 rounded-lg px-4 py-2.5 text-white focus:ring-brand-cyan focus:border-brand-cyan text-sm"
                            >
                                <option value="">-- {{ __('Select Template') }} --</option>
                                <option value="completed">✅ {{ __('Confirmed') }}</option>
                                <option value="cancelled">❌ {{ __('Cancelled') }}</option>
                            </select>
                        </div>

                        <!-- Language Selector -->
                        <div v-if="selectedTemplate">
                            <label class="text-brand-cyan text-sm font-semibold">{{ __('Language') }}</label>
                            <div class="flex gap-2 mt-1">
                                <button 
                                    @click="selectedLanguage = 'en'" 
                                    :class="selectedLanguage === 'en' ? 'bg-brand-cyan text-gray-900' : 'bg-gray-800/50 text-gray-400 border-gray-600/50'"
                                    class="flex-1 px-3 py-2 rounded-md text-sm font-bold border transition-colors"
                                >
                                    🇬🇧 EN
                                </button>
                                <button 
                                    @click="selectedLanguage = 'id'" 
                                    :class="selectedLanguage === 'id' ? 'bg-brand-cyan text-gray-900' : 'bg-gray-800/50 text-gray-400 border-gray-600/50'"
                                    class="flex-1 px-3 py-2 rounded-md text-sm font-bold border transition-colors"
                                >
                                    🇮🇩 ID
                                </button>
                                <button 
                                    @click="selectedLanguage = 'ms'" 
                                    :class="selectedLanguage === 'ms' ? 'bg-brand-cyan text-gray-900' : 'bg-gray-800/50 text-gray-400 border-gray-600/50'"
                                    class="flex-1 px-3 py-2 rounded-md text-sm font-bold border transition-colors"
                                >
                                    🇲🇾 MS
                                </button>
                            </div>
                        </div>

                        <!-- Message Textarea -->
                        <div>
                            <label class="text-brand-cyan text-sm font-semibold">{{ __('Message') }}</label>
                            <textarea 
                                v-model="notifyForm.message" 
                                rows="8" 
                                class="mt-1 block w-full bg-gray-800/50 border-gray-600/50 rounded-lg px-4 py-2.5 text-white focus:ring-brand-cyan focus:border-brand-cyan text-sm"
                                :placeholder="__('Select a template above or write a custom message...')"
                            ></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            <button 
                                @click="sendEmailNotification"
                                :disabled="notifyForm.processing || !notifyForm.message"
                                class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg v-if="notifyForm.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ notifyForm.processing ? __('Sending...') : __('Send Email') }}
                            </button>
                            <button 
                                v-if="request.phone"
                                @click="openWhatsApp"
                                :disabled="!notifyForm.message"
                                class="w-full flex items-center justify-center gap-2 bg-green-600 text-white font-bold py-2.5 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                                {{ __('Send via WhatsApp') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
