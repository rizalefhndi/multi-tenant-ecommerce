<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    product: Object,
});

const quantity = ref(1);
const isAdding = ref(false);

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const addToCart = () => {
    if (quantity.value < 1 || quantity.value > props.product.stock) {
        alert('Invalid quantity!');
        return;
    }

    isAdding.value = true;

    router.post(route('cart.add'), {
        product_id: props.product.id,
        quantity: quantity.value,
    }, {
        onFinish: () => {
            isAdding.value = false;
        },
    });
};
</script>

<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Product Details</h2>
                <div class="flex gap-3">
                    <Link
                        :href="route('products.edit', product.id)"
                        class="text-indigo-600 hover:text-indigo-800"
                    >
                        Edit
                    </Link>
                    <Link
                        :href="route('products.index')"
                        class="text-gray-600 hover:text-gray-800"
                    >
                        ← Back
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
                        <!-- Product Image -->
                        <div class="aspect-square bg-gray-200 rounded-lg overflow-hidden">
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="flex flex-col">
                            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                                {{ product.name }}
                            </h1>

                            <!-- Status Badges -->
                            <div class="flex gap-2 mb-4">
                                <span
                                    :class="[
                                        'px-3 py-1 text-sm font-semibold rounded-full',
                                        product.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800'
                                    ]"
                                >
                                    {{ product.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span
                                    :class="[
                                        'px-3 py-1 text-sm font-semibold rounded-full',
                                        product.stock > 0
                                            ? 'bg-blue-100 text-blue-800'
                                            : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ product.stock > 0 ? `${product.stock} in stock` : 'Out of Stock' }}
                                </span>
                            </div>

                            <!-- Price -->
                            <div class="mb-6">
                                <span class="text-4xl font-bold text-indigo-600">
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>

                            <!-- Description -->
                            <div class="mb-6 flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                                <p class="text-gray-600 leading-relaxed">
                                    {{ product.description || 'No description available.' }}
                                </p>
                            </div>

                            <!-- Add to Cart Section -->
                            <div v-if="product.is_active && product.stock > 0" class="border-t pt-6">
                                <div class="flex items-center gap-4 mb-4">
                                    <label class="text-sm font-medium text-gray-700">Quantity:</label>
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="quantity = Math.max(1, quantity - 1)"
                                            class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50"
                                        >
                                            -
                                        </button>
                                        <input
                                            v-model.number="quantity"
                                            type="number"
                                            min="1"
                                            :max="product.stock"
                                            class="w-20 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        />
                                        <button
                                            @click="quantity = Math.min(product.stock, quantity + 1)"
                                            class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>

                                <button
                                    @click="addToCart"
                                    :disabled="isAdding"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ isAdding ? 'Adding...' : 'Add to Cart' }}
                                </button>
                            </div>

                            <!-- Unavailable Message -->
                            <div v-else class="border-t pt-6">
                                <div class="bg-red-50 text-red-800 px-4 py-3 rounded-lg">
                                    <p class="font-semibold">Product Unavailable</p>
                                    <p class="text-sm">
                                        {{ !product.is_active ? 'This product is currently inactive.' : 'This product is out of stock.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
