<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    transaction: {
        type: Object,
        required: true,
    },
});

const tx = ref(props.transaction);
const isRefreshing = ref(false);
const pollingInterval = ref(null);
const nowTs = ref(Date.now());
const countdownInterval = ref(null);
const redirectTimeout = ref(null);
const redirectCountdownInterval = ref(null);
const redirectSeconds = ref(5);

const statusColorClass = computed(() => {
    const status = (tx.value?.status || '').toLowerCase();
    if (status === 'settlement' || status === 'capture') return 'bg-emerald-500';
    if (status === 'pending') return 'bg-amber-500';
    if (status === 'expire' || status === 'cancel' || status === 'deny') return 'bg-red-500';
    return 'bg-gray-500';
});

const expiryText = computed(() => {
    const expiry = tx.value?.payment_details?.expiry_time;
    if (!expiry) return null;

    const expiryTime = new Date(expiry).getTime();
    const diff = expiryTime - nowTs.value;
    if (Number.isNaN(expiryTime) || diff <= 0) return 'Expired';

    const totalSeconds = Math.floor(diff / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    const hh = String(hours).padStart(2, '0');
    const mm = String(minutes).padStart(2, '0');
    const ss = String(seconds).padStart(2, '0');

    return `${hh}:${mm}:${ss}`;
});

const isPaid = computed(() => {
    const status = (tx.value?.status || '').toLowerCase();
    return status === 'settlement' || status === 'capture';
});

const paymentTypeLabel = computed(() => {
    const type = (tx.value?.payment_type || '').toLowerCase();
    if (type === 'bank_transfer') return 'Bank Transfer';
    if (type === 'qris') return 'QRIS';
    if (type === 'gopay') return 'GoPay';
    if (type === 'shopeepay') return 'ShopeePay';
    return tx.value?.payment_type || '-';
});

const paymentProviderLabel = computed(() => {
    const provider = (tx.value?.payment_provider || '').toLowerCase();
    if (!provider) return '-';
    if (provider === 'bca') return 'BCA';
    if (provider === 'bni') return 'BNI';
    if (provider === 'bri') return 'BRI';
    if (provider === 'permata') return 'Permata';
    if (provider === 'gopay') return 'GoPay';
    if (provider === 'shopeepay') return 'ShopeePay';
    return tx.value?.payment_provider;
});

const refreshStatus = async () => {
    if (!tx.value?.order_id) return;

    isRefreshing.value = true;
    try {
        const response = await fetch(route('api.billing.status', { orderId: tx.value.order_id }), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!response.ok) return;
        const data = await response.json();
        if (data?.transaction) {
            tx.value = {
                ...tx.value,
                ...data.transaction,
            };
        }
    } finally {
        isRefreshing.value = false;
    }
};

const clearAutoRedirectTimers = () => {
    if (redirectTimeout.value) {
        clearTimeout(redirectTimeout.value);
        redirectTimeout.value = null;
    }

    if (redirectCountdownInterval.value) {
        clearInterval(redirectCountdownInterval.value);
        redirectCountdownInterval.value = null;
    }
};

const startAutoRedirect = () => {
    if (redirectTimeout.value) return;

    redirectSeconds.value = 5;
    redirectCountdownInterval.value = setInterval(() => {
        if (redirectSeconds.value <= 1) {
            redirectSeconds.value = 0;
            clearAutoRedirectTimers();
            return;
        }

        redirectSeconds.value -= 1;
    }, 1000);

    redirectTimeout.value = setTimeout(() => {
        clearAutoRedirectTimers();
        globalThis.location.href = '/my-stores';
    }, 5000);
};

watch(isPaid, (paid) => {
    if (paid) {
        startAutoRedirect();
        return;
    }

    clearAutoRedirectTimers();
});

onMounted(() => {
    if (isPaid.value) {
        startAutoRedirect();
    }

    countdownInterval.value = setInterval(() => {
        nowTs.value = Date.now();
    }, 1000);

    pollingInterval.value = setInterval(async () => {
        if (!isPaid.value) {
            await refreshStatus();
        }
    }, 10000);
});

onBeforeUnmount(() => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
    }
    if (countdownInterval.value) {
        clearInterval(countdownInterval.value);
    }

    clearAutoRedirectTimers();
});
</script>

