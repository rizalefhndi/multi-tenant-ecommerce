<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plans: Array,
    currentPlan: Object,
});

const isYearly = ref(false);
const selectedPlan = ref(null);
const processing = ref(false);

const getPrice = (plan) => {
    if (plan.is_free) return 'Gratis';
    const price = isYearly.value ? plan.price_yearly : plan.price_monthly;
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
};

const getBillingLabel = (plan) => {
    if (plan.is_free) return 'Selamanya';
    return isYearly.value ? '/tahun' : '/bulan';
};

const selectPlan = (plan) => {
    if (plan.is_current || plan.is_custom) return;
    selectedPlan.value = plan;
};

const confirmChange = () => {
    if (!selectedPlan.value) return;
    
    processing.value = true;
    router.post(route('subscription.change'), {
        plan_id: selectedPlan.value.id,
        billing_cycle: isYearly.value ? 'yearly' : 'monthly',
    }, {
        onFinish: () => {
            processing.value = false;
            selectedPlan.value = null;
        },
    });
};
</script>

<template>
    <Head title="Pilih Paket" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Pilih Paket
                </h2>
                <Link
                    :href="route('subscription.index')"
                    class="text-sm text-indigo-600 hover:text-indigo-700"
                >
                    ← Kembali
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Billing Toggle -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center gap-4 bg-gray-100 rounded-full p-1.5">
                        <button
                            @click="isYearly = false"
                            class="px-6 py-2 rounded-full text-sm font-medium transition-all"
                            :class="!isYearly 
                                ? 'bg-white shadow text-gray-900' 
                                : 'text-gray-600 hover:text-gray-900'"
                        >
                            Bulanan
                        </button>
                        <button
                            @click="isYearly = true"
                            class="px-6 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-2"
                            :class="isYearly 
                                ? 'bg-white shadow text-gray-900' 
                                : 'text-gray-600 hover:text-gray-900'"
                        >
                            Tahunan
                            <span class="text-xs bg-green-500 text-white px-2 py-0.5 rounded-full">
                                -16%
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Plans Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        @click="selectPlan(plan)"
                        class="relative rounded-2xl overflow-hidden transition-all"
                        :class="{
                            'ring-2 ring-indigo-500 shadow-lg': selectedPlan?.id === plan.id,
                            'ring-2 ring-green-500': plan.is_current,
                            'bg-white shadow-sm hover:shadow-md cursor-pointer': !plan.is_current && !plan.is_custom,
                            'bg-gray-50': plan.is_current,
                            'bg-white shadow-sm': plan.is_custom,
                        }"
                    >
                        <!-- Current Badge -->
                        <div 
                            v-if="plan.is_current"
                            class="absolute top-0 right-0 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg"
                        >
                            PAKET ANDA
                        </div>

                        <!-- Featured Badge -->
                        <div 
                            v-if="plan.is_featured && !plan.is_current"
                            class="absolute top-0 right-0 bg-indigo-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg"
                        >
                            POPULER
                        </div>

                        <div class="p-6">
                            <!-- Plan Name -->
                            <div class="mb-4">
                                <h3 class="text-xl font-bold text-gray-900">{{ plan.name }}</h3>
                                <p class="text-gray-500 text-sm mt-1">{{ plan.description }}</p>
                            </div>

                            <!-- Price -->
                            <div class="mb-6">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl font-bold text-gray-900">{{ getPrice(plan) }}</span>
                                    <span class="text-gray-500">{{ getBillingLabel(plan) }}</span>
                                </div>
                                <p 
                                    v-if="isYearly && plan.yearly_savings_percent > 0" 
                                    class="text-green-600 text-sm font-medium mt-1"
                                >
                                    Hemat {{ plan.yearly_savings_percent }}%
                                </p>
                            </div>

                            <!-- Quotas -->
                            <div class="space-y-2 mb-6 pb-6 border-b border-gray-200">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/>
                                        <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ plan.max_products_display }} produk</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ plan.max_orders_display }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ plan.max_storage_display }}</span>
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="space-y-2">
                                <div 
                                    v-for="(feature, index) in plan.features?.slice(0, 5)" 
                                    :key="index"
                                    class="flex items-start gap-2 text-sm text-gray-600"
                                >
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ feature }}</span>
                                </div>
                                <p 
                                    v-if="plan.features?.length > 5" 
                                    class="text-sm text-indigo-600"
                                >
                                    +{{ plan.features.length - 5 }} fitur lainnya
                                </p>
                            </div>

                            <!-- Select Button -->
                            <div class="mt-6">
                                <button
                                    v-if="!plan.is_current && !plan.is_custom"
                                    class="w-full py-2.5 rounded-lg font-medium transition-colors"
                                    :class="selectedPlan?.id === plan.id
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                >
                                    {{ selectedPlan?.id === plan.id ? 'Dipilih' : 'Pilih Paket' }}
                                </button>
                                <button
                                    v-else-if="plan.is_custom"
                                    class="w-full py-2.5 rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
                                >
                                    Hubungi Kami
                                </button>
                                <div 
                                    v-else
                                    class="w-full py-2.5 text-center text-green-600 font-medium"
                                >
                                    Paket Saat Ini
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirm Modal -->
                <div 
                    v-if="selectedPlan"
                    class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
                    @click.self="selectedPlan = null"
                >
                    <div class="bg-white rounded-2xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            Konfirmasi Perubahan Paket
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Anda akan {{ selectedPlan.is_upgrade ? 'upgrade' : 'ganti' }} ke paket 
                            <strong>{{ selectedPlan.name }}</strong>
                            dengan billing <strong>{{ isYearly ? 'tahunan' : 'bulanan' }}</strong>.
                        </p>

                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600">Paket</span>
                                <span class="font-medium">{{ selectedPlan.name }}</span>
                            </div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600">Billing</span>
                                <span class="font-medium">{{ isYearly ? 'Tahunan' : 'Bulanan' }}</span>
                            </div>
                            <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                                <span class="font-medium">Total</span>
                                <span class="font-semibold text-indigo-600">{{ getPrice(selectedPlan) }}</span>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button
                                @click="selectedPlan = null"
                                class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Batal
                            </button>
                            <button
                                @click="confirmChange"
                                :disabled="processing"
                                class="flex-1 px-4 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50"
                            >
                                {{ processing ? 'Memproses...' : 'Konfirmasi' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
