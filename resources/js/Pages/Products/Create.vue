<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    name: '',
    description: '',
    price: '',
    stock: '',
    image: '',
    is_active: true,
});

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head title="Create Product" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Product</h2>
                <Link
                    :href="route('products.index')"
                    class="text-gray-600 hover:text-gray-800"
                >
                    ← Back to Products
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Product Name -->
                        <div>
                            <InputLabel for="name" value="Product Name *" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                placeholder="e.g., iPhone 15 Pro Max"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Description -->
                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                placeholder="Enter product description..."
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <!-- Price & Stock (Grid) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Price -->
                            <div>
                                <InputLabel for="price" value="Price (Rp) *" />
                                <TextInput
                                    id="price"
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full"
                                    required
                                    placeholder="0"
                                />
                                <InputError class="mt-2" :message="form.errors.price" />
                            </div>

                            <!-- Stock -->
                            <div>
                                <InputLabel for="stock" value="Stock *" />
                                <TextInput
                                    id="stock"
                                    v-model="form.stock"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                    required
                                    placeholder="0"
                                />
                                <InputError class="mt-2" :message="form.errors.stock" />
                            </div>
                        </div>

                        <!-- Image URL -->
                        <div>
                            <InputLabel for="image" value="Image URL" />
                            <TextInput
                                id="image"
                                v-model="form.image"
                                type="url"
                                class="mt-1 block w-full"
                                placeholder="https://example.com/image.jpg"
                            />
                            <InputError class="mt-2" :message="form.errors.image" />
                            <p class="mt-1 text-sm text-gray-500">
                                Enter a URL to a product image
                            </p>
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active (Product will be visible to customers)
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t">
                            <Link
                                :href="route('products.index')"
                                class="text-gray-600 hover:text-gray-800"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Product' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
