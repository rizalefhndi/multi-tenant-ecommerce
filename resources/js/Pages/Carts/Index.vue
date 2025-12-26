<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartItemComponent from '@/Components/CartItemComponent.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cart: Object,
});

const clearCart = () => {
    if (confirm('Are you sure you want to clear your cart?')) {
        router.delete(route('cart.clear'), {
            preserveScroll: true,
        });
    }
};

// Checkout sekarang redirect ke halaman checkout
const goToCheckout = () => {
    router.visit(route('checkout.index'));
};
</script>

<template>
    <Head title="Shopping Cart" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Shopping Cart</h2>
                <Link
                    :href="route('products.index')"
                    class="text-indigo-600 hover:text-indigo-800"
                >
                    Continue Shopping
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Cart Items -->
                <div v-if="cart.items.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Items List -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Cart Items ({{ cart.total_items }})
                                </h3>
                                <button
                                    @click="clearCart"
                                    class="text-sm text-red-600 hover:text-red-800"
                                >
                                    Clear Cart
                                </button>
                            </div>

                            <div class="space-y-4">
                                <CartItemComponent
                                    v-for="item in cart.items"
                                    :key="item.id"
                                    :item="item"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-6">Order Summary</h3>

                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span>{{ cart.formatted_total }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Shipping</span>
                                    <span class="text-green-600">Free</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Tax</span>
                                    <span>Included</span>
                                </div>
                            </div>

                            <div class="border-t pt-4 mb-6">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-gray-900">Total</span>
                                    <span class="text-2xl font-bold text-indigo-600">
                                        {{ cart.formatted_total }}
                                    </span>
                                </div>
                            </div>

                            <Link
                                :href="route('checkout.index')"
                                class="w-full block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 mb-3"
                            >
                                Proceed to Checkout
                            </Link>

                            <Link
                                :href="route('products.index')"
                                class="block w-full text-center text-indigo-600 hover:text-indigo-800 font-medium py-2"
                            >
                                Continue Shopping
                            </Link>

                            <!-- Trust Badges -->
                            <div class="mt-6 pt-6 border-t space-y-3">
                                <div class="flex items-center gap-3 text-sm text-gray-600">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Secure checkout</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-gray-600">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Free shipping</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-gray-600">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Easy returns</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty Cart -->
                <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-2">Your cart is empty</h3>
                    <p class="text-gray-600 mb-6">
                        Start shopping to add items to your cart
                    </p>
                    <Link
                        :href="route('products.index')"
                        class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Browse Products
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
