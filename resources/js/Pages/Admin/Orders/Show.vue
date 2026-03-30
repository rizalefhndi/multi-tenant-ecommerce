<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStatusBadge from '@/Components/OrderStatusBadge.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    order: Object,
    items: Array,
    transactions: Array,
    allowedTransitions: Array,
});

const statusForm = useForm({
    status: props.order.status,
    tracking_number: props.order.shipping_tracking_number || '',
    admin_notes: '',
});

const trackingForm = useForm({
    shipping_courier: props.order.shipping_courier || '',
    shipping_service: props.order.shipping_service || '',
    shipping_tracking_number: props.order.shipping_tracking_number || '',
});

const updateStatus = () => {
    statusForm.patch(route('admin.orders.update-status', props.order.id), {
        preserveScroll: true,
    });
};

const updateTracking = () => {
    trackingForm.patch(route('admin.orders.update-tracking', props.order.id), {
        preserveScroll: true,
    });
};

const verifyPayment = (transactionId) => {
    router.post(route('admin.orders.verify-payment', [props.order.id, transactionId]), {}, {
        preserveScroll: true,
    });
};

const rejectPayment = (transactionId) => {
    const reason = globalThis.prompt('Reason for rejection:');
    if (!reason) {
        return;
    }

    router.post(route('admin.orders.reject-payment', [props.order.id, transactionId]), {
        reason,
    }, {
        preserveScroll: true,
    });
};

const pendingManualPayments = computed(() => {
    return (props.transactions || []).filter((trx) => trx.can_verify);
});
</script>

