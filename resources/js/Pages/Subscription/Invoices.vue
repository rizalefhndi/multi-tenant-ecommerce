<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoices: Object,
});

const getStatusClasses = (color) => {
    const classes = {
        gray: 'bg-gray-100 text-gray-700',
        warning: 'bg-amber-100 text-amber-700',
        success: 'bg-green-100 text-green-700',
        danger: 'bg-red-100 text-red-700',
        dark: 'bg-gray-800 text-white',
        info: 'bg-blue-100 text-blue-700',
    };
    return classes[color] || classes.gray;
};
</script>

<template>
    <Head title="Riwayat Tagihan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Riwayat Tagihan
                </h2>
                <Link
                    :href="route('subscription.index')"
                    class="text-sm text-indigo-600 hover:text-indigo-700"
                >
                    ← Kembali
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                
                <!-- Empty State -->
                <div 
                    v-if="invoices.data.length === 0"
                    class="bg-white rounded-xl shadow-sm p-12 text-center"
                >
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada tagihan</h3>
                    <p class="text-gray-500">Anda belum memiliki riwayat tagihan.</p>
                </div>

                <!-- Invoices List -->
                <div v-else class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Invoice
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Periode
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr 
                                v-for="invoice in invoices.data" 
                                :key="invoice.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ invoice.invoice_number }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ invoice.plan_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ invoice.period_display }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ invoice.formatted_total }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="px-2.5 py-0.5 text-xs font-medium rounded-full"
                                        :class="getStatusClasses(invoice.status_color)"
                                    >
                                        {{ invoice.status_label }}
                                    </span>
                                    <div v-if="invoice.is_overdue" class="text-xs text-red-600 mt-1">
                                        Jatuh tempo: {{ invoice.due_at }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <Link
                                        :href="route('subscription.invoice', invoice.id)"
                                        class="text-indigo-600 hover:text-indigo-700 text-sm font-medium"
                                    >
                                        Lihat Detail
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div 
                        v-if="invoices.links && invoices.links.length > 3"
                        class="px-6 py-4 border-t border-gray-200 flex items-center justify-between"
                    >
                        <div class="text-sm text-gray-500">
                            Menampilkan {{ invoices.from }} - {{ invoices.to }} dari {{ invoices.total }} tagihan
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in invoices.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                class="px-3 py-1 text-sm rounded-lg transition-colors"
                                :class="link.active 
                                    ? 'bg-indigo-600 text-white' 
                                    : link.url 
                                        ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        : 'bg-gray-50 text-gray-400 cursor-not-allowed'"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
