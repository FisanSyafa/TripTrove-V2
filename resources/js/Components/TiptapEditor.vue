<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { watch } from 'vue';
import InputError from '@/Components/InputError.vue'; // Pastikan InputError di-import

// --- PERBAIKAN: Tambahkan props untuk form dan fieldName ---
const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    form: {
        type: Object,
        required: true, // Wajibkan form untuk dilewatkan
    },
    fieldName: {
        type: String,
        default: 'description', // Default ke 'description'
    },
});
// ---

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-invert max-w-none bg-gray-700 border-gray-600 rounded-b-md shadow-sm focus:border-brand-cyan focus:ring-brand-cyan text-white p-4 min-h-[250px] outline-none',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(() => props.modelValue, (newValue) => {
    if (editor.value && editor.value.getHTML() !== newValue) {
        editor.value.commands.setContent(newValue, false);
    }
});
</script>

<template>
    <div v-if="editor" class="border border-gray-600 rounded-md shadow-sm">
        <div class="flex items-center space-x-2 p-2 bg-gray-900 rounded-t-md border-b border-gray-600">
            <button type="button" @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-brand-cyan text-black': editor.isActive('bold') }" class="px-2 py-1 rounded text-sm font-bold">B</button>
            <button type="button" @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-brand-cyan text-black': editor.isActive('italic') }" class="px-2 py-1 rounded text-sm italic">I</button>
            <button type="button" @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'bg-brand-cyan text-black': editor.isActive('bulletList') }" class="px-2 py-1 rounded text-sm">List</button>
            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'bg-brand-cyan text-black': editor.isActive('heading', { level: 2 }) }" class="px-2 py-1 rounded text-sm font-bold">H2</button>
            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="{ 'bg-brand-cyan text-black': editor.isActive('heading', { level: 3 }) }" class="px-2 py-1 rounded text-sm font-bold">H3</button>
            <button type="button" @click="editor.chain().focus().setParagraph().run()" :class="{ 'bg-brand-cyan text-black': editor.isActive('paragraph') }" class="px-2 py-1 rounded text-sm">P</button>
        </div>
        <EditorContent :editor="editor" />
    </div>
    <InputError class="mt-2" :message="form.errors[fieldName]" />
</template>

<style>
/* CSS khusus untuk Tiptap agar terlihat rapi */
.ProseMirror:focus {
    outline: none;
}

.ProseMirror ul {
    list-style-type: disc;
    padding-left: 1.5rem;
}
</style>