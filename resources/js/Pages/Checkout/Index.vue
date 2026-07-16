<script setup>
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
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
const shippingOptions = ref([]);
const isLoadingShipping = ref(false);

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

// Fetch Shipping Options
const fetchShippingOptions = async () => {
    if (!selectedAddress.value || !selectedAddress.value.city_id) {
        shippingOptions.value = [];
        return;
    }

    isLoadingShipping.value = true;
    shippingOptions.value = [];
    form.shipping_courier = null;
    form.shipping_service = null;
    form.shipping_cost = 0;

    try {
        const response = await axios.post(route('api.shipping.options'), {
            city_id: selectedAddress.value.city_id,
            weight: props.totalWeight || 1000 // default 1kg if 0
        });

        if (response.data.success) {
            shippingOptions.value = response.data.data;
            // Auto select first option if available
            if (shippingOptions.value.length > 0) {
                selectShippingOption(shippingOptions.value[0]);
            }
        }
    } catch (error) {
        console.error('Failed to fetch shipping options', error);
        alert('Gagal mengambil opsi pengiriman. Silakan coba lagi.');
    } finally {
        isLoadingShipping.value = false;
    }
};

// Select Shipping Option
const selectShippingOption = (option) => {
    form.shipping_courier = option.courier_code;
    form.shipping_service = option.service;
    form.shipping_cost = option.cost;
};

// Watch for address changes to refetch shipping
watch(() => form.address_id, (newVal) => {
    if (newVal) {
        fetchShippingOptions();
    }
});

onMounted(() => {
    if (form.address_id) {
        fetchShippingOptions();
    }
});

