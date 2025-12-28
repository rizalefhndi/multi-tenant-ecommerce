<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AddressCard from '@/Components/AddressCard.vue';
import AddressFormModal from '@/Components/AddressFormModal.vue';

const props = defineProps({
    cart: Object,
    cartItems: Array,
    addresses: Array,
    hasAddress: Boolean,
    defaultAddressId: Number,
    totalWeight: Number,
    stockIssues: Array,
    paymentMethods: Array,
});

// Form data
const form = useForm({
    address_id: props.defaultAddressId,
    payment_method: 'bank_transfer',
    shipping_courier: null,
    shipping_service: null,
    shipping_cost: 0,
    customer_notes: '',
});

// State
const isProcessing = ref(false);
const showAddressModal = ref(false);
const selectedAddress = computed(() => {
    return props.addresses.find(addr => addr.id === form.address_id);
});

// Available payment methods (filter hanya yang available)
const availablePaymentMethods = computed(() => {
    return props.paymentMethods.filter(method => method.is_available);
});

// Totals
const subtotal = computed(() => props.cart.subtotal);
const shippingCost = computed(() => form.shipping_cost);
const discount = computed(() => 0);
const total = computed(() => subtotal.value + shippingCost.value - discount.value);

// Format currency
const formatCurrency = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};

// Select address
const selectAddress = (addressId) => {
    form.address_id = addressId;
};

// Open add address modal
const openAddressModal = () => {
    showAddressModal.value = true;
};

// Close add address modal
const closeAddressModal = () => {
    showAddressModal.value = false;
};

// Handle address added/saved
const handleAddressSaved = (address) => {
    closeAddressModal();
    // Reload page to get updated addresses
    router.reload({ only: ['addresses'] });
    // Select the new address
    form.address_id = address.id;
};

