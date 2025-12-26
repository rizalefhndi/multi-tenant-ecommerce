<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    address: {
        type: Object,
        required: true,
    },
    selected: {
        type: Boolean,
        default: false,
    },
    selectable: {
        type: Boolean,
        default: false,
    },
    showActions: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['select', 'edit', 'delete', 'set-default']);

const handleSelect = () => {
    if (props.selectable) {
        emit('select', props.address.id);
    }
};

const handleEdit = () => {
    emit('edit', props.address);
};

const handleDelete = () => {
    if (confirm('Yakin ingin menghapus alamat ini?')) {
        emit('delete', props.address.id);
    }
};

const handleSetDefault = () => {
    emit('set-default', props.address.id);
};

const getLabelIcon = (label) => {
    switch (label) {
        case 'Home':
            return '🏠';
        case 'Office':
            return '🏢';
        default:
            return '📍';
    }
};
</script>

<template>
    <div 
        class="relative p-4 border-2 rounded-xl transition-all"
        :class="[
            selected 
                ? 'border-indigo-600 bg-indigo-50' 
                : 'border-gray-200 hover:border-gray-300',
            selectable ? 'cursor-pointer' : ''
        ]"
        @click="handleSelect"
    >
        <!-- Selected Indicator -->
        <div 
            v-if="selected"
            class="absolute top-3 right-3"
        >
            <div class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>

        <!-- Content -->
        <div class="flex items-start gap-3">
            <!-- Label Icon -->
            <span class="text-2xl">{{ address.label_icon || getLabelIcon(address.label) }}</span>
            
            <!-- Address Info -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-gray-900">{{ address.label }}</span>
                    <span 
                        v-if="address.is_default" 
                        class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full"
                    >
                        Utama
                    </span>
                </div>
                
                <p class="font-medium text-gray-800">{{ address.recipient_name }}</p>
                <p class="text-sm text-gray-600">{{ address.phone }}</p>
                <p class="mt-1 text-sm text-gray-600 line-clamp-2">
                    {{ address.full_address || `${address.address_line_1}, ${address.city}, ${address.province} ${address.postal_code}` }}
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div v-if="showActions && !selectable" class="mt-4 pt-3 border-t border-gray-200 flex items-center gap-4">
            <button 
                @click.stop="handleEdit"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </button>

            <button 
                v-if="!address.is_default"
                @click.stop="handleSetDefault"
                class="text-sm text-gray-600 hover:text-gray-800 font-medium flex items-center gap-1"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                Jadikan Utama
            </button>

            <button 
                @click.stop="handleDelete"
                class="text-sm text-red-600 hover:text-red-800 font-medium flex items-center gap-1 ml-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus
            </button>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
