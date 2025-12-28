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
        <div class="min-h-screen bg-white overflow-x-hidden">
            <!-- Hero Section (Edgy/Vibe) -->
            <div class="relative pt-12 pb-24 px-4 sm:px-6 lg:px-8">
                <!-- Background Typography -->
                <div class="absolute inset-0 pointer-events-none overflow-hidden select-none">
                     <span class="absolute top-[5%] left-[-2%] text-[10rem] md:text-[14rem] font-black text-gray-50 opacity-0 md:opacity-100 uppercase tracking-tighter leading-none whitespace-nowrap z-0">
                        Redefine
                    </span>
                    <span class="absolute top-[20%] right-[-5%] text-[10rem] md:text-[14rem] font-black text-gray-50 opacity-0 md:opacity-100 uppercase tracking-tighter leading-none whitespace-nowrap z-0">
                        Style
                    </span>
                </div>

                <div class="max-w-[1400px] mx-auto relative z-10 hidden md:block">
                     <!-- Top Heading Layout using Grid -->
                     <div class="flex justify-between items-end mb-[-60px] relative z-20 px-12">
                         <h1 class="text-7xl font-black text-gray-900 tracking-tighter transform -rotate-2">
                             Redefine <br/>
                             <span class="text-transparent bg-clip-text bg-gradient-to-r from-gray-900 to-gray-600">YOUR</span>
                         </h1>
                         <h1 class="text-7xl font-black text-gray-900 tracking-tighter transform rotate-2 text-right">
                             Unique <br/>
                             <span class="text-black">STYLE</span>
                         </h1>
                     </div>
                </div>

                <!-- Main Hero Card -->
                <div class="max-w-7xl mx-auto relative mt-8 md:mt-24">
                    <div class="bg-[#FF6B6B] rounded-[3rem] p-8 md:p-16 relative overflow-visible shadow-2xl min-h-[500px] md:min-h-[600px] flex items-center">
                        
                        <!-- Content Left -->
                        <div class="relative z-20 w-full md:w-1/2 text-white">
                            <span class="inline-block px-4 py-2 bg-white/20 backdrop-blur-md rounded-full text-sm font-bold uppercase tracking-wider mb-6">
                                New Arrivals 2024
                            </span>
                            <h2 class="text-5xl md:text-6xl font-black leading-tight mb-8">
                                Where Art Meets <br/>
                                <span class="italic text-[#FFD93D]">Streetwear</span>
                            </h2>
                            <p class="text-white/80 text-lg font-medium mb-10 max-w-md">
                                Step into the future of fashion. Bold cuts, premium fabrics, and designs that speak louder than words.
                            </p>
                            
                            <div class="flex items-center gap-6">
                                <button class="px-8 py-4 bg-white text-[#FF6B6B] rounded-full font-black text-lg hover:bg-gray-100 hover:scale-105 transition-all shadow-lg flex items-center gap-2 group">
                                    New Drops 
                                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                                
                                <div class="flex -space-x-4">
                                    <div class="w-12 h-12 rounded-full border-2 border-[#FF6B6B] bg-gray-200 overflow-hidden">
                                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=64&h=64" class="w-full h-full object-cover">
                                    </div>
                                    <div class="w-12 h-12 rounded-full border-2 border-[#FF6B6B] bg-gray-200 overflow-hidden">
                                        <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=64&h=64" class="w-full h-full object-cover">
                                    </div>
                                    <div class="w-12 h-12 rounded-full border-2 border-[#FF6B6B] bg-gray-200 overflow-hidden">
                                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=64&h=64" class="w-full h-full object-cover">
                                    </div>
                                    <div class="h-12 pl-6 flex items-center text-sm font-bold text-white/90">
                                        Rated 5 Stars by <br/> The Tribe
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Model Image (Right/Center) -->
                        <div class="hidden md:block absolute bottom-0 right-[10%] w-[45%] h-[115%] z-30 pointer-events-none">
                             <img 
                                src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                                alt="Model" 
                                class="w-full h-full object-cover object-top mask-image-b"
                                style="mask-image: linear-gradient(to bottom, black 80%, transparent 100%); -webkit-mask-image: linear-gradient(to bottom, black 80%, transparent 100%); mix-blend-mode: normal;" 
                            />
                            <!-- Note: Using a standard image here. Efficient 'cutout' requires a transparent PNG. Standard crop will work with mask for now to look integrated. -->
                        </div>

                        <!-- Abstract Shapes -->
                        <div class="absolute right-0 top-0 w-full h-full overflow-hidden rounded-[3rem] pointer-events-none z-10">
                            <div class="absolute top-[-20%] right-[-10%] w-[600px] h-[600px] bg-[#FF8787] rounded-full mix-blend-screen opacity-50 blur-3xl"></div>
                            <div class="absolute bottom-[-10%] left-[20%] w-[400px] h-[400px] bg-[#FF5252] rounded-full mix-blend-multiply opacity-50 blur-3xl"></div>
                        </div>

                         <!-- Floating Features (Right Side) -->
                        <div class="hidden md:flex flex-col gap-6 absolute right-8 top-1/2 transform -translate-y-1/2 z-40">
                             <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl text-center text-white w-24 hover:bg-white/20 transition-colors cursor-pointer">
                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span class="text-xs font-bold leading-tight block">Future Threads</span>
                             </div>
                             <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl text-center text-white w-24 hover:bg-white/20 transition-colors cursor-pointer">
                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                <span class="text-xs font-bold leading-tight block">Unique Designs</span>
                             </div>
                             <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl text-center text-white w-24 hover:bg-white/20 transition-colors cursor-pointer">
                                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-xs font-bold leading-tight block">Limited Drops</span>
                             </div>
                        </div>

                         <!-- Featured Product Card (Bottom Right Floating) -->
                        <div class="hidden lg:block absolute bottom-8 right-8 z-40 bg-white p-4 rounded-2xl shadow-xl w-64 transform rotate-3 hover:rotate-0 transition-transform duration-300">
                             <div class="aspect-square bg-gray-100 rounded-xl mb-3 overflow-hidden">
                                  <img src="https://images.unsplash.com/photo-1576566588028-4147f3842f27?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover">
                             </div>
                             <h4 class="font-bold text-gray-900">Urban Vanguard Tee</h4>
                             <p class="text-xs text-gray-500 mb-3">Unmatched comfort</p>
                             <button class="w-full py-2 bg-[#FF6B6B] text-white rounded-lg font-bold text-sm flex items-center justify-center gap-2 hover:bg-[#ff5252]">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                 $26.72
                             </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Marquee / Strip -->
            <div class="overflow-hidden bg-black py-4 -rotate-1 mb-20 transform translate-y-[-2rem] z-30 relative shadow-2xl">
                <div class="flex items-center gap-8 text-white font-black text-2xl uppercase tracking-widest whitespace-nowrap animate-marquee">
                    <span>Streetwear</span> • <span>Urban</span> • <span>Vintage</span> • <span>Limited</span> • <span>Accessories</span> • <span>Denim</span> •
                    <span>Streetwear</span> • <span>Urban</span> • <span>Vintage</span> • <span>Limited</span> • <span>Accessories</span> • <span>Denim</span>
                </div>
            </div>

            <!-- Categories Section (Visual) -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-32">
                <div class="flex justify-between items-end mb-10">
                    <h2 class="text-4xl md:text-5xl font-black text-gray-900 uppercase tracking-tighter">Explore <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#FF6B6B] to-[#FFD93D]">Vibe</span></h2>
                    
                    <div class="flex gap-2">
                         <button 
                            @click="scroll(-300)"
                            class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center hover:bg-black hover:text-white transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                         <button 
                            @click="scroll(300)"
                            class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center hover:bg-black hover:text-white transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>

                <div 
                    ref="scrollContainer"
                    class="flex gap-6 overflow-x-auto pb-8 snap-x no-scrollbar"
                >
                    <div 
                        v-for="category in categories" 
                        :key="category.name"
                        class="min-w-[280px] md:min-w-[320px] aspect-[3/4] rounded-3xl overflow-hidden relative group cursor-pointer snap-start"
                    >
                        <img 
                            :src="category.image" 
                            :alt="category.name"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>
                        
                        <div class="absolute bottom-6 left-6">
                            <span class="text-[#FFD93D] font-bold text-sm tracking-wider uppercase mb-1 block">{{ category.count }}</span>
                            <h3 class="text-white font-black text-3xl uppercase tracking-tighter">{{ category.name }}</h3>
                        </div>

                        <!-- Arrow Icon -->
                        <div class="absolute top-6 right-6 w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-32">
                <div class="flex items-end justify-between mb-16">
                     <div>
                        <span class="text-[#FF6B6B] font-bold tracking-widest uppercase text-sm mb-2 block">New Season</span>
                        <h2 class="text-4xl md:text-6xl font-black text-gray-900 uppercase tracking-tighter leading-none">
                            Latest <br/> <span class="text-gray-200">Drops</span>
                        </h2>
                     </div>
                     <Link href="#" class="hidden sm:inline-flex items-center gap-2 group font-bold uppercase tracking-wider text-sm">
                        View All Products
                        <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-black group-hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </span>
                     </Link>
                </div>
                
                <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-16">
                    <div 
                        v-for="product in products.data" 
                        :key="product.id"
                        class="group cursor-pointer"
                    >
                        <!-- Card Image -->
                        <Link :href="route('products.show', product.id)" class="block relative aspect-[3/4] bg-gray-100 rounded-[2rem] overflow-hidden mb-6">
                            <img 
                                :src="product.image || 'https://via.placeholder.com/400'" 
                                :alt="product.name"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            />
                            <!-- Overlay Action -->
                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-sm">
                                <button 
                                    @click.prevent="addToCart(product.id)"
                                    class="bg-white text-black px-8 py-4 rounded-full font-black uppercase tracking-wider transform translate-y-8 group-hover:translate-y-0 transition-all duration-300 hover:scale-105 shadow-xl"
                                >
                                    Add to Cart
                                </button>
                            </div>
                            <!-- Tags -->
                            <div class="absolute top-4 left-4 flex gap-2">
                                <span v-if="product.stock < 5" class="px-3 py-1 bg-black text-white text-[10px] font-bold uppercase tracking-widest rounded-full">Low Stock</span>
                            </div>
                        </Link>
                        
                        <!-- Card Info -->
                        <div class="text-center group-hover:translate-y-[-5px] transition-transform duration-300">
                            <h3 class="font-bold text-lg text-gray-900 group-hover:text-[#FF6B6B] transition-colors mb-2 uppercase tracking-tight">{{ product.name }}</h3>
                             <p class="text-sm text-gray-500 mb-3 line-clamp-1 max-w-[200px] mx-auto">{{ product.description }}</p>
                            <span class="inline-block px-4 py-1 bg-gray-50 rounded-full font-black text-lg text-black">{{ formatCurrency(product.price) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-24 bg-gray-50 rounded-3xl">
                    <p class="text-gray-400 text-lg">No products found.</p>
                </div>

                <!-- Pagination -->
                <div v-if="products.data.length > 0" class="mt-12 flex justify-center gap-2">
                    <template v-for="link in products.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="w-10 h-10 flex items-center justify-center rounded-full text-sm font-bold transition-all"
                            :class="link.active 
                                ? 'bg-[#FF6B6B] text-white' 
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
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
.mask-image-b {
    mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
}

.animate-marquee {
    display: flex;
    white-space: nowrap;
    animation: marquee 20s linear infinite;
}

/* Pause animation on hover if desired
.animate-marquee:hover {
    animation-play-state: paused;
}
*/

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
