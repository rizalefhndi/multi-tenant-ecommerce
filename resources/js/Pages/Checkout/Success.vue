<script setup>
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    order: Object,
    items: Array,
    transaction: Object,
});

// Animation state
const showConfetti = ref(true);
const isPaying = ref(false);

const isMidtransPayment = computed(() => {
    if (!props.transaction) return false;
    return ['virtual_account', 'gopay', 'shopeepay', 'qris'].includes(props.transaction.payment_method);
});

onMounted(() => {
    if (props.order.status === 'payment_received' || props.order.status === 'processing') {
        // Hide confetti after 5 seconds
        setTimeout(() => {
            showConfetti.value = false;
        }, 5000);
    } else {
        showConfetti.value = false;
        
        // Auto trigger payment for midtrans if pending
        if (isMidtransPayment.value && props.order.status === 'pending_payment') {
            payNow();
        }
    }
});

const loadSnapScript = (clientKey) => {
    return new Promise((resolve) => {
        if (window.snap) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        // TODO: Update URL based on env (sandbox/production)
        script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
        script.setAttribute('data-client-key', clientKey);
        script.onload = () => resolve();
        document.head.appendChild(script);
    });
};

const payNow = async () => {
    if (isPaying.value) return;
    isPaying.value = true;
    
    try {
        const response = await axios.post(route('api.payment.snap-token'), {
            order_id: props.order.id
        });
        
        const { snap_token, client_key } = response.data;
        
        await loadSnapScript(client_key);
        
        window.snap.pay(snap_token, {
            onSuccess: function(result) {
                router.reload();
            },
            onPending: function(result) {
                // Do nothing, already pending
                console.log('Pending', result);
            },
            onError: function(result) {
                alert('Payment failed');
                console.error(result);
            },
            onClose: function() {
                isPaying.value = false;
            }
        });
    } catch (error) {
        console.error(error);
        alert('Gagal memuat pembayaran');
        isPaying.value = false;
    }
};

// Copy to clipboard
const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('Disalin ke clipboard!');
};
</script>