<template>
    <Head title="Payment Instruction - ONYX" />

    <div class="min-h-screen bg-white text-black font-sans selection:bg-black selection:text-white">
        <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-black">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <Link href="/" class="flex-shrink-0">
                        <span class="font-black text-2xl tracking-widest uppercase">ONYX</span>
                    </Link>
                    <Link href="/my-stores" class="text-sm font-bold uppercase tracking-wider text-gray-600 hover:text-black transition-colors">
                        My Stores
                    </Link>
                </div>
            </div>
        </nav>

        <main class="pt-24 pb-16 px-6">
            <div class="max-w-4xl mx-auto">
                <div class="mb-8">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Phase 6 • Payment Instruction</p>
                    <h1 class="text-4xl sm:text-5xl font-black uppercase tracking-tighter mb-3">Waiting for Payment</h1>
                    <p class="text-gray-600">Order <span class="font-bold">{{ tx.order_id }}</span> for store {{ tx.tenant?.store_name }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <section class="lg:col-span-2 border-2 border-black p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-black uppercase">Payment Status</h2>
                            <span :class="statusColorClass" class="px-3 py-1 text-white text-xs font-bold uppercase tracking-wider">
                                {{ tx.status }}
                            </span>
                        </div>

                        <div v-if="isPaid" class="border-2 border-emerald-500 bg-emerald-50 p-4 mb-5">
                            <p class="font-bold text-emerald-700">Payment received successfully.</p>
                            <p class="text-sm text-emerald-700">Your store will be activated automatically shortly.</p>
                            <p class="text-sm text-emerald-700 mt-1">
                                Redirecting to My Stores in {{ redirectSeconds }} seconds...
                            </p>
                        </div>

                        <div v-if="tx.payment_details?.va_numbers?.length" class="mb-5">
                            <h3 class="font-black uppercase text-sm mb-2">Virtual Account</h3>
                            <div class="space-y-2">
                                <div
                                    v-for="va in tx.payment_details.va_numbers"
                                    :key="`${va.bank}-${va.va_number}`"
                                    class="border border-gray-300 p-3 flex items-center justify-between"
                                >
                                    <span class="font-bold uppercase">{{ va.bank }}</span>
                                    <span class="font-mono font-black">{{ va.va_number }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="tx.payment_details?.permata_va_number" class="mb-5">
                            <h3 class="font-black uppercase text-sm mb-2">Permata VA</h3>
                            <div class="border border-gray-300 p-3 font-mono font-black">
                                {{ tx.payment_details.permata_va_number }}
                            </div>
                        </div>

                        <div v-if="tx.payment_details?.actions?.length" class="mb-5">
                            <h3 class="font-black uppercase text-sm mb-2">E-Wallet / QR Actions</h3>
                            <div class="space-y-2">
                                <a
                                    v-for="action in tx.payment_details.actions"
                                    :key="`${action.name}-${action.url}`"
                                    :href="action.url"
                                    target="_blank"
                                    class="block border border-gray-300 p-3 hover:border-black transition-colors"
                                >
                                    <span class="font-bold uppercase text-sm">{{ action.name || 'Open Action' }}</span>
                                    <p class="text-xs text-gray-500 truncate">{{ action.url }}</p>
                                </a>
                            </div>
                        </div>
                    </section>

                    <aside class="border-2 border-black p-6 h-fit">
                        <h2 class="text-lg font-black uppercase mb-4">Summary</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span>Package</span>
                                <span class="font-bold">{{ tx.package?.name || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total</span>
                                <span class="font-black">{{ tx.formatted_gross_amount }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Payment Type</span>
                                <span class="font-bold">{{ paymentTypeLabel }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Provider</span>
                                <span class="font-bold">{{ paymentProviderLabel }}</span>
                            </div>
                            <div v-if="expiryText" class="border-t border-gray-300 pt-3 mt-3 flex justify-between">
                                <span class="font-bold">Countdown</span>
                                <span class="font-black" :class="expiryText === 'Expired' ? 'text-red-600' : 'text-black'">{{ expiryText }}</span>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="refreshStatus"
                            :disabled="isRefreshing"
                            class="w-full mt-5 py-3 border-2 border-black text-sm font-black uppercase tracking-widest hover:bg-black hover:text-white transition-colors disabled:opacity-60"
                        >
                            <span v-if="isRefreshing">Refreshing...</span>
                            <span v-else>Refresh Status</span>
                        </button>

                        <Link href="/my-stores" class="block w-full mt-3 py-3 bg-black text-white text-center text-sm font-black uppercase tracking-widest">
                            Back to My Stores
                        </Link>
                    </aside>
                </div>
            </div>
        </main>
    </div>
</template>
