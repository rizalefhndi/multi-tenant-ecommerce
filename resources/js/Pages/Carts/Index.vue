<script setup>
import StoreLayout from '@/Layouts/StoreLayout.vue';
import CartItemComponent from '@/Components/CartItemComponent.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    cart: Object,
});

const page = usePage();
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');
const shopRoute = computed(() => isAdmin.value ? route('products.index') : route('customer.home'));

const clearCart = () => {
    if (confirm('Are you sure you want to clear your cart?')) {
        router.delete(route('cart.clear'), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Shopping Bag" />

    <StoreLayout>
        <div class="min-h-screen bg-white">
            <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-12 py-12">
                <!-- Header -->
                 <div class="flex items-end justify-between mb-12 border-b border-gray-100 pb-6">
                    <h1 class="text-6xl sm:text-7xl md:text-8xl font-black text-black tracking-tighter leading-none">
                        YOUR BAG
                    </h1>
                     <p class="text-xl font-bold text-gray-500 mb-2 uppercase tracking-wide">
                        {{ cart.total_items }} Items
                    </p>
                </div>

                <!-- Empty State -->
                <div v-if="cart.items.length === 0" class="py-24 text-center">
                    <div class="w-64 h-64 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-8">
                         <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Your bag is empty</h2>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">Looks like you haven't found your style yet. Explore our collection to find something unique.</p>
                    <Link
                        :href="shopRoute"
                        class="inline-block px-12 py-4 bg-black text-white text-lg font-bold uppercase tracking-wider rounded-full hover:scale-105 transition-transform"
                    >
                        Start Shopping
                    </Link>
                </div>

                <!-- Content Grid -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-24 relative">
                    <!-- Items List (Left) -->
                    <div class="lg:col-span-8">
                        <div class="flex justify-between items-center mb-6">
                             <a 
                                href="#" 
                                @click.prevent="clearCart" 
                                class="text-xs font-bold text-red-500 uppercase tracking-widest hover:text-red-600 transition-colors"
                            >
                                Clear All
                            </a>
                        </div>
                        <div class="divide-y divide-gray-100 border-t border-gray-100">
                            <CartItemComponent
                                v-for="item in cart.items"
                                :key="item.id"
                                :item="item"
                            />
                        </div>
                    </div>

                    <!-- Summary (Right - Sticky) -->
                    <div class="lg:col-span-4 relative">
                        <div class="sticky top-32">
                             <div class="bg-gray-50 rounded-3xl p-8">
                                <h3 class="text-2xl font-black uppercase tracking-tight text-gray-900 mb-8">Summary</h3>
                                
                                <div class="space-y-4 mb-8">
                                    <div class="flex justify-between text-gray-600 font-medium">
                                        <span>Subtotal</span>
                                        <span class="text-gray-900 font-bold">{{ cart.formatted_total }}</span>
                                    </div>
                                    <div class="flex justify-between text-gray-600 font-medium">
                                        <span>Shipping</span>
                                        <span class="text-green-600 font-bold uppercase text-sm">Free</span>
                                    </div>
                                    <div class="flex justify-between text-gray-600 font-medium">
                                        <span>Tax</span>
                                        <span class="text-gray-900 font-bold">Included</span>
                                    </div>
                                </div>

                                <div class="border-t border-gray-200 pt-6 mb-8">
                                    <div class="flex justify-between items-end">
                                        <span class="text-lg font-black uppercase tracking-wide text-gray-400">Total</span>
                                        <span class="text-3xl font-black text-black tracking-tight">
                                            {{ cart.formatted_total }}
                                        </span>
                                    </div>
                                </div>

                                <Link
                                    :href="route('checkout.index')"
                                    class="w-full block text-center py-5 bg-black text-white text-lg font-black uppercase tracking-wider rounded-full hover:scale-[1.02] active:scale-[0.98] transition-all shadow-xl shadow-black/20 mb-4"
                                >
                                    Checkout
                                </Link>
                                
                                <p class="text-xs text-center text-gray-400 font-bold uppercase tracking-widest">
                                    Secure Payment • Free Returns
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StoreLayout>
</template>
