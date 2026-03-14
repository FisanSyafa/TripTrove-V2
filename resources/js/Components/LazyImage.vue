<template>
    <div class="lazy-image-wrapper" :style="{ paddingBottom: aspectRatio }">
        <img
            v-if="isLoaded"
            :src="src"
            :alt="alt"
            :class="['lazy-image', 'loaded', imgClass]"
            @load="onLoad"
        />
        <img
            v-else
            :data-src="src"
            :alt="alt"
            :class="['lazy-image', 'loading', imgClass]"
            ref="imageRef"
        />
        <div v-if="!isLoaded" class="lazy-image-placeholder">
            <div class="spinner"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    src: {
        type: String,
        required: true
    },
    alt: {
        type: String,
        default: ''
    },
    aspectRatio: {
        type: String,
        default: '56.25%' // 16:9 default
    },
    imgClass: {
        type: String,
        default: ''
    }
});

const imageRef = ref(null);
const isLoaded = ref(false);
let observer = null;

const loadImage = () => {
    if (imageRef.value) {
        const img = imageRef.value;
        img.src = img.dataset.src;
        img.onload = () => {
            isLoaded.value = true;
        };
    }
};

const onLoad = () => {
    isLoaded.value = true;
};

onMounted(() => {
    if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadImage();
                    if (observer && imageRef.value) {
                        observer.unobserve(imageRef.value);
                    }
                }
            });
        }, {
            rootMargin: '50px'
        });

        if (imageRef.value) {
            observer.observe(imageRef.value);
        }
    } else {
        // Fallback untuk browser yang tidak support IntersectionObserver
        loadImage();
    }
});

onUnmounted(() => {
    if (observer && imageRef.value) {
        observer.unobserve(imageRef.value);
    }
});
</script>

<style scoped>
.lazy-image-wrapper {
    position: relative;
    overflow: hidden;
    background: #f0f0f0;
    border-radius: inherit;
}

.lazy-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.3s ease;
}

.lazy-image.loading {
    opacity: 0;
}

.lazy-image.loaded {
    opacity: 1;
}

.lazy-image-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e0e0e0;
    border-top-color: #007bff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
