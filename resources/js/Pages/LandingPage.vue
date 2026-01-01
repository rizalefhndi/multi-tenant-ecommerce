<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Streetwear Empire" />

    <div class="min-h-screen bg-white text-black font-sans selection:bg-black selection:text-white">
        <!-- Navbar -->
        <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-black">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Text Logo -->
                    <div class="flex-shrink-0">
                         <span class="font-black text-2xl tracking-widest uppercase">ONYX</span>
                    </div>

                    <!-- Desktop Nav -->
                    <div class="hidden md:flex items-center gap-8">
                        <Link href="#features" class="text-sm font-bold uppercase tracking-wider hover:underline underline-offset-4 decoration-2">Features</Link>
                        <Link href="#pricing" class="text-sm font-bold uppercase tracking-wider hover:underline underline-offset-4 decoration-2">Pricing</Link>
                         <Link :href="route('login')" class="text-sm font-bold uppercase tracking-wider hover:underline underline-offset-4 decoration-2">Login</Link>
                        <Link
                            :href="route('register')"
                            class="px-6 py-3 bg-black text-white text-xs font-black uppercase tracking-widest rounded-full hover:scale-105 transition-transform"
                        >
                            Start Free
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="pt-20">
            <!-- Split Hero -->
            <div class="min-h-[90vh] grid grid-cols-1 lg:grid-cols-2">
                <!-- Text Side -->
                <div class="flex flex-col justify-center px-8 lg:px-20 py-20 bg-white">
                     <div class="inline-flex items-center gap-2 mb-8">
                        <span class="w-3 h-3 bg-black rounded-full animate-pulse"></span>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-500">Multi-Tenant E-Commerce V2.0</span>
                    </div>

                    <h1 class="text-7xl sm:text-8xl lg:text-9xl font-black leading-[0.85] tracking-tighter uppercase mb-8">
                        Build <br/>
                        Your <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-b from-black to-gray-400">Empire</span>
                    </h1>

                    <p class="text-xl text-gray-600 font-medium max-w-lg mb-12 leading-relaxed">
                        The ultimate platform for launching unlimited branded stores. Scalable, secure, and designed for the boldest entrepreneurs.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <Link
                            :href="route('register')"
                            class="px-8 py-4 bg-black text-white font-black uppercase tracking-widest text-sm rounded-full hover:scale-105 transition-transform shadow-xl"
                        >
                            Launch Now
                        </Link>
                        <a
                            href="#features"
                            class="px-8 py-4 bg-transparent border-2 border-black text-black font-black uppercase tracking-widest text-sm rounded-full hover:bg-black hover:text-white transition-colors"
                        >
                            Explore
                        </a>
                    </div>
                </div>

                <!-- Visual Side -->
                <div class="relative bg-black hidden lg:block overflow-hidden">
                    <img 
                        src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=1000&auto=format&fit=crop"
                        alt="Streetwear Model"
                        class="w-full h-full object-cover grayscale opacity-70 hover:scale-105 transition-transform duration-700"
                    />
                    <div class="absolute inset-x-0 bottom-0 p-12 bg-gradient-to-t from-black/90 to-transparent">
                        <div class="flex justify-between items-end text-white border-b border-white/20 pb-6">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest mb-2">Featured Store</p>
                                <h3 class="text-3xl font-black uppercase italic">Urban Sombrero</h3>
                            </div>
                            <div class="text-right">
                                <p class="text-4xl font-black">2.4M</p>
                                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Monthly Visits</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticker -->
            <div class="bg-black text-white py-4 overflow-hidden whitespace-nowrap border-y border-white/10">
                <div class="inline-flex gap-8 animate-marquee">
                    <span v-for="i in 10" :key="i" class="text-2xl font-black uppercase italic tracking-widest mx-4">
                        Unlimited Stores • Secure Payments • Custom Domains • 
                    </span>
                </div>
            </div>

            <!-- Features Grid -->
            <section id="features" class="py-24 bg-dots-pattern">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                     <div class="mb-20">
                        <h2 class="text-6xl font-black uppercase tracking-tighter mb-6">Why Choose Us?</h2>
                        <div class="w-24 h-2 bg-black"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-0 border-t border-l border-black">
                        <!-- Feature 1 -->
                        <div class="p-12 border-r border-b border-black hover:bg-black hover:text-white transition-colors group cursor-crosshair">
                            <div class="mb-6 w-12 h-12 bg-black text-white group-hover:bg-white group-hover:text-black rounded-full flex items-center justify-center transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <h3 class="text-2xl font-black uppercase mb-4">Multi-Tenancy</h3>
                            <p class="font-medium text-gray-500 group-hover:text-gray-300">
                                Create and manage unlimited stores from a single dashboard. Total isolation, total control.
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="p-12 border-r border-b border-black hover:bg-black hover:text-white transition-colors group cursor-crosshair">
                            <div class="mb-6 w-12 h-12 bg-black text-white group-hover:bg-white group-hover:text-black rounded-full flex items-center justify-center transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                            </div>
                            <h3 class="text-2xl font-black uppercase mb-4">Custom Domains</h3>
                            <p class="font-medium text-gray-500 group-hover:text-gray-300">
                                Give each store its own identity. Connect any domain in seconds with automatic SSL.
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="p-12 border-r border-b border-black hover:bg-black hover:text-white transition-colors group cursor-crosshair">
                            <div class="mb-6 w-12 h-12 bg-black text-white group-hover:bg-white group-hover:text-black rounded-full flex items-center justify-center transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 class="text-2xl font-black uppercase mb-4">Global Payments</h3>
                            <p class="font-medium text-gray-500 group-hover:text-gray-300">
                                Accept payments from anywhere. Integrated with Stripe, PayPal, and more.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

             <!-- Large CTA -->
            <section class="py-32 bg-black text-white text-center px-6">
                <h2 class="text-5xl sm:text-7xl font-black uppercase tracking-tighter mb-8 max-w-4xl mx-auto">
                    Ready To Scale <br/> Your Brand?
                </h2>
                <Link
                    :href="route('register')"
                    class="inline-block px-12 py-5 bg-white text-black font-black uppercase tracking-widest text-lg rounded-full hover:scale-105 hover:bg-gray-200 transition-all"
                >
                    Get Started Now
                </Link>
            </section>

             <!-- Footer -->
            <footer class="bg-white border-t-2 border-black py-12 px-6">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="font-black text-xl tracking-widest uppercase">ONYX</div>
                    <div class="text-sm font-bold text-gray-500 uppercase tracking-widest">
                        © 2025 ONYX. All Rights Reserved.
                    </div>
                    <div class="flex gap-6">
                        <a href="#" class="text-black hover:text-gray-500"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
                        <a href="#" class="text-black hover:text-gray-500"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg></a>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</template>

<style scoped>
/* Gradient Text Utility */
.bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}
</style>
