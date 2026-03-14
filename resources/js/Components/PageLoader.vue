<template>
    <Transition name="fade">
        <div v-if="isLoading" class="page-loader">
            <div class="loader-content">
                <!-- Logo TripTrove Putih dengan animasi grayscale -->
                <div class="logo-container">
                    <!-- Single rotating ring -->
                    <div class="spinner-ring"></div>
                    
                    <!-- Logo dengan color animation -->
                    <img 
                        src="/images/logo_triptrove_putih.png" 
                        alt="TripTrove" 
                        class="logo-image"
                    />
                </div>
                
                <!-- Loading text dengan dots -->
                <div class="loading-text">
                    <span>{{ text }}</span>
                    <span class="dots">
                        <span class="dot">.</span>
                        <span class="dot">.</span>
                        <span class="dot">.</span>
                    </span>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
    text: {
        type: String,
        default: 'Loading'
    }
});

const isLoading = ref(false);

onMounted(() => {
    router.on('start', () => {
        isLoading.value = true;
    });

    router.on('finish', () => {
        setTimeout(() => {
            isLoading.value = false;
        }, 300);
    });
});
</script>

<style scoped>
/* Full page overlay dengan background hitam lebih transparan */
.page-loader {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loader-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

/* Logo container */
.logo-container {
    position: relative;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Single rotating ring */
.spinner-ring {
    position: absolute;
    width: 80px;
    height: 80px;
    border: 2.5px solid transparent;
    border-top-color: rgba(255, 255, 255, 0.7);
    border-radius: 50%;
    animation: spin 1.2s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Logo putih dengan animasi grayscale bolak-balik */
.logo-image {
    width: 50px;
    height: auto;
    position: relative;
    z-index: 2;
    filter: drop-shadow(0 2px 8px rgba(255, 255, 255, 0.3));
    animation: logoColorPulse 2s ease-in-out infinite;
}

/* Animasi warna: abu-abu ↔ warna asli (seperti hover effect) */
@keyframes logoColorPulse {
    0%, 100% {
        filter: grayscale(100%) brightness(0.8) drop-shadow(0 2px 8px rgba(255, 255, 255, 0.2));
    }
    50% {
        filter: grayscale(0%) brightness(1) drop-shadow(0 2px 8px rgba(255, 255, 255, 0.4));
    }
}

/* Loading text */
.loading-text {
    display: flex;
    align-items: center;
    gap: 2px;
    font-size: 15px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
    letter-spacing: 0.5px;
}

/* Animated dots */
.dots {
    display: inline-flex;
    gap: 2px;
}

.dot {
    animation: dotFade 1.4s ease-in-out infinite;
}

.dot:nth-child(1) {
    animation-delay: 0s;
}

.dot:nth-child(2) {
    animation-delay: 0.2s;
}

.dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes dotFade {
    0%, 60%, 100% {
        opacity: 0.3;
    }
    30% {
        opacity: 1;
    }
}

/* Fade transition */
.fade-enter-active {
    transition: opacity 0.2s ease;
}

.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Responsive */
@media (max-width: 640px) {
    .logo-container {
        width: 70px;
        height: 70px;
    }
    
    .spinner-ring {
        width: 70px;
        height: 70px;
    }
    
    .logo-image {
        width: 45px;
    }
    
    .loading-text {
        font-size: 14px;
    }
}
</style>
