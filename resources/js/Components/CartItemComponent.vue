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
    <div class="flex gap-6 py-6 border-b border-gray-100 last:border-0 group">
        <!-- Product Image -->
        <div class="flex-shrink-0 w-32 h-40 bg-gray-100 rounded-2xl overflow-hidden relative">
            <img
                v-if="item.product.image"
                :src="item.product.image"
                :alt="item.product.name"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <!-- Remove Button (Overlay on hover/mobile) -->
             <button
                @click="removeItem"
                class="absolute top-2 right-2 w-8 h-8 flex items-center justify-center bg-white/90 backdrop-blur-sm rounded-full text-red-500 hover:text-red-600 hover:bg-white shadow-sm transition-all opacity-0 group-hover:opacity-100 focus:opacity-100"
                title="Remove item"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>

        <!-- Info & Controls -->
        <div class="flex-1 flex flex-col justify-between py-1">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 leading-tight mb-1">
                        {{ item.product.name }}
                    </h3>
                    <p class="text-sm text-gray-500 line-clamp-1 font-medium">
                        {{ item.product.description }}
                    </p>
                </div>
                <!-- Price (Mobile hidden or stacked) -->
                <p class="text-lg font-black text-gray-900 hidden sm:block">
                    {{ item.formatted_subtotal }}
                </p>
            </div>

            <div class="flex items-end justify-between mt-4">
                 <!-- Quantity Controls (Minimal Pill) -->
                <div class="flex items-center gap-3">
                     <div class="flex items-center bg-gray-100 rounded-full px-1 py-1">
                        <button
                            @click="decrement"
                            :disabled="isUpdating"
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-gray-600 hover:bg-gray-50 shadow-sm disabled:opacity-50 transition-all font-bold text-lg"
                        >
                            -
                        </button>
                        <span class="w-10 text-center font-bold text-gray-900">{{ item.quantity }}</span>
                        <button
                            @click="increment"
                            :disabled="isUpdating || item.quantity >= item.product.stock"
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-black text-white hover:bg-gray-800 shadow-sm disabled:opacity-50 transition-all font-bold text-lg"
                        >
                            +
                        </button>
                    </div>
                     <span v-if="item.quantity >= item.product.stock" class="text-xs font-bold text-[#FF6B6B] uppercase tracking-wide">
                        Max
                    </span>
                </div>
                
                <!-- Mobile Price shown here -->
                <p class="text-lg font-black text-gray-900 sm:hidden">
                    {{ item.formatted_subtotal }}
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
