<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import throttle from 'lodash/throttle'; // Pastikan lodash terinstal: npm install lodash

// Komponen Pagination (buat file ini nanti atau gunakan library)
// import Pagination from '@/Components/Pagination.vue'; 

const props = defineProps({
    packages: Object, // Objek Paginasi Laravel
    filters: Object, // Filter pencarian yang aktif
});

const search = ref(props.filters.search);

// Kirim request pencarian saat input berubah (dengan throttle)
watch(search, throttle(function (value) {
    router.get(route('admin.packages.index'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}, 300));

// Fungsi hapus paket
const deletePackage = (slug) => {
    if (confirm('Apakah Anda yakin ingin menghapus paket ini?')) {
        router.delete(route('admin.packages.destroy', slug), {
            preserveScroll: true // Agar halaman tidak scroll ke atas
        });
    }
}
</script>

<template>
    <Head title="Manage Packages" />
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-brand-cyan">Daftar Paket Wisata</h1>
            <Link :href="route('admin.packages.create')" class="inline-block bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan text-white font-bold py-2 px-5 rounded-lg shadow-md transition-all transform hover:scale-105">
                + Tambah Paket
            </Link>
        </div>

        <div v-if="$page.props.flash?.message" class="bg-green-600 border border-green-700 text-white p-4 rounded-lg mb-4 shadow animate-pulse">
            {{ $page.props.flash.message }}
        </div>
         <div v-if="$page.props.flash?.error" class="bg-red-600 border border-red-700 text-white p-4 rounded-lg mb-4 shadow animate-pulse">
            {{ $page.props.flash.error }}
        </div>

        <div class="mb-4">
            <input type="text" v-model="search" placeholder="Cari nama atau destinasi..."
                   class="w-full md:w-1/3 bg-[#111c2e] border-gray-600/50 rounded-xl px-4 py-2.5 focus:ring-brand-cyan focus:border-brand-cyan transition text-gray-100 shadow-sm placeholder-gray-500">
        </div>

        <div class="bg-[#111c2e] rounded-xl shadow-lg overflow-x-auto border border-gray-700/40">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-[#0c1222]/80 border-b border-gray-600/50 text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="p-4">Nama Paket</th>
                        <th class="p-4">Durasi</th>
                        <th class="p-4">Harga</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="packages.data.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500 italic">Belum ada paket wisata yang ditambahkan.</td>
                    </tr>
                    <tr v-for="pkg in packages.data" :key="pkg.id" class="border-b border-gray-700 hover:bg-gray-700/30 transition-colors duration-150">
                        <td class="p-4 font-medium text-white">{{ pkg.name }}</td>
                        <td class="p-4">{{ pkg.duration_days }} hari</td>
                        <td class="p-4">Rp {{ new Intl.NumberFormat('id-ID').format(pkg.price) }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full uppercase"
                                  :class="{
                                    'bg-green-500/80 text-green-100 border border-green-600': pkg.status === 'published',
                                    'bg-yellow-500/80 text-yellow-100 border border-yellow-600': pkg.status === 'draft',
                                    'bg-gray-500/80 text-gray-100 border border-gray-600': pkg.status === 'archived'
                                  }">
                                {{ pkg.status }}
                            </span>
                        </td>
                        <td class="p-4 text-right whitespace-nowrap space-x-3">
                            <Link :href="route('admin.packages.edit', pkg.slug)" class="text-yellow-400 hover:text-yellow-300 font-medium transition-colors">Edit</Link>
                            <button @click="deletePackage(pkg.slug)" class="text-red-500 hover:text-red-400 font-medium transition-colors">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

         <div v-if="packages.links.length > 3" class="mt-6 flex justify-center space-x-1">
            <Link v-for="(link, key) in packages.links"
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
        <Pagination class="mt-6" :links="packages.links" />

    </AdminLayout>
</template>