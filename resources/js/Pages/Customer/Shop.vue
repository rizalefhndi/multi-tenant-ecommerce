<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import StoreLayout from '@/Layouts/StoreLayout.vue';

const props = defineProps({
    products: Object,
});

const loadingProductId = ref(null);
const scrollContainer = ref(null);

const categories = [
    { name: 'Streetwear', image: 'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?auto=format&fit=crop&w=400&q=80', count: '42 Items' },
    { name: 'Oversized', image: 'https://images.unsplash.com/photo-1503342394128-c104d54dba01?auto=format&fit=crop&w=400&q=80', count: '18 Items' },
    { name: 'Accessories', image: 'https://images.unsplash.com/photo-1576053139778-7e32f2ae3cfd?auto=format&fit=crop&w=400&q=80', count: '56 Items' },
    { name: 'Denim', image: 'https://images.unsplash.com/photo-1582552938357-32b906df40cb?auto=format&fit=crop&w=400&q=80', count: '24 Items' },
    { name: 'Footwear', image: 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?auto=format&fit=crop&w=400&q=80', count: '12 Items' },
];

const scroll = (offset) => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({ left: offset, behavior: 'smooth' });
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const addToCart = (productId) => {
    loadingProductId.value = productId;
    router.post(route('cart.add'), {
        product_id: productId,
        quantity: 1,
    }, {
        preserveScroll: true,
        onFinish: () => {
            loadingProductId.value = null;
        },
    });
};
</script>

<template>
    <Head title="Shop" />

    <StoreLayout>
        <div class="min-h-screen bg-white overflow-x-hidden text-black selection:bg-black selection:text-white">
            <!-- Hero Section (Brutalist) -->
            <div class="relative pt-12 pb-24 px-4 sm:px-6 lg:px-8 border-b-4 border-black">
                <!-- Background Typography -->
                <div class="absolute inset-0 pointer-events-none overflow-hidden select-none flex flex-col justify-center opacity-10">
                     <span class="text-[12rem] md:text-[20rem] font-black uppercase tracking-tighter leading-none whitespace-nowrap -ml-20">
                        BRUTAL
                    </span>
                </div>

                <div class="max-w-[1440px] mx-auto relative z-10 flex flex-col items-center text-center mt-12 md:mt-24">
                     <!-- Top Heading -->
                     <h1 class="text-7xl md:text-9xl font-black uppercase tracking-tighter leading-[0.85] mb-8">
                         Raw <br/>
                         <span class="bg-black text-white px-4">Aesthetics</span>
                     </h1>
                     <p class="text-xl md:text-2xl font-bold max-w-2xl mb-12 uppercase tracking-widest border-4 border-black p-4 bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                         No compromises. No fake curves. <br/> Pure streetwear essentials.
                     </p>
                </div>
            </div>

            <!-- Categories Marquee / Strip -->
            <div class="overflow-hidden bg-black py-6 border-b-4 border-black relative z-30">
                <div class="flex items-center gap-8 text-white font-black text-3xl uppercase tracking-widest whitespace-nowrap animate-marquee">
                    <span>Streetwear</span> • <span>Urban</span> • <span>Vintage</span> • <span>Limited</span> • <span>Accessories</span> • <span>Denim</span> •
                    <span>Streetwear</span> • <span>Urban</span> • <span>Vintage</span> • <span>Limited</span> • <span>Accessories</span> • <span>Denim</span>
                </div>
            </div>

            <!-- Categories Section (Visual) -->
            <div class="max-w-[1440px] mx-auto px-6 lg:px-12 py-24 border-b-4 border-black">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-16 gap-8">
                    <h2 class="text-6xl md:text-8xl font-black uppercase tracking-tighter leading-none">
                        The <br/> <span class="bg-black text-white px-2">Grid</span>
                    </h2>
                    
                    <div class="flex gap-4">
                         <button 
                            @click="scroll(-400)"
                            class="w-16 h-16 border-4 border-black flex items-center justify-center hover:bg-black hover:text-white transition-none shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[4px] hover:translate-y-[4px]"
                        >
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                         <button 
                            @click="scroll(400)"
                            class="w-16 h-16 border-4 border-black flex items-center justify-center hover:bg-black hover:text-white transition-none shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[4px] hover:translate-y-[4px]"
                        >
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>

                <div 
                    ref="scrollContainer"
                    class="flex gap-8 overflow-x-auto pb-12 snap-x no-scrollbar"
                >
                    <div 
                        v-for="category in categories" 
                        :key="category.name"
                        class="min-w-[300px] md:min-w-[400px] aspect-square border-4 border-black relative group cursor-crosshair snap-start bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] transition-shadow"
                    >
                        <img 
                            :src="category.image" 
                            :alt="category.name"
                            class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"
                        />
                        
                        <div class="absolute inset-0 border-4 border-transparent group-hover:border-black transition-colors pointer-events-none"></div>

                        <div class="absolute bottom-0 left-0 w-full bg-white border-t-4 border-black p-4">
                            <div class="flex justify-between items-center">
                                <h3 class="font-black text-3xl uppercase tracking-tighter">{{ category.name }}</h3>
                                <span class="font-bold text-sm tracking-widest uppercase bg-black text-white px-2 py-1">{{ category.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid Section -->
            <div class="max-w-[1440px] mx-auto px-6 lg:px-12 py-24">
                <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-20 gap-8">
                     <div>
                        <span class="bg-black text-white px-3 py-1 font-black tracking-widest uppercase text-sm mb-4 inline-block">New Season</span>
                        <h2 class="text-6xl md:text-8xl font-black uppercase tracking-tighter leading-none">
                            Drop <br/> <span>01</span>
                        </h2>
                     </div>
                     <Link href="#" class="inline-flex items-center gap-4 border-4 border-black px-8 py-4 font-black uppercase tracking-widest text-sm hover:bg-black hover:text-white transition-none shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[4px] hover:translate-y-[4px]">
                        View All
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                     </Link>
                </div>
                
                <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div 
                        v-for="product in products.data" 
                        :key="product.id"
                        class="group cursor-crosshair border-4 border-black bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] flex flex-col"
                    >
                        <!-- Card Image -->
                        <Link :href="route('products.show', product.id)" class="block relative aspect-[4/5] border-b-4 border-black overflow-hidden bg-gray-100">
                            <img 
                                :src="product.image || 'https://via.placeholder.com/400'" 
                                :alt="product.name"
                                class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"
                            />
                            
                            <!-- Overlay Action -->
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-none flex items-center justify-center backdrop-blur-sm">
                                <button 
                                    @click.prevent="addToCart(product.id)"
                                    class="bg-white text-black border-4 border-black px-8 py-4 font-black uppercase tracking-widest transform translate-y-8 group-hover:translate-y-0 transition-all duration-200 hover:bg-black hover:text-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]"
                                >
                                    Add To Cart
                                </button>
                            </div>
                            <!-- Tags -->
                            <div class="absolute top-4 left-4 flex gap-2 z-10">
                                <span v-if="product.stock < 5" class="px-3 py-1 bg-white border-2 border-black text-black text-xs font-black uppercase tracking-widest">Low Stock</span>
                            </div>
                        </Link>
                        
                        <!-- Card Info -->
                        <div class="p-6 flex flex-col flex-grow justify-between group-hover:bg-black group-hover:text-white transition-none">
                            <div>
                                <h3 class="font-black text-2xl uppercase tracking-tighter mb-2 line-clamp-2">{{ product.name }}</h3>
                                <p class="text-sm font-bold uppercase tracking-widest text-gray-500 group-hover:text-gray-400 mb-4 line-clamp-1">{{ product.description }}</p>
                            </div>
                            <span class="inline-block font-black text-2xl">{{ formatCurrency(product.price) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-32 border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                    <p class="font-black uppercase tracking-widest text-2xl">No Drops Available.</p>
                </div>

                <!-- Pagination -->
                <div v-if="products.data.length > 0" class="mt-20 flex justify-center gap-4">
                    <template v-for="link in products.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="w-12 h-12 flex items-center justify-center border-4 border-black font-black transition-none"
                            :class="link.active 
                                ? 'bg-black text-white' 
                                : 'bg-white text-black hover:bg-black hover:text-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[4px] hover:translate-y-[4px]'"
                        >
                            <span v-html="link.label"></span>
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </StoreLayout>
</template>

<style scoped>
.animate-marquee {
    display: flex;
    white-space: nowrap;
    animation: marquee 20s linear infinite;
}

@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
