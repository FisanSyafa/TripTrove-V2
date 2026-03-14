<template>
    <Transition name="fade">
        <div v-if="isLoading" class="loading-bar-container">
            <div class="loading-bar"></div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const isLoading = ref(false);

onMounted(() => {
    router.on('start', () => {
        isLoading.value = true;
    });

    router.on('finish', () => {
        isLoading.value = false;
    });
});
</script>

<style scoped>
.loading-bar-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    height: 3px;
    background: transparent;
}

.loading-bar {
    height: 100%;
    background: linear-gradient(90deg, #00d4ff, #007bff);
    animation: loading 1.5s ease-in-out infinite;
    box-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
}

@keyframes loading {
    0% {
        width: 0%;
        margin-left: 0%;
    }
    50% {
        width: 50%;
        margin-left: 25%;
    }
    100% {
        width: 0%;
        margin-left: 100%;
    }
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
