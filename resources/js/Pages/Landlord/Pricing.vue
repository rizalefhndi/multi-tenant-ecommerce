<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plans: Array,
    auth: Object,
});

// Toggle between monthly and yearly billing
const isYearly = ref(false);

// Dropdown menu state
const showDropdown = ref(false);

// Get price based on billing cycle
const getPrice = (plan) => {
    if (plan.is_free) return 'Free';
    
    const price = isYearly.value ? plan.price_yearly : plan.price_monthly;
    return '$' + new Intl.NumberFormat('en-US').format(price);
};

// Get billing cycle label
const getBillingLabel = (plan) => {
    if (plan.is_free) return 'Selamanya';
    return isYearly.value ? '/tahun' : '/bulan';
};

// Get savings text for yearly
const getSavingsText = (plan) => {
    if (!isYearly.value || plan.is_free || plan.yearly_savings_percent <= 0) return null;
    return `Hemat ${plan.yearly_savings_percent}%`;
};
</script>

<template>
    <Head title="Pricing - ONYX" />

    <div class="min-h-screen bg-white text-black font-sans selection:bg-black selection:text-white">
        <!-- Navbar -->
        <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-black">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Text Logo -->
                    <Link href="/" class="flex-shrink-0">
                         <span class="font-black text-2xl tracking-widest uppercase">ONYX</span>
                    </Link>

                    <!-- Navigation Items -->
                    <div class="flex items-center gap-8">
                        <!-- Desktop Nav Links -->
                        <div class="hidden md:flex items-center gap-8">
                            <Link href="/#features" class="text-sm font-bold uppercase tracking-wider hover:underline underline-offset-4 decoration-2">Features</Link>
                            <Link href="/pricing" class="text-sm font-bold uppercase tracking-wider hover:underline underline-offset-4 decoration-2">Pricing</Link>
                        </div>

                        <!-- Guest Navigation -->
                        <div v-if="!auth.user" class="flex items-center gap-6">
                            <Link href="/login" class="text-sm font-bold uppercase tracking-wider hover:underline underline-offset-4 decoration-2">
                                Login
                            </Link>
                            <Link 
                                href="/register" 
                                class="px-6 py-3 bg-black text-white text-xs font-black uppercase tracking-widest rounded-full hover:scale-105 transition-transform"
                            >
                                Start Free
                            </Link>
                        </div>

                        <!-- Authenticated Navigation -->
                        <div v-else class="relative">
                            <button
                                @click="showDropdown = !showDropdown"
                                class="flex items-center gap-2 px-4 py-2 border-2 border-transparent hover:border-black rounded-full transition-colors font-bold uppercase tracking-wider text-sm"
                            >
                                <span>{{ auth.user.name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <Transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div
                                    v-show="showDropdown"
                                    class="absolute right-0 mt-2 w-48 bg-white border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] z-50"
                                >
                                    <div class="px-4 py-3 border-b-2 border-black">
                                        <p class="text-xs font-black uppercase tracking-widest text-gray-500">Signed in as</p>
                                        <p class="text-sm font-bold truncate">{{ auth.user.email }}</p>
                                    </div>
                                    <Link
                                        href="/logout"
                                        method="post"
                                        as="button"
                                        class="w-full text-left px-4 py-3 text-sm font-bold uppercase tracking-wider hover:bg-black hover:text-white transition-colors flex items-center gap-2"
                                    >
                                        Logout
                                    </Link>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pt-20">
            <!-- Header Section -->
            <div class="text-center pt-24 pb-16 px-4">
                <div class="inline-flex items-center gap-2 mb-6">
                    <span class="w-2 h-2 bg-black rounded-full animate-pulse"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500">Flexible Plans</span>
                </div>
                
                <h1 class="text-6xl sm:text-8xl font-black uppercase tracking-tighter mb-8 leading-[0.9]">
                    Choose Your <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-b from-black to-gray-500">Empire</span>
                </h1>
                
                <p class="text-xl font-medium text-gray-600 max-w-2xl mx-auto mb-10">
                    Scale your brand with clear, transparent pricing. No hidden fees. Just pure growth.
                </p>

                <!-- Billing Toggle -->
                <div class="inline-flex items-center p-2 border-2 border-black rounded-full gap-2">
                    <button
                        @click="isYearly = false"
                        class="px-8 py-2 rounded-full text-sm font-black uppercase tracking-widest transition-all"
                        :class="!isYearly 
                            ? 'bg-black text-white shadow-lg' 
                            : 'text-gray-500 hover:text-black'"
                    >
                        Monthly
                    </button>
                    <button
                        @click="isYearly = true"
                        class="px-8 py-2 rounded-full text-sm font-black uppercase tracking-widest transition-all flex items-center gap-2"
                        :class="isYearly 
                            ? 'bg-black text-white shadow-lg' 
                            : 'text-gray-500 hover:text-black'"
                    >
                        Yearly
                        <span class="text-[10px] bg-red-600 text-white px-2 py-0.5 rounded-full animate-pulse">
                            -16%
                        </span>
                    </button>
                </div>
            </div>

            <!-- Pricing Grid -->
            <div class="max-w-7xl mx-auto px-6 pb-32">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-0 border-2 border-black bg-black">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        class="relative group bg-white p-8 border-b-2 last:border-b-0 lg:border-b-0 lg:border-r-2 lg:last:border-r-0 border-black hover:bg-black hover:text-white transition-all duration-300 flex flex-col"
                    >
                        <!-- Top Badge -->
                         <div 
                            v-if="plan.is_featured" 
                            class="absolute top-0 right-0 bg-black text-white text-xs font-black uppercase tracking-widest px-4 py-1 border-l-2 border-b-2 border-white group-hover:bg-white group-hover:text-black group-hover:border-black transition-colors"
                        >
                            Best Seller
                        </div>

                        <!-- Header -->
                        <div class="mb-8">
                            <h3 class="text-2xl font-black uppercase italic tracking-wider mb-2">{{ plan.name }}</h3>
                            <p class="text-sm font-medium text-gray-500 group-hover:text-gray-400 min-h-[40px]">{{ plan.description }}</p>
                        </div>

                        <!-- Price -->
                        <div class="mb-8">
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-black tracking-tighter">{{ getPrice(plan) }}</span>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-gray-400 group-hover:text-gray-500">{{ getBillingLabel(plan) }}</span>
                            <p 
                                v-if="getSavingsText(plan)" 
                                class="text-red-600 text-xs font-bold uppercase tracking-widest mt-2 group-hover:text-white"
                            >
                                {{ getSavingsText(plan) }}
                            </p>
                        </div>

                        <!-- CTA Button -->
                        <div class="mb-8">
                            <Link
                                :href="plan.is_custom ? '/contact' : '/register?plan=' + plan.slug"
                                class="block w-full text-center py-4 border-2 border-black font-black uppercase tracking-widest text-xs transition-all duration-300"
                                :class="plan.is_featured 
                                    ? 'bg-black text-white group-hover:bg-white group-hover:text-black'
                                    : 'bg-transparent text-black hover:bg-black hover:text-white group-hover:bg-white group-hover:text-black'"
                            >
                                {{ plan.is_custom ? 'Contact Sales' : plan.is_free ? 'Start Free' : 'Select Plan' }}
                            </Link>
                        </div>

                        <!-- Features -->
                        <div class="space-y-4 mt-auto">
                            <!-- Quotas -->
                             <div class="pb-6 border-b-2 border-black/10 group-hover:border-white/20 space-y-2">
                                <div class="flex items-center gap-3 text-sm font-bold">
                                    <span class="w-1.5 h-1.5 bg-black rounded-full group-hover:bg-white"></span>
                                    <span>{{ plan.max_products_display }} Products</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm font-bold">
                                    <span class="w-1.5 h-1.5 bg-black rounded-full group-hover:bg-white"></span>
                                    <span>{{ plan.max_orders_display }} Orders</span>
                                </div>
                             </div>

                            <!-- List -->
                            <div 
                                v-for="(feature, index) in plan.features" 
                                :key="index"
                                class="flex items-start gap-3 text-sm font-medium group-hover:text-gray-300"
                            >
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ feature }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <section class="border-t-2 border-black bg-gray-50">
                <div class="max-w-4xl mx-auto px-6 py-24">
                    <h2 class="text-5xl font-black uppercase tracking-tighter mb-16 text-center">Common Questions</h2>
                    
                    <div class="grid gap-6">
                        <div class="bg-white border-2 border-black p-8 hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transition-shadow cursor-default">
                            <h3 class="text-xl font-black uppercase mb-3">No Hidden Fees?</h3>
                            <p class="font-medium text-gray-600">Pure transparency. The price you see is the price you pay. No setup fees, no surprise charges. Everything is included.</p>
                        </div>

                        <div class="bg-white border-2 border-black p-8 hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transition-shadow cursor-default">
                            <h3 class="text-xl font-black uppercase mb-3">Change Plans Anytime?</h3>
                            <p class="font-medium text-gray-600">Absolutely. Upgrade instantly to unlock more power. Downgrade changes take effect at the next billing cycle.</p>
                        </div>

                         <div class="bg-white border-2 border-black p-8 hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transition-shadow cursor-default">
                            <h3 class="text-xl font-black uppercase mb-3">What Happens to My Data?</h3>
                            <p class="font-medium text-gray-600">Your data is yours. If you leave, we freeze your account for 30 days. You can export everything before you go.</p>
                        </div>
                    </div>
                </div>
            </section>

             <!-- Footer -->
            <footer class="bg-white border-t-2 border-black py-12 px-6">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="font-black text-xl tracking-widest uppercase">ONYX</div>
                    <div class="text-sm font-bold text-gray-500 uppercase tracking-widest">
                        © {{ new Date().getFullYear() }} ONYX. All Rights Reserved.
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
.bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}
</style>
