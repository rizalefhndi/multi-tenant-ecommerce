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
        <div class="min-h-screen bg-white text-black selection:bg-black selection:text-white">
            <div class="max-w-[1440px] mx-auto px-6 lg:px-12 py-12">
                <!-- Header -->
                 <div class="flex flex-col md:flex-row items-start md:items-end justify-between mb-12 border-b-4 border-black pb-6 gap-4">
                    <h1 class="text-6xl md:text-8xl font-black text-black tracking-tighter leading-none uppercase">
                        YOUR <br/> <span class="bg-black text-white px-2">BAG</span>
                    </h1>
                     <p class="text-xl font-black text-black bg-gray-100 border-2 border-black px-4 py-2 uppercase tracking-widest">
                        {{ cart.total_items }} Items
                    </p>
                </div>

                <!-- Empty State -->
                <div v-if="cart.items.length === 0" class="py-24 text-center border-4 border-black shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] bg-white mt-12">
                    <div class="w-32 h-32 border-4 border-black flex items-center justify-center mx-auto mb-8 bg-gray-100">
                         <svg class="w-16 h-16 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h2 class="text-4xl font-black text-black mb-4 uppercase tracking-tighter">Your bag is empty</h2>
                    <p class="text-black font-bold mb-8 max-w-md mx-auto uppercase tracking-widest text-sm">Looks like you haven't found your style yet. Explore our collection to find something unique.</p>
                    <Link
                        :href="shopRoute"
                        class="inline-flex px-12 py-6 bg-white border-4 border-black text-black text-xl font-black uppercase tracking-widest shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:bg-black hover:text-white hover:shadow-none hover:translate-x-[8px] hover:translate-y-[8px] transition-none"
                    >
                        Start Shopping
                    </Link>
                </div>

                <!-- Content Grid -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 relative">
                    <!-- Items List (Left) -->
                    <div class="lg:col-span-8">
                        <div class="flex justify-between items-center mb-6">
                             <a 
                                href="#" 
                                @click.prevent="clearCart" 
                                class="text-sm font-black text-white bg-red-600 border-4 border-red-600 px-4 py-2 uppercase tracking-widest hover:bg-black hover:border-black transition-none shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]"
                            >
                                Clear All
                            </a>
                        </div>
                        <div class="divide-y-4 divide-black border-t-4 border-b-4 border-black">
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
                             <div class="bg-white border-4 border-black p-8 shadow-[12px_12px_0px_0px_rgba(0,0,0,1)]">
                                <h3 class="text-3xl font-black uppercase tracking-tighter text-black mb-8 pb-4 border-b-4 border-black">Summary</h3>
                                
                                <div class="space-y-6 mb-8 font-bold text-lg">
                                    <div class="flex justify-between text-black">
                                        <span>Subtotal</span>
                                        <span class="font-black">{{ cart.formatted_total }}</span>
                                    </div>
                                    <div class="flex justify-between text-black">
                                        <span>Shipping</span>
                                        <span class="text-black font-black uppercase text-sm border-2 border-black px-2 py-1">Free</span>
                                    </div>
                                    <div class="flex justify-between text-black">
                                        <span>Tax</span>
                                        <span class="text-black font-black uppercase text-sm border-2 border-black px-2 py-1">Included</span>
                                    </div>
                                </div>

                                <div class="border-t-4 border-black pt-6 mb-8">
                                    <div class="flex justify-between items-end">
                                        <span class="text-xl font-black uppercase tracking-widest text-black">Total</span>
                                        <span class="text-4xl font-black text-black tracking-tight">
                                            {{ cart.formatted_total }}
                                        </span>
                                    </div>
                                </div>

                                <Link
                                    :href="route('checkout.index')"
                                    class="w-full flex items-center justify-center py-6 bg-black border-4 border-black text-white text-xl font-black uppercase tracking-widest shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:bg-white hover:text-black hover:shadow-none hover:translate-x-[8px] hover:translate-y-[8px] transition-none mb-6"
                                >
                                    Checkout
                                </Link>
                                
                                <p class="text-xs text-center text-black font-black uppercase tracking-widest flex justify-center items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
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
