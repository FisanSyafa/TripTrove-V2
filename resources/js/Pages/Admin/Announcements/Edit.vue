<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    announcement: Object,
});

const form = useForm({
    message: props.announcement.message,
    is_active: Boolean(props.announcement.is_active),
});

const submit = () => {
    form.put(route('admin.announcements.update', props.announcement.id));
};
</script>

<template>
    <Head title="Edit Announcement" />

    <AdminLayout>
        <Link :href="route('admin.announcements.index')" class="inline-flex items-center text-sm text-brand-cyan hover:text-blue-400 mb-6 group">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition-transform">
                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
            Back to List
        </Link>

        <div class="max-w-2xl mx-auto">
            <div class="bg-[#111c2e] rounded-xl shadow-lg p-6 border border-gray-700/40">
                <h2 class="text-2xl font-bold mb-6 text-brand-cyan border-b border-gray-600/50 pb-2">Edit Announcement</h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <div>
                        <InputLabel for="message" value="Message" class="!text-brand-cyan !font-semibold" />
                        <TextInput 
                            id="message" 
                            type="text" 
                            class="mt-1 block w-full bg-[#0c1222] border-gray-600/50 rounded-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white" 
                            v-model="form.message" 
                            required 
                            autofocus 
                        />
                        <InputError class="mt-2" :message="form.errors.message" />
                    </div>

                    <div class="flex items-center p-4 bg-[#0c1222]/60 rounded-lg border border-gray-600/50">
                        <Checkbox 
                            name="is_active" 
                            v-model:checked="form.is_active" 
                            class="text-brand-cyan focus:ring-brand-cyan bg-gray-700 border-gray-500"
                        />
                        <span class="ms-3 text-sm text-gray-300">
                            Set as <strong>Active Announcement</strong>
                            <span v-if="form.is_active" class="text-brand-cyan text-xs block mt-0.5">
                                (Other active announcements will be turned off)
                            </span>
                        </span>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-700">
                        <Link :href="route('admin.announcements.index')" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Cancel
                        </Link>
                        <PrimaryButton 
                            class="!py-2.5 !px-6 bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan focus:ring-offset-gray-800" 
                            :class="{ 'opacity-25': form.processing }" 
                            :disabled="form.processing"
                        >
                            Update Announcement
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>