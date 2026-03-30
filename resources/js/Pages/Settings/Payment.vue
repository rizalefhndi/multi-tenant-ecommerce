<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
    paymentMethods: Object,
});

const form = useForm({
    enabled_payment_methods: props.settings.enabled_payment_methods || ['bank_transfer'],
    bank_name: props.settings.bank_name || '',
    bank_account_number: props.settings.bank_account_number || '',
    bank_account_holder: props.settings.bank_account_holder || '',
    midtrans_enabled: props.settings.midtrans_enabled || false,
});

const togglePaymentMethod = (method) => {
    const index = form.enabled_payment_methods.indexOf(method);
    if (index > -1) {
        form.enabled_payment_methods.splice(index, 1);
    } else {
        form.enabled_payment_methods.push(method);
    }
};

const submit = () => {
    form.post(route('settings.payment.update'), {
        preserveScroll: true,
    });
};

const bankList = [
    'BCA', 'BNI', 'BRI', 'Mandiri', 'CIMB Niaga',
    'Permata', 'BTN', 'Danamon', 'OCBC NISP', 'Maybank'
];
</script>

<template>
    <Head>
        <title>Pengaturan Pembayaran</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <p class="onyx-kicker">Settings / Payment</p>
                            <h1 class="onyx-title text-2xl md:text-4xl mt-2">Pembayaran</h1>
                            <p class="mt-2 text-black/60">Aktifkan metode pembayaran yang ingin ditawarkan ke pelanggan.</p>
                        </div>
                        <Link :href="route('settings.index')" class="onyx-action-ghost w-full md:w-auto">Kembali ke Settings</Link>
                    </div>
                </section>

                <form @submit.prevent="submit" class="space-y-6">
                    <section class="onyx-panel p-5 md:p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <p class="onyx-kicker">Metode Pembayaran Aktif</p>
                            <span class="onyx-chip">{{ form.enabled_payment_methods.length }} aktif</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                            <label
                                v-for="(label, method) in paymentMethods"
                                :key="method"
                                class="flex items-center gap-3 p-3 border cursor-pointer transition-colors"
                                :class="form.enabled_payment_methods.includes(method) ? 'border-black bg-black text-white' : 'border-black/30 bg-white hover:border-black'"
                            >
                                <input
                                    type="checkbox"
                                    :checked="form.enabled_payment_methods.includes(method)"
                                    @change="togglePaymentMethod(method)"
                                    class="rounded border-black text-black focus:ring-0"
                                />
                                <span class="text-sm font-semibold uppercase tracking-[0.06em]">{{ label }}</span>
                            </label>
                        </div>
                    </section>

                    <section class="onyx-panel p-5 md:p-6">
                        <p class="onyx-kicker mb-4">Rekening Transfer Manual</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="onyx-kicker mb-1.5 block">Nama Bank</p>
                                <select v-model="form.bank_name" class="h-11 w-full border border-black bg-white px-3 text-sm">
                                    <option value="">Pilih Bank</option>
                                    <option v-for="bank in bankList" :key="bank" :value="bank">{{ bank }}</option>
                                </select>
                            </div>

                            <div>
                                <p class="onyx-kicker mb-1.5 block">Nomor Rekening</p>
                                <input type="text" v-model="form.bank_account_number" class="h-11 w-full border border-black bg-white px-3 text-sm" placeholder="1234567890" />
                            </div>

                            <div>
                                <p class="onyx-kicker mb-1.5 block">Nama Pemilik</p>
                                <input type="text" v-model="form.bank_account_holder" class="h-11 w-full border border-black bg-white px-3 text-sm" placeholder="Nama sesuai rekening" />
                            </div>
                        </div>
                    </section>

                    <section class="onyx-panel p-5 md:p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                            <div>
                                <p class="onyx-kicker">Payment Gateway</p>
                                <h2 class="onyx-title text-lg mt-1">Midtrans</h2>
                            </div>

                            <label class="flex items-center gap-3 border border-black bg-white px-3 py-2 h-11 cursor-pointer">
                                <input type="checkbox" v-model="form.midtrans_enabled" class="rounded border-black text-black focus:ring-0" />
                                <span class="text-xs font-semibold uppercase tracking-[0.1em]">{{ form.midtrans_enabled ? 'Enabled' : 'Disabled' }}</span>
                            </label>
                        </div>

                        <div v-if="form.midtrans_enabled" class="onyx-panel-soft p-4">
                            <p class="text-sm font-semibold uppercase tracking-[0.08em]">Midtrans Aktif</p>
                            <p class="text-sm text-black/65 mt-1">Pelanggan dapat membayar via VA, QRIS, GoPay, ShopeePay, dan kartu kredit.</p>
                        </div>

                        <div v-else class="onyx-panel-soft p-4">
                            <p class="text-sm font-semibold uppercase tracking-[0.08em]">Midtrans Belum Aktif</p>
                            <p class="text-sm text-black/65 mt-1">Aktifkan Midtrans untuk menerima pembayaran otomatis dari checkout.</p>
                        </div>
                    </section>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing" class="onyx-action disabled:opacity-60">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
