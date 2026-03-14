<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

// Gunakan useForm untuk form
const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

// Fungsi submit
const submit = () => {
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset(); // Kosongkan form setelah berhasil
        },
    });
};
</script>

<template>
    <Head title="Hubungi Kami" />
    <MainLayout>
        <div class="container mx-auto py-12 px-6">
            <div class="max-w-2xl mx-auto">
                <h1 class="text-3xl font-bold mb-8 text-brand-cyan text-center">Hubungi Kami</h1>
                <p class="text-center text-gray-400 mb-8">
                    Punya pertanyaan atau butuh bantuan merencanakan perjalanan Anda? Jangan ragu untuk mengirim pesan kepada kami.
                </p>

                <div v-if="$page.props.flash?.message" class="bg-green-600 border border-green-700 text-white p-4 rounded-lg mb-6 shadow">
                    {{ $page.props.flash.message }}
                </div>

                <form @submit.prevent="submit" class="space-y-6 bg-gray-800 p-8 rounded-lg shadow-lg border border-brand-border">
                    <div>
                        <InputLabel for="name" value="Nama Anda" class="!text-brand-cyan !font-semibold" />
                        <TextInput id="name" type="text" class="mt-1 block w-full bg-gray-700" v-model="form.name" required />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email Anda" class="!text-brand-cyan !font-semibold" />
                        <TextInput id="email" type="email" class="mt-1 block w-full bg-gray-700" v-model="form.email" required />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="subject" value="Subjek" class="!text-brand-cyan !font-semibold" />
                        <TextInput id="subject" type="text" class="mt-1 block w-full bg-gray-700" v-model="form.subject" required />
                        <InputError class="mt-2" :message="form.errors.subject" />
                    </div>

                    <div>
                        <InputLabel for="message" value="Pesan Anda" class="!text-brand-cyan !font-semibold" />
                        <textarea id="message" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white" v-model="form.message" rows="6" required></textarea>
                        <InputError class="mt-2" :message="form.errors.message" />
                    </div>

                    <div class="flex items-center justify-end">
                        <PrimaryButton class="ms-4 bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Kirim Pesan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>