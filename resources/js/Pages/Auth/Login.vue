<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PageLoader from '@/Components/PageLoader.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { getCurrentInstance } from 'vue';

// Helper Global
const app = getCurrentInstance();
const __ = (key) => app.appContext.config.globalProperties.__(key) || key;

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <PageLoader :text="__('Loading')" />
    <Head :title="__('Log in')" />

    <div class="min-h-screen flex">
        <!-- Left Side - Image/Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-gray-900 via-gray-800 to-black relative overflow-hidden">
            <!-- Animated background circles -->
            <div class="absolute inset-0">
                <div class="absolute top-0 left-0 w-96 h-96 bg-brand-cyan/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-brand-blue/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
            </div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center items-center w-full p-12 text-white">
                <div class="max-w-md text-center">
                    <!-- Logo -->
                    <img src="/images/logo_triptrove_putih.png" alt="TripTrove" class="w-32 mx-auto mb-8 drop-shadow-2xl">
                    
                    <!-- Brand Name -->
                    <h1 class="text-5xl font-bold mb-4 bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text">
                        TripTrove
                    </h1>
                    
                    <!-- Tagline -->
                    <p class="text-xl text-gray-300 mb-8">
                        {{ __('Your Trusted Travel Partner') }}
                    </p>
                    
                    <!-- Features -->
                    <div class="space-y-4 text-left">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-cyan/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-300">{{ __('Best Price Guarantee') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-cyan/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-300">{{ __('Professional Guides') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-cyan/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-brand-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-300">{{ __('Safe & Trusted') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <img src="/images/logo_triptrove_hitam.png" alt="TripTrove" class="w-24 mx-auto mb-4">
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text">
                        TripTrove
                    </h2>
                </div>

                <!-- Welcome Text -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Welcome back!') }}</h2>
                    <p class="text-gray-600">{{ __('Please enter your details to sign in') }}</p>
                </div>

                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                    {{ $page.props.flash.error }}
                </div>

                <div v-if="status" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-600">
                    {{ status }}
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" :value="__('Email')" class="text-gray-700 font-semibold" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-cyan focus:border-transparent"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="your@email.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" :value="__('Password')" class="text-gray-700 font-semibold" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-cyan focus:border-transparent"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-brand-blue hover:text-brand-cyan font-semibold"
                        >
                            {{ __('Forgot your password?') }}
                        </Link>
                    </div>

                    <PrimaryButton
                        class="w-full justify-center py-3 bg-gradient-to-r from-brand-cyan to-brand-blue hover:from-brand-blue hover:to-brand-cyan transition-all duration-300"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ __('Login') }}
                    </PrimaryButton>
                </form>

                <!-- Divider -->
                <div class="my-6 relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-gray-50 text-gray-500">{{ __('Or continue with') }}</span>
                    </div>
                </div>

                <!-- Google Login -->
                <a 
                    :href="route('auth.google.redirect')"
                    class="w-full inline-flex justify-center items-center px-4 py-3 bg-white border-2 border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200"
                >
                    <svg class="w-5 h-5 me-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                        <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                        <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.223,0-9.641-3.357-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                        <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.574l6.19,5.238C39.712,35.619,44,29.561,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                    </svg>
                    {{ __('Log in with Google') }}
                </a>

                <!-- Register Link -->
                <p class="mt-8 text-center text-sm text-gray-600">
                    {{ __("Don't have an account?") }}
                    <Link :href="route('register')" class="font-semibold text-brand-blue hover:text-brand-cyan">
                        {{ __('Register') }}
                    </Link>
                </p>

                <!-- Back to Home -->
                <div class="mt-6 text-center">
                    <Link :href="route('home')" class="text-sm text-gray-500 hover:text-gray-700 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        {{ __('Back to Home') }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.from-brand-cyan { --tw-gradient-from: #00d4ff; }
.to-brand-blue { --tw-gradient-to: #007bff; }
.text-brand-blue { color: #007bff; }
.text-brand-cyan { color: #00d4ff; }
.bg-brand-cyan\/10 { background-color: rgba(0, 212, 255, 0.1); }
.bg-brand-cyan\/20 { background-color: rgba(0, 212, 255, 0.2); }
.bg-brand-blue\/10 { background-color: rgba(0, 123, 255, 0.1); }
</style>
