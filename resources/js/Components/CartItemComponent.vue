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
    <div class="flex flex-col sm:flex-row gap-6 py-6 group bg-white">
        <!-- Product Image -->
        <div class="flex-shrink-0 w-full sm:w-40 h-48 sm:h-40 bg-gray-100 border-4 border-black relative">
            <img
                v-if="item.product.image"
                :src="item.product.image"
                :alt="item.product.name"
                class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-black">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <!-- Remove Button (Overlay on hover/mobile) -->
             <button
                @click="removeItem"
                class="absolute top-0 right-0 w-10 h-10 flex items-center justify-center bg-red-600 border-b-4 border-l-4 border-black text-white hover:bg-black hover:text-white transition-none sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100"
                title="Remove item"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Info & Controls -->
        <div class="flex-1 flex flex-col justify-between py-1">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <h3 class="text-2xl font-black text-black leading-tight mb-2 uppercase tracking-tighter">
                        {{ item.product.name }}
                    </h3>
                    <p class="text-sm text-black font-bold max-w-sm line-clamp-2">
                        {{ item.product.description }}
                    </p>
                </div>
                <!-- Price (Mobile hidden or stacked) -->
                <p class="text-2xl font-black text-black hidden sm:block bg-gray-100 px-3 py-1 border-2 border-black">
                    {{ item.formatted_subtotal }}
                </p>
            </div>

            <div class="flex items-end justify-between mt-6">
                 <!-- Quantity Controls (Brutalist) -->
                <div class="flex items-center gap-4">
                     <div class="flex items-center bg-white border-4 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        <button
                            @click="decrement"
                            :disabled="isUpdating"
                            class="w-10 h-10 flex items-center justify-center bg-white text-black hover:bg-black hover:text-white disabled:opacity-50 transition-none font-black text-xl border-r-4 border-black"
                        >
                            -
                        </button>
                        <span class="w-12 text-center font-black text-black text-lg">{{ item.quantity }}</span>
                        <button
                            @click="increment"
                            :disabled="isUpdating || item.quantity >= item.product.stock"
                            class="w-10 h-10 flex items-center justify-center bg-white text-black hover:bg-black hover:text-white disabled:opacity-50 transition-none font-black text-xl border-l-4 border-black"
                        >
                            +
                        </button>
                    </div>
                     <span v-if="item.quantity >= item.product.stock" class="text-xs font-black text-white bg-red-600 border-2 border-red-600 px-2 py-1 uppercase tracking-widest shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                        Max
                    </span>
                </div>
                
                <!-- Mobile Price shown here -->
                <p class="text-2xl font-black text-black sm:hidden bg-gray-100 px-3 py-1 border-2 border-black">
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
