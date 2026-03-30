<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    product: Object,
});

const isDeleting = ref(false);

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const deleteProduct = () => {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        isDeleting.value = true;
        router.delete(route('products.destroy', props.product.id), {
            onFinish: () => {
                isDeleting.value = false;
            },
        });
    }
};

const toggleStatus = () => {
    router.patch(route('products.toggle-status', props.product.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head>
        <title>Product Details</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <Link
                                :href="route('products.index')"
                                class="h-10 w-10 border border-black bg-white text-black hover:bg-black hover:text-white transition-colors flex items-center justify-center"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </Link>
                            <div>
                                <p class="onyx-kicker">Catalog Inspector</p>
                                <h1 class="onyx-title text-2xl md:text-3xl mt-2">Product Details</h1>
                                <p class="text-black/60 mt-2">Review listing quality, stock state, and publication status from one panel.</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Link :href="route('products.edit', product.id)" class="onyx-action-ghost">Edit</Link>
                            <button
                                @click="deleteProduct"
                                :disabled="isDeleting"
                                class="inline-flex items-center justify-center border border-red-900 bg-red-700 px-4 py-2 text-xs font-semibold uppercase tracking-[0.1em] text-white hover:bg-red-800 disabled:bg-red-400 disabled:border-red-400"
                            >
                                {{ isDeleting ? 'Deleting...' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    <div class="lg:col-span-2 space-y-6">
                        <section class="onyx-panel p-5 md:p-6">
                            <p class="onyx-kicker mb-4">Product Media</p>
                            <div class="aspect-square border border-black/20 bg-black/5 overflow-hidden">
                                <img
                                    v-if="product.image"
                                    :src="product.image"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-black/45">
                                    <svg class="w-20 h-20 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs uppercase tracking-[0.08em]">No image available</span>
                                </div>
                            </div>
                        </section>

                        <section class="onyx-panel p-5 md:p-6">
                            <p class="onyx-kicker mb-3">Description</p>
                            <p class="text-black/75 leading-relaxed">
                                {{ product.description || 'No description available.' }}
                            </p>
                        </section>
                    </div>

                    <aside class="lg:col-span-1">
                        <section class="onyx-panel p-5 md:p-6 sticky top-6">
                            <h2 class="onyx-title text-xl mb-4">{{ product.name }}</h2>

                            <div class="flex flex-wrap gap-2 mb-5">
                                <span class="onyx-chip" :class="product.is_active ? 'bg-emerald-100 border-emerald-900/40 text-emerald-900' : 'bg-black/10 border-black/50 text-black'">
                                    {{ product.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="onyx-chip" :class="product.stock > 0 ? 'bg-sky-100 border-sky-900/40 text-sky-900' : 'bg-rose-100 border-rose-900/40 text-rose-900'">
                                    {{ product.stock > 0 ? `${product.stock} in stock` : 'Out of stock' }}
                                </span>
                            </div>

                            <div class="mb-5">
                                <p class="onyx-kicker mb-1">Price</p>
                                <p class="text-3xl font-bold text-black">{{ formatPrice(product.price) }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mb-5 pt-5 border-t border-black/15">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.08em] text-black/55">Stock</p>
                                    <p class="text-base font-semibold text-black mt-1">{{ product.stock }} units</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.08em] text-black/55">Status</p>
                                    <p class="text-base font-semibold mt-1" :class="product.is_active ? 'text-emerald-700' : 'text-black/70'">
                                        {{ product.is_active ? 'Published' : 'Draft' }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2.5 pt-5 border-t border-black/15">
                                <button
                                    @click="toggleStatus"
                                    :class="[
                                        'w-full inline-flex items-center justify-center border px-4 py-2 text-xs font-semibold uppercase tracking-[0.1em] transition-colors',
                                        product.is_active
                                            ? 'border-black/50 bg-white text-black hover:bg-black hover:text-white'
                                            : 'border-emerald-900/60 bg-emerald-100 text-emerald-900 hover:bg-emerald-200'
                                    ]"
                                >
                                    {{ product.is_active ? 'Deactivate Product' : 'Activate Product' }}
                                </button>

                                <Link
                                    :href="route('products.edit', product.id)"
                                    class="onyx-action w-full"
                                >
                                    Edit Product
                                </Link>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