// Submit checkout
const submitCheckout = () => {
    if (!form.address_id) {
        alert('Please select a shipping address');
        return;
    }

    if (!form.shipping_courier) {
        alert('Please select a shipping method');
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

    <StoreLayout>
        <div class="min-h-screen bg-white text-black">
            <!-- Header section -->
            <div class="max-w-[1440px] mx-auto px-6 lg:px-12 py-12">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 border-b-4 border-black pb-6">
                    <h2 class="text-5xl md:text-7xl font-black text-black tracking-tighter uppercase leading-none">
                        CHECKOUT
                    </h2>
                    <Link
                        :href="route('cart.index')"
                        class="mt-4 sm:mt-0 font-black uppercase tracking-widest text-black border-b-4 border-black hover:bg-black hover:text-white transition-none"
                    >
                        Back to Cart
                    </Link>
                </div>
            
                <!-- Stock Issues Warning -->
                <div v-if="stockIssues && stockIssues.length > 0" class="mb-12 bg-white border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] p-6">
                    <div class="flex items-start gap-4">
                        <svg class="w-8 h-8 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h4 class="font-black text-xl uppercase tracking-widest">Stock Issues</h4>
                            <ul class="mt-2 text-black font-bold list-square list-inside">
                                <li v-for="issue in stockIssues" :key="issue">{{ issue }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:gap-16">
                    <!-- Left Column - Address & Payment -->
                    <div class="lg:col-span-2 space-y-12">
                        <!-- Shipping Address -->
                        <div class="bg-white border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                            <div class="p-6 border-b-4 border-black bg-gray-100">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-2xl font-black uppercase tracking-tighter text-black flex items-center gap-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Shipping Address
                                    </h3>
                                    <button
                                        @click="openAddressModal"
                                        class="text-sm font-black uppercase tracking-widest text-black border-2 border-black px-3 py-1 hover:bg-black hover:text-white transition-none flex items-center gap-2 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Address
                                    </button>
                                </div>
                            </div>

                            <div class="p-6">
                                <!-- No Address -->
                                <div v-if="!hasAddress" class="text-center py-12 border-4 border-dashed border-black">
                                    <svg class="mx-auto h-16 w-16 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <h4 class="mt-4 text-2xl font-black uppercase tracking-tighter">No address found</h4>
                                    <p class="mt-2 text-black font-bold text-sm uppercase tracking-widest">Add a shipping address to continue</p>
                                    <button
                                        @click="openAddressModal"
                                        class="mt-6 px-8 py-3 bg-black text-white text-lg font-black uppercase tracking-widest hover:bg-white hover:text-black border-4 border-black transition-none shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[4px] hover:translate-y-[4px] hover:shadow-none"
                                    >
                                        Add Address
                                    </button>
                                </div>

                                <!-- Address List -->
                                <div v-else class="space-y-4">
                                    <div
                                        v-for="address in addresses"
                                        :key="address.id"
                                        @click="selectAddress(address.id)"
                                        class="relative p-6 border-4 cursor-pointer transition-none"
                                        :class="form.address_id === address.id 
                                            ? 'border-black bg-black text-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]' 
                                            : 'border-black bg-white hover:bg-gray-100 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]'"
                                    >
                                        <!-- Selected Indicator -->
                                        <div 
                                            v-if="form.address_id === address.id"
                                            class="absolute top-4 right-4"
                                        >
                                            <div class="w-8 h-8 bg-white border-2 border-black flex items-center justify-center shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                                <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-4">
                                            <span class="text-3xl mt-1">{{ address.label_icon }}</span>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span class="text-xl font-black uppercase tracking-tighter" :class="form.address_id === address.id ? 'text-white' : 'text-black'">{{ address.label }}</span>
                                                    <span v-if="address.is_default" class="px-2 py-1 text-xs font-black uppercase tracking-widest bg-white text-black border-2 border-black">
                                                        Primary
                                                    </span>
                                                </div>
                                                <p class="text-sm font-bold uppercase tracking-widest mb-1" :class="form.address_id === address.id ? 'text-gray-300' : 'text-gray-700'">{{ address.recipient_name }}</p>
                                                <p class="text-sm font-bold" :class="form.address_id === address.id ? 'text-gray-300' : 'text-gray-500'">{{ address.phone }}</p>
                                                <p class="mt-2 text-base font-bold line-clamp-2" :class="form.address_id === address.id ? 'text-white' : 'text-black'">{{ address.full_address }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Method -->
                        <div v-if="form.address_id" class="bg-white border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                            <div class="p-6 border-b-4 border-black bg-gray-100">
                                <h3 class="text-2xl font-black uppercase tracking-tighter text-black flex items-center gap-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Shipping Method
                                </h3>
                            </div>

                            <div class="p-6">
                                <div v-if="isLoadingShipping" class="flex justify-center py-12">
                                    <div class="w-12 h-12 border-8 border-black border-t-white rounded-full animate-spin"></div>
                                </div>
                                <div v-else-if="shippingOptions.length === 0" class="text-center py-12 text-black font-black uppercase tracking-widest border-4 border-dashed border-black">
                                    No shipping options available for this address.
                                </div>
                                <div v-else class="space-y-4">
                                    <div
                                        v-for="option in shippingOptions"
                                        :key="option.value"
                                        @click="selectShippingOption(option)"
                                        class="relative p-6 border-4 cursor-pointer transition-none"
                                        :class="(form.shipping_courier === option.courier_code && form.shipping_service === option.service)
                                            ? 'border-black bg-black text-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]' 
                                            : 'border-black bg-white hover:bg-gray-100 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]'"
                                    >
                                        <div class="flex items-center gap-6">
                                            <!-- Radio Button -->
                                            <div 
                                                class="w-6 h-6 border-4 flex items-center justify-center flex-shrink-0"
                                                :class="(form.shipping_courier === option.courier_code && form.shipping_service === option.service)
                                                    ? 'border-white bg-black' 
                                                    : 'border-black bg-white'"
                                            >
                                                <div 
                                                    v-if="form.shipping_courier === option.courier_code && form.shipping_service === option.service"
                                                    class="w-3 h-3 bg-white"
                                                ></div>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-black text-lg uppercase tracking-widest">{{ option.courier_name }} - {{ option.service }}</p>
                                                        <p class="text-sm font-bold" :class="(form.shipping_courier === option.courier_code && form.shipping_service === option.service) ? 'text-gray-300' : 'text-gray-500'">{{ option.description }} (ETA: {{ option.etd }} days)</p>
                                                    </div>
                                                    <div class="text-right flex-shrink-0">
                                                        <p class="font-black text-xl">{{ option.formatted_cost }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="bg-white border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                            <div class="p-6 border-b-4 border-black bg-gray-100">
                                <h3 class="text-2xl font-black uppercase tracking-tighter text-black flex items-center gap-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Payment Method
                                </h3>
                            </div>

                            <div class="p-6 space-y-4">
                                <div
                                    v-for="method in availablePaymentMethods"
                                    :key="method.id"
                                    @click="form.payment_method = method.id"
                                    class="relative p-6 border-4 cursor-pointer transition-none"
                                    :class="form.payment_method === method.id 
                                        ? 'border-black bg-black text-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]' 
                                        : 'border-black bg-white hover:bg-gray-100 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]'"
                                >
                                    <div class="flex items-center gap-6">
                                        <!-- Radio Button -->
                                        <div 
                                            class="w-6 h-6 border-4 flex items-center justify-center flex-shrink-0"
                                            :class="form.payment_method === method.id 
                                                ? 'border-white bg-black' 
                                                : 'border-black bg-white'"
                                        >
                                            <div 
                                                v-if="form.payment_method === method.id"
                                                class="w-3 h-3 bg-white"
                                            ></div>
                                        </div>

                                        <!-- Icon -->
                                        <div class="w-14 h-14 bg-white border-4 border-black flex items-center justify-center shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                            <svg v-if="method.icon === 'bank'" class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <svg v-else-if="method.icon === 'wallet'" class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                            </svg>
                                            <svg v-else class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>

                                        <!-- Info -->
                                        <div class="flex-1">
                                            <p class="font-black text-xl uppercase tracking-widest">{{ method.name }}</p>
                                            <p class="text-sm font-bold mt-1" :class="form.payment_method === method.id ? 'text-gray-300' : 'text-gray-600'">{{ method.description }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- No available methods message -->
                                <div v-if="availablePaymentMethods.length === 0" class="text-center py-12 text-black font-black uppercase tracking-widest border-4 border-dashed border-black">
                                    No payment methods available
                                </div>
                            </div>
                        </div>

                        <!-- Customer Notes -->
                        <div class="bg-white border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                            <div class="p-6 border-b-4 border-black bg-gray-100">
                                <h3 class="text-2xl font-black uppercase tracking-tighter text-black flex items-center gap-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Notes (Optional)
                                </h3>
                            </div>
                            <div class="p-6">
                                <textarea
                                    v-model="form.customer_notes"
                                    rows="4"
                                    class="w-full border-4 border-black p-4 font-bold focus:ring-0 focus:border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] outline-none"
                                    placeholder="Add any specific instructions for the seller..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] sticky top-32">
                            <div class="p-6 border-b-4 border-black bg-black text-white">
                                <h3 class="text-3xl font-black uppercase tracking-tighter">Order Summary</h3>
                            </div>

                            <div class="p-6">
                                <!-- Cart Items Preview -->
                                <div class="space-y-6 mb-8 border-b-4 border-black pb-8">
                                    <div 
                                        v-for="item in cartItems" 
                                        :key="item.id"
                                        class="flex gap-4"
                                    >
                                        <div class="w-20 h-24 border-4 border-black bg-gray-100 flex-shrink-0 relative overflow-hidden group">
                                            <img 
                                                v-if="item.product_image"
                                                :src="item.product_image" 
                                                :alt="item.product_name"
                                                class="w-full h-full object-cover grayscale"
                                            />
                                            <div v-else class="w-full h-full flex items-center justify-center">
                                                <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 flex flex-col justify-between py-1">
                                            <div>
                                                <p class="font-black text-lg uppercase tracking-tighter line-clamp-2 leading-none mb-2">{{ item.product_name }}</p>
                                                <p class="text-sm font-bold uppercase tracking-widest bg-gray-100 border-2 border-black inline-block px-2">QTY: {{ item.quantity }}</p>
                                            </div>
                                            <div>
                                                <p class="font-black text-xl">{{ item.formatted_subtotal }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing -->
                                <div class="space-y-4 mb-8 font-bold text-lg">
                                    <div class="flex justify-between text-black">
                                        <span>Subtotal ({{ cart.total_items }} item)</span>
                                        <span class="font-black">{{ cart.formatted_subtotal }}</span>
                                    </div>
                                    <div class="flex justify-between text-black">
                                        <span>Shipping</span>
                                        <span v-if="shippingCost > 0" class="font-black">{{ formatCurrency(shippingCost) }}</span>
                                        <span v-else class="font-black uppercase tracking-widest bg-black text-white px-2 py-1">Free</span>
                                    </div>
                                    <div v-if="discount > 0" class="flex justify-between text-black">
                                        <span>Discount</span>
                                        <span class="font-black">-{{ formatCurrency(discount) }}</span>
                                    </div>
                                </div>

                                <!-- Total -->
                                <div class="border-t-4 border-black pt-6 mb-8">
                                    <div class="flex justify-between items-end">
                                        <span class="text-xl font-black uppercase tracking-widest text-black">Total</span>
                                        <span class="text-4xl font-black text-black tracking-tight">{{ formatCurrency(total) }}</span>
                                    </div>
                                </div>

                                <button
                                    @click="submitCheckout"
                                    :disabled="isProcessing || !form.address_id || !form.shipping_courier || (stockIssues && stockIssues.length > 0)"
                                    class="w-full bg-black hover:bg-white text-white hover:text-black border-4 border-black font-black py-6 px-6 transition-none flex items-center justify-center gap-4 text-xl uppercase tracking-widest shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[8px] hover:translate-y-[8px] disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="isProcessing" class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ isProcessing ? 'PROCESSING...' : 'PLACE ORDER' }}
                                </button>

                                <!-- Security badges -->
                                <div class="mt-8 flex items-center justify-center gap-6 text-sm font-black uppercase tracking-widest text-black">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        SECURE
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        VERIFIED
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
    </StoreLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
