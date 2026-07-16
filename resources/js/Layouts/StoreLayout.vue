<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

const showingNavigationDropdown = ref(false);
const showUserMenu = ref(false);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin' || user.value?.role === 'superadmin');
const cartCount = computed(() => page.props.cart?.total_items || 0);

const navItems = [
    { name: 'Shop', href: route('customer.home'), active: route().current('customer.home') },
    // Temporarily removing placeholders so user doesn't get confused by unclickable links
    // { name: 'Collections', href: '#', active: false },
    // { name: 'About', href: '#', active: false },
    // { name: 'Contact', href: '#', active: false },
];

const getInitials = (name) => {
    return name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U';
};
</script>

<template>
    <div>
        <FlashMessage />
        <div class="min-h-screen bg-white font-sans text-black">
            <!-- Store Navbar (Brutalist) -->
            <nav class="bg-white sticky top-0 z-50 border-b-4 border-black">
                <div class="mx-auto max-w-[1440px] px-6 lg:px-12">
                    <div class="grid grid-cols-3 h-20 md:h-24 items-center gap-2 md:gap-4">
                        
                        <!-- Left: Navigation / Mobile Menu -->
                        <div class="flex items-center justify-start">
                            <div class="hidden md:flex items-center gap-8">
                                <Link
                                    v-for="item in navItems"
                                    :key="item.name"
                                    :href="item.href"
                                    class="text-sm font-black uppercase tracking-widest hover:underline underline-offset-8 decoration-4"
                                    :class="item.active ? 'underline' : ''"
                                >
                                    {{ item.name }}
                                </Link>
                            </div>

                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="md:hidden p-2 border-4 border-black hover:bg-black hover:text-white"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>

                        <!-- Center: Logo -->
                        <div class="flex items-center justify-center">
                            <Link :href="route('customer.home')" class="text-2xl md:text-4xl font-black tracking-tighter uppercase text-center">
                                MYSTORE
                            </Link>
                        </div>

                        <!-- Right: Actions -->
                        <div class="flex items-center justify-end gap-2 md:gap-4">
                            <!-- Search -->
                             <button class="hidden sm:block p-2 border-4 border-black hover:bg-black hover:text-white transition-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>

                            <!-- User Info / Login -->
                            <div v-if="user" class="relative">
                                <button 
                                    @click="showUserMenu = !showUserMenu"
                                    class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 border-4 border-black hover:bg-black hover:text-white transition-none font-black"
                                >
                                    <img 
                                        v-if="user.avatar" 
                                        :src="user.avatar" 
                                        alt="User" 
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else class="text-sm md:text-base">{{ getInitials(user.name) }}</span>
                                </button>
                                <!-- Brutalist Dropdown -->
                                <div v-if="showUserMenu" @click.away="showUserMenu = false" class="absolute right-0 mt-2 w-48 bg-white border-4 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] z-50 overflow-hidden">
                                     <Link v-if="isAdmin" :href="route('admin.dashboard')" class="block px-4 py-3 text-sm font-black uppercase border-b-4 border-black hover:bg-black hover:text-white transition-none">Admin Panel</Link>
                                     <Link :href="route('profile.edit')" class="block px-4 py-3 text-sm font-bold uppercase border-b-4 border-black hover:bg-black hover:text-white transition-none">Profile</Link>
                                     <Link :href="route('orders.index')" class="block px-4 py-3 text-sm font-bold uppercase border-b-4 border-black hover:bg-black hover:text-white transition-none">My Orders</Link>
                                     <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-3 text-sm font-bold uppercase text-red-600 hover:bg-red-600 hover:text-white transition-none">Log Out</Link>
                                </div>
                            </div>
                            <Link v-else :href="route('login')" class="text-sm font-black uppercase tracking-widest hover:underline underline-offset-8 decoration-4">
                                Login
                            </Link>

                            <!-- Cart -->
                            <Link 
                                :href="route('cart.index')"
                                class="flex items-center gap-1 md:gap-2 border-4 border-black bg-white text-black px-2 md:px-4 py-1.5 md:py-2 hover:bg-black hover:text-white transition-none font-black uppercase tracking-widest md:shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-none md:hover:translate-x-[4px] md:hover:translate-y-[4px]"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span v-if="cartCount > 0" class="text-sm md:text-base">{{ cartCount }}</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div v-if="showingNavigationDropdown" class="md:hidden bg-white border-t-4 border-black">
                     <div class="flex flex-col">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="item.href"
                            class="block px-6 py-4 text-base font-black uppercase tracking-widest border-b-4 border-black hover:bg-black hover:text-white transition-none"
                            :class="item.active ? 'bg-black text-white' : 'text-black'"
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
            
            <!-- Brutalist Footer -->
            <footer class="bg-white border-t-4 border-black py-12 px-6 mt-20">
                <div class="max-w-[1440px] mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="font-black text-3xl tracking-tighter uppercase">MYSTORE</div>
                    <div class="text-sm font-black uppercase tracking-widest">
                        © 2026. BRUTALIST EMPIRE.
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>