// Submit checkout
const submitCheckout = () => {
    if (!form.address_id) {
        alert('Please select a shipping address');
        return;
    }

    if (!form.payment_method) {
        alert('Please select a payment method');
        return;
    }

    if (props.stockIssues && props.stockIssues.length > 0) {
        alert('There are stock issues:\n' + props.stockIssues.join('\n'));
        return;
    }

    isProcessing.value = true;
    form.post(route('checkout.process'), {
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Checkout" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checkout</h2>
                <Link
                    :href="route('cart.index')"
                    class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Cart
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stock Issues Warning -->
                <div v-if="stockIssues && stockIssues.length > 0" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h4 class="font-medium text-red-800">Stock Issues</h4>
                            <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                <li v-for="issue in stockIssues" :key="issue">{{ issue }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column - Address & Payment -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Shipping Address -->
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-gray-100">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Shipping Address
                                    </h3>
                                    <button
                                        @click="openAddressModal"
                                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Address
                                    </button>
                                </div>
                            </div>

                            <div class="p-6">
                                <!-- No Address -->
                                <div v-if="!hasAddress" class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <h4 class="mt-3 text-gray-900 font-medium">No address found</h4>
                                    <p class="mt-1 text-gray-500 text-sm">Add a shipping address to continue</p>
                                    <button
                                        @click="openAddressModal"
                                        class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                                    >
                                        Add Address
                                    </button>
                                </div>

                                <!-- Address List -->
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="address in addresses"
                                        :key="address.id"
                                        @click="selectAddress(address.id)"
                                        class="relative p-4 border-2 rounded-lg cursor-pointer transition-all"
                                        :class="form.address_id === address.id 
                                            ? 'border-indigo-600 bg-indigo-50' 
                                            : 'border-gray-200 hover:border-gray-300'"
                                    >
                                        <!-- Selected Indicator -->
                                        <div 
                                            v-if="form.address_id === address.id"
                                            class="absolute top-3 right-3"
                                        >
                                            <div class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-3">
                                            <span class="text-xl">{{ address.label_icon }}</span>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium text-gray-900">{{ address.label }}</span>
                                                    <span v-if="address.is_default" class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full">
                                                        Primary
                                                    </span>
                                                </div>
                                                <p class="mt-1 text-sm font-medium text-gray-700">{{ address.recipient_name }}</p>
                                                <p class="text-sm text-gray-500">{{ address.phone }}</p>
                                                <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ address.full_address }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Payment Method
                                </h3>
                            </div>

                            <div class="p-6 space-y-3">
                                <div
                                    v-for="method in availablePaymentMethods"
                                    :key="method.id"
                                    @click="form.payment_method = method.id"
                                    class="relative p-4 border-2 rounded-lg cursor-pointer transition-all"
                                    :class="form.payment_method === method.id 
                                        ? 'border-indigo-600 bg-indigo-50' 
                                        : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <div class="flex items-center gap-4">
                                        <!-- Radio Button -->
                                        <div 
                                            class="w-5 h-5 border-2 rounded-full flex items-center justify-center"
                                            :class="form.payment_method === method.id 
                                                ? 'border-indigo-600' 
                                                : 'border-gray-300'"
                                        >
                                            <div 
                                                v-if="form.payment_method === method.id"
                                                class="w-3 h-3 bg-indigo-600 rounded-full"
                                            ></div>
                                        </div>

                                        <!-- Icon -->
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <svg v-if="method.icon === 'bank'" class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <svg v-else-if="method.icon === 'wallet'" class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                            </svg>
                                            <svg v-else class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>

                                        <!-- Info -->
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">{{ method.name }}</p>
                                            <p class="text-sm text-gray-500">{{ method.description }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- No available methods message -->
                                <div v-if="availablePaymentMethods.length === 0" class="text-center py-6 text-gray-500">
                                    No payment methods available
                                </div>
                            </div>
                        </div>

                        <!-- Customer Notes -->
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Catatan (Opsional)
                                </h3>
                            </div>
                            <div class="p-6">
                                <textarea
                                    v-model="form.customer_notes"
                                    rows="3"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Notes for seller (color, size, etc)"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-sm sticky top-6 overflow-hidden">
                            <div class="p-6 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-gray-900">Order Summary</h3>
                            </div>

                            <div class="p-6">
                                <!-- Cart Items Preview -->
                                <div class="space-y-4 mb-6">
                                    <div 
                                        v-for="item in cartItems" 
                                        :key="item.id"
                                        class="flex gap-3"
                                    >
                                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                            <img 
                                                v-if="item.product_image"
                                                :src="item.product_image" 
                                                :alt="item.product_name"
                                                class="w-full h-full object-cover"
                                            />
                                            <div v-else class="w-full h-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ item.product_name }}</p>
                                            <p class="text-sm text-gray-500">{{ item.quantity }}x {{ item.formatted_price }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900">{{ item.formatted_subtotal }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div class="border-t border-gray-200 my-6"></div>

                                <!-- Pricing -->
                                <div class="space-y-3 mb-6">
                                    <div class="flex justify-between text-gray-600">
                                        <span>Subtotal ({{ cart.total_items }} item)</span>
                                        <span>{{ cart.formatted_subtotal }}</span>
                                    </div>
                                    <div class="flex justify-between text-gray-600">
                                        <span>Shipping</span>
                                        <span v-if="shippingCost > 0">{{ formatCurrency(shippingCost) }}</span>
                                        <span v-else class="text-green-600">Gratis</span>
                                    </div>
                                    <div v-if="discount > 0" class="flex justify-between text-green-600">
                                        <span>Discount</span>
                                        <span>-{{ formatCurrency(discount) }}</span>
                                    </div>
                                </div>

                                <!-- Total -->
                                <div class="border-t border-gray-200 pt-4 mb-6">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900">Total</span>
                                        <span class="text-2xl font-bold text-indigo-600">{{ formatCurrency(total) }}</span>
                                    </div>
                                </div>

                                <!-- Checkout Button -->
                                <button
                                    @click="submitCheckout"
                                    :disabled="isProcessing || !form.address_id || (stockIssues && stockIssues.length > 0)"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold py-4 px-6 rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
                                >
                                    <svg v-if="isProcessing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ isProcessing ? 'Processing...' : 'Place Order' }}
                                </button>

                                <!-- Security badges -->
                                <div class="mt-4 flex items-center justify-center gap-4 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                        </svg>
                                        Secure
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Verified
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Form Modal -->
        <AddressFormModal 
            :show="showAddressModal"
            @close="closeAddressModal"
            @saved="handleAddressSaved"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