<template>
    <Head title="Pesanan Berhasil" />

    <StoreLayout>
        <div class="py-12 bg-white min-h-screen text-black">
            <div class="max-w-3xl mx-auto px-6 lg:px-8">
                <!-- Success Card -->
                <div class="bg-white border-4 border-black shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] relative">
                    <!-- Success Header -->
                    <div class="bg-black border-b-4 border-black px-6 py-16 text-center text-white relative overflow-hidden">
                        <!-- Confetti Animation -->
                        <div v-if="showConfetti" class="absolute inset-0 pointer-events-none">
                            <div class="confetti-piece" v-for="i in 20" :key="i" :style="{ left: `${i * 5}%`, animationDelay: `${i * 0.1}s` }"></div>
                        </div>

                        <!-- Success Icon -->
                        <div v-if="order.status === 'pending_payment'" class="relative z-10">
                            <div class="mx-auto w-24 h-24 bg-white border-4 border-white flex items-center justify-center mb-6 shadow-[8px_8px_0px_0px_rgba(255,255,255,0.3)]">
                                <svg class="w-12 h-12 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h1 class="text-4xl md:text-5xl font-black mb-2 uppercase tracking-tighter">Waiting for Payment</h1>
                            <p class="text-white font-bold uppercase tracking-widest text-sm">Please complete your payment</p>
                        </div>
                        <div v-else class="relative z-10">
                            <div class="mx-auto w-24 h-24 bg-white border-4 border-white flex items-center justify-center mb-6 animate-bounce-once shadow-[8px_8px_0px_0px_rgba(255,255,255,0.3)]">
                                <svg class="w-16 h-16 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h1 class="text-4xl md:text-5xl font-black mb-2 uppercase tracking-tighter">Order Successful!</h1>
                            <p class="text-white font-bold uppercase tracking-widest text-sm">Thank you for your order</p>
                        </div>
                    </div>

                    <!-- Order Info -->
                    <div class="p-8 border-b-4 border-black bg-gray-100">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                            <div>
                                <p class="text-sm font-black uppercase tracking-widest text-black mb-1">Order Number</p>
                                <p class="text-3xl font-black text-black uppercase">{{ order.order_number }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-black uppercase tracking-widest text-black mb-1">Order Date</p>
                                <p class="text-xl font-bold text-black">{{ order.created_at }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info (for bank transfer) -->
                    <div v-if="transaction && transaction.payment_method === 'bank_transfer'" class="p-8 border-b-4 border-black bg-white">
                        <div class="flex items-start gap-6">
                            <div class="w-16 h-16 border-4 border-black flex items-center justify-center flex-shrink-0 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] bg-gray-100">
                                <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-2xl font-black uppercase tracking-tighter">Waiting for Payment</h4>
                                <p class="text-base font-bold mt-2">
                                    Please transfer to the following account before <br/> <span class="bg-black text-white px-2 py-1 mt-2 inline-block">{{ transaction.expires_at }}</span>
                                </p>

                                <div v-if="transaction.bank_transfer_info" class="mt-6 border-4 border-black p-6 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] bg-gray-100">
                                    <div class="space-y-4">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                            <span class="text-sm font-black uppercase tracking-widest">Bank</span>
                                            <span class="text-xl font-black uppercase">{{ transaction.bank_transfer_info.bank_name }}</span>
                                        </div>
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                            <span class="text-sm font-black uppercase tracking-widest">Account Number</span>
                                            <div class="flex items-center gap-4">
                                                <span class="text-2xl font-black uppercase">{{ transaction.bank_transfer_info.account_number }}</span>
                                                <button 
                                                    @click="copyToClipboard(transaction.bank_transfer_info.account_number)"
                                                    class="border-2 border-black p-1 hover:bg-black hover:text-white transition-none"
                                                >
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                            <span class="text-sm font-black uppercase tracking-widest">Account Name</span>
                                            <span class="text-xl font-black uppercase">{{ transaction.bank_transfer_info.account_holder }}</span>
                                        </div>
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 pt-4 border-t-4 border-black">
                                            <span class="text-sm font-black uppercase tracking-widest">Transfer Amount</span>
                                            <div class="flex items-center gap-4">
                                                <span class="text-3xl font-black bg-black text-white px-2">{{ transaction.formatted_amount }}</span>
                                                <button 
                                                    @click="copyToClipboard(transaction.amount.toString())"
                                                    class="border-2 border-black p-1 hover:bg-black hover:text-white transition-none"
                                                >
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-sm font-bold uppercase mt-6">
                                    * Transfer the exact amount to speed up verification
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Midtrans Payment CTA -->
                    <div v-if="isMidtransPayment && order.status === 'pending_payment'" class="p-8 border-b-4 border-black bg-gray-100 text-center">
                        <h4 class="text-2xl font-black uppercase tracking-tighter mb-4">Complete Payment</h4>
                        <p class="text-lg font-bold mb-8">Complete your payment using {{ transaction.payment_method_label || 'Midtrans' }}</p>
                        <button 
                            @click="payNow" 
                            :disabled="isPaying"
                            class="bg-black hover:bg-white hover:text-black text-white font-black py-4 px-12 border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[8px] hover:translate-y-[8px] transition-none uppercase tracking-widest text-xl disabled:opacity-50"
                        >
                            {{ isPaying ? 'LOADING...' : 'PAY NOW' }}
                        </button>
                    </div>

                    <!-- Order Items -->
                    <div class="p-8 border-b-4 border-black">
                        <h4 class="text-xl font-black uppercase tracking-widest mb-6 border-b-4 border-black pb-2 inline-block">Order Items ({{ items.length }})</h4>
                        <div class="space-y-6">
                            <div 
                                v-for="item in items" 
                                :key="item.id"
                                class="flex gap-6 p-4 border-4 border-black bg-gray-100 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]"
                            >
                                <div class="w-24 h-24 border-4 border-black bg-white flex-shrink-0 relative overflow-hidden group">
                                    <img 
                                        v-if="item.product_image"
                                        :src="item.product_image" 
                                        :alt="item.product_name"
                                        class="w-full h-full object-cover grayscale"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0 flex flex-col justify-between py-1">
                                    <div>
                                        <p class="font-black text-xl uppercase tracking-tighter leading-none mb-2">{{ item.product_name }}</p>
                                        <p class="text-sm font-bold uppercase tracking-widest bg-white border-2 border-black px-2 inline-block">QTY: {{ item.quantity }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-black text-xl">{{ item.formatted_subtotal }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div v-if="order.shipping_address" class="p-8 border-b-4 border-black">
                        <h4 class="text-xl font-black uppercase tracking-widest mb-4 bg-black text-white px-3 py-1 inline-block">Shipping Address</h4>
                        <div class="text-black border-4 border-black p-6 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] bg-gray-100">
                            <p class="font-black text-2xl uppercase tracking-tighter mb-2">{{ order.shipping_address.recipient_name }}</p>
                            <p class="font-bold mb-2">{{ order.shipping_address.phone }}</p>
                            <p class="font-bold uppercase">{{ order.shipping_address.full_address || order.shipping_address.address }}</p>
                        </div>
                    </div>

                    <!-- Price Summary -->
                    <div class="p-8 border-b-4 border-black bg-gray-100">
                        <div class="space-y-4 font-bold text-lg uppercase tracking-widest">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span class="font-black">{{ order.formatted_subtotal }}</span>
                            </div>
                            <div v-if="order.shipping_cost > 0" class="flex justify-between">
                                <span>Shipping</span>
                                <span class="font-black">{{ order.formatted_shipping_cost }}</span>
                            </div>
                            <div class="flex justify-between text-2xl font-black pt-4 border-t-4 border-black">
                                <span>Total</span>
                                <span class="bg-black text-white px-2 py-1">{{ order.formatted_total }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-8 flex flex-col sm:flex-row gap-6">
                        <Link
                            :href="route('orders.show', order.id)"
                            class="flex-1 flex items-center justify-center gap-3 px-6 py-4 bg-black text-white hover:bg-white hover:text-black border-4 border-black font-black uppercase tracking-widest transition-none shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[8px] hover:translate-y-[8px]"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            VIEW ORDER
                        </Link>
                        <Link
                            :href="route('products.index')"
                            class="flex-1 flex items-center justify-center gap-3 px-6 py-4 bg-white text-black hover:bg-black hover:text-white border-4 border-black font-black uppercase tracking-widest transition-none shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[8px] hover:translate-y-[8px]"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            CONTINUE SHOPPING
                        </Link>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-8 text-center text-sm font-black uppercase tracking-widest text-black">
                    <p>Have questions? <a href="#" class="border-b-2 border-black hover:bg-black hover:text-white transition-none">Contact Us</a></p>
                </div>
            </div>
        </div>
    </StoreLayout>
</template>

<style scoped>
@keyframes bounce-once {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.animate-bounce-once {
    animation: bounce-once 0.5s ease-in-out;
}

@keyframes confetti-fall {
    0% {
        transform: translateY(-100%) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(400%) rotate(720deg);
        opacity: 0;
    }
}

.confetti-piece {
    position: absolute;
    width: 10px;
    height: 10px;
    top: -20px;
    background-color: hsl(calc(var(--i, 0) * 36), 80%, 60%);
    animation: confetti-fall 3s ease-in-out forwards;
}

.confetti-piece:nth-child(1) { --i: 1; background-color: #f43f5e; }
.confetti-piece:nth-child(2) { --i: 2; background-color: #f97316; }
.confetti-piece:nth-child(3) { --i: 3; background-color: #eab308; }
.confetti-piece:nth-child(4) { --i: 4; background-color: #22c55e; }
.confetti-piece:nth-child(5) { --i: 5; background-color: #3b82f6; }
.confetti-piece:nth-child(6) { --i: 6; background-color: #8b5cf6; }
.confetti-piece:nth-child(7) { --i: 7; background-color: #ec4899; }
.confetti-piece:nth-child(8) { --i: 8; background-color: #14b8a6; }
.confetti-piece:nth-child(9) { --i: 9; background-color: #f43f5e; }
.confetti-piece:nth-child(10) { --i: 10; background-color: #f97316; }
.confetti-piece:nth-child(11) { --i: 11; background-color: #eab308; }
.confetti-piece:nth-child(12) { --i: 12; background-color: #22c55e; }
.confetti-piece:nth-child(13) { --i: 13; background-color: #3b82f6; }
.confetti-piece:nth-child(14) { --i: 14; background-color: #8b5cf6; }
.confetti-piece:nth-child(15) { --i: 15; background-color: #ec4899; }
.confetti-piece:nth-child(16) { --i: 16; background-color: #14b8a6; }
.confetti-piece:nth-child(17) { --i: 17; background-color: #f43f5e; }
.confetti-piece:nth-child(18) { --i: 18; background-color: #f97316; }
.confetti-piece:nth-child(19) { --i: 19; background-color: #eab308; }
.confetti-piece:nth-child(20) { --i: 20; background-color: #22c55e; }
</style>
