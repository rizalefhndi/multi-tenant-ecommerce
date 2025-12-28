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
        <div class="min-h-screen bg-white">
            <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-12 py-12">
                <!-- Breadcrumb -->
                <nav class="text-sm font-bold uppercase tracking-wide text-gray-400 mb-8">
                    <Link :href="route('customer.home')" class="hover:text-black transition-colors">Shop</Link>
                    <span class="mx-3">/</span>
                    <span class="text-black">{{ product.name }}</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
                    <!-- Left: Gallery (Visual) -->
                    <div class="lg:col-span-5 relative">
                        <!-- Abstract Background Shapes -->
                        <div class="absolute top-[-5%] left-[-10%] w-[80%] h-[80%] bg-[#f0f0f0] rounded-[3rem] -z-10 transform -rotate-2"></div>
                        <div class="absolute bottom-[-5%] right-[-5%] w-[60%] h-[60%] bg-[#FF6B6B]/10 rounded-[4rem] -z-10 transform rotate-3"></div>

                        <div class="aspect-[4/5] w-full bg-white rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 relative group">
                            <img 
                                :src="product.image || 'https://via.placeholder.com/800'" 
                                :alt="product.name"
                                class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-105"
                            />
                            <!-- Tag Overlay -->
                             <div class="absolute top-6 left-6 flex flex-col gap-2">
                                <span v-if="product.stock < 5" class="px-4 py-2 bg-black text-white text-xs font-bold uppercase tracking-wider rounded-full">
                                    Low Stock
                                </span>
                                <span class="px-4 py-2 bg-white/80 backdrop-blur-md text-black text-xs font-bold uppercase tracking-wider rounded-full">
                                    New Arrival
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Info (Sticky) -->
                    <div class="lg:col-span-7 relative">
                        <div class="sticky top-32">
                            <!-- Header -->
                            <h1 class="text-4xl sm:text-5xl md:text-6xl font-black text-gray-900 leading-[0.9] tracking-tighter mb-4 uppercase break-words">
                                {{ product.name }}
                            </h1>
                            
                            <div class="flex items-center gap-6 mb-8 border-b border-gray-100 pb-8">
                                <p class="text-3xl font-black text-[#FF6B6B]">
                                    {{ formatCurrency(product.price) }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <span 
                                        v-if="product.stock > 0"
                                        class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"
                                    ></span>
                                    <span v-else class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                        {{ product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-8">
                                <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-3">Description</h3>
                                <p class="text-base text-gray-600 leading-relaxed font-medium max-w-xl">
                                    {{ product.description || 'Elevate your style with this premium piece. Designed for those who dare to stand out.' }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div v-if="product.stock > 0" class="space-y-6">
                                <!-- Quantity -->
                                <div>
                                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-3">Quantity</h3>
                                    <div class="inline-flex items-center bg-gray-100 rounded-full p-1">
                                        <button 
                                            @click="decreaseQuantity"
                                            class="w-10 h-10 flex items-center justify-center rounded-full bg-white hover:bg-gray-50 text-black shadow-sm transition-all text-lg font-bold"
                                            :disabled="quantity <= 1"
                                        >
                                            -
                                        </button>
                                        <span class="w-12 text-center text-lg font-bold">{{ quantity }}</span>
                                        <button 
                                            @click="increaseQuantity"
                                            class="w-10 h-10 flex items-center justify-center rounded-full bg-black hover:bg-gray-800 text-white shadow-lg shadow-black/20 transition-all text-lg font-bold"
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
                                    class="w-full max-w-sm py-4 bg-black text-white text-base font-black uppercase tracking-wider rounded-full hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 shadow-lg shadow-black/20 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                                >
                                    <span v-if="isAddingToCart">Processing...</span>
                                    <span v-else>Add to Cart</span>
                                    <svg v-if="!isAddingToCart" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </button>
                                
                                <p class="text-center text-xs text-gray-400 font-bold uppercase tracking-widest flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Secure Checkout • Free Shipping Over Rp 500k
                                </p>
                            </div>

                             <div v-else class="mt-8">
                                <button 
                                    disabled
                                    class="w-full py-6 bg-gray-100 text-gray-400 text-xl font-black uppercase tracking-wider rounded-full cursor-not-allowed"
                                >
                                    Sold Out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <div v-if="relatedProducts?.length > 0" class="mt-32 border-t border-gray-100 pt-16">
                    <div class="flex items-end justify-between mb-12">
                        <h2 class="text-4xl font-black text-gray-900 tracking-tight uppercase">You May Also Like</h2>
                        <Link href="#" class="hidden sm:block font-bold border-b-2 border-black pb-1 hover:text-[#FF6B6B] hover:border-[#FF6B6B] transition-colors">
                            View Collection
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        <Link 
                            v-for="related in relatedProducts" 
                            :key="related.id"
                            :href="route('products.show', related.id)"
                            class="group cursor-pointer"
                        >
                            <div class="aspect-[4/5] bg-gray-100 rounded-3xl overflow-hidden mb-4 relative">
                                <img 
                                    :src="related.image || 'https://via.placeholder.com/300'" 
                                    :alt="related.name"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                />
                                <div class="absolute inset-0 bg-black/5 group-hover:bg-black/0 transition-colors"></div>
                            </div>
                            <h3 class="font-bold text-lg text-gray-900 group-hover:underline decoration-2 underline-offset-4 decoration-[#FF6B6B] truncate">{{ related.name }}</h3>
                            <p class="text-gray-500 font-bold mt-1">{{ formatCurrency(related.price) }}</p>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </StoreLayout>
</template>
