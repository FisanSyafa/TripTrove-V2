<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';

defineProps({
    vehicles: Object, // Paginasi
    filters: Object,
});

const deleteVehicle = (id) => {
    if (confirm('Yakin ingin menghapus kendaraan ini?')) {
        router.delete(route('admin.vehicles.destroy', id), {
            preserveScroll: true,
        });
    }
}
</script>
<template>
    <Head title="Vehicles" />
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-brand-cyan">Daftar Kendaraan</h1>
            <Link :href="route('admin.vehicles.create')" class="inline-block bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan text-white font-bold py-2 px-5 rounded-lg shadow-md transition-all">
                + Tambah Kendaraan
            </Link>
        </div>

        <div v-if="$page.props.flash?.message" class="bg-green-600 text-white p-4 rounded-lg mb-4">{{ $page.props.flash.message }}</div>
        <div v-if="$page.props.flash?.error" class="bg-red-600 text-white p-4 rounded-lg mb-4">{{ $page.props.flash.error }}</div>
        
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto border border-brand-border">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-gray-700/50 border-b border-gray-600 text-xs uppercase">
                    <tr>
                        <th class="p-4">Nama Kendaraan</th>
                        <th class="p-4">Plat Nomor</th>
                        <th class="p-4">Tipe</th>
                        <th class="p-4">Tersedia</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                     <tr v-if="vehicles.data.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500 italic">Belum ada kendaraan.</td>
                    </tr>
                    <tr v-for="vehicle in vehicles.data" :key="vehicle.id" class="border-b border-gray-700 hover:bg-gray-700/30">
                        <td class="p-4 font-medium text-white">{{ vehicle.name }}</td>
                        <td class="p-4 font-mono">{{ vehicle.license_plate }}</td>
                        <td class="p-4">{{ vehicle.vehicle_type.name }}</td>
                        <td class="p-4">
                            <span :class="vehicle.is_available ? 'text-green-400' : 'text-red-400'">
                                {{ vehicle.is_available ? 'Ya' : 'Tidak' }}
                            </span>
                        </td>
                        <td class="p-4 text-right whitespace-nowrap space-x-3">
                            <Link :href="route('admin.vehicles.edit', vehicle.id)" class="text-yellow-400 hover:text-yellow-300 font-medium">Edit</Link>
                            <button @click="deleteVehicle(vehicle.id)" class="text-red-500 hover:text-red-400 font-medium">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>