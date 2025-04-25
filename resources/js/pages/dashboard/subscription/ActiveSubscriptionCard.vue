<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import Button from '@/components/ui/button/Button.vue'

const props = defineProps<{
    subscription: any | null
}>()

const emit = defineEmits(['cancelled'])

const form = useForm({
    subscription_id: props.subscription?.id
})

function cancelSubscription() {
    if (confirm('Yakin batalkan langganan aktif ini?')) {
        form.post(route('subscription.cancel'), {
            onSuccess: () => emit('cancelled')
        })
    }
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
    <div v-if="subscription" class="border p-4 rounded bg-yellow-50 shadow">
        <h2 class="text-lg font-semibold mb-2">Batalkan Langganan</h2>
        <p><strong>Paket:</strong> {{ subscription.package.name }}</p>
        <p><strong>Mulai:</strong> {{ formatDate(subscription.starts_at) }}</p>
        <p><strong>Berakhir:</strong> {{ formatDate(subscription.ends_at) }}</p>

        <div class="flex justify-end gap-2 mt-4">
            <Button variant="outline" @click="$emit('cancelled')">Kembali</Button>
            <Button variant="destructive" @click="cancelSubscription" :disabled="form.processing">
                {{ form.processing ? 'Membatalkan...' : 'Batalkan Langganan' }}
            </Button>
        </div>
    </div>
</template>