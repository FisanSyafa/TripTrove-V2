<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    announcements: Object,
});

const form = useForm({});

const deleteAnnouncement = (id) => {
    if (confirm('Are you sure you want to delete this announcement?')) {
        form.delete(route('admin.announcements.destroy', id));
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Manage Announcements" />

    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-brand-cyan">Manage Announcements</h2>
            <Link :href="route('admin.announcements.create')">
                <PrimaryButton class="!py-2.5 !px-5 bg-gradient-to-r from-brand-cyan to-brand-blue hover:shadow-lg hover:from-brand-blue hover:to-brand-cyan transition-all">
                    + Create New
                </PrimaryButton>
            </Link>
        </div>

        <div class="bg-[#111c2e] rounded-xl shadow-lg border border-gray-700/40 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-[#0c1222]/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Message</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Created At</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#111c2e] divide-y divide-gray-700/40">
                        <tr v-for="item in announcements.data" :key="item.id" class="hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-white font-medium">
                                {{ item.message }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span v-if="item.is_active" class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-500/20 text-green-400 border border-green-500/30">
                                    Active
                                </span>
                                <span v-else class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-gray-600/30 text-gray-400 border border-gray-600">
                                    Inactive
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                <Link :href="route('admin.announcements.edit', item.id)" class="text-brand-cyan hover:text-blue-400 font-bold transition-colors">
                                    Edit
                                </Link>
                                <button @click="deleteAnnouncement(item.id)" class="text-red-500 hover:text-red-400 font-bold transition-colors">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="announcements.data.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">
                                No announcements found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            <Pagination :links="announcements.links" />
        </div>
    </AdminLayout>
</template>