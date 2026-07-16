<script setup>
import StoreLayout from '@/Layouts/StoreLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const activeTab = ref('profile');

// Profile Form
const profileForm = useForm({
    name: user.value.name,
    email: user.value.email,
});

// Password Form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const updateProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

const getInitials = (name) => {
    return name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'U';
};

const tabs = [
    { id: 'profile', name: 'Profile', icon: 'user' },
    { id: 'security', name: 'Security', icon: 'shield' },
    { id: 'addresses', name: 'Addresses', icon: 'location' },
];
</script>

<template>
    <Head title="My Profile" />

    <StoreLayout>
        <div class="py-6 md:py-12 bg-white min-h-screen">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="mb-8">
                    <p class="onyx-kicker">Account</p>
                    <h1 class="onyx-title text-2xl md:text-3xl mt-2">Account Settings</h1>
                    <p class="text-black/60 mt-2">Manage your profile information and account security</p>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    
                    <!-- Sidebar -->
                    <div class="lg:w-64 flex-shrink-0">
                        <!-- Profile Card -->
                        <div class="onyx-panel p-6 mb-6">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-20 h-20 border border-black bg-black flex items-center justify-center text-white text-2xl font-bold mb-4">
                                    {{ getInitials(user?.name) }}
                                </div>
                                <h3 class="font-bold text-black uppercase tracking-[0.08em]">{{ user?.name }}</h3>
                                <p class="text-sm text-black/60">{{ user?.email }}</p>
                                <span class="mt-3 inline-flex items-center border border-black bg-emerald-100 px-3 py-1 text-emerald-900 text-xs font-bold uppercase tracking-[0.1em]">
                                    <span class="w-1.5 h-1.5 bg-emerald-600 mr-2 border border-emerald-900"></span>
                                    Verified
                                </span>
                            </div>
                        </div>

                        <!-- Navigation Tabs -->
                        <nav class="onyx-panel p-0 overflow-hidden flex flex-col">
                            <button
                                v-for="(tab, index) in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                class="w-full flex items-center gap-3 px-5 py-4 text-left transition-colors uppercase tracking-[0.1em] text-xs font-bold"
                                :class="[
                                    activeTab === tab.id 
                                        ? 'bg-black text-white' 
                                        : 'bg-white text-black hover:bg-black hover:text-white',
                                    index !== tabs.length - 1 ? 'border-b border-black/10' : ''
                                ]"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="tab.icon === 'user'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    <path v-if="tab.icon === 'shield'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    <path v-if="tab.icon === 'location'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ tab.name }}</span>
                            </button>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1">
                        
                        <!-- Profile Tab -->
                        <div v-if="activeTab === 'profile'" class="onyx-panel p-6 lg:p-8">
                            <div class="mb-6 border-b border-black/10 pb-4">
                                <h2 class="onyx-title text-xl">Profile Information</h2>
                                <p class="text-sm text-black/60 mt-1">Update your name and email</p>
                            </div>

                            <form @submit.prevent="updateProfile" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-[0.1em] text-black mb-2">Full Name</label>
                                        <input
                                            type="text"
                                            v-model="profileForm.name"
                                            class="w-full px-4 py-3 border-2 border-black focus:ring-0 focus:outline-none focus:border-black transition-all bg-black/[0.02]"
                                            :class="{ 'border-red-600 bg-red-50': profileForm.errors.name }"
                                        />
                                        <p v-if="profileForm.errors.name" class="mt-2 text-xs font-bold text-red-600 uppercase">{{ profileForm.errors.name }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-[0.1em] text-black mb-2">Email</label>
                                        <input
                                            type="email"
                                            v-model="profileForm.email"
                                            class="w-full px-4 py-3 border-2 border-black focus:ring-0 focus:outline-none focus:border-black transition-all bg-black/[0.02]"
                                            :class="{ 'border-red-600 bg-red-50': profileForm.errors.email }"
                                        />
                                        <p v-if="profileForm.errors.email" class="mt-2 text-xs font-bold text-red-600 uppercase">{{ profileForm.errors.email }}</p>
                                    </div>
                                </div>

                                <div v-if="mustVerifyEmail && !user?.email_verified_at" class="p-4 border-2 border-amber-500 bg-amber-50">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-bold text-amber-900 uppercase">Email not verified</p>
                                            <Link
                                                :href="route('verification.send')"
                                                method="post"
                                                as="button"
                                                class="text-sm text-amber-700 hover:text-amber-900 font-bold underline mt-1"
                                            >
                                                Resend verification email
                                            </Link>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="status === 'verification-link-sent'" class="p-4 border-2 border-emerald-500 bg-emerald-50 text-sm font-bold text-emerald-800 uppercase">
                                    New verification link has been sent to your email.
                                </div>

                                <div class="flex items-center gap-4 pt-4 border-t border-black/10">
                                    <button
                                        type="submit"
                                        :disabled="profileForm.processing"
                                        class="onyx-action"
                                    >
                                        {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                                    </button>
                                    <Transition
                                        enter-active-class="transition ease-in-out duration-300"
                                        enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out duration-300"
                                        leave-to-class="opacity-0"
                                    >
                                        <span v-if="profileForm.recentlySuccessful" class="text-sm font-bold uppercase text-black">
                                            ✓ Saved
                                        </span>
                                    </Transition>
                                </div>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div v-if="activeTab === 'security'" class="space-y-6">
                            <!-- Change Password -->
                            <div class="onyx-panel p-6 lg:p-8">
                                <div class="mb-6 border-b border-black/10 pb-4">
                                    <h2 class="onyx-title text-xl">Change Password</h2>
                                    <p class="text-sm text-black/60 mt-1">Use a strong and unique password</p>
                                </div>

                                <form @submit.prevent="updatePassword" class="space-y-6">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-[0.1em] text-black mb-2">Current Password</label>
                                        <div class="relative">
                                            <input
                                                :type="showCurrentPassword ? 'text' : 'password'"
                                                v-model="passwordForm.current_password"
                                                class="w-full px-4 py-3 pr-12 border-2 border-black focus:ring-0 focus:outline-none focus:border-black transition-all bg-black/[0.02]"
                                                :class="{ 'border-red-600 bg-red-50': passwordForm.errors.current_password }"
                                            />
                                            <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-black/50 hover:text-black">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path v-if="!showCurrentPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                </svg>
                                            </button>
                                        </div>
                                        <p v-if="passwordForm.errors.current_password" class="mt-2 text-xs font-bold text-red-600 uppercase">{{ passwordForm.errors.current_password }}</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-[0.1em] text-black mb-2">New Password</label>
                                            <div class="relative">
                                                <input
                                                    :type="showNewPassword ? 'text' : 'password'"
                                                    v-model="passwordForm.password"
                                                    class="w-full px-4 py-3 pr-12 border-2 border-black focus:ring-0 focus:outline-none focus:border-black transition-all bg-black/[0.02]"
                                                    :class="{ 'border-red-600 bg-red-50': passwordForm.errors.password }"
                                                />
                                                <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-black/50 hover:text-black">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path v-if="!showNewPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <p v-if="passwordForm.errors.password" class="mt-2 text-xs font-bold text-red-600 uppercase">{{ passwordForm.errors.password }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-[0.1em] text-black mb-2">Confirm Password</label>
                                            <div class="relative">
                                                <input
                                                    :type="showConfirmPassword ? 'text' : 'password'"
                                                    v-model="passwordForm.password_confirmation"
                                                    class="w-full px-4 py-3 pr-12 border-2 border-black focus:ring-0 focus:outline-none focus:border-black transition-all bg-black/[0.02]"
                                                />
                                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-black/50 hover:text-black">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path v-if="!showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 pt-4 border-t border-black/10">
                                        <button
                                            type="submit"
                                            :disabled="passwordForm.processing"
                                            class="onyx-action"
                                        >
                                            {{ passwordForm.processing ? 'Saving...' : 'Change Password' }}
                                        </button>
                                        <Transition
                                            enter-active-class="transition ease-in-out duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition ease-in-out duration-300"
                                            leave-to-class="opacity-0"
                                        >
                                            <span v-if="passwordForm.recentlySuccessful" class="text-sm font-bold uppercase text-black">
                                                ✓ Password changed
                                            </span>
                                        </Transition>
                                    </div>
                                </form>
                            </div>

                            <!-- Delete Account -->
                            <div class="onyx-panel p-6 lg:p-8 border-red-900 shadow-[4px_4px_0_0_rgba(185,28,28,0.95)]">
                                <div class="flex flex-col sm:flex-row items-start gap-4">
                                    <div class="w-12 h-12 border-2 border-red-900 bg-red-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="onyx-title text-lg text-red-900">Delete Account</h3>
                                        <p class="text-sm text-red-900/70 mt-1">
                                            Once your account is deleted, all data will be permanently lost. Make sure you have downloaded any data you need.
                                        </p>
                                        <button class="mt-4 px-4 py-2 bg-red-700 text-white text-xs uppercase tracking-[0.1em] font-bold border-2 border-red-900 hover:bg-white hover:text-red-900 transition-colors">
                                            Delete My Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Addresses Tab -->
                        <div v-if="activeTab === 'addresses'" class="onyx-panel p-6 lg:p-8">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 border-b border-black/10 pb-4">
                                <div>
                                    <h2 class="onyx-title text-xl">Shipping Addresses</h2>
                                    <p class="text-sm text-black/60 mt-1">Manage addresses for order delivery</p>
                                </div>
                                <Link
                                    :href="route('addresses.create')"
                                    class="onyx-action"
                                >
                                    + Add Address
                                </Link>
                            </div>

                            <div class="text-center py-12 border-2 border-black/20 border-dashed bg-black/[0.02]">
                                <svg class="w-12 h-12 mx-auto text-black/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-black font-bold uppercase tracking-[0.1em]">No saved addresses yet</p>
                                <Link
                                    :href="route('addresses.index')"
                                    class="inline-flex items-center mt-4 text-xs font-bold uppercase tracking-[0.1em] text-black hover:underline"
                                >
                                    Manage Addresses
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StoreLayout>
</template>
