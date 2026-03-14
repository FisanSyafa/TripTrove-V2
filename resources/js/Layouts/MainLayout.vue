<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import PageLoader from '@/Components/PageLoader.vue';

const scrolled = ref(false);
const showingNavigationDropdown = ref(false);

const handleScroll = () => {
    scrolled.value = window.scrollY > 50;
};

const navigateToSection = (sectionId) => {
    showingNavigationDropdown.value = false;
    
    // Jika sudah di halaman home, langsung scroll
    if (route().current('home')) {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    } else {
        // Navigasi ke home, lalu scroll setelah halaman dimuat
        router.visit(route('home'), {
            onSuccess: () => {
                setTimeout(() => {
                    const element = document.getElementById(sectionId);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth' });
                    }
                }, 100);
            }
        });
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    
    // Handle hash on page load
    if (window.location.hash) {
        setTimeout(() => {
            const element = document.getElementById(window.location.hash.substring(1));
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }, 100);
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <PageLoader text="Loading" />
    <Head title="TripTrove" />
    <div class="bg-brand-dark text-white min-h-screen font-sans">
        <header 
            :class="['fixed top-0 left-0 right-0 z-50 transition-all duration-300', 
                     { 'bg-gray-900/95 backdrop-blur-sm shadow-lg border-b border-white/10 py-3': scrolled, 
                       'bg-gray-900 border-b border-white/5 py-5': !scrolled }]"
        >
            <nav class="container mx-auto px-4 md:px-6">
                <div class="flex justify-between items-center">
                    
                    <div class="shrink-0 flex items-center">
                        <Link :href="route('home')" class="flex items-center gap-2 group">
                            <img src="/images/logo_triptrove_putih.png" alt="TripTrove Logo" class="h-8 md:h-10 w-auto transition-transform duration-300 group-hover:scale-105">
                            <span class="text-xl md:text-2xl font-bold bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text">
                                TripTrove
                            </span>
                        </Link>
                    </div>

                    <div class="flex items-center gap-2 md:gap-6">
                        
                        <div class="hidden md:flex items-center space-x-6">
                            <Link :href="route('home')" class="text-sm font-semibold text-gray-300 hover:text-brand-cyan transition-colors">{{ __('Home') }}</Link>
                            <button @click="navigateToSection('features')" class="text-sm font-semibold text-gray-300 hover:text-brand-cyan transition-colors">{{ __('Features') }}</button>
                            <button @click="navigateToSection('faq')" class="text-sm font-semibold text-gray-300 hover:text-brand-cyan transition-colors">{{ __('FAQ') }}</button>
                            <button @click="navigateToSection('contact')" class="text-sm font-semibold text-gray-300 hover:text-brand-cyan transition-colors">{{ __('Contact') }}</button>
                            
                            <div v-if="$page.props.auth.user" class="inline-block ml-2">
                                <Link :href="route('dashboard')" class="px-4 py-2 bg-brand-blue hover:bg-blue-600 rounded-full text-sm font-bold transition-all shadow-lg shadow-brand-blue/20">
                                    Dashboard
                                </Link>
                            </div>
                            <div v-else class="flex items-center gap-4 ml-2">
                                <Link :href="route('login')" class="text-sm font-semibold text-gray-300 hover:text-white transition-colors">{{ __('Log in') }}</Link>
                                <Link :href="route('register')" class="px-4 py-2 bg-white text-brand-dark hover:bg-gray-100 rounded-full text-sm font-bold transition-all">
                                    {{ __('Register') }}
                                </Link>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            
                            <div class="hidden md:block h-6 w-px bg-gray-700 mx-2"></div>

                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex items-center justify-center p-2 rounded-lg hover:bg-white/10 transition text-gray-300">
                                            <span class="text-xs font-bold bg-gray-800/80 border border-gray-600 px-1.5 py-0.5 rounded">
                                                {{ $page.props.currency || 'IDR' }}
                                            </span>
                                            <svg class="ml-1 h-3 w-3 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('currency.switch', 'IDR')" as="button" class="flex justify-between"><span>Rupiah</span> <span class="text-gray-400 text-xs">IDR</span></DropdownLink>
                                        <DropdownLink :href="route('currency.switch', 'USD')" as="button" class="flex justify-between"><span>Dollar</span> <span class="text-gray-400 text-xs">USD</span></DropdownLink>
                                        <DropdownLink :href="route('currency.switch', 'MYR')" as="button" class="flex justify-between"><span>Ringgit</span> <span class="text-gray-400 text-xs">MYR</span></DropdownLink>
                                        <DropdownLink :href="route('currency.switch', 'SGD')" as="button" class="flex justify-between"><span>SGD</span> <span class="text-gray-400 text-xs">SGD</span></DropdownLink>
                                        <DropdownLink :href="route('currency.switch', 'EUR')" as="button" class="flex justify-between"><span>Euro</span> <span class="text-gray-400 text-xs">EUR</span></DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>

                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex items-center justify-center p-2 rounded-lg hover:bg-white/10 transition text-gray-300">
                                            <img v-if="$page.props.locale === 'id'" src="https://flagcdn.com/w40/id.png" alt="ID" class="h-3.5 md:h-4 rounded-[1px]">
                                            <img v-else-if="$page.props.locale === 'ms'" src="https://flagcdn.com/w40/my.png" alt="MS" class="h-3.5 md:h-4 rounded-[1px]">
                                            <img v-else src="https://flagcdn.com/w40/us.png" alt="EN" class="h-3.5 md:h-4 rounded-[1px]">
                                            
                                            <span class="ml-1.5 text-xs font-bold hidden sm:inline">
                                                {{ $page.props.locale === 'id' ? 'ID' : ($page.props.locale === 'ms' ? 'MS' : 'EN') }}
                                            </span>
                                            <svg class="ml-1 h-3 w-3 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('lang.switch', 'id')" as="button" class="flex items-center"><img src="https://flagcdn.com/w40/id.png" class="h-3 mr-2"> Indonesia</DropdownLink>
                                        <DropdownLink :href="route('lang.switch', 'en')" as="button" class="flex items-center"><img src="https://flagcdn.com/w40/us.png" class="h-3 mr-2"> English</DropdownLink>
                                        <DropdownLink :href="route('lang.switch', 'ms')" as="button" class="flex items-center"><img src="https://flagcdn.com/w40/my.png" class="h-3 mr-2"> Malaysia</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>

                            <div class="-mr-2 flex items-center md:hidden ml-1">
                                <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-200 hover:text-white hover:bg-gray-800 focus:outline-none transition duration-150 ease-in-out">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                        </div> </div>
                </div>
            </nav>

            <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="md:hidden bg-gray-900 border-b border-gray-700 shadow-2xl">
                <div class="pt-2 pb-3 space-y-1 px-4">
                    <ResponsiveNavLink :href="route('home')" :active="route().current('home')" class="text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg">
                        {{ __('Home') }}
                    </ResponsiveNavLink>
                    <button @click="navigateToSection('features')" class="block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800 hover:border-brand-cyan transition duration-150 ease-in-out">
                        {{ __('Features') }}
                    </button>
                    <button @click="navigateToSection('faq')" class="block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800 hover:border-brand-cyan transition duration-150 ease-in-out">
                        {{ __('FAQ') }}
                    </button>
                    <button @click="navigateToSection('contact')" class="block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800 hover:border-brand-cyan transition duration-150 ease-in-out">
                        {{ __('Contact') }}
                    </button>
                </div>

                <div class="pt-4 pb-6 border-t border-gray-700">
                    <div v-if="$page.props.auth.user">
                        <div class="px-4 mb-3">
                            <div class="font-medium text-base text-gray-200">{{ $page.props.auth.user.name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>
                        <div class="space-y-1 px-2">
                            <ResponsiveNavLink :href="route('dashboard')" class="text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg">Dashboard</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('profile.edit')" class="text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg">Profile</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                    <div v-else class="px-4 space-y-3">
                        <Link :href="route('login')" class="block w-full text-center px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-800 hover:text-white transition">
                            {{ __('Log in') }}
                        </Link>
                        <Link :href="route('register')" class="block w-full text-center px-4 py-2 bg-brand-blue text-white rounded-lg font-bold hover:bg-blue-600 transition shadow-lg">
                            {{ __('Register') }}
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <main class="pt-20"> 
            <slot />
        </main>

        <footer class="bg-gradient-to-r from-gray-900 via-brand-dark to-gray-900 border-t border-brand-border mt-0 py-12">
            <div class="container mx-auto text-center">
                <div class="flex justify-center items-center mb-6">
                    <img src="/images/logo_triptrove_putih.png" alt="TripTrove" class="h-10 opacity-80 grayscale hover:grayscale-0 transition-all duration-500">
                </div>
                <p class="text-gray-500 text-sm">
                    &copy; {{ new Date().getFullYear() }} TripTrove. All rights reserved. <br>
                    Designed with <span class="text-red-500">❤️</span> for travelers.
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Tambahan untuk memastikan ResponsiveNavLink terlihat bagus di dark mode jika komponennya belum support */
:deep(.border-indigo-400) {
    border-color: #00d4ff !important;
}
:deep(.text-indigo-700) {
    color: #00d4ff !important;
}
:deep(.bg-indigo-50) {
    background-color: rgba(0, 212, 255, 0.1) !important;
}
</style>