<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import throttle from 'lodash/throttle';

const props = defineProps({
    requests: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, throttle(function (value) {
    router.get(route('admin.dream-tour-requests.index'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}, 300));

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
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};
</script>

<template>
    <Head title="Dream Tour Requests" />
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-brand-cyan">{{ __('Dream Tour Requests') }}</h1>
        </div>

        <div v-if="$page.props.flash?.message" class="bg-green-600 text-white p-4 rounded-lg mb-4">
            {{ $page.props.flash.message }}
        </div>

        <div class="mb-4">
            <input 
                type="text" 
                v-model="search" 
                :placeholder="__('Search by name, email, or destination...')"
                class="w-full md:w-1/2 bg-[#111c2e] border-gray-600/50 rounded-xl px-4 py-2.5 focus:ring-brand-cyan focus:border-brand-cyan transition text-gray-100 shadow-sm placeholder-gray-500"
            >
        </div>

        <div class="bg-[#111c2e] rounded-xl shadow-lg overflow-x-auto border border-gray-700/40">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-[#0c1222]/80 border-b border-gray-600/50 text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="p-4">{{ __('Customer') }}</th>
                        <th class="p-4">{{ __('Email') }}</th>
                        <th class="p-4">{{ __('Requested Destinations') }}</th>
                        <th class="p-4">{{ __('Participants') }}</th>
                        <th class="p-4">{{ __('Requested Date') }}</th>
                        <th class="p-4">{{ __('Status') }}</th>
                        <th class="p-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="requests.data.length === 0">
                        <td colspan="7" class="p-4 text-center text-gray-500 italic">
                            {{ __('No dream tour requests yet.') }}
                        </td>
                    </tr>
                    <tr 
                        v-for="request in requests.data" 
                        :key="request.id" 
                        class="border-b border-gray-700 hover:bg-gray-700/30 transition-colors duration-150"
                    >
                        <td class="p-4 font-medium text-white">{{ request.name }}</td>
                        <td class="p-4 text-gray-400">{{ request.email }}</td>
                        <td class="p-4">
                            <div class="flex flex-wrap gap-1">
                                <span 
                                    v-for="(dest, index) in request.destinations.slice(0, 2)" 
                                    :key="index"
                                    class="px-2 py-1 bg-brand-blue/20 text-brand-cyan text-xs rounded-md border border-brand-blue/30"
                                >
                                    {{ dest }}
                                </span>
                                <span 
                                    v-if="request.destinations.length > 2"
                                    class="px-2 py-1 bg-gray-700/50 text-gray-400 text-xs rounded-md"
                                >
                                    +{{ request.destinations.length - 2 }}
                                </span>
                            </div>
                        </td>
                        <td class="p-4 text-gray-400">
                            {{ request.num_adults }} {{ __('adults') }}
                            <span v-if="request.num_children > 0">, {{ request.num_children }} {{ __('children') }}</span>
                        </td>
                        <td class="p-4 text-gray-400">{{ formatDate(request.departure_date) }}</td>
                        <td class="p-4">
                            <span 
                                :class="getStatusColor(request.status)"
                                class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border"
                            >
                                {{ __(request.status.charAt(0).toUpperCase() + request.status.slice(1)) }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <Link 
                                :href="route('admin.dream-tour-requests.show', request.id)"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-brand-blue/20 text-brand-cyan rounded-lg hover:bg-brand-blue/30 transition-colors border border-brand-blue/30 text-sm font-medium"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ __('View Details') }}
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="requests.links.length > 3" class="mt-6 flex justify-center">
            <nav class="flex gap-2">
                <Link
                    v-for="(link, index) in requests.links"
                    :key="index"
                    :href="link.url"
                    v-html="link.label"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        link.active 
                            ? 'bg-brand-cyan text-gray-900' 
                            : 'bg-[#111c2e] text-gray-400 hover:bg-gray-700/50 border border-gray-700/40',
                        !link.url && 'opacity-50 cursor-not-allowed'
                    ]"
                    :disabled="!link.url"
                />
            </nav>
        </div>
    </AdminLayout>
</template>
