<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    packages: {
        type: Array,
        default: () => [],
    },
    defaultPackageId: {
        type: [Number, String, null],
        default: null,
    },
    snap: {
        type: Object,
        default: () => ({
            url: '',
            clientKey: '',
        }),
    },
});

const selectedPackageId = ref(props.defaultPackageId || props.packages?.[0]?.id || null);
const paymentMethod = ref('bank_transfer');
const paymentProvider = ref('bca');
const isSubmitting = ref(false);
const errorMessage = ref('');

const selectedPackage = computed(() => {
    return props.packages.find((pkg) => Number(pkg.id) === Number(selectedPackageId.value));
});

const paymentOptions = [
    { type: 'bank_transfer', provider: 'bca', label: 'VA BCA' },
    { type: 'bank_transfer', provider: 'bni', label: 'VA BNI' },
    { type: 'bank_transfer', provider: 'bri', label: 'VA BRI' },
    { type: 'bank_transfer', provider: 'permata', label: 'VA Permata' },
    { type: 'qris', provider: null, label: 'QRIS' },
    { type: 'gopay', provider: 'gopay', label: 'GoPay' },
    { type: 'shopeepay', provider: 'shopeepay', label: 'ShopeePay' },
];

const selectPayment = (option) => {
    paymentMethod.value = option.type;
    paymentProvider.value = option.provider;
};

const isOptionSelected = (option) => {
    return paymentMethod.value === option.type && paymentProvider.value === option.provider;
};

const selectedPaymentLabel = computed(() => {
    const selected = paymentOptions.find((option) => isOptionSelected(option));
    return selected?.label || paymentMethod.value;
});

let snapScriptPromise = null;

const loadSnapScript = () => {
    if (globalThis.snap) {
        return Promise.resolve();
    }

    if (snapScriptPromise) {
        return snapScriptPromise;
    }

    const scriptUrl = props.snap?.url;
    const clientKey = props.snap?.clientKey;

    if (!scriptUrl || !clientKey) {
        return Promise.reject(new Error('Konfigurasi Snap belum tersedia.'));
    }

    snapScriptPromise = new Promise((resolve, reject) => {
        const existingScript = document.querySelector('script[data-midtrans-snap="true"]');
        if (existingScript) {
            existingScript.addEventListener('load', () => resolve());
            existingScript.addEventListener('error', () => reject(new Error('Gagal memuat Snap script.')));
            return;
        }

        const script = document.createElement('script');
        script.src = scriptUrl;
        script.dataset.clientKey = clientKey;
        script.dataset.midtransSnap = 'true';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Gagal memuat Snap script.'));
        document.body.appendChild(script);
    });

    return snapScriptPromise;
};

const submitCheckout = async () => {
    errorMessage.value = '';

    if (!selectedPackage.value) {
        errorMessage.value = 'Paket belum dipilih.';
        return;
    }

    isSubmitting.value = true;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(route('api.billing.snap-token'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token || '',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                tenant_id: props.tenant.id,
                package_id: selectedPackage.value.id,
                payment_type: paymentMethod.value,
                payment_provider: paymentProvider.value,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            errorMessage.value = data.message || 'Gagal membuat transaksi pembayaran.';
            return;
        }

        const snapToken = data?.snap_token;
        const orderId = data?.transaction?.order_id;
        if (!snapToken || !orderId) {
            errorMessage.value = 'Token pembayaran tidak valid. Silakan coba lagi.';
            return;
        }

        await loadSnapScript();

        globalThis.snap.pay(snapToken, {
            onSuccess: () => {
                globalThis.location.href = route('billing.pending.page', { orderId });
            },
            onPending: () => {
                globalThis.location.href = route('billing.pending.page', { orderId });
            },
            onClose: () => {
                globalThis.location.href = route('billing.pending.page', { orderId });
            },
            onError: () => {
                globalThis.location.href = route('billing.pending.page', { orderId });
            },
        });
    } catch (error) {
        errorMessage.value = error?.message || 'Terjadi kesalahan jaringan. Silakan coba lagi.';
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <Head title="Billing Checkout - ONYX" />

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
            <div class="max-w-5xl mx-auto">
                <div class="mb-10">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Phase 5 • Custom Checkout</p>
                    <h1 class="text-4xl sm:text-5xl font-black uppercase tracking-tighter mb-3">Complete Payment</h1>
                    <p class="text-gray-600">
                        Toko <span class="font-bold">{{ tenant.store_name }}</span> menunggu aktivasi. Pilih paket & metode pembayaran.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <section class="lg:col-span-2 border-2 border-black p-6">
                        <h2 class="text-lg font-black uppercase mb-4">Pilih Paket</h2>
                        <div class="space-y-3 mb-8">
                            <label
                                v-for="pkg in packages"
                                :key="pkg.id"
                                class="flex items-center justify-between border-2 p-4 cursor-pointer transition-colors"
                                :class="Number(selectedPackageId) === Number(pkg.id) ? 'border-black bg-gray-100' : 'border-gray-300 hover:border-black'"
                            >
                                <div class="flex items-start gap-3">
                                    <input v-model="selectedPackageId" :value="pkg.id" type="radio" class="mt-1" />
                                    <div>
                                        <p class="font-bold uppercase">{{ pkg.name }}</p>
                                        <p class="text-sm text-gray-500">{{ pkg.description }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Durasi {{ pkg.duration_in_days }} hari</p>
                                    </div>
                                </div>
                                <p class="font-black">{{ pkg.formatted_price }}</p>
                            </label>
                        </div>

                        <h2 class="text-lg font-black uppercase mb-4">Metode Pembayaran</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button
                                v-for="option in paymentOptions"
                                :key="option.label"
                                type="button"
                                @click="selectPayment(option)"
                                class="border-2 p-4 text-left font-bold uppercase text-sm transition-colors"
                                :class="isOptionSelected(option) ? 'border-black bg-black text-white' : 'border-gray-300 hover:border-black'"
                            >
                                {{ option.label }}
                            </button>
                        </div>
                    </section>

                    <aside class="border-2 border-black p-6 h-fit">
                        <h2 class="text-lg font-black uppercase mb-4">Ringkasan</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span>Toko</span>
                                <span class="font-bold">{{ tenant.store_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Paket</span>
                                <span class="font-bold">{{ selectedPackage?.name || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Metode</span>
                                <span class="font-bold">{{ selectedPaymentLabel }}</span>
                            </div>
                            <div class="border-t border-gray-300 pt-3 mt-3 flex justify-between text-base">
                                <span class="font-bold">Total</span>
                                <span class="font-black">{{ selectedPackage?.formatted_price || 'Rp 0' }}</span>
                            </div>
                        </div>

                        <p v-if="errorMessage" class="text-red-600 text-sm font-bold mt-4">{{ errorMessage }}</p>

                        <button
                            type="button"
                            @click="submitCheckout"
                            :disabled="isSubmitting"
                            class="w-full mt-5 py-4 bg-black text-white text-sm font-black uppercase tracking-widest hover:scale-[1.02] transition-transform disabled:opacity-50"
                        >
                            <span v-if="isSubmitting">Processing...</span>
                            <span v-else>Pay Now</span>
                        </button>
                    </aside>
                </div>
            </div>
        </main>
    </div>
</template>
