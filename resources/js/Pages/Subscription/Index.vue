<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    subscription: Object,
    plan: Object,
    usage: Object,
});

// Get usage bar color based on percentage
const getUsageColor = (percentage) => {
    if (percentage >= 90) return 'bg-red-500';
    if (percentage >= 70) return 'bg-amber-500';
    return 'bg-indigo-500';
};

// Get usage text color
const getUsageTextColor = (percentage) => {
    if (percentage >= 90) return 'text-red-600';
    if (percentage >= 70) return 'text-amber-600';
    return 'text-indigo-600';
};
</script>

<template>
    <Head title="Langganan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Langganan & Penggunaan
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                
                <!-- Subscription Status Card -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <!-- Plan Info -->
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        Paket {{ plan?.name || 'Tidak Ada' }}
                                    </h3>
                                    <span 
                                        class="px-2.5 py-0.5 text-xs font-medium rounded-full"
                                        :class="{
                                            'bg-blue-100 text-blue-700': subscription.status_color === 'info',
                                            'bg-green-100 text-green-700': subscription.status_color === 'success',
                                            'bg-amber-100 text-amber-700': subscription.status_color === 'warning',
                                            'bg-red-100 text-red-700': subscription.status_color === 'danger',
                                            'bg-gray-100 text-gray-700': subscription.status_color === 'dark',
                                        }"
                                    >
                                        {{ subscription.status_label }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-600">
                                    <span v-if="subscription.is_on_trial">
                                        Trial berakhir dalam <strong>{{ subscription.trial_days_remaining }} hari</strong>
                                        ({{ subscription.trial_ends_at }})
                                    </span>
                                    <span v-else-if="subscription.subscription_ends_at">
                                        Berakhir: <strong>{{ subscription.subscription_ends_at }}</strong>
                                        ({{ subscription.subscription_days_remaining }} hari lagi)
                                    </span>
                                    <span v-else>
                                        Aktif tanpa batas waktu
                                    </span>
                                </p>
                                
                                <p class="text-sm text-gray-500 mt-1">
                                    Billing: {{ subscription.billing_cycle_label }}
                                    <span v-if="plan && !plan.is_free" class="ml-2">
                                        • {{ plan.formatted_price_monthly }}/bulan
                                    </span>
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap gap-3">
                                <Link
                                    :href="route('subscription.plans')"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                    Upgrade Paket
                                </Link>
                                <Link
                                    :href="route('subscription.invoices')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Riwayat Tagihan
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Products Usage -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-medium text-gray-900">Produk</h4>
                            <span 
                                class="text-sm font-medium"
                                :class="getUsageTextColor(usage.products.percentage)"
                            >
                                {{ usage.products.percentage }}%
                            </span>
                        </div>
                        
                        <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden mb-3">
                            <div 
                                class="absolute h-full rounded-full transition-all duration-500"
                                :class="getUsageColor(usage.products.percentage)"
                                :style="{ width: usage.products.percentage + '%' }"
                            ></div>
                        </div>
                        
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold">{{ usage.products.used }}</span>
                            dari
                            <span class="font-semibold">{{ usage.products.display }}</span>
                        </p>
                    </div>

                    <!-- Orders Usage -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-medium text-gray-900">Pesanan Bulan Ini</h4>
                            <span 
                                class="text-sm font-medium"
                                :class="getUsageTextColor(usage.orders.percentage)"
                            >
                                {{ usage.orders.percentage }}%
                            </span>
                        </div>
                        
                        <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden mb-3">
                            <div 
                                class="absolute h-full rounded-full transition-all duration-500"
                                :class="getUsageColor(usage.orders.percentage)"
                                :style="{ width: usage.orders.percentage + '%' }"
                            ></div>
                        </div>
                        
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold">{{ usage.orders.used }}</span>
                            dari
                            <span class="font-semibold">{{ usage.orders.display }}</span>
                        </p>
                    </div>

                    <!-- Storage Usage -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-medium text-gray-900">Penyimpanan</h4>
                            <span 
                                class="text-sm font-medium"
                                :class="getUsageTextColor(usage.storage.percentage)"
                            >
                                {{ usage.storage.percentage }}%
                            </span>
                        </div>
                        
                        <div class="relative h-2 bg-gray-200 rounded-full overflow-hidden mb-3">
                            <div 
                                class="absolute h-full rounded-full transition-all duration-500"
                                :class="getUsageColor(usage.storage.percentage)"
                                :style="{ width: usage.storage.percentage + '%' }"
                            ></div>
                        </div>
                        
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold">{{ usage.storage.used }} MB</span>
                            dari
                            <span class="font-semibold">{{ usage.storage.display }}</span>
                        </p>
                    </div>
                </div>

                <!-- Current Plan Features -->
                <div class="bg-white rounded-xl shadow-sm p-6" v-if="plan && plan.features">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Fitur Paket Anda</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div 
                            v-for="(feature, index) in plan.features" 
                            :key="index"
                            class="flex items-center gap-2 text-sm text-gray-600"
                        >
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ feature }}</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200" v-if="plan.is_free">
                        <p class="text-sm text-gray-600 mb-3">
                            Ingin lebih banyak fitur? Upgrade ke paket berbayar untuk mendapatkan:
                        </p>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Lebih banyak produk & pesanan</li>
                            <li>• Custom domain</li>
                            <li>• Integrasi payment gateway</li>
                            <li>• Dan masih banyak lagi...</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
