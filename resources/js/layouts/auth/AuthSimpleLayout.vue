<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-svh flex">
        <!-- Left side - Form -->
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-12 lg:px-8 bg-gradient-to-br from-pink-100 via-purple-50 to-blue-100">
            <div class="w-full max-w-md">
                <div class="flex flex-col gap-8">
                    <!-- Logo -->
                    <div class="flex flex-col items-center gap-4">
                        <Link :href="home()" class="flex items-center gap-3">
                            <img src="/images/logo.png" alt="Cheku Left" class="h-12 w-12 object-contain" />
                            <span class="text-2xl font-bold bg-gradient-to-r from-orange-500 to-orange-600 bg-clip-text text-transparent">
                                Cheku Left
                            </span>
                        </Link>
                    </div>
                    
                    <!-- Title -->
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold text-gray-900">{{ title }}</h1>
                        <p v-if="description" class="text-sm text-gray-500">
                            {{ description }}
                        </p>
                    </div>
                    
                    <!-- Form slot -->
                    <slot />
                </div>
            </div>
        </div>

        <!-- Right side - Illustration (hidden on mobile) -->
        <div class="hidden lg:flex flex-1 items-center justify-center relative overflow-hidden">
            <!-- Gradient background -->
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500"></div>
            
            <!-- Decorative shapes -->
            <div class="absolute inset-0 overflow-hidden">
                <!-- Large blob -->
                <div class="absolute -top-1/4 -right-1/4 w-[800px] h-[800px] rounded-full bg-gradient-to-br from-blue-400/30 to-purple-500/30 blur-3xl"></div>
                <div class="absolute -bottom-1/4 -left-1/4 w-[600px] h-[600px] rounded-full bg-gradient-to-tr from-pink-400/30 to-indigo-500/30 blur-3xl"></div>
                
                <!-- Floating elements -->
                <div class="absolute top-20 left-20 w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-sm animate-pulse"></div>
                <div class="absolute top-40 right-32 w-12 h-12 rounded-full bg-yellow-400/60 animate-bounce" style="animation-duration: 3s;"></div>
                <div class="absolute bottom-32 left-40 w-10 h-10 rounded-lg bg-pink-400/60 animate-pulse" style="animation-delay: 1s;"></div>
                <div class="absolute bottom-48 right-20 w-14 h-14 rounded-full bg-blue-400/60 animate-bounce" style="animation-duration: 4s;"></div>
                
                <!-- Icons/badges floating -->
                <div class="absolute top-1/4 left-1/3 p-3 rounded-xl bg-white/20 backdrop-blur-sm shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div class="absolute top-1/2 right-1/4 p-3 rounded-xl bg-white/20 backdrop-blur-sm shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="absolute bottom-1/3 left-1/4 p-3 rounded-xl bg-white/20 backdrop-blur-sm shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            
            <!-- Center content -->
            <div class="relative z-10 text-center text-white px-12">
                <h2 class="text-4xl font-bold mb-4">Manage Your Butchery</h2>
                <p class="text-lg text-white/80">Track sales, manage stock, and grow your business with Cheku Left POS system.</p>
            </div>
        </div>
    </div>
</template>
