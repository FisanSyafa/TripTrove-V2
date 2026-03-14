<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    messages: Object
});

const form = useForm({});

// State untuk Modal Detail
const showModal = ref(false);
const selectedMessage = ref(null);

// Fungsi membuka modal
const openDetail = (msg) => {
    selectedMessage.value = msg;
    showModal.value = true;
};

// Fungsi menutup modal
const closeDetail = () => {
    showModal.value = false;
    selectedMessage.value = null;
};

const deleteMessage = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
        form.delete(route('admin.messages.destroy', id), {
            preserveScroll: true,
            onSuccess: () => closeDetail(), // Tutup modal jika menghapus dari dalam modal
        });
    }
};

// Fungsi format nomor WA
const formatWaNumber = (phone) => {
    if (!phone) return '';
    let cleanPhone = phone.replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) {
        cleanPhone = '62' + cleanPhone.slice(1);
    }
    return cleanPhone;
};

const replyViaWa = (msg) => {
    const phone = formatWaNumber(msg.phone_number);
    if (!phone) {
        alert('Nomor telepon tidak valid atau tidak tersedia.');
        return;
    }
    
    const text = `Halo ${msg.name}, kami dari TripTrove telah menerima pesan Anda mengenai "${msg.subject}".`;
    const url = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
};
</script>

<template>
    <Head title="Pesan Masuk" />

    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-brand-cyan">Pesan Masuk</h1>
        </div>

        <div v-if="$page.props.flash?.message" class="bg-green-600/90 text-white p-4 rounded-lg mb-6 shadow-md backdrop-blur-sm">
            {{ $page.props.flash.message }}
        </div>

        <div class="bg-[#111c2e] rounded-xl shadow-lg overflow-hidden border border-gray-700/40">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-300">
                    <thead class="bg-[#0c1222]/80 border-b border-gray-600/50 text-xs uppercase text-gray-400">
                        <tr>
                            <th class="p-4 font-semibold tracking-wide">Tanggal</th>
                            <th class="p-4 font-semibold tracking-wide">Pengirim</th>
                            <th class="p-4 font-semibold tracking-wide">Subjek</th>
                            <th class="p-4 font-semibold tracking-wide">Cuplikan Pesan</th>
                            <th class="p-4 text-center font-semibold tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-if="messages.data.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-500 italic">
                                Belum ada pesan masuk.
                            </td>
                        </tr>
                        <tr v-for="msg in messages.data" :key="msg.id" class="hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="p-4 whitespace-nowrap text-gray-400">
                                {{ new Date(msg.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-white mb-0.5">{{ msg.name }}</div>
                                <div class="text-xs text-gray-400">{{ msg.email }}</div>
                                <div v-if="msg.phone_number" class="text-xs text-brand-cyan mt-0.5">{{ msg.phone_number }}</div>
                            </td>
                            <td class="p-4 font-medium text-gray-200">
                                {{ msg.subject }}
                            </td>
                            <td class="p-4 text-gray-400 max-w-xs truncate cursor-pointer hover:text-white transition-colors" @click="openDetail(msg)" title="Klik untuk baca selengkapnya">
                                {{ msg.message }}
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button 
                                        @click="openDetail(msg)"
                                        class="p-2 bg-blue-600/20 text-blue-400 hover:bg-blue-600 hover:text-white rounded-lg transition-all"
                                        title="Lihat Detail"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    <button 
                                        v-if="msg.phone_number"
                                        @click="replyViaWa(msg)"
                                        class="p-2 bg-green-600/20 text-green-400 hover:bg-green-600 hover:text-white rounded-lg transition-all"
                                        title="Balas via WhatsApp"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                        </svg>
                                    </button>
                                    
                                    <button 
                                        @click="deleteMessage(msg.id)" 
                                        class="p-2 bg-red-600/20 text-red-400 hover:bg-red-600 hover:text-white rounded-lg transition-all"
                                        title="Hapus Pesan"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="messages.links.length > 3" class="mt-6 flex justify-center space-x-1">
            <Link v-for="(link, key) in messages.links"
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

        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4" @click.self="closeDetail">
            <div class="bg-[#111c2e] rounded-xl shadow-2xl w-full max-w-2xl border border-gray-700/40 transform transition-all scale-100">
                
                <div class="flex justify-between items-center p-6 border-b border-gray-700 bg-gray-750 rounded-t-xl">
                    <h3 class="text-xl font-bold text-white">Detail Pesan</h3>
                    <button @click="closeDetail" class="text-gray-400 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-6" v-if="selectedMessage">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-700/30 p-4 rounded-lg border border-gray-700">
                            <p class="text-xs uppercase text-gray-500 font-bold mb-1">Nama Pengirim</p>
                            <p class="text-gray-200 font-medium">{{ selectedMessage.name }}</p>
                        </div>
                        <div class="bg-gray-700/30 p-4 rounded-lg border border-gray-700">
                            <p class="text-xs uppercase text-gray-500 font-bold mb-1">Waktu Kirim</p>
                            <p class="text-gray-200 font-medium">
                                {{ new Date(selectedMessage.created_at).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' }) }}
                            </p>
                        </div>
                        <div class="bg-gray-700/30 p-4 rounded-lg border border-gray-700">
                            <p class="text-xs uppercase text-gray-500 font-bold mb-1">Email</p>
                            <p class="text-gray-200 font-medium">{{ selectedMessage.email }}</p>
                        </div>
                         <div class="bg-gray-700/30 p-4 rounded-lg border border-gray-700">
                            <p class="text-xs uppercase text-gray-500 font-bold mb-1">WhatsApp / Telepon</p>
                            <p class="text-brand-cyan font-medium font-mono">{{ selectedMessage.phone_number || '-' }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs uppercase text-gray-500 font-bold mb-2">Subjek: <span class="text-white normal-case text-base">{{ selectedMessage.subject }}</span></p>
                        <div class="bg-gray-900 p-5 rounded-lg border border-gray-700 text-gray-300 leading-relaxed whitespace-pre-wrap max-h-64 overflow-y-auto custom-scrollbar">
                            {{ selectedMessage.message }}
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-700 bg-gray-750 rounded-b-xl flex justify-end space-x-3">
                    <button 
                        @click="closeDetail" 
                        class="px-5 py-2 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors font-medium"
                    >
                        Tutup
                    </button>
                    
                    <button 
                        v-if="selectedMessage?.phone_number"
                        @click="replyViaWa(selectedMessage)"
                        class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center shadow-lg"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        Balas WA
                    </button>

                    <button 
                        @click="deleteMessage(selectedMessage.id)" 
                        class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-lg"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #1f2937; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #374151; 
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #4b5563; 
}
</style>