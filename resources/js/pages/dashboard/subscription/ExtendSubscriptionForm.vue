<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import Button from '@/components/ui/button/Button.vue'

const props = defineProps<{
    subscription: any | null
}>()

const emit = defineEmits(['extended'])

const form = useForm({
    subscription_id: props.subscription?.id,
    add_months: 1
})

function submit() {
    form.post(route('subscription.extend'), {
        onSuccess: () => emit('extended')
    })
}

function formatDate(dateStr: string): string {
    const date = new Date(dateStr)
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>

<template>
    <div v-if="subscription" class="border p-4 rounded bg-white shadow">
        <h2 class="text-lg font-semibold mb-2">Perpanjang Langganan</h2>
        <p><strong>Paket:</strong> {{ subscription.package.name }}</p>
        <p><strong>Saat ini berakhir pada:</strong> {{ formatDate(subscription.ends_at) }}</p>
        
        <div class="mt-3">
            <label class="block text-sm mb-1">Tambah Durasi (bulan)</label>
            <input 
                type="number" 
                v-model="form.add_months" 
                class="w-full border p-2 rounded"
                min="1" 
            />
            <div v-if="form.errors.add_months" class="text-red-500 text-sm mt-1">
                {{ form.errors.add_months }}
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <Button variant="outline" @click="$emit('extended')">Batal</Button>
            <Button 
                @click="submit" 
                :disabled="form.processing"
            >
                {{ form.processing ? 'Memperpanjang...' : 'Perpanjang' }}
            </Button>
        </div>
    </div>
</template>