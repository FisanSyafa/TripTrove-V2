<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import throttle from 'lodash/throttle'; // Pastikan lodash terinstal: npm install lodash

// Komponen Pagination (jika Anda membuatnya)
// import Pagination from '@/Components/Pagination.vue'; 

const props = defineProps({
    bookings: Object, // Ini adalah Objek Paginasi dari Laravel
    filters: Object,
});

const search = ref(props.filters.search);

// Fungsi watch untuk pencarian
watch(search, throttle(function (value) {
    router.get(route('admin.bookings.index'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}, 300));
</script>
<template>
    <Head title="Manage Bookings" />
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
             <h1 class="text-3xl font-bold text-brand-cyan">Daftar Pesanan (Bookings)</h1>
             </div>

        <div v-if="$page.props.flash?.message" class="bg-green-600 text-white p-4 rounded-lg mb-4">{{ $page.props.flash.message }}</div>
        <div v-if="$page.props.flash?.error" class="bg-red-600 text-white p-4 rounded-lg mb-4">{{ $page.props.flash.error }}</div>


        <div class="mb-4">
            <input type="text" v-model="search" placeholder="Cari kode booking, nama user, atau paket..."
                   class="w-full md:w-1/2 bg-[#111c2e] border-gray-600/50 rounded-xl px-4 py-2.5 focus:ring-brand-cyan focus:border-brand-cyan transition text-gray-100 shadow-sm placeholder-gray-500">
        </div>

        <div class="bg-[#111c2e] rounded-xl shadow-lg overflow-x-auto border border-gray-700/40">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-[#0c1222]/80 border-b border-gray-600/50 text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="p-4">Kode Booking</th>
                        <th class="p-4">Pelanggan</th>
                        <th class="p-4">Paket</th>
                        <th class="p-4">Tgl Berangkat</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                     <tr v-if="bookings.data.length === 0">
                        <td colspan="6" class="p-4 text-center text-gray-500 italic">Belum ada pesanan masuk.</td>
                    </tr>
                    <tr v-for="booking in bookings.data" :key="booking.id" class="border-b border-gray-700 hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="p-4 font-mono text-white">{{ booking.booking_code }}</td>
                        <td class="p-4">{{ booking.user ? booking.user.name : booking.guest_name }}</td>
                        <td class="p-4">{{ booking.tour_package.name }}</td>
                        <td class="p-4">{{ booking.departure_date }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full uppercase"
                                  :class="{
                                    'bg-yellow-500/80 text-yellow-100 border border-yellow-600': booking.status === 'pending',
                                    'bg-cyan-500/80 text-cyan-100 border border-cyan-600': booking.status === 'waiting_confirmation',
                                    'bg-blue-500/80 text-blue-100 border border-blue-600': booking.status === 'paid',
                                    'bg-green-500/80 text-green-100 border border-green-600': booking.status === 'confirmed',
                                    'bg-red-500/80 text-red-100 border border-red-600': booking.status === 'cancelled',
                                    'bg-purple-500/80 text-purple-100 border border-purple-600': booking.status === 'completed',
                                  }">
                                {{ booking.status.replace('_', ' ') }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <Link :href="route('admin.bookings.show', booking.id)" class="text-brand-cyan hover:text-blue-400 font-medium transition-colors">
                                Lihat Detail
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

         <div v-if="bookings.links.length > 3" class="mt-6 flex justify-center space-x-1">
            <Link v-for="(link, key) in bookings.links"
                  :key="key"
                  :href="link.url ?? '#'"
                  v-html="link.label"
                  class="px-4 py-2 rounded-lg text-sm transition-colors duration-150"
                  :class="{
                      'bg-gradient-to-r from-brand-cyan to-brand-blue text-white font-bold shadow-md': link.active,
                      'bg-gray-700 hover:bg-gray-600 text-gray-300': link.url && !link.active,
                      'text-gray-500 cursor-default': !link.url
                  }"
                  :disabled="!link.url"
            />
        </div>
        </AdminLayout>
</template>