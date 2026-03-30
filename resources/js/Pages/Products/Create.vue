<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    name: '',
    description: '',
    price: '',
    stock: '',
    image: '',
    is_active: true,
});

const previewImage = computed(() => {
    if (form.image && form.image.match(/^https?:\/\/.+/)) {
        return form.image;
    }
    return null;
});

const formatPrice = (value) => {
    return new Intl.NumberFormat('id-ID').format(value || 0);
};

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head>
        <title>Add Product</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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
                                <p class="onyx-kicker">Catalog Builder</p>
                                <h1 class="onyx-title text-2xl md:text-3xl mt-2">Add New Product</h1>
                                <p class="text-black/60 mt-2">Fill product details, review preview, then publish to your storefront.</p>
                            </div>
                        </div>
                        <span class="onyx-chip">Create Flow</span>
                    </div>
                </section>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                        <div class="lg:col-span-2 space-y-6">
                            <section class="onyx-panel p-5 md:p-6">
                                <p class="onyx-kicker mb-4">Basic Information</p>

                                <div class="mb-5">
                                    <label for="name" class="block text-sm font-semibold text-black mb-2">Product Name <span class="text-red-600">*</span></label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none"
                                        :class="{ 'border-red-600': form.errors.name }"
                                        required
                                        autofocus
                                        placeholder="e.g. iPhone 15 Pro Max"
                                    />
                                    <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-semibold text-black mb-2">Description</label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        class="w-full border border-black bg-white px-3 py-2.5 text-sm resize-none focus:outline-none"
                                        :class="{ 'border-red-600': form.errors.description }"
                                        placeholder="Describe the product in detail"
                                    ></textarea>
                                    <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                                </div>
                            </section>

                            <section class="onyx-panel p-5 md:p-6">
                                <p class="onyx-kicker mb-4">Pricing & Inventory</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label for="price" class="block text-sm font-semibold text-black mb-2">Price <span class="text-red-600">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black/60 text-sm">Rp</span>
                                            <input
                                                id="price"
                                                v-model="form.price"
                                                type="number"
                                                step="1"
                                                min="0"
                                                class="h-11 w-full border border-black bg-white pl-9 pr-3 text-sm focus:outline-none"
                                                :class="{ 'border-red-600': form.errors.price }"
                                                required
                                                placeholder="0"
                                            />
                                        </div>
                                        <p v-if="form.errors.price" class="mt-2 text-sm text-red-600">{{ form.errors.price }}</p>
                                        <p v-if="form.price" class="mt-2 text-xs uppercase tracking-[0.08em] text-black/60">Preview: Rp {{ formatPrice(form.price) }}</p>
                                    </div>

                                    <div>
                                        <label for="stock" class="block text-sm font-semibold text-black mb-2">Stock Quantity <span class="text-red-600">*</span></label>
                                        <div class="relative">
                                            <input
                                                id="stock"
                                                v-model="form.stock"
                                                type="number"
                                                min="0"
                                                class="h-11 w-full border border-black bg-white px-3 pr-14 text-sm focus:outline-none"
                                                :class="{ 'border-red-600': form.errors.stock }"
                                                required
                                                placeholder="0"
                                            />
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs uppercase tracking-[0.08em] text-black/50">units</span>
                                        </div>
                                        <p v-if="form.errors.stock" class="mt-2 text-sm text-red-600">{{ form.errors.stock }}</p>
                                    </div>
                                </div>
                            </section>

                            <section class="onyx-panel p-5 md:p-6">
                                <p class="onyx-kicker mb-4">Image Source</p>

                                <div>
                                    <label for="image" class="block text-sm font-semibold text-black mb-2">Image URL</label>
                                    <input
                                        id="image"
                                        v-model="form.image"
                                        type="url"
                                        class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none"
                                        :class="{ 'border-red-600': form.errors.image }"
                                        placeholder="https://example.com/product-image.jpg"
                                    />
                                    <p v-if="form.errors.image" class="mt-2 text-sm text-red-600">{{ form.errors.image }}</p>
                                    <p class="mt-2 text-xs uppercase tracking-[0.08em] text-black/55">Use a direct image URL for accurate card preview</p>
                                </div>
                            </section>
                        </div>

                        <aside class="lg:col-span-1">
                            <section class="onyx-panel p-5 md:p-6 sticky top-6">
                                <div class="flex items-center justify-between mb-4">
                                    <p class="onyx-kicker">Live Preview</p>
                                    <span class="onyx-chip" :class="form.is_active ? 'bg-emerald-100 border-emerald-900/40 text-emerald-900' : 'bg-black/10 border-black/50 text-black'">
                                        {{ form.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>

                                <div class="aspect-square border border-black/20 bg-black/5 overflow-hidden mb-4">
                                    <img
                                        v-if="previewImage"
                                        :src="previewImage"
                                        alt="Product preview"
                                        class="w-full h-full object-cover"
                                        @error="form.image = ''"
                                    />
                                    <div v-else class="w-full h-full flex flex-col items-center justify-center text-black/45">
                                        <svg class="w-14 h-14 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-xs uppercase tracking-[0.08em]">No image</span>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <h3 class="font-semibold text-black line-clamp-2">{{ form.name || 'Product Name' }}</h3>
                                    <p class="text-2xl font-bold text-black">Rp {{ formatPrice(form.price) }}</p>
                                    <p class="text-xs uppercase tracking-[0.08em] text-black/60">
                                        {{ form.stock > 0 ? `${form.stock} in stock` : 'Out of stock' }}
                                    </p>
                                </div>

                                <div class="mt-6 pt-5 border-t border-black/15">
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <div>
                                            <p class="text-sm font-semibold text-black">Product Status</p>
                                            <p class="text-xs text-black/55">Visible to customers</p>
                                        </div>
                                        <div class="relative">
                                            <input
                                                v-model="form.is_active"
                                                type="checkbox"
                                                class="sr-only peer"
                                            />
                                            <div class="w-11 h-6 bg-black/20 rounded-full peer-checked:bg-black after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-black/20 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                                        </div>
                                    </label>
                                </div>

                                <div class="mt-6 pt-5 border-t border-black/15 space-y-2.5">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="onyx-action w-full disabled:bg-black/25 disabled:text-black/50 disabled:border-black/20 disabled:hover:bg-black/25 disabled:hover:text-black/50"
                                    >
                                        {{ form.processing ? 'Creating...' : 'Create Product' }}
                                    </button>
                                    <Link
                                        :href="route('products.index')"
                                        class="onyx-action-ghost w-full"
                                    >
                                        Cancel
                                    </Link>
                                </div>
                            </section>
                        </aside>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
