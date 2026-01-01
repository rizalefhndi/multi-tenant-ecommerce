<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

const showingNavigationDropdown = ref(false);
const showUserMenu = ref(false);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const cartCount = computed(() => page.props.cart?.total_items || 0);

const isLandlord = computed(() => {
    return page.url.startsWith('/landlord') || route().current('landlord.*');
});

const isAdmin = computed(() => {
    return user.value?.role === 'admin';
});

const navItems = computed(() => {
    if (isLandlord.value) {
        return [
            { name: 'Dashboard', href: route('landlord.dashboard'), active: route().current('landlord.dashboard'), icon: 'home' },
            { name: 'Tenants', href: route('landlord.tenants.index'), active: route().current('landlord.tenants.*'), icon: 'users' },
        ];
    }
    
    // Customer navigation
    if (!isAdmin.value) {
        return [
            { name: 'Shop', href: route('customer.home'), active: route().current('customer.home'), icon: 'shopping-bag' },
            { name: 'My Orders', href: route('orders.index'), active: route().current('orders.*'), icon: 'clipboard' },
        ];
    }
    
    // Admin navigation
    return [
        { name: 'Dashboard', href: route('dashboard'), active: route().current('dashboard'), icon: 'home' },
        { name: 'Products', href: route('products.index'), active: route().current('products.*'), icon: 'box' },
        { name: 'Orders', href: route('admin.orders.index'), active: route().current('admin.orders.*') || route().current('orders.*'), icon: 'shopping-bag' },
        { name: 'Analytics', href: route('admin.analytics.index'), active: route().current('admin.analytics.*'), icon: 'chart' },
    ];
});

const userMenuItems = computed(() => {
    if (isAdmin.value) {
        return [
            { name: 'Profile', href: route('profile.edit'), icon: 'user' },
            { name: 'Settings', href: route('settings.index'), icon: 'settings' },
            { name: 'Subscription', href: route('subscription.index'), icon: 'credit-card' },
        ];
    }
    // Customer menu
    return [
        { name: 'Profile', href: route('profile.edit'), icon: 'user' },
        { name: 'Addresses', href: route('addresses.index'), icon: 'map-pin' },
    ];
});

const getInitials = (name) => {
    return name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U';
};
</script>

<template>
    <div>
        <FlashMessage />
        <div class="min-h-screen bg-gray-50">
            <!-- Modern Navbar -->
            <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        
                        <!-- Left: Logo + Navigation -->
                        <div class="flex items-center gap-8">
                            <!-- Logo -->
                            <Link :href="route('dashboard')" class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="hidden sm:block font-bold text-gray-900">ONYX</span>
                            </Link>

                            <!-- Desktop Navigation -->
                            <div class="hidden md:flex items-center gap-1">
                                <Link
                                    v-for="item in navItems"
                                    :key="item.name"
                                    :href="item.href"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                                    :class="item.active 
                                        ? 'bg-indigo-50 text-indigo-700' 
                                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
                                >
                                    {{ item.name }}
                                </Link>
                            </div>
                        </div>

                        <!-- Right: Actions -->
                        <div class="flex items-center gap-3">
                            <!-- Cart Button -->
                            <Link 
                                v-if="!isLandlord"
                                :href="route('cart.index')"
                                class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span 
                                    v-if="cartCount > 0"
                                    class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
                                >
                                    {{ cartCount > 9 ? '9+' : cartCount }}
                                </span>
                            </Link>

                            <!-- Notifications -->
                            <button class="hidden sm:flex p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors relative">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>

                            <!-- User Menu -->
                            <div class="relative">
                                <button 
                                    @click="showUserMenu = !showUserMenu"
                                    class="flex items-center gap-3 p-1.5 pr-3 rounded-full hover:bg-gray-100 transition-colors"
                                >
                                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                        {{ getInitials(user?.name) }}
                                    </div>
                                    <span class="hidden sm:block text-sm font-medium text-gray-700">{{ user?.name }}</span>
                                    <svg class="hidden sm:block w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- User Dropdown -->
                                <Transition
                                    enter-active-class="transition ease-out duration-100"
                                    enter-from-class="transform opacity-0 scale-95"
                                    enter-to-class="transform opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-75"
                                    leave-from-class="transform opacity-100 scale-100"
                                    leave-to-class="transform opacity-0 scale-95"
                                >
                                    <div 
                                        v-if="showUserMenu"
                                        @click.away="showUserMenu = false"
                                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50"
                                    >
                                        <!-- User Info -->
                                        <div class="px-4 py-3 border-b border-gray-100">
                                            <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                                        </div>

                                        <!-- Menu Items -->
                                        <div class="py-1">
                                            <Link
                                                v-for="item in userMenuItems"
                                                :key="item.name"
                                                :href="item.href"
                                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                                @click="showUserMenu = false"
                                            >
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path v-if="item.icon === 'user'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    <path v-if="item.icon === 'settings'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path v-if="item.icon === 'credit-card'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                </svg>
                                                {{ item.name }}
                                            </Link>
                                        </div>

                                        <!-- Logout -->
                                        <div class="border-t border-gray-100 pt-1">
                                            <Link
                                                :href="route('logout')"
                                                method="post"
                                                as="button"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                                @click="showUserMenu = false"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                Keluar
                                            </Link>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <!-- Mobile Menu Button -->
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="md:hidden p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <svg v-if="!showingNavigationDropdown" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div v-if="showingNavigationDropdown" class="md:hidden border-t border-gray-100 bg-white">
                        <div class="px-4 py-3 space-y-1">
                            <Link
                                v-for="item in navItems"
                                :key="item.name"
                                :href="item.href"
                                class="block px-4 py-3 rounded-lg text-base font-medium transition-colors"
                                :class="item.active 
                                    ? 'bg-indigo-50 text-indigo-700' 
                                    : 'text-gray-600 hover:bg-gray-50'"
                                @click="showingNavigationDropdown = false"
                            >
                                {{ item.name }}
                            </Link>
                        </div>
                    </div>
                </Transition>
            </nav>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
