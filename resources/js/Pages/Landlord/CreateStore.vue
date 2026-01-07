<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    plan: Object,
    auth: Object,
});

const page = usePage();

const form = useForm({
    store_name: '',
    subdomain: '',
    plan_id: props.plan?.id || null,
});

// Success modal state
const showSuccessModal = ref(false);
const createdStore = ref(null);

// Watch for flash success message
watch(() => page.props.flash?.success, (success) => {
    if (success && success.store_name) {
        createdStore.value = success;
        showSuccessModal.value = true;
    }
}, { immediate: true });

// Auto-generate subdomain from store name
const generateSubdomain = () => {
    form.subdomain = form.store_name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '')
        .substring(0, 63);
};

// Full domain preview
const domainPreview = computed(() => {
    return form.subdomain ? `${form.subdomain}.localhost:8000` : 'yourstore.localhost:8000';
});

const submit = () => {
    form.post(route('store.store'), {
        preserveScroll: true,
    });
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
};

const goToMyStores = () => {
    window.location.href = '/my-stores';
};

const visitStore = () => {
    if (createdStore.value?.full_url) {
        window.open(createdStore.value.full_url, '_blank');
    }
};
</script>

<template>
    <Head title="Create Your Store - ONYX" />

    <div class="min-h-screen bg-white text-black font-sans selection:bg-black selection:text-white">
        <!-- Success Modal -->
        <Teleport to="body">
            <div 
                v-if="showSuccessModal" 
                class="fixed inset-0 z-[100] flex items-center justify-center"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
                
                <!-- Modal -->
                <div class="relative bg-white border-4 border-black p-8 max-w-md w-full mx-4 animate-modal-in">
                    <!-- Success Icon -->
                    <div class="w-20 h-20 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <!-- Title -->
                    <h2 class="text-2xl font-black uppercase text-center mb-2">
                        Store Created!
                    </h2>
                    <p class="text-gray-600 text-center mb-6">
                        Your store <span class="font-bold">{{ createdStore?.store_name }}</span> is now live.
                    </p>

                    <!-- Store URL -->
                    <div class="bg-gray-100 border-2 border-black p-4 mb-4">
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Your Store URL</p>
                        <div class="flex items-center justify-between gap-2">
                            <code class="text-sm font-mono font-bold truncate">{{ createdStore?.full_url }}</code>
                            <button 
                                @click="copyToClipboard(createdStore?.full_url)"
                                class="p-2 bg-black text-white hover:scale-110 transition-transform flex-shrink-0"
                                title="Copy URL"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Admin Credentials -->
                    <div class="bg-amber-50 border-2 border-amber-400 p-4 mb-6">
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Admin Credentials
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Email:</span>
                                <code class="text-sm font-mono font-bold">{{ createdStore?.admin_email }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Password:</span>
                                <span class="text-sm font-bold text-amber-700">Sama dengan akun Anda</span>
                            </div>
                        </div>
                        <p class="text-xs text-amber-600 mt-3 italic">
                            Gunakan kredensial ini untuk login sebagai admin di toko Anda.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-3">
                        <button 
                            @click="goToMyStores"
                            class="w-full py-4 bg-black text-white text-sm font-bold uppercase tracking-widest hover:scale-[1.02] transition-transform"
                        >
                            Go to My Stores
                        </button>
                        <button 
                            @click="visitStore"
                            class="w-full py-4 border-2 border-black text-black text-sm font-bold uppercase tracking-widest hover:bg-gray-100 transition-colors"
                        >
                            Visit Store →
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Navbar -->
        <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-black">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <Link href="/" class="flex-shrink-0">
                         <span class="font-black text-2xl tracking-widest uppercase">ONYX</span>
                    </Link>
                    <div class="flex items-center gap-4">
                        <Link 
                            href="/my-stores" 
                            class="text-sm font-bold uppercase tracking-wider text-gray-600 hover:text-black transition-colors"
                        >
                            My Stores
                        </Link>
                        <span class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ auth.user.name }}</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pt-20">
            <div class="max-w-2xl mx-auto px-6 py-24">
                <!-- Header -->
                <div class="text-center mb-16">
                    <div class="inline-flex items-center gap-2 mb-6">
                        <span class="w-2 h-2 bg-black rounded-full animate-pulse"></span>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-500">Final Step</span>
                    </div>
                    
                    <h1 class="text-5xl sm:text-6xl font-black uppercase tracking-tighter mb-6 leading-[0.9]">
                        Create Your<br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-b from-black to-gray-500">Empire</span>
                    </h1>
                    
                    <p class="text-lg font-medium text-gray-600">
                        Set up your store in seconds. Start selling immediately.
                    </p>
                </div>

                <!-- Selected Plan Info -->
                <div v-if="plan" class="mb-12 p-6 border-2 border-black bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Selected Plan</p>
                            <p class="text-2xl font-black uppercase italic">{{ plan.name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black">{{ plan.formatted_price_monthly }}</p>
                            <p class="text-xs font-bold uppercase tracking-widest text-gray-500">
                                {{ plan.is_free ? 'Forever' : '/month' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Store Name -->
                    <div>
                        <label for="store_name" class="block text-sm font-black uppercase tracking-widest mb-3">
                            Store Name
                        </label>
                        <input
                            id="store_name"
                            v-model="form.store_name"
                            @input="generateSubdomain"
                            type="text"
                            placeholder="My Awesome Store"
                            class="w-full px-6 py-4 border-2 border-black text-lg font-bold focus:outline-none focus:ring-0 focus:border-black placeholder:text-gray-400"
                            :class="{ 'border-red-500': form.errors.store_name }"
                        />
                        <p v-if="form.errors.store_name" class="mt-2 text-sm font-bold text-red-600">
                            {{ form.errors.store_name }}
                        </p>
                    </div>

                    <!-- Subdomain -->
                    <div>
                        <label for="subdomain" class="block text-sm font-black uppercase tracking-widest mb-3">
                            Store URL
                        </label>
                        <div class="relative">
                            <input
                                id="subdomain"
                                v-model="form.subdomain"
                                type="text"
                                placeholder="yourstore"
                                class="w-full px-6 py-4 border-2 border-black text-lg font-bold focus:outline-none focus:ring-0 focus:border-black placeholder:text-gray-400 pr-40"
                                :class="{ 'border-red-500': form.errors.subdomain }"
                            />
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm">
                                .localhost:8000
                            </span>
                        </div>
                        <p v-if="form.errors.subdomain" class="mt-2 text-sm font-bold text-red-600">
                            {{ form.errors.subdomain }}
                        </p>
                        <p class="mt-2 text-sm text-gray-500">
                            Your store will be available at: 
                            <span class="font-bold text-black">{{ domainPreview }}</span>
                        </p>
                    </div>

                    <!-- Hidden Plan ID -->
                    <input type="hidden" v-model="form.plan_id" />

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-5 bg-black text-white text-sm font-black uppercase tracking-widest hover:scale-[1.02] transition-transform disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Launch My Store</span>
                        </button>
                    </div>

                    <!-- Back Link -->
                    <div class="text-center">
                        <Link 
                            href="/pricing" 
                            class="text-sm font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:underline underline-offset-4"
                        >
                            ← Change Plan
                        </Link>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>

<style scoped>
.bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}

@keyframes modal-in {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.animate-modal-in {
    animation: modal-in 0.3s ease-out forwards;
}
</style>
