<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    types: Object, // Paginasi
    filters: Object,
});

const deleteType = (id) => {
    if (confirm('Yakin ingin menghapus tipe ini?')) {
        router.delete(route('admin.vehicle-types.destroy', id), {
            preserveScroll: true,
        });
    }
}
</script>
<template>
    <Head title="Vehicle Types" />
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-brand-cyan">Tipe Kendaraan</h1>
            <Link :href="route('admin.vehicle-types.create')" class="inline-block bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan text-white font-bold py-2 px-5 rounded-lg shadow-md transition-all">
                + Tambah Tipe
            </Link>
        </div>

        <div v-if="$page.props.flash?.message" class="bg-green-600 text-white p-4 rounded-lg mb-4">{{ $page.props.flash.message }}</div>
        <div v-if="$page.props.flash?.error" class="bg-red-600 text-white p-4 rounded-lg mb-4">{{ $page.props.flash.error }}</div>

        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto border border-brand-border">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-gray-700/50 border-b border-gray-600 text-xs uppercase">
                    <tr>
                        <th class="p-4">Nama Tipe</th>
                        <th class="p-4">Kode</th>
                        <th class="p-4">Biaya Tambahan</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="types.data.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500 italic">Belum ada tipe kendaraan.</td>
                    </tr>
                    <tr v-for="type in types.data" :key="type.id" class="border-b border-gray-700 hover:bg-gray-700/30">
                        <td class="p-4 font-medium text-white">{{ type.name }}</td>
                        <td class="p-4 font-mono">{{ type.code }}</td>
                        <td class="p-4">Rp {{ new Intl.NumberFormat('id-ID').format(type.additional_charge) }}</td>
                        <td class="p-4 text-right whitespace-nowrap space-x-3">
                            <Link :href="route('admin.vehicle-types.edit', type.id)" class="text-yellow-400 hover:text-yellow-300 font-medium">Edit</Link>
                            <button @click="deleteType(type.id)" class="text-red-500 hover:text-red-400 font-medium">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>