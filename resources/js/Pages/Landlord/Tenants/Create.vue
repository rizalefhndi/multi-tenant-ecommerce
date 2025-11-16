<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { watch } from 'vue';

const form = useForm({
    id: '',
    name: '',
    email: '',
    domain: '',
});

// Auto-generate domain from id
watch(() => form.id, (newId) => {
    if (newId && !form.domain) {
        form.domain = `${newId}.localhost`;
    }
});

const submit = () => {
    form.post(route('landlord.tenants.store'));
};
</script>

<template>
    <Head title="Create Tenant" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Tenant</h2>
                <Link
                    :href="route('landlord.tenants.index')"
                    class="text-gray-600 hover:text-gray-800"
                >
                    ← Back to Tenants
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <!-- Info Alert -->
                    <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Creating a new tenant will automatically:
                                </p>
                                <ul class="mt-2 text-sm text-blue-700 list-disc list-inside space-y-1">
                                    <li>Create a new PostgreSQL database</li>
                                    <li>Run all tenant migrations</li>
                                    <li>Seed initial data (users, products)</li>
                                    <li>Setup domain mapping</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Tenant ID -->
                        <div>
                            <InputLabel for="id" value="Tenant ID *" />
                            <TextInput
                                id="id"
                                v-model="form.id"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                placeholder="e.g., store1, shop2, tenant3"
                            />
                            <InputError class="mt-2" :message="form.errors.id" />
                            <p class="mt-1 text-sm text-gray-500">
                                Use lowercase letters, numbers, and dashes only. This will be used as database name: tenant{id}
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
                                placeholder="e.g., John's Store, ABC Shop"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                            <p class="mt-1 text-sm text-gray-500">
                                Display name for the tenant
                            </p>
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
                                placeholder="e.g., admin@store1.com"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                            <p class="mt-1 text-sm text-gray-500">
                                Primary contact email for this tenant
                            </p>
                        </div>

                        <!-- Domain -->
                        <div>
                            <InputLabel for="domain" value="Domain *" />
                            <TextInput
                                id="domain"
                                v-model="form.domain"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                placeholder="e.g., store1.localhost"
                            />
                            <InputError class="mt-2" :message="form.errors.domain" />
                            <p class="mt-1 text-sm text-gray-500">
                                Domain to access this tenant. For local development, use: {id}.localhost
                            </p>
                        </div>

                        <!-- Preview -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-medium text-gray-900 mb-3">Preview:</h4>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Database Name:</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ form.id ? `tenant${form.id}` : '-' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Access URL:</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ form.domain ? `http://${form.domain}:8000` : '-' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Host File Reminder -->
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Don't forget!</strong> Add this line to your hosts file:
                                    </p>
                                    <code class="block mt-2 text-sm bg-yellow-100 text-yellow-800 px-3 py-2 rounded">
                                        127.0.0.1 {{ form.domain || 'your-domain.localhost' }}
                                    </code>
                                    <p class="mt-2 text-xs text-yellow-600">
                                        Windows: C:\Windows\System32\drivers\etc\hosts<br>
                                        Mac/Linux: /etc/hosts
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t">
                            <Link
                                :href="route('landlord.tenants.index')"
                                class="text-gray-600 hover:text-gray-800"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Creating Tenant...' : 'Create Tenant' }}
                            </PrimaryButton>
                        </div>

                        <!-- Processing Info -->
                        <div v-if="form.processing" class="text-center text-sm text-gray-600">
                            <svg class="inline animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating database and running migrations... This may take a moment.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
