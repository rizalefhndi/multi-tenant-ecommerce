<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

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

const hasFilters = computed(() => Boolean(search.value || status.value || stock.value));
const totalProducts = computed(() => props.products?.total || 0);
const activeProducts = computed(() => props.products?.data?.filter((p) => p.is_active).length || 0);
const inStockProducts = computed(() => props.products?.data?.filter((p) => p.stock > 0).length || 0);
const outOfStockProducts = computed(() => props.products?.data?.filter((p) => p.stock <= 0).length || 0);
</script>

<template>
    <Head>
        <title>Products</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8 relative overflow-hidden">
                    <div class="absolute top-4 right-4 onyx-kicker">Inventory Command</div>
                    <p class="onyx-kicker">Products</p>
                    <div class="mt-2 flex flex-col lg:flex-row gap-6 lg:items-end lg:justify-between">
                        <div>
                            <h1 class="onyx-title text-2xl md:text-4xl">Manage Product Catalog</h1>
                            <p class="text-black/60 mt-2 max-w-2xl">Control product status, monitor stock movement, and curate your catalog from one ONYX console.</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                            <Link :href="route('products.create')" class="onyx-action w-full sm:w-auto">Add Product</Link>
                            <button @click="resetFilters" class="onyx-action-ghost w-full sm:w-auto">Refresh View</button>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-2 xl:grid-cols-4 gap-3">
                        <div class="onyx-panel-soft p-4 text-center">
                            <p class="onyx-kicker">Total</p>
                            <p class="text-3xl font-bold mt-2">{{ totalProducts }}</p>
                        </div>
                        <div class="onyx-panel-soft p-4 text-center">
                            <p class="onyx-kicker">Active</p>
                            <p class="text-3xl font-bold mt-2">{{ activeProducts }}</p>
                        </div>
                        <div class="onyx-panel-soft p-4 text-center">
                            <p class="onyx-kicker">In Stock</p>
                            <p class="text-3xl font-bold mt-2">{{ inStockProducts }}</p>
                        </div>
                        <div class="onyx-panel-soft p-4 text-center">
                            <p class="onyx-kicker">Out</p>
                            <p class="text-3xl font-bold mt-2">{{ outOfStockProducts }}</p>
                        </div>
                    </div>
                </section>

                <section class="onyx-panel p-5 md:p-6">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <p class="onyx-kicker">Filter Products</p>
                        <p class="text-xs uppercase tracking-[0.08em] text-black/55">Live search updates automatically</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-2 xl:col-span-6">
                            <p class="onyx-kicker mb-1.5">Search</p>
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                    <svg v-if="!isSearching" class="w-4 h-4 text-black/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <svg v-else class="w-4 h-4 text-black animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </div>

                                <input
                                    ref="searchInput"
                                    v-model="search"
                                    type="text"
                                    placeholder="Search products"
                                    class="h-11 w-full border border-black py-2.5 pl-10 pr-12 text-sm bg-white focus:outline-none"
                                />

                                <button
                                    v-if="search"
                                    @click="clearSearch"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center border border-black/70 text-black hover:bg-black hover:text-white transition-colors"
                                    title="Clear search"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="xl:col-span-2">
                            <p class="onyx-kicker mb-1.5">Status</p>
                            <select
                                v-model="status"
                                class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="xl:col-span-2">
                            <p class="onyx-kicker mb-1.5">Stock</p>
                            <select
                                v-model="stock"
                                class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none"
                            >
                                <option value="">All Stock</option>
                                <option value="in_stock">In Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>

                        <div class="xl:col-span-2">
                            <p class="onyx-kicker mb-1.5">Actions</p>
                            <button
                                @click="resetFilters"
                                :disabled="!hasFilters"
                                class="onyx-action-ghost h-11 w-full disabled:border-black/20 disabled:text-black/35 disabled:hover:bg-white disabled:hover:text-black/35"
                            >
                                Reset
                            </button>
                        </div>
                    </div>

                    <div v-if="hasFilters" class="mt-4 flex flex-wrap items-center gap-2">
                        <span class="onyx-kicker">Active Filters</span>

                        <span v-if="search" class="onyx-chip gap-2">
                            {{ search }}
                            <button @click="search = ''; applyFilters()" class="text-black/70 hover:text-black">x</button>
                        </span>

                        <span v-if="status" class="onyx-chip gap-2">
                            {{ status === 'active' ? 'Active' : 'Inactive' }}
                            <button @click="status = ''; applyFilters()" class="text-black/70 hover:text-black">x</button>
                        </span>

                        <span v-if="stock" class="onyx-chip gap-2">
                            {{ stock === 'in_stock' ? 'In Stock' : 'Out of Stock' }}
                            <button @click="stock = ''; applyFilters()" class="text-black/70 hover:text-black">x</button>
                        </span>
                    </div>
                </section>

                <div v-if="products.data.length > 0" class="space-y-6">
                    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        <ProductCard
                            v-for="product in products.data"
                            :key="product.id"
                            :product="product"
                        />
                    </section>

                    <section class="onyx-panel p-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <p class="text-sm text-black/70">
                                Showing <span class="font-semibold text-black">{{ products.from }}</span> to <span class="font-semibold text-black">{{ products.to }}</span> of <span class="font-semibold text-black">{{ products.total }}</span> products
                            </p>

                            <div class="flex flex-wrap gap-2">
                                <template v-for="link in products.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border transition-colors',
                                            link.active
                                                ? 'bg-black text-white border-black'
                                                : 'bg-white text-black border-black hover:bg-black hover:text-white'
                                        ]"
                                    >
                                        <span v-html="link.label"></span>
                                    </Link>
                                    <span
                                        v-else
                                        class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border border-black/20 bg-black/5 text-black/40 cursor-not-allowed"
                                    >
                                        <span v-html="link.label"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </section>
                </div>

                <section v-else class="onyx-panel p-12 md:p-16 text-center">
                    <div class="w-16 h-16 border border-black bg-black/5 flex items-center justify-center mx-auto mb-5">
                        <svg class="w-8 h-8 text-black/55" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <h3 class="onyx-title text-xl">No Products Found</h3>
                    <p class="text-black/60 mt-2 mb-6 max-w-md mx-auto">
                        {{ filters.search ? 'Try changing your search keyword or filters.' : 'Start your catalog by adding the first product.' }}
                    </p>
                    <Link :href="route('products.create')" class="onyx-action">Add Product</Link>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
