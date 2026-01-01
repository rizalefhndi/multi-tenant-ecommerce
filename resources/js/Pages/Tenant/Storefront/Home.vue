<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tenant: {
        type: Object,
        default: () => ({ id: 'demo', name: 'Demo Store' }),
    },
    products: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head :title="tenant.name" />

    <div class="min-h-screen bg-white text-black font-sans">
        <!-- Navbar -->
        <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-black">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <div class="flex-shrink-0">
                        <span class="font-black text-2xl tracking-widest uppercase">{{ tenant.name }}</span>
                    </div>

                    <div class="hidden md:flex items-center gap-8">
                        <Link href="#products" class="text-sm font-bold uppercase tracking-wider hover:underline underline-offset-4 decoration-2">Products</Link>
                        <Link href="#about" class="text-sm font-bold uppercase tracking-wider hover:underline underline-offset-4 decoration-2">About</Link>
                        <Link href="/login" class="px-6 py-3 bg-black text-white text-xs font-black uppercase tracking-widest rounded-full hover:scale-105 transition-transform">
                            Login
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="pt-20">
            <div class="min-h-[60vh] flex flex-col justify-center px-8 lg:px-20 py-20 bg-gradient-to-br from-gray-50 to-white">
                <div class="inline-flex items-center gap-2 mb-8">
                    <span class="w-3 h-3 bg-black rounded-full animate-pulse"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500">Now Open</span>
                </div>

                <h1 class="text-6xl sm:text-7xl lg:text-8xl font-black leading-[0.85] tracking-tighter uppercase mb-8">
                    Welcome to <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-b from-black to-gray-400">{{ tenant.name }}</span>
                </h1>

                <p class="text-xl text-gray-600 font-medium max-w-lg mb-12 leading-relaxed">
                    Discover our exclusive collection of premium products.
                </p>

                <div class="flex flex-wrap gap-4">
                    <a
                        href="#products"
                        class="px-8 py-4 bg-black text-white font-black uppercase tracking-widest text-sm rounded-full hover:scale-105 transition-transform shadow-xl"
                    >
                        Shop Now
                    </a>
                </div>
            </div>

            <!-- Products Section -->
            <section id="products" class="py-24 bg-white">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="mb-12">
                        <h2 class="text-4xl font-black uppercase tracking-tighter mb-4">Our Products</h2>
                        <div class="w-16 h-1 bg-black"></div>
                    </div>

                    <div v-if="products.length === 0" class="text-center py-20">
                        <p class="text-gray-500 text-lg">No products available yet. Check back soon!</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="product in products" :key="product.id" class="group cursor-pointer">
                            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden mb-4">
                                <img
                                    :src="product.image || 'https://via.placeholder.com/400'"
                                    :alt="product.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                            </div>
                            <h3 class="font-bold text-lg uppercase">{{ product.name }}</h3>
                            <p class="text-gray-600 font-medium">{{ product.price }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="bg-black text-white py-12 px-6">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="font-black text-xl tracking-widest uppercase">{{ tenant.name }}</div>
                    <div class="text-sm font-medium text-gray-400 uppercase tracking-widest">
                        Powered by ONYX
                    </div>
                </div>
            </footer>
        </main>
    </div>
</template>

<style scoped>
.bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}
</style>
