<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
    { id: 'profile', name: 'Profil', icon: 'user' },
    { id: 'security', name: 'Keamanan', icon: 'shield' },
    { id: 'addresses', name: 'Alamat', icon: 'location' },
];
</script>

<template>
    <Head title="Profil Saya" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Pengaturan Akun</h1>
                    <p class="text-gray-500 mt-1">Kelola informasi profil dan keamanan akun Anda</p>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    
                    <!-- Sidebar -->
                    <div class="lg:w-64 flex-shrink-0">
                        <!-- Profile Card -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white text-2xl font-bold mb-4">
                                    {{ getInitials(user?.name) }}
                                </div>
                                <h3 class="font-semibold text-gray-900">{{ user?.name }}</h3>
                                <p class="text-sm text-gray-500">{{ user?.email }}</p>
                                <span class="mt-3 inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                    Terverifikasi
                                </span>
                            </div>
                        </div>

                        <!-- Navigation Tabs -->
                        <nav class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-left transition-colors"
                                :class="activeTab === tab.id 
                                    ? 'bg-indigo-50 text-indigo-700' 
                                    : 'text-gray-600 hover:bg-gray-50'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="tab.icon === 'user'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    <path v-if="tab.icon === 'shield'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    <path v-if="tab.icon === 'location'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="font-medium">{{ tab.name }}</span>
                            </button>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1">
                        
                        <!-- Profile Tab -->
                        <div v-if="activeTab === 'profile'" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-900">Informasi Profil</h2>
                                <p class="text-sm text-gray-500 mt-1">Update nama dan email akun Anda</p>
                            </div>

                            <form @submit.prevent="updateProfile" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                                        <input
                                            type="text"
                                            v-model="profileForm.name"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                            :class="{ 'border-red-500': profileForm.errors.name }"
                                        />
                                        <p v-if="profileForm.errors.name" class="mt-2 text-sm text-red-600">{{ profileForm.errors.name }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                        <input
                                            type="email"
                                            v-model="profileForm.email"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                            :class="{ 'border-red-500': profileForm.errors.email }"
                                        />
                                        <p v-if="profileForm.errors.email" class="mt-2 text-sm text-red-600">{{ profileForm.errors.email }}</p>
                                    </div>
                                </div>

                                <div v-if="mustVerifyEmail && !user?.email_verified_at" class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-yellow-800">Email belum diverifikasi</p>
                                            <Link
                                                :href="route('verification.send')"
                                                method="post"
                                                as="button"
                                                class="text-sm text-yellow-700 underline hover:text-yellow-900"
                                            >
                                                Kirim ulang email verifikasi
                                            </Link>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="status === 'verification-link-sent'" class="p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                                    Link verifikasi baru telah dikirim ke email Anda.
                                </div>

                                <div class="flex items-center gap-4">
                                    <button
                                        type="submit"
                                        :disabled="profileForm.processing"
                                        class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-all"
                                    >
                                        {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                    </button>
                                    <Transition
                                        enter-active-class="transition ease-in-out"
                                        enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out"
                                        leave-to-class="opacity-0"
                                    >
                                        <span v-if="profileForm.recentlySuccessful" class="text-sm text-green-600 font-medium">
                                            ✓ Tersimpan
                                        </span>
                                    </Transition>
                                </div>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div v-if="activeTab === 'security'" class="space-y-6">
                            <!-- Change Password -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                                <div class="mb-6">
                                    <h2 class="text-xl font-semibold text-gray-900">Ubah Password</h2>
                                    <p class="text-sm text-gray-500 mt-1">Gunakan password yang kuat dan unik</p>
                                </div>

                                <form @submit.prevent="updatePassword" class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password Saat Ini</label>
                                        <div class="relative">
                                            <input
                                                :type="showCurrentPassword ? 'text' : 'password'"
                                                v-model="passwordForm.current_password"
                                                class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                                :class="{ 'border-red-500': passwordForm.errors.current_password }"
                                            />
                                            <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path v-if="!showCurrentPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                </svg>
                                            </button>
                                        </div>
                                        <p v-if="passwordForm.errors.current_password" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
                                            <div class="relative">
                                                <input
                                                    :type="showNewPassword ? 'text' : 'password'"
                                                    v-model="passwordForm.password"
                                                    class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                                    :class="{ 'border-red-500': passwordForm.errors.password }"
                                                />
                                                <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path v-if="!showNewPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <p v-if="passwordForm.errors.password" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.password }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
                                            <div class="relative">
                                                <input
                                                    :type="showConfirmPassword ? 'text' : 'password'"
                                                    v-model="passwordForm.password_confirmation"
                                                    class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                                />
                                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path v-if="!showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <button
                                            type="submit"
                                            :disabled="passwordForm.processing"
                                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-all"
                                        >
                                            {{ passwordForm.processing ? 'Menyimpan...' : 'Ubah Password' }}
                                        </button>
                                        <Transition
                                            enter-active-class="transition ease-in-out"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition ease-in-out"
                                            leave-to-class="opacity-0"
                                        >
                                            <span v-if="passwordForm.recentlySuccessful" class="text-sm text-green-600 font-medium">
                                                ✓ Password diubah
                                            </span>
                                        </Transition>
                                    </div>
                                </form>
                            </div>

                            <!-- Delete Account -->
                            <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6 lg:p-8">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">Hapus Akun</h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Setelah akun dihapus, semua data akan hilang permanen. Pastikan Anda sudah mengunduh data yang diperlukan.
                                        </p>
                                        <button class="mt-4 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                            Hapus Akun Saya
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Addresses Tab -->
                        <div v-if="activeTab === 'addresses'" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Alamat Pengiriman</h2>
                                    <p class="text-sm text-gray-500 mt-1">Kelola alamat untuk pengiriman pesanan</p>
                                </div>
                                <Link
                                    :href="route('addresses.create')"
                                    class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all"
                                >
                                    + Tambah Alamat
                                </Link>
                            </div>

                            <div class="text-center py-12 text-gray-500">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p>Belum ada alamat tersimpan</p>
                                <Link
                                    :href="route('addresses.index')"
                                    class="inline-flex items-center mt-4 text-indigo-600 hover:text-indigo-700 font-medium"
                                >
                                    Kelola Alamat
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
    </AuthenticatedLayout>
</template>
