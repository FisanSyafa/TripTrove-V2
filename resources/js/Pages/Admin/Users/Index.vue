<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import throttle from 'lodash/throttle';

const props = defineProps({
    users: Object, // Paginasi
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, throttle(function (value) {
    router.get(route('admin.users.index'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}, 300));

// BARU: Fungsi untuk konfirmasi hapus
const confirmDelete = (user) => {
    if (window.confirm(`Apakah Anda yakin ingin menghapus "${user.name}"?`)) {
        router.delete(route('admin.users.destroy', user.id), {
            preserveScroll: true,
            // Anda bisa tambahkan onSuccess jika perlu, misal:
            // onSuccess: () => {
            //     // Tampilkan notifikasi sukses
            // }
        });
    }
};
</script>

<template>
    <Head title="Manage Users" />
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-brand-cyan">Daftar Pengguna</h1>
            
            <Link 
                :href="route('admin.users.create')" 
                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium shadow-md transition-colors"
            >
                + Tambah User
            </Link>
        </div>

        <div v-if="$page.props.flash?.message" class="bg-green-600 text-white p-4 rounded-lg mb-4">{{ $page.props.flash.message }}</div>
        <div v-if="$page.props.flash?.error" class="bg-red-600 text-white p-4 rounded-lg mb-4">{{ $page.props.flash.error }}</div>

        <div class="mb-4">
            <input type="text" v-model="search" placeholder="Cari nama atau email..."
                   class="w-full md:w-1/3 bg-[#111c2e] border-gray-600/50 rounded-xl px-4 py-2.5 focus:ring-brand-cyan focus:border-brand-cyan transition text-gray-100 shadow-sm placeholder-gray-500">
        </div>

        <div class="bg-[#111c2e] rounded-xl shadow-lg overflow-x-auto border border-gray-700/40">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-[#0c1222]/80 border-b border-gray-600/50 text-xs uppercase text-gray-400">
                    <tr>
                        <th class="p-4">Nama</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Tgl Daftar</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="users.data.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500 italic">Pengguna tidak ditemukan.</td>
                    </tr>
                    <tr v-for="user in users.data" :key="user.id" class="border-b border-gray-700 hover:bg-gray-700/30">
                        <td class="p-4 font-medium text-white">{{ user.name }}</td>
                        <td class="p-4">{{ user.email }}</td>
                        <td class="p-4">
                            <span class="font-mono px-2 py-0.5 rounded text-xs"
                                  :class="{
                                      'bg-red-500 text-white': user.role === 'admin',
                                      'bg-cyan-500 text-black': user.role === 'guide',
                                      'bg-blue-500 text-white': user.role === 'driver',
                                      'bg-gray-500 text-white': user.role === 'user',
                                  }">
                                {{ user.role }}
                            </span>
                        </td>
                        <td class="p-4">{{ new Date(user.created_at).toLocaleDateString('id-ID') }}</td>
                        <td class="p-4 text-right whitespace-nowrap space-x-4">
                            <Link :href="route('admin.users.edit', user.id)" class="text-yellow-400 hover:text-yellow-300 font-medium">Edit</Link>
                            
                            <button 
                                @click="confirmDelete(user)" 
                                class="text-red-400 hover:text-red-300 font-medium"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div v-if="users.links.length > 3" class="mt-6 flex justify-center space-x-1">
            <Link v-for="(link, key) in users.links"
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