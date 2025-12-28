<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    product: Object,
});

const form = useForm({
    name: props.product.name,
    description: props.product.description,
    price: props.product.price,
    stock: props.product.stock,
    image: props.product.image,
    is_active: props.product.is_active,
});

const isDeleting = ref(false);

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
    form.put(route('products.update', props.product.id));
};

const deleteProduct = () => {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        isDeleting.value = true;
        router.delete(route('products.destroy', props.product.id));
    }
};
</script>

<template>
    <Head :title="`Edit: ${product.name}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Hero Header -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Link 
                                :href="route('products.index')"
                                class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center transition-colors"
                            >
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </Link>
                            <div>
                                <h1 class="text-2xl font-bold text-white">Edit Product</h1>
                                <p class="text-white/80 mt-1">Update product information</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span 
                                :class="[
                                    'px-3 py-1 rounded-full text-sm font-medium',
                                    product.is_active ? 'bg-green-400/20 text-green-100' : 'bg-gray-400/20 text-gray-200'
                                ]"
                            >
                                {{ product.is_active ? '✓ Active' : '✗ Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Form -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Basic Info Card -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
                                        <p class="text-sm text-gray-500">Product name and description</p>
                                    </div>
                                </div>

                                <!-- Product Name -->
                                <div class="mb-6">
                                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Product Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        :class="{ 'border-red-500': form.errors.name }"
                                        required
                                    />
                                    <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none"
                                        :class="{ 'border-red-500': form.errors.description }"
                                        placeholder="Describe your product in detail..."
                                    ></textarea>
                                    <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                                </div>
                            </div>

                            <!-- Pricing & Inventory Card -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-900">Pricing & Inventory</h2>
                                        <p class="text-sm text-gray-500">Set your price and stock level</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Price -->
                                    <div>
                                        <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Price <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                                            <input
                                                id="price"
                                                v-model="form.price"
                                                type="number"
                                                step="1"
                                                min="0"
                                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                                :class="{ 'border-red-500': form.errors.price }"
                                                required
                                            />
                                        </div>
                                        <p v-if="form.errors.price" class="mt-2 text-sm text-red-600">{{ form.errors.price }}</p>
                                        <p v-if="form.price" class="mt-2 text-sm text-gray-500">
                                            Preview: <span class="font-medium text-gray-700">Rp {{ formatPrice(form.price) }}</span>
                                        </p>
                                    </div>

                                    <!-- Stock -->
                                    <div>
                                        <label for="stock" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Stock Quantity <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                id="stock"
                                                v-model="form.stock"
                                                type="number"
                                                min="0"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                                :class="{ 'border-red-500': form.errors.stock }"
                                                required
                                            />
                                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">units</span>
                                        </div>
                                        <p v-if="form.errors.stock" class="mt-2 text-sm text-red-600">{{ form.errors.stock }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Card -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-900">Product Image</h2>
                                        <p class="text-sm text-gray-500">Update the image URL for your product</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Image URL
                                    </label>
                                    <input
                                        id="image"
                                        v-model="form.image"
                                        type="url"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        :class="{ 'border-red-500': form.errors.image }"
                                        placeholder="https://example.com/product-image.jpg"
                                    />
                                    <p v-if="form.errors.image" class="mt-2 text-sm text-red-600">{{ form.errors.image }}</p>
                                </div>
                            </div>

                            <!-- Danger Zone -->
                            <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-900">Danger Zone</h2>
                                        <p class="text-sm text-gray-500">Irreversible actions</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="deleteProduct"
                                    :disabled="isDeleting"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white font-medium rounded-xl transition-colors flex items-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    {{ isDeleting ? 'Deleting...' : 'Delete Product' }}
                                </button>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Preview Card -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview</h3>
                                
                                <!-- Image Preview -->
                                <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden mb-4">
                                    <img 
                                        v-if="previewImage"
                                        :src="previewImage" 
                                        alt="Product preview"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                        <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm">No image</span>
                                    </div>
                                </div>

                                <!-- Product Info Preview -->
                                <div class="space-y-3">
                                    <h4 class="font-semibold text-gray-900 line-clamp-2">
                                        {{ form.name || 'Product Name' }}
                                    </h4>
                                    <p class="text-2xl font-bold text-indigo-600">
                                        Rp {{ formatPrice(form.price) }}
                                    </p>
                                    <div class="flex items-center gap-2 text-sm">
                                        <span 
                                            class="px-2 py-1 rounded-full text-xs font-medium"
                                            :class="form.stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                        >
                                            {{ form.stock > 0 ? `${form.stock} in stock` : 'Out of stock' }}
                                        </span>
                                        <span 
                                            class="px-2 py-1 rounded-full text-xs font-medium"
                                            :class="form.is_active ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'"
                                        >
                                            {{ form.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Status Toggle -->
                                <div class="mt-6 pt-6 border-t border-gray-100">
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <div>
                                            <span class="text-sm font-semibold text-gray-700">Product Status</span>
                                            <p class="text-xs text-gray-500">Make visible to customers</p>
                                        </div>
                                        <div class="relative">
                                            <input
                                                v-model="form.is_active"
                                                type="checkbox"
                                                class="sr-only peer"
                                            />
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-indigo-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-6 pt-6 border-t border-gray-100 space-y-3">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-2"
                                    >
                                        <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                    </button>
                                    <Link
                                        :href="route('products.index')"
                                        class="w-full py-3 border-2 border-gray-200 hover:border-gray-300 text-gray-700 font-semibold rounded-xl transition-all flex items-center justify-center gap-2"
                                    >
                                        Cancel
                                    </Link>
                                </div>
                            </div>
                        </div>
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
