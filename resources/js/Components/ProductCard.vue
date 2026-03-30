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
    <div class="onyx-panel overflow-hidden group bg-white transition-transform duration-200 hover:-translate-y-0.5">
        <div class="relative aspect-square bg-black/5 overflow-hidden border-b border-black/20">
            <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-black/35">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <div class="absolute top-3 left-3 flex flex-col gap-2">
                <span
                    v-if="product.is_active"
                    class="onyx-chip bg-emerald-100 text-emerald-900 border-emerald-900/40"
                >
                    Active
                </span>
                <span
                    v-else
                    class="onyx-chip bg-black/10 text-black border-black/50"
                >
                    Inactive
                </span>
            </div>

            <div class="absolute top-3 right-3 flex flex-col gap-2">
                <span
                    v-if="product.stock <= 0"
                    class="onyx-chip bg-rose-100 text-rose-900 border-rose-900/40"
                >
                    Out of Stock
                </span>
                <span
                    v-else-if="product.stock < 10"
                    class="onyx-chip bg-amber-100 text-amber-900 border-amber-900/40"
                >
                    Low Stock
                </span>
                <span
                    v-else
                    class="onyx-chip bg-sky-100 text-sky-900 border-sky-900/40"
                >
                    {{ product.stock }} in stock
                </span>
            </div>
        </div>

        <div class="p-4 md:p-5">
            <h3 class="text-lg font-semibold text-black mb-1 line-clamp-1">
                {{ product.name }}
            </h3>

            <p class="text-sm text-black/55 mb-3 line-clamp-2 min-h-[2.5rem]">
                {{ product.description || 'No description available' }}
            </p>

            <div class="flex items-center justify-between mb-4">
                <span class="text-xl font-bold text-black">
                    {{ formatPrice(product.price) }}
                </span>
            </div>

            <div v-if="showActions" class="flex gap-2">
                <template v-if="isAdmin">
                    <Link
                        :href="route('products.edit', product.id)"
                        class="flex-1 onyx-action py-2.5 px-3 gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </Link>

                    <button
                        @click="toggleStatus"
                        :class="[
                            'px-3 py-2.5 border transition-colors duration-200',
                            product.is_active
                                ? 'border-black/40 bg-white text-black hover:bg-black hover:text-white'
                                : 'border-emerald-800/40 bg-emerald-100 text-emerald-900 hover:bg-emerald-200'
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
                        class="px-3 py-2.5 border border-black/40 hover:bg-black hover:text-white transition-colors duration-200"
                        title="View Details"
                    >
                        <svg class="w-5 h-5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </Link>
                </template>

                <template v-else>
                    <button
                        @click="addToCart"
                        :disabled="!product.is_active || product.stock <= 0"
                        class="flex-1 onyx-action disabled:bg-black/20 disabled:text-black/45 disabled:border-black/20 disabled:cursor-not-allowed py-2.5 px-3 gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span v-if="product.stock > 0 && product.is_active">Add to Cart</span>
                        <span v-else>Unavailable</span>
                    </button>

                    <Link
                        :href="route('products.show', product.id)"
                        class="px-3 py-2.5 border border-black/40 hover:bg-black hover:text-white transition-colors duration-200"
                        title="View Details"
                    >
                        <svg class="w-5 h-5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
