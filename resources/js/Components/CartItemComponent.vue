<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const isUpdating = ref(false);

/**
 * Update quantity
 */
const updateQuantity = (newQuantity) => {
    if (newQuantity < 1) return;

    isUpdating.value = true;

    router.patch(route('cart.update', props.item.id), {
        quantity: newQuantity,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isUpdating.value = false;
        },
    });
};

/**
 * Increment quantity
 */
const increment = () => {
    if (props.item.quantity >= props.item.product.stock) {
        alert('Cannot add more than available stock!');
        return;
    }
    updateQuantity(props.item.quantity + 1);
};

/**
 * Decrement quantity
 */
const decrement = () => {
    updateQuantity(props.item.quantity - 1);
};

/**
 * Remove item
 */
const removeItem = () => {
    if (confirm('Remove this item from cart?')) {
        router.delete(route('cart.remove', props.item.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="flex gap-4 p-4 bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Product Image -->
        <div class="flex-shrink-0 w-24 h-24 bg-gray-200 rounded-lg overflow-hidden">
            <img
                v-if="item.product.image"
                :src="item.product.image"
                :alt="item.product.name"
                class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Product Info -->
        <div class="flex-1 min-w-0">
            <h3 class="text-lg font-semibold text-gray-800 mb-1 truncate">
                {{ item.product.name }}
            </h3>
            <p class="text-sm text-gray-600 mb-2 line-clamp-2">
                {{ item.product.description }}
            </p>
            <p class="text-sm text-gray-500">
                {{ item.formatted_price }} × {{ item.quantity }}
            </p>
        </div>

        <!-- Quantity Controls -->
        <div class="flex flex-col items-end justify-between">
            <button
                @click="removeItem"
                class="text-red-500 hover:text-red-700 transition-colors"
                title="Remove item"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>

            <div class="flex flex-col items-end gap-2">
                <!-- Quantity Selector -->
                <div class="flex items-center gap-2 bg-gray-100 rounded-lg p-1">
                    <button
                        @click="decrement"
                        :disabled="isUpdating"
                        class="w-8 h-8 flex items-center justify-center rounded bg-white hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </button>

                    <span class="w-8 text-center font-semibold">
                        {{ item.quantity }}
                    </span>

                    <button
                        @click="increment"
                        :disabled="isUpdating || item.quantity >= item.product.stock"
                        class="w-8 h-8 flex items-center justify-center rounded bg-white hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>

                <!-- Subtotal -->
                <p class="text-lg font-bold text-indigo-600">
                    {{ item.formatted_subtotal }}
                </p>

                <!-- Stock Warning -->
                <p
                    v-if="item.quantity >= item.product.stock"
                    class="text-xs text-orange-600"
                >
                    Max stock reached
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
