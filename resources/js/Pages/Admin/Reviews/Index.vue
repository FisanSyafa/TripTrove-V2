<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    reviews: Object, // Paginasi
    filters: Object,
});

// Fungsi untuk approve/unapprove
const toggleApproval = (review) => {
    router.post(route('admin.reviews.update', review.id), {
    _method: 'put', // Method Spoofing
    is_approved: !review.is_approved
    }, { 
        preserveScroll: true,
        preserveState: true,
    });
};

// Fungsi hapus
const deleteReview = (id) => {
    if (confirm('Yakin ingin menghapus review ini secara permanen?')) {
        router.post(route('admin.reviews.destroy', id), {
            _method: 'delete'
        }, { preserveScroll: true });
    }
};

// Filter status
const statusFilter = ref(props.filters.status || '');
watch(statusFilter, (value) => {
    router.get(route('admin.reviews.index'), { status: value }, {
        preserveState: true,
        replace: true,
    });
});

</script>
<template>
    <Head title="Manage Reviews" />
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-brand-cyan">Moderasi Ulasan</h1>
            <div>
                <select v-model="statusFilter" class="bg-gray-700 border-gray-600 rounded-lg focus:ring-brand-cyan focus:border-brand-cyan text-sm">
                    <option value="">Semua Status</option>
                    <option value="approved">Disetujui</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
        </div>

        <div v-if="$page.props.flash?.message" class="bg-green-600 text-white p-4 rounded-lg mb-4">{{ $page.props.flash.message }}</div>

        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto border border-brand-border">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-gray-700/50 border-b border-gray-600 text-xs uppercase">
                    <tr>
                        <th class="p-4">Paket Wisata</th>
                        <th class="p-4">User</th>
                        <th class="p-4">Rating</th>
                        <th class="p-4">Komentar</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="reviews.data.length === 0">
                        <td colspan="6" class="p-4 text-center text-gray-500 italic">Belum ada ulasan.</td>
                    </tr>
                    <tr v-for="review in reviews.data" :key="review.id" class="border-b border-gray-700 hover:bg-gray-700/30">
                        <td class="p-4 font-medium text-white">{{ review.tour_package.name }}</td>
                        <td class="p-4">{{ review.user.name }}</td>
                        <td class="p-4">
                            <div class="flex items-center">
                                <svg v-for="n in 5" :key="n" class="w-4 h-4" :class="n <= review.rating ? 'text-yellow-400' : 'text-gray-600'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19 10 15.27z"/>
                                </svg>
                            </div>
                        </td>
                        <td class="p-4 text-xs max-w-xs truncate" :title="review.comment">{{ review.comment || '-' }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full uppercase"
                                :class="{
                                    'bg-green-500/80 text-green-100': review.is_approved,
                                    'bg-yellow-500/80 text-yellow-100': !review.is_approved,
                                }">
                                {{ review.is_approved ? 'Disetujui' : 'Pending' }}
                            </span>
                        </td>
                        <td class="p-4 text-right whitespace-nowrap space-x-3">
                            <button @click="toggleApproval(review)" 
                                    class="font-medium"
                                    :class="review.is_approved ? 'text-yellow-400 hover:text-yellow-300' : 'text-green-400 hover:text-green-300'">
                                {{ review.is_approved ? 'Batalkan' : 'Setujui' }}
                            </button>
                            <button @click="deleteReview(review.id)" class="text-red-500 hover:text-red-400 font-medium">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div v-if="reviews.links.length > 3" class="mt-6 flex justify-center space-x-1">
            <Link v-for="(link, key) in reviews.links"
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