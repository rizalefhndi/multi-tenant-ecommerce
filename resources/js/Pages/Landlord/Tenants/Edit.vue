<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    tenant: Object,
});

const form = useForm({
    name: props.tenant.name,
    email: props.tenant.email,
});

const submit = () => {
    form.put(route('landlord.tenants.update', props.tenant.id));
};
</script>

<template>
    <Head title="Edit Tenant" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Tenant</h2>
                <Link
                    :href="route('landlord.tenants.show', tenant.id)"
                    class="text-gray-600 hover:text-gray-800"
                >
                    ← Back to Details
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <!-- Info Alert -->
                    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Note:</strong> Tenant ID and domains cannot be changed after creation.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Tenant ID (Read-only) -->
                        <div>
                            <InputLabel for="id" value="Tenant ID" />
                            <TextInput
                                id="id"
                                :model-value="tenant.id"
                                type="text"
                                class="mt-1 block w-full bg-gray-100"
                                disabled
                            />
                            <p class="mt-1 text-sm text-gray-500">
                                Tenant ID cannot be changed
                            </p>
                        </div>

                        <!-- Tenant Name -->
                        <div>
                            <InputLabel for="name" value="Tenant Name *" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Tenant Email -->
                        <div>
                            <InputLabel for="email" value="Tenant Email *" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Current Domains (Read-only) -->
                        <div>
                            <InputLabel value="Domains" />
                            <div class="mt-2 space-y-2">
                                <div
                                    v-for="domain in tenant.domains"
                                    :key="domain.id"
                                    class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200"
                                >
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    <span class="font-medium text-gray-900">{{ domain.domain }}</span>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Domains are managed separately and cannot be changed here
                            </p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t">
                            <Link
                                :href="route('landlord.tenants.show', tenant.id)"
                                class="text-gray-600 hover:text-gray-800"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
