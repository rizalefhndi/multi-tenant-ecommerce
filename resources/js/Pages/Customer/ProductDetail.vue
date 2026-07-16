<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue';

const props = defineProps({
    product: Object,
    relatedProducts: Array,
});

const quantity = ref(1);
const isAddingToCart = ref(false);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const increaseQuantity = () => {
    if (quantity.value < props.product.stock) {
        quantity.value++;
    }
};

const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const addToCart = () => {
    isAddingToCart.value = true;
    router.post(route('cart.add'), {
        product_id: props.product.id,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isAddingToCart.value = false;
        },
    });
};
</script>

<template>
    <Head :title="product.name" />

    <StoreLayout>
        <div class="min-h-screen bg-white text-black selection:bg-black selection:text-white pb-24">
            <div class="max-w-[1440px] mx-auto px-6 lg:px-12 py-12">
                <!-- Breadcrumb -->
                <nav class="text-sm font-black uppercase tracking-widest text-black mb-12 border-b-4 border-black pb-4">
                    <Link :href="route('customer.home')" class="hover:underline underline-offset-8 decoration-4">Shop</Link>
                    <span class="mx-3">/</span>
                    <span class="bg-black text-white px-2 py-1">{{ product.name }}</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
                    <!-- Left: Gallery (Visual) -->
                    <div class="lg:col-span-5 relative">
                        <!-- Brutalist Frame -->
                        <div class="aspect-[4/5] w-full bg-white border-4 border-black shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] relative group">
                            <img 
                                :src="product.image || 'https://via.placeholder.com/800'" 
                                :alt="product.name"
                                class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"
                            />
                            <!-- Tag Overlay -->
                             <div class="absolute top-4 left-4 flex flex-col gap-3">
                                <span v-if="product.stock < 5" class="px-4 py-2 bg-white border-4 border-black text-black text-xs font-black uppercase tracking-widest shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                                    Low Stock
                                </span>
                                <span class="px-4 py-2 bg-black border-4 border-black text-white text-xs font-black uppercase tracking-widest shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                                    New Arrival
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Info (Sticky) -->
                    <div class="lg:col-span-7 relative">
                        <div class="sticky top-32 border-4 border-black p-8 md:p-12 shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] bg-white">
                            <!-- Header -->
                            <h1 class="text-5xl md:text-7xl font-black text-black leading-[0.85] tracking-tighter mb-6 uppercase break-words">
                                {{ product.name }}
                            </h1>
                            
                            <div class="flex flex-col sm:flex-row sm:items-center gap-6 mb-8 border-b-4 border-black pb-8">
                                <p class="text-4xl font-black text-black bg-gray-100 px-4 py-2 border-4 border-black">
                                    {{ formatCurrency(product.price) }}
                                </p>
                                <div class="flex items-center gap-3">
                                    <span 
                                        v-if="product.stock > 0"
                                        class="w-4 h-4 bg-black border-2 border-black animate-pulse"
                                    ></span>
                                    <span v-else class="w-4 h-4 border-4 border-black bg-white"></span>
                                    <span class="text-sm font-black uppercase tracking-widest text-black">
                                        {{ product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-12">
                                <h3 class="text-sm font-black uppercase tracking-widest text-black mb-4 bg-gray-100 px-2 py-1 inline-block border-2 border-black">Description</h3>
                                <p class="text-lg text-black leading-relaxed font-bold max-w-xl">
                                    {{ product.description || 'Elevate your style with this premium piece. Designed for those who dare to stand out.' }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div v-if="product.stock > 0" class="space-y-8 border-t-4 border-black pt-8">
                                <!-- Quantity -->
                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-widest text-black mb-4">Quantity</h3>
                                    <div class="inline-flex items-center border-4 border-black bg-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                                        <button 
                                            @click="decreaseQuantity"
                                            class="w-16 h-16 flex items-center justify-center bg-white hover:bg-black hover:text-white transition-none text-2xl font-black border-r-4 border-black"
                                            :disabled="quantity <= 1"
                                        >
                                            -
                                        </button>
                                        <span class="w-20 text-center text-2xl font-black">{{ quantity }}</span>
                                        <button 
                                            @click="increaseQuantity"
                                            class="w-16 h-16 flex items-center justify-center bg-white hover:bg-black hover:text-white transition-none text-2xl font-black border-l-4 border-black"
                                            :disabled="quantity >= product.stock"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>

                                <!-- Add to Cart -->
                                <button 
                                    @click="addToCart"
                                    :disabled="isAddingToCart"
                                    class="w-full py-6 bg-white border-4 border-black text-black text-xl font-black uppercase tracking-widest hover:bg-black hover:text-white transition-none shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[8px] hover:translate-y-[8px] flex items-center justify-center gap-4 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="isAddingToCart">Processing...</span>
                                    <span v-else>Add to Cart</span>
                                    <svg v-if="!isAddingToCart" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </button>
                                
                                <p class="text-center text-sm text-black font-black uppercase tracking-widest flex items-center justify-center gap-2 pt-4">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    Secure Checkout • Free Shipping
                                </p>
                            </div>

                             <div v-else class="mt-8 border-t-4 border-black pt-8">
                                <button 
                                    disabled
                                    class="w-full py-6 bg-gray-200 border-4 border-gray-400 text-gray-500 text-xl font-black uppercase tracking-widest cursor-not-allowed"
                                >
                                    Sold Out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <div v-if="relatedProducts?.length > 0" class="mt-32 border-t-4 border-black pt-16">
                    <div class="flex items-end justify-between mb-12">
                        <h2 class="text-5xl font-black text-black tracking-tighter uppercase leading-none">More <br/> <span class="bg-black text-white px-2">Drops</span></h2>
                        <Link href="#" class="hidden sm:block font-black uppercase tracking-widest border-b-4 border-black pb-1 hover:bg-black hover:text-white transition-none">
                            View Collection
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        <Link 
                            v-for="related in relatedProducts" 
                            :key="related.id"
                            :href="route('products.show', related.id)"
                            class="group cursor-crosshair border-4 border-black bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] flex flex-col hover:bg-black hover:text-white transition-none"
                        >
                            <div class="aspect-[4/5] bg-gray-100 border-b-4 border-black overflow-hidden relative">
                                <img 
                                    :src="related.image || 'https://via.placeholder.com/300'" 
                                    :alt="related.name"
                                    class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"
                                />
                            </div>
                            <div class="p-6">
                                <h3 class="font-black text-xl uppercase tracking-tighter mb-2 line-clamp-1">{{ related.name }}</h3>
                                <p class="font-bold text-lg">{{ formatCurrency(related.price) }}</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </StoreLayout>
</template>
