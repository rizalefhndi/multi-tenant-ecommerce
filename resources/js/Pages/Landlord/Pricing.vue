<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    plans: Array,
});

// Toggle between monthly and yearly billing
const isYearly = ref(false);

// Get price based on billing cycle
const getPrice = (plan) => {
    if (plan.is_free) return 'Gratis';
    
    const price = isYearly.value ? plan.price_yearly : plan.price_monthly;
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
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
    <Head title="Harga - Multi-Tenant E-Commerce" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
        <!-- Navigation -->
        <nav class="border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <Link href="/" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">MT</span>
                        </div>
                        <span class="text-white font-semibold">Multi-Tenant</span>
                    </Link>
                    
                    <div class="flex items-center gap-4">
                        <Link href="/login" class="text-gray-300 hover:text-white transition-colors">
                            Masuk
                        </Link>
                        <Link 
                            href="/register" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                        >
                            Mulai Gratis
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Header -->
        <div class="text-center pt-16 pb-12 px-4">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                Pilih Paket yang Tepat untuk Bisnis Anda
            </h1>
            <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                Mulai gratis, upgrade kapan saja. Tanpa biaya tersembunyi.
            </p>

            <!-- Billing Toggle -->
            <div class="mt-8 inline-flex items-center gap-4 bg-white/5 backdrop-blur-sm rounded-full p-1.5">
                <button
                    @click="isYearly = false"
                    class="px-6 py-2 rounded-full text-sm font-medium transition-all"
                    :class="!isYearly 
                        ? 'bg-white text-gray-900 shadow-lg' 
                        : 'text-gray-300 hover:text-white'"
                >
                    Bulanan
                </button>
                <button
                    @click="isYearly = true"
                    class="px-6 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-2"
                    :class="isYearly 
                        ? 'bg-white text-gray-900 shadow-lg' 
                        : 'text-gray-300 hover:text-white'"
                >
                    Tahunan
                    <span class="text-xs bg-green-500 text-white px-2 py-0.5 rounded-full">
                        -16%
                    </span>
                </button>
            </div>
        </div>

        <!-- Pricing Cards -->
        <div class="max-w-7xl mx-auto px-4 pb-20">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="relative rounded-2xl overflow-hidden transition-transform hover:-translate-y-1"
                    :class="plan.is_featured 
                        ? 'bg-gradient-to-b from-indigo-500 to-purple-600 p-[2px]' 
                        : 'bg-white/5 backdrop-blur-sm border border-white/10'"
                >
                    <!-- Featured Badge -->
                    <div 
                        v-if="plan.is_featured" 
                        class="absolute top-0 right-0 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg"
                    >
                        POPULER
                    </div>

                    <div 
                        class="p-6 h-full"
                        :class="plan.is_featured ? 'bg-slate-900 rounded-xl' : ''"
                    >
                        <!-- Plan Name -->
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-white">{{ plan.name }}</h3>
                            <p class="text-gray-400 text-sm mt-1">{{ plan.description }}</p>
                        </div>

                        <!-- Price -->
                        <div class="mb-6">
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-bold text-white">{{ getPrice(plan) }}</span>
                                <span class="text-gray-400">{{ getBillingLabel(plan) }}</span>
                            </div>
                            <p 
                                v-if="getSavingsText(plan)" 
                                class="text-green-400 text-sm font-medium mt-1"
                            >
                                {{ getSavingsText(plan) }}
                            </p>
                        </div>

                        <!-- CTA Button -->
                        <Link
                            :href="plan.is_custom ? '/contact' : '/register?plan=' + plan.slug"
                            class="block w-full text-center py-3 rounded-lg font-semibold transition-colors mb-6"
                            :class="plan.is_featured 
                                ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white hover:from-indigo-600 hover:to-purple-700'
                                : plan.is_free 
                                    ? 'bg-white text-gray-900 hover:bg-gray-100'
                                    : 'bg-white/10 text-white hover:bg-white/20'"
                        >
                            {{ plan.is_custom ? 'Hubungi Kami' : plan.is_free ? 'Mulai Gratis' : 'Pilih Paket' }}
                        </Link>

                        <!-- Quotas -->
                        <div class="space-y-3 mb-6 pb-6 border-b border-white/10">
                            <div class="flex items-center gap-2 text-sm text-gray-300">
                                <svg class="w-4 h-4 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4Z"/>
                                    <path d="M0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                                </svg>
                                <span>{{ plan.max_products_display }} produk</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-300">
                                <svg class="w-4 h-4 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ plan.max_orders_display }} pesanan</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-300">
                                <svg class="w-4 h-4 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ plan.max_storage_display }} storage</span>
                            </div>
                        </div>

                        <!-- Features List -->
                        <div class="space-y-3">
                            <div 
                                v-for="(feature, index) in plan.features" 
                                :key="index"
                                class="flex items-start gap-2 text-sm text-gray-300"
                            >
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ feature }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="max-w-4xl mx-auto px-4 pb-20">
            <h2 class="text-3xl font-bold text-white text-center mb-12">Pertanyaan Umum</h2>
            
            <div class="space-y-4">
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                    <h3 class="text-lg font-semibold text-white mb-2">Apakah ada biaya tersembunyi?</h3>
                    <p class="text-gray-400">Tidak ada! Harga yang tertera sudah termasuk semua fitur yang ada di paket tersebut. Tidak ada biaya setup, tidak ada biaya tambahan.</p>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                    <h3 class="text-lg font-semibold text-white mb-2">Bisa upgrade atau downgrade kapan saja?</h3>
                    <p class="text-gray-400">Ya! Anda bisa mengubah paket kapan saja. Jika upgrade, Anda akan langsung mendapat fitur baru. Jika downgrade, perubahan berlaku di periode billing berikutnya.</p>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                    <h3 class="text-lg font-semibold text-white mb-2">Bagaimana dengan data saya jika berhenti berlangganan?</h3>
                    <p class="text-gray-400">Data Anda aman. Jika subscription berakhir, akun akan di-freeze selama 30 hari. Anda masih bisa mengakses data tetapi tidak bisa menambah produk/pesanan baru sampai berlangganan lagi.</p>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                    <h3 class="text-lg font-semibold text-white mb-2">Metode pembayaran apa saja yang diterima?</h3>
                    <p class="text-gray-400">Kami menerima pembayaran via Transfer Bank, Virtual Account, GoPay, ShopeePay, QRIS, dan Kartu Kredit/Debit.</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center pb-20 px-4">
            <div class="max-w-2xl mx-auto bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 md:p-12">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">
                    Siap Memulai?
                </h2>
                <p class="text-indigo-100 mb-6">
                    Buat toko online Anda dalam hitungan menit. Gratis selamanya untuk paket Free.
                </p>
                <Link
                    href="/register"
                    class="inline-flex items-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors"
                >
                    Mulai Sekarang
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-white/10 py-8">
            <div class="max-w-7xl mx-auto px-4 text-center text-gray-400 text-sm">
                <p>&copy; {{ new Date().getFullYear() }} Multi-Tenant E-Commerce. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
