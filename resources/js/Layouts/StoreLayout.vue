<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

const showingNavigationDropdown = ref(false);
const showUserMenu = ref(false);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const cartCount = computed(() => page.props.cart?.total_items || 0);

const navItems = [
    { name: 'Shop', href: route('customer.home'), active: route().current('customer.home') },
    { name: 'Collections', href: '#', active: false },
    { name: 'About', href: '#', active: false },
    { name: 'Contact', href: '#', active: false },
];

const getInitials = (name) => {
    return name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U';
};
</script>

<template>
    <div>
        <FlashMessage />
        <div class="min-h-screen bg-white font-sans text-gray-900">
            <!-- Store Navbar -->
            <nav class="bg-white sticky top-0 z-50 transition-all duration-300">
                <div class="mx-auto max-w-[1440px] px-6 lg:px-12">
                    <div class="flex h-24 items-center justify-between relative">
                        
                        <!-- Left: Navigation -->
                        <div class="hidden md:flex items-center gap-8">
                            <Link
                                v-for="item in navItems"
                                :key="item.name"
                                :href="item.href"
                                class="text-sm font-bold uppercase tracking-wide hover:text-gray-500 transition-colors"
                                :class="item.active ? 'text-black' : 'text-gray-600'"
                            >
                                {{ item.name }}
                            </Link>
                        </div>

                        <!-- Mobile Menu Button -->
                         <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="md:hidden p-2 text-gray-900"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Center: Logo -->
                        <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <Link :href="route('customer.home')" class="text-3xl font-black tracking-tighter hover:opacity-80 transition-opacity">
                                MYSTORE
                            </Link>
                        </div>

                        <!-- Right: Actions -->
                        <div class="flex items-center gap-4">
                            <!-- Search (Visual Only) -->
                             <button class="p-2 text-gray-900 hover:text-gray-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>

                            <!-- User Info / Login -->
                            <div v-if="user" class="relative">
                                <button 
                                    @click="showUserMenu = !showUserMenu"
                                    class="flex items-center gap-2"
                                >
                                     <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center border border-gray-200">
                                        <img 
                                            v-if="user.avatar" 
                                            :src="user.avatar" 
                                            alt="User" 
                                            class="w-full h-full rounded-full object-cover"
                                        />
                                        <span v-else class="text-xs font-bold">{{ getInitials(user.name) }}</span>
                                    </div>
                                </button>
                                <!-- Dropdown -->
                                <div v-if="showUserMenu" @click.away="showUserMenu = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-1 z-50">
                                     <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</Link>
                                     <Link :href="route('orders.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My Orders</Link>
                                     <div class="border-t border-gray-100 my-1"></div>
                                     <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Log Out</Link>
                                </div>
                            </div>
                            <Link v-else :href="route('login')" class="text-sm font-bold uppercase hover:text-gray-500">
                                Login
                            </Link>

                            <!-- Cart -->
                            <Link 
                                :href="route('cart.index')"
                                class="flex items-center gap-2 bg-red-500 text-white px-4 py-2 rounded-full hover:bg-red-600 transition-all shadow-md hover:shadow-lg"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span class="text-sm font-bold" v-if="cartCount > 0">{{ cartCount }} Product{{ cartCount !== 1 ? 's' : '' }}</span>
                                <span class="text-sm font-bold" v-else>Cart</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div v-if="showingNavigationDropdown" class="md:hidden bg-white border-t border-gray-100">
                     <div class="px-4 py-3 space-y-1">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="item.href"
                            class="block px-4 py-3 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg"
                        >
                            {{ item.name }}
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
