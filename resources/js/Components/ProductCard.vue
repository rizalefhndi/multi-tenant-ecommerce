<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Props untuk ProductCard
 */
const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    showActions: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

/**
 * Format harga ke Rupiah
 */
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

/**
 * Add to cart (Customer only)
 */
const addToCart = () => {
    router.post(route('cart.add'), {
        product_id: props.product.id,
        quantity: 1,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Success message handled by flash
        },
    });
};

/**
 * Toggle product status (Admin only)
 */
const toggleStatus = () => {
    router.patch(route('products.toggle-status', props.product.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group">
        <!-- Product Image -->
        <div class="relative aspect-square bg-gray-100 overflow-hidden">
            <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <!-- Status Badges -->
            <div class="absolute top-3 left-3 flex flex-col gap-2">
                <span
                    v-if="product.is_active"
                    class="px-2.5 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full"
                >
                    Active
                </span>
                <span
                    v-else
                    class="px-2.5 py-1 text-xs font-semibold text-gray-700 bg-gray-200 rounded-full"
                >
                    Inactive
                </span>
            </div>

            <div class="absolute top-3 right-3 flex flex-col gap-2">
                <span
                    v-if="product.stock <= 0"
                    class="px-2.5 py-1 text-xs font-semibold text-white bg-red-500 rounded-full"
                >
                    Out of Stock
                </span>
                <span
                    v-else-if="product.stock < 10"
                    class="px-2.5 py-1 text-xs font-semibold text-orange-700 bg-orange-100 rounded-full"
                >
                    Low Stock
                </span>
                <span
                    v-else
                    class="px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full"
                >
                    {{ product.stock }} in stock
                </span>
            </div>
        </div>

        <!-- Product Info -->
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-1">
                {{ product.name }}
            </h3>

            <p class="text-sm text-gray-500 mb-3 line-clamp-2 min-h-[2.5rem]">
                {{ product.description || 'No description available' }}
            </p>

            <div class="flex items-center justify-between mb-4">
                <span class="text-xl font-bold text-indigo-600">
                    {{ formatPrice(product.price) }}
                </span>
            </div>

            <!-- Actions -->
            <div v-if="showActions" class="flex gap-2">
                <!-- Admin Actions -->
                <template v-if="isAdmin">
                    <Link
                        :href="route('products.edit', product.id)"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </Link>

                    <button
                        @click="toggleStatus"
                        :class="[
                            'px-3 py-2.5 rounded-xl transition-colors duration-200',
                            product.is_active 
                                ? 'bg-gray-100 hover:bg-gray-200 text-gray-700' 
                                : 'bg-green-100 hover:bg-green-200 text-green-700'
                        ]"
                        :title="product.is_active ? 'Deactivate' : 'Activate'"
                    >
                        <svg v-if="product.is_active" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <Link
                        :href="route('products.show', product.id)"
                        class="px-3 py-2.5 border-2 border-gray-200 rounded-xl hover:bg-gray-50 transition-colors duration-200"
                        title="View Details"
                    >
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </Link>
                </template>

                <!-- Customer Actions -->
                <template v-else>
                    <button
                        @click="addToCart"
                        :disabled="!product.is_active || product.stock <= 0"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold py-2.5 px-4 rounded-xl transition-colors duration-200 flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span v-if="product.stock > 0 && product.is_active">Add to Cart</span>
                        <span v-else>Unavailable</span>
                    </button>

                    <Link
                        :href="route('products.show', product.id)"
                        class="px-3 py-2.5 border-2 border-gray-200 rounded-xl hover:bg-gray-50 transition-colors duration-200"
                        title="View Details"
                    >
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