<template>
    <Head>
        <title>Admin Order {{ order.order_number }}</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <Link
                                :href="route('admin.orders.index')"
                                class="h-10 w-10 border border-black bg-white text-black hover:bg-black hover:text-white transition-colors flex items-center justify-center"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </Link>
                            <div>
                                <p class="onyx-kicker">Admin Ops</p>
                                <h1 class="onyx-title text-2xl md:text-3xl mt-2">Order Detail</h1>
                                <p class="font-mono text-sm text-black/60 mt-2">{{ order.order_number }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <OrderStatusBadge :status="order.status" :label="order.status_label" size="lg" />
                            <Link :href="route('admin.orders.invoice', order.id)" class="onyx-action-ghost">Invoice</Link>
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
                    <div class="xl:col-span-2 space-y-6">
                        <section class="onyx-panel p-5 md:p-6">
                            <h2 class="onyx-title text-sm mb-4">Customer</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.08em] text-black/55">Name</p>
                                    <p class="font-semibold text-black mt-1">{{ order.customer?.name || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.08em] text-black/55">Email</p>
                                    <p class="text-black mt-1">{{ order.customer?.email || '-' }}</p>
                                </div>
                            </div>
                        </section>

                        <section class="onyx-panel overflow-hidden">
                            <div class="p-5 md:p-6 border-b border-black/10">
                                <h2 class="onyx-title text-sm">Order Items</h2>
                            </div>
                            <div class="divide-y divide-black/10">
                                <article
                                    v-for="item in items"
                                    :key="item.id"
                                    class="p-4 md:p-5 flex gap-4 hover:bg-black/[0.03]"
                                >
                                    <div class="w-16 h-16 border border-black/15 bg-black/5 overflow-hidden flex-shrink-0">
                                        <img
                                            v-if="item.product_image"
                                            :src="item.product_image"
                                            :alt="item.product_name"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-black">{{ item.product_name }}</p>
                                        <p class="text-sm text-black/60">{{ item.quantity }} x {{ item.formatted_price }}</p>
                                        <p v-if="item.product_sku" class="text-xs font-mono text-black/45 mt-1">SKU: {{ item.product_sku }}</p>
                                    </div>
                                    <p class="font-semibold text-black">{{ item.formatted_subtotal }}</p>
                                </article>
                            </div>
                        </section>

                        <section class="onyx-panel p-5 md:p-6">
                            <h2 class="onyx-title text-sm mb-4">Payment Verification</h2>
                            <div v-if="pendingManualPayments.length" class="space-y-3">
                                <article
                                    v-for="trx in pendingManualPayments"
                                    :key="trx.id"
                                    class="border border-black/15 bg-black/[0.02] p-4"
                                >
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.08em] text-black/55">Transaction</p>
                                            <p class="font-mono text-black">{{ trx.transaction_id }}</p>
                                            <p class="text-sm text-black/70 mt-1">{{ trx.formatted_amount }} • {{ trx.payment_method_label }}</p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button class="onyx-action" @click="verifyPayment(trx.id)">Verify</button>
                                            <button class="onyx-action-ghost border-red-900 text-red-700 hover:bg-red-700 hover:text-white" @click="rejectPayment(trx.id)">Reject</button>
                                        </div>
                                    </div>
                                    <a
                                        v-if="trx.transfer_proof_url"
                                        :href="trx.transfer_proof_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex mt-3 text-sm text-black underline"
                                    >
                                        View transfer proof
                                    </a>
                                </article>
                            </div>
                            <p v-else class="text-sm text-black/60">No pending manual verification.</p>
                        </section>
                    </div>

                    <aside class="xl:col-span-1 space-y-6">
                        <section class="onyx-panel p-5 md:p-6">
                            <h2 class="onyx-title text-sm mb-4">Update Status</h2>
                            <form class="space-y-3" @submit.prevent="updateStatus">
                                <div>
                                    <p class="onyx-kicker mb-1.5">Next Status</p>
                                    <select v-model="statusForm.status" class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none">
                                        <option v-for="option in allowedTransitions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <p class="onyx-kicker mb-1.5">Tracking Number (optional)</p>
                                    <input v-model="statusForm.tracking_number" type="text" class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none" />
                                </div>
                                <div>
                                    <p class="onyx-kicker mb-1.5">Admin Notes</p>
                                    <textarea v-model="statusForm.admin_notes" rows="3" class="w-full border border-black bg-white px-3 py-2.5 text-sm focus:outline-none"></textarea>
                                </div>
                                <button type="submit" class="onyx-action w-full" :disabled="statusForm.processing">{{ statusForm.processing ? 'Saving...' : 'Update Status' }}</button>
                            </form>
                        </section>

                        <section class="onyx-panel p-5 md:p-6">
                            <h2 class="onyx-title text-sm mb-4">Shipping Update</h2>
                            <form class="space-y-3" @submit.prevent="updateTracking">
                                <div>
                                    <p class="onyx-kicker mb-1.5">Courier</p>
                                    <input v-model="trackingForm.shipping_courier" type="text" class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none" />
                                </div>
                                <div>
                                    <p class="onyx-kicker mb-1.5">Service</p>
                                    <input v-model="trackingForm.shipping_service" type="text" class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none" />
                                </div>
                                <div>
                                    <p class="onyx-kicker mb-1.5">Tracking Number</p>
                                    <input v-model="trackingForm.shipping_tracking_number" type="text" class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none" />
                                </div>
                                <button type="submit" class="onyx-action-ghost w-full" :disabled="trackingForm.processing">{{ trackingForm.processing ? 'Saving...' : 'Update Shipping' }}</button>
                            </form>
                        </section>

                        <section class="onyx-panel p-5 md:p-6">
                            <h2 class="onyx-title text-sm mb-4">Payment Summary</h2>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between text-black/70"><span>Subtotal</span><span>{{ order.formatted_subtotal }}</span></div>
                                <div class="flex justify-between text-black/70"><span>Shipping</span><span>{{ order.formatted_shipping_cost }}</span></div>
                                <div class="flex justify-between text-black font-semibold pt-2 border-t border-black/15"><span>Total</span><span>{{ order.formatted_total }}</span></div>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
