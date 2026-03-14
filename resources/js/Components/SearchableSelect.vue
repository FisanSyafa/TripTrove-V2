<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    options: {
        type: Array,
        required: true
    },
    placeholder: {
        type: String,
        default: 'Select an option'
    },
    searchPlaceholder: {
        type: String,
        default: 'Search...'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    // Allows customize which property to use as value and label
    valueField: {
        type: String,
        default: 'value'
    },
    labelField: {
        type: String,
        default: 'label'
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);

// Get the selected label to display in the button
const selectedLabel = computed(() => {
    const selected = props.options.find(opt => opt[props.valueField] === props.modelValue);
    return selected ? selected[props.labelField] : props.placeholder;
});

// Filter options based on search query
const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    
    const query = searchQuery.value.toLowerCase();
    return props.options.filter(opt => {
        // Search through all string/number values in the option object
        return Object.values(opt).some(val => 
            String(val).toLowerCase().includes(query)
        );
    });
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
    }
};

const selectOption = (option) => {
    emit('update:modelValue', option[props.valueField]);
    emit('change', option);
    isOpen.value = false;
};

// Close when clicking outside
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});

// Auto-focus search input when opening
const searchInput = ref(null);
watch(isOpen, (newValue) => {
    if (newValue) {
        setTimeout(() => {
            if (searchInput.value) searchInput.value.focus();
        }, 50);
    }
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <!-- Trigger Button -->
        <button
            type="button"
            @click="toggleDropdown"
            class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-gray-300 rounded-lg shadow-sm text-left focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue transition-all"
            :class="{ 'opacity-50 cursor-not-allowed': disabled, 'border-brand-blue ring-2 ring-brand-blue/20': isOpen }"
        >
            <span class="block truncate text-gray-900" :class="{ 'text-gray-400': !modelValue }">
                <slot name="selection" :selected="options.find(opt => opt[valueField] === modelValue)">
                    {{ selectedLabel }}
                </slot>
            </span>
            <span class="pointer-events-none flex items-center">
                <svg
                    class="h-4 w-4 text-gray-400 transition-transform duration-200"
                    :class="{ 'rotate-180': isOpen }"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </span>
        </button>

        <!-- Dropdown Panel -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden"
            >
                <!-- Search Input -->
                <div class="p-3 border-b border-gray-50 bg-gray-50/30">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            ref="searchInput"
                            v-model="searchQuery"
                            type="text"
                            class="block w-full pl-10 pr-3 py-2 border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-blue/20 focus:border-brand-blue placeholder-gray-400"
                            :placeholder="searchPlaceholder"
                            @keydown.esc="isOpen = false"
                        />
                    </div>
                </div>

                <!-- Options List -->
                <ul class="max-h-60 overflow-y-auto py-1 text-sm text-gray-700">
                    <li v-if="filteredOptions.length === 0" class="px-4 py-6 text-center text-gray-500 italic">
                        No results found for "{{ searchQuery }}"
                    </li>
                    <li
                        v-for="opt in filteredOptions"
                        :key="opt[valueField]"
                        @click="selectOption(opt)"
                        class="px-4 py-2.5 cursor-pointer hover:bg-blue-50 hover:text-brand-blue flex items-center justify-between transition-colors"
                        :class="{ 'bg-blue-50 text-brand-blue font-semibold': opt[valueField] === modelValue }"
                    >
                        <span>
                            <slot name="option" :option="opt">
                                {{ opt[labelField] }}
                            </slot>
                        </span>
                        <svg
                            v-if="opt[valueField] === modelValue"
                            class="h-4 w-4"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </li>
                </ul>
            </div>
        </transition>
    </div>
</template>

<style scoped>
/* Optional: Custom scrollbar styling for the dropdown */
ul::-webkit-scrollbar {
    width: 6px;
}
ul::-webkit-scrollbar-track {
    background: transparent;
}
ul::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 3px;
}
ul::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
