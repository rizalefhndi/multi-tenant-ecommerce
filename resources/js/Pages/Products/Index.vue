<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    products: Object,
    filters: Object,
});

// Reactive filters
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const stock = ref(props.filters.stock || '');
const isSearching = ref(false);
const searchInput = ref(null);

const applyFilters = () => {
    isSearching.value = true;
    router.get(route('products.index'), {
        search: search.value,
        status: status.value,
        stock: stock.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isSearching.value = false;
        },
    });
};

const resetFilters = () => {
    search.value = '';
    status.value = '';
    stock.value = '';
    applyFilters();
};

const clearSearch = () => {
    search.value = '';
    searchInput.value?.focus();
    applyFilters();
};

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

watch([status, stock], () => {
    applyFilters();
});
</script>

<template>
    <Head title="Produk" />

    <AuthenticatedLayout>
        <div class="min-h-screen">
            <!-- Hero Section -->
            <div class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 overflow-hidden">
                <!-- Decorative Elements -->
                <div class="absolute inset-0">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-72 h-72 bg-pink-400/20 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
                </div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                        <!-- Text Content -->
                        <div class="text-center lg:text-left">
                            <h1 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                                Kelola Produk Anda
                            </h1>
                            <p class="text-lg text-white/80 max-w-xl mb-6">
                                Tambah, edit, dan kelola semua produk toko Anda dengan mudah. Pantau stok dan status produk dalam satu tempat.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                                <Link
                                    :href="route('products.create')"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-indigo-600 font-semibold rounded-xl hover:bg-gray-100 transition-colors shadow-lg"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Produk Baru
                                </Link>
                                <button
                                    @click="resetFilters"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-xl hover:bg-white/30 transition-colors backdrop-blur-sm"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Refresh Data
                                </button>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                                <p class="text-4xl font-bold text-white">{{ products?.total || 0 }}</p>
                                <p class="text-white/80 text-sm mt-1">Total Produk</p>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                                <p class="text-4xl font-bold text-white">{{ products?.data?.filter(p => p.is_active).length || 0 }}</p>
                                <p class="text-white/80 text-sm mt-1">Produk Aktif</p>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                                <p class="text-4xl font-bold text-white">{{ products?.data?.filter(p => p.stock > 0).length || 0 }}</p>
                                <p class="text-white/80 text-sm mt-1">Stok Tersedia</p>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                                <p class="text-4xl font-bold text-white">{{ products?.data?.filter(p => p.stock === 0).length || 0 }}</p>
                                <p class="text-white/80 text-sm mt-1">Stok Habis</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                
                <!-- Filters Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative group">
                                <!-- Search Icon / Loading Spinner -->
                                <div class="absolute left-4 top-1/2 -translate-y-1/2">
                                    <svg v-if="!isSearching" class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <svg v-else class="w-5 h-5 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                
                                <input
                                    ref="searchInput"
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari produk..."
                                    class="w-full pl-12 pr-24 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-gray-900 placeholder-gray-400"
                                />
                                
                                <!-- Clear Button & Shortcut -->
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
                                    <!-- Clear Button -->
                                    <button
                                        v-if="search"
                                        @click="clearSearch"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                        title="Hapus pencarian"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    <!-- Shortcut Hint -->
                                    <kbd class="hidden sm:inline-flex items-center px-2 py-1 text-xs font-medium text-gray-400 bg-gray-100 rounded-md">
                                        Ctrl+K
                                    </kbd>
                                </div>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="w-full lg:w-48">
                            <div class="relative">
                                <select
                                    v-model="status"
                                    class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white appearance-none cursor-pointer"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="active">✓ Aktif</option>
                                    <option value="inactive">✗ Nonaktif</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Filter -->
                        <div class="w-full lg:w-48">
                            <div class="relative">
                                <select
                                    v-model="stock"
                                    class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white appearance-none cursor-pointer"
                                >
                                    <option value="">Semua Stok</option>
                                    <option value="in_stock">📦 Tersedia</option>
                                    <option value="out_of_stock">⚠️ Habis</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Reset Button -->
                        <button
                            v-if="search || status || stock"
                            @click="resetFilters"
                            class="px-4 py-3.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors flex items-center gap-2 whitespace-nowrap"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset
                        </button>
                    </div>

                    <!-- Active Filters Tags -->
                    <div v-if="search || status || stock" class="mt-4 flex flex-wrap gap-2">
                        <span class="text-sm text-gray-500">Filter aktif:</span>
                        <span 
                            v-if="search"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm"
                        >
                            "{{ search }}"
                            <button @click="search = ''; applyFilters()" class="hover:text-indigo-900">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                        <span 
                            v-if="status"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm"
                        >
                            {{ status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            <button @click="status = ''; applyFilters()" class="hover:text-purple-900">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                        <span 
                            v-if="stock"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm"
                        >
                            {{ stock === 'in_stock' ? 'Tersedia' : 'Habis' }}
                            <button @click="stock = ''; applyFilters()" class="hover:text-orange-900">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                    </div>
                </div>

                <!-- Products Grid -->
                <div v-if="products.data.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                        <ProductCard
                            v-for="product in products.data"
                            :key="product.id"
                            :product="product"
                        />
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-sm text-gray-600">
                                Menampilkan <span class="font-semibold">{{ products.from }}</span> - <span class="font-semibold">{{ products.to }}</span> dari <span class="font-semibold">{{ products.total }}</span> produk
                            </div>

                            <div class="flex gap-2">
                                <template v-for="link in products.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                                            link.active
                                                ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md'
                                                : 'bg-white text-gray-700 hover:bg-gray-100 border-2 border-gray-200'
                                        ]"
                                    >
                                        <span v-html="link.label"></span>
                                    </Link>
                                    <span
                                        v-else
                                        class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 border-2 border-gray-100 cursor-not-allowed"
                                    >
                                        <span v-html="link.label"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak ada produk ditemukan</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        {{ filters.search ? 'Coba ubah kata kunci pencarian atau filter Anda' : 'Mulai dengan menambahkan produk pertama Anda' }}
                    </p>
                    <Link
                        :href="route('products.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Produk
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
