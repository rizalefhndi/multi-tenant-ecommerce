<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    success: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const showSuccessAlert = ref(false);

onMounted(() => {
    if (props.success) {
        showSuccessAlert.value = true;
    }
});

const closeAlert = () => {
    showSuccessAlert.value = false;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login" />

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
        <!-- Left Side - Visual Branding -->
        <div class="hidden lg:block relative bg-black overflow-hidden h-full">
             <!-- Background Image -->
             <div class="absolute inset-0">
                <img 
                    src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=1000&auto=format&fit=crop"
                    alt="Fashion Model"
                    class="w-full h-full object-cover grayscale opacity-60"
                />
                <div class="absolute inset-0 bg-black/40"></div>
            </div>

            <!-- Overlay Text -->
            <div class="relative z-10 h-full flex flex-col justify-between p-12 text-white">
                <div class="font-black text-2xl tracking-widest uppercase">ONYX</div>
                
                <div>
                    <h1 class="text-7xl font-black mb-4 leading-[0.9] tracking-tighter uppercase">
                        Welcome <br/> Back
                    </h1>
                    <p class="text-xl max-w-md font-medium tracking-wide text-gray-300">
                        Continue your journey. Own your style.
                    </p>
                </div>

                <div class="flex gap-4 text-sm font-bold tracking-widest uppercase text-gray-400">
                    <span>© 2025</span>
                    <span>•</span>
                    <span>Secure Access</span>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex items-center justify-center p-6 sm:p-12 bg-white h-full relative">
            <!-- Success Alert Modal -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 scale-90"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-90"
            >
                <div
                    v-if="showSuccessAlert"
                    class="absolute inset-0 z-50 flex items-center justify-center p-6 bg-black/80 backdrop-blur-sm"
                    @click.self="closeAlert"
                >
                    <div class="bg-white border-4 border-black p-8 max-w-md w-full shadow-2xl transform">
                        <!-- Success Icon -->
                        <div class="flex justify-center mb-6">
                            <div class="w-20 h-20 bg-black rounded-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Title -->
                        <h2 class="text-3xl font-black uppercase text-center mb-4 tracking-tighter">
                            Account Created!
                        </h2>

                        <!-- Message -->
                        <p class="text-center text-gray-600 font-medium mb-8 leading-relaxed">
                            {{ success }}
                        </p>

                        <!-- Action Button -->
                        <button
                            @click="closeAlert"
                            class="w-full py-4 bg-black text-white font-black uppercase tracking-widest text-sm hover:scale-[1.02] active:scale-[0.98] transition-transform shadow-xl"
                        >
                            Got It!
                        </button>
                    </div>
                </div>
            </Transition>

            <div class="w-full max-w-md space-y-10">
                <!-- Mobile Header -->
                <div class="lg:hidden mb-8">
                     <div class="font-black text-2xl tracking-widest uppercase mb-8">ONYX</div>
                     <h2 class="text-4xl font-black text-gray-900 tracking-tighter uppercase">Sign In</h2>
                </div>
                
                <!-- Desktop Header -->
                <div class="hidden lg:block text-left">
                    <h2 class="text-4xl font-black text-gray-900 tracking-tighter uppercase">Sign In</h2>
                    <p class="mt-2 text-gray-500">Enter your credentials to access your account.</p>
                </div>

                <!-- Status Message -->
                <div v-if="status" class="p-4 bg-black text-white rounded-lg text-sm flex items-center gap-2 font-bold uppercase tracking-wide">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email -->
                    <div class="group">
                        <label for="email" class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 group-focus-within:text-black transition-colors">
                            Email Address
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            autofocus
                            autocomplete="username"
                            class="block w-full px-0 py-3 bg-transparent border-0 border-b-2 border-gray-200 text-gray-900 font-bold placeholder-gray-300 focus:!ring-0 focus:!shadow-none focus:!outline-none focus:!border-black !ring-0 !shadow-none !outline-none border-b-2 transition-colors duration-300 appearance-none text-lg"
                            :class="{ 'border-red-500': form.errors.email }"
                            placeholder="name@example.com"
                        />
                        <p v-if="form.errors.email" class="mt-2 text-xs font-bold text-red-600 uppercase tracking-wide">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="group">
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-black uppercase tracking-widest text-gray-500 group-focus-within:text-black transition-colors">
                                Password
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs font-bold text-gray-400 hover:text-black uppercase tracking-wider transition-colors"
                            >
                                Forgot?
                            </Link>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                autocomplete="current-password"
                                class="block w-full px-0 py-3 bg-transparent border-0 border-b-2 border-gray-200 text-gray-900 font-bold placeholder-gray-300 focus:!ring-0 focus:!shadow-none focus:!outline-none focus:!border-black !ring-0 !shadow-none !outline-none border-b-2 transition-colors duration-300 appearance-none text-lg pr-10"
                                :class="{ 'border-red-500': form.errors.password }"
                                placeholder="••••••••"
                            />
                             <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-0 top-3 text-gray-300 hover:text-black transition-colors"
                            >
                                <span class="text-xs font-bold uppercase">{{ showPassword ? 'Hide' : 'Show' }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-xs font-bold text-red-600 uppercase tracking-wide">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            type="checkbox"
                            v-model="form.remember"
                            class="h-4 w-4 text-black border-gray-300 rounded focus:ring-black cursor-pointer"
                        />
                        <label for="remember-me" class="ml-2 block text-sm font-bold text-gray-600 cursor-pointer select-none">
                            Keep me logged in
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center items-center py-4 bg-black text-white font-black uppercase tracking-widest text-sm rounded-full hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 shadow-xl disabled:opacity-70 disabled:cursor-not-allowed group"
                    >
                        <span v-if="!form.processing">Sign In</span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing
                        </span>
                    </button>

                    <div class="relative py-4">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t-2 border-gray-100"></div>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase tracking-widest font-bold">
                            <span class="bg-white px-4 text-gray-400">Or Continue With</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" class="flex items-center justify-center gap-3 py-3 border-2 border-gray-100 rounded-lg hover:border-black hover:bg-black hover:text-white transition-all duration-300 group">
                            <svg class="w-5 h-5 fill-current transition-colors" viewBox="0 0 24 24">
                                <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                            </svg>
                            <span class="text-xs font-black uppercase tracking-wider">Google</span>
                        </button>
                        <button type="button" class="flex items-center justify-center gap-3 py-3 border-2 border-gray-100 rounded-lg hover:border-black hover:bg-black hover:text-white transition-all duration-300 group">
                            <svg class="w-5 h-5 fill-current transition-colors" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12c0 5.01 3.66 9.15 8.44 9.88v-6.99H7.9v-2.89h2.54V9.8c0-2.51 1.49-3.89 3.77-3.89 1.09 0 2.24.19 2.24.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.89h-2.33v6.99C18.34 21.15 22 17.01 22 12c0-5.52-4.48-10-10-10z"/>
                            </svg>
                            <span class="text-xs font-black uppercase tracking-wider">Facebook</span>
                        </button>
                    </div>

                    <div class="text-center pt-2">
                        <p class="text-sm text-gray-500">
                            New here?
                            <Link :href="route('register')" class="font-black text-black hover:underline uppercase tracking-wide ml-1">
                                Create Account
                            </Link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Force remove default browser focus styles */
input:focus, input:active {
    outline: none !important;
    box-shadow: none !important;
    --tw-ring-shadow: none !important;
}

/* Handle Chrome/Safari Autofill background */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active {
    -webkit-box-shadow: 0 0 0 30px white inset !important;
    -webkit-text-fill-color: #000 !important;
    transition: background-color 5000s ease-in-out 0s;
}
</style>
