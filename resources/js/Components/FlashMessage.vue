<script setup>
import { computed, ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
const currentMessage = ref('');
const currentType = ref('success');

// Watch for page props changes (after navigation)
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success || flash?.error) {
            currentMessage.value = flash.success || flash.error;
            currentType.value = flash.success ? 'success' : 'error';
            show.value = true;
            
            setTimeout(() => {
                show.value = false;
            }, 4000);
        }
    },
    { deep: true, immediate: true }
);

// Also listen for Inertia navigation events
router.on('finish', () => {
    const flash = page.props.flash;
    if (flash?.success || flash?.error) {
        currentMessage.value = flash.success || flash.error;
        currentType.value = flash.success ? 'success' : 'error';
        show.value = true;
        
        setTimeout(() => {
            show.value = false;
        }, 4000);
    }
});

const close = () => {
    show.value = false;
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform translate-x-full opacity-0"
            enter-to-class="transform translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform translate-x-0 opacity-100"
            leave-to-class="transform translate-x-full opacity-0"
        >
            <div
                v-if="show && currentMessage"
                class="fixed top-20 right-4 max-w-sm w-full shadow-xl rounded-xl pointer-events-auto overflow-hidden"
                :class="currentType === 'success' ? 'bg-green-500' : 'bg-red-500'"
                style="z-index: 9999;"
            >
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <!-- Success Icon -->
                            <svg
                                v-if="currentType === 'success'"
                                class="h-6 w-6 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <!-- Error Icon -->
                            <svg
                                v-else
                                class="h-6 w-6 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-white">
                                {{ currentMessage }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <button
                                @click="close"
                                class="inline-flex text-white hover:text-gray-200 focus:outline-none"
                            >
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
