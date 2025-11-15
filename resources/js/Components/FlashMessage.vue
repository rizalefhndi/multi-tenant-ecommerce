<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);

const message = computed(() => page.props.flash?.success || page.props.flash?.error);
const type = computed(() => page.props.flash?.success ? 'success' : 'error');

// Watch for flash messages
watch(message, (newMessage) => {
    if (newMessage) {
        show.value = true;
        setTimeout(() => {
            show.value = false;
        }, 5000); // Auto hide after 5 seconds
    }
});

const close = () => {
    show.value = false;
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform translate-x-full opacity-0"
        enter-to-class="transform translate-x-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="transform translate-x-0 opacity-100"
        leave-to-class="transform translate-x-full opacity-0"
    >
        <div
            v-if="show && message"
            :class="[
                'fixed top-4 right-4 max-w-md w-full shadow-lg rounded-lg pointer-events-auto overflow-hidden z-50',
                type === 'success' ? 'bg-green-50 border-green-400' : 'bg-red-50 border-red-400',
                'border-l-4'
            ]"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <!-- Success Icon -->
                        <svg
                            v-if="type === 'success'"
                            class="h-6 w-6 text-green-400"
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
                            class="h-6 w-6 text-red-400"
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
                    <div class="ml-3 w-0 flex-1">
                        <p
                            :class="[
                                'text-sm font-medium',
                                type === 'success' ? 'text-green-800' : 'text-red-800'
                            ]"
                        >
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button
                            @click="close"
                            :class="[
                                'inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
                                type === 'success'
                                    ? 'text-green-400 hover:text-green-500 focus:ring-green-500'
                                    : 'text-red-400 hover:text-red-500 focus:ring-red-500'
                            ]"
                        >
                            <span class="sr-only">Close</span>
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
</template>
