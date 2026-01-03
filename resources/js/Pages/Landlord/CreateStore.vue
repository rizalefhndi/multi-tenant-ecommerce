<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    plan: Object,
    auth: Object,
});

const form = useForm({
    store_name: '',
    subdomain: '',
    plan_id: props.plan?.id || null,
});

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
    form.post(route('store.store'));
};
</script>

<template>
    <Head title="Create Your Store - ONYX" />

    <div class="min-h-screen bg-white text-black font-sans selection:bg-black selection:text-white">
        <!-- Navbar -->
        <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-black">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <Link href="/" class="flex-shrink-0">
                         <span class="font-black text-2xl tracking-widest uppercase">ONYX</span>
                    </Link>
                    <div class="flex items-center gap-4">
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
</style>
