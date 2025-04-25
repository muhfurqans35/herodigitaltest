<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import Button from '@/components/ui/button/Button.vue'

const props = defineProps<{
    userId: number | null
    packages: any[]
}>()

const emit = defineEmits(['updated'])

const form = useForm({
    user_id: props.userId,
    package_id: null as number | null,
    duration_months: 1,
})

function submit() {
    form.post(route('subscription.assign'), {
        onSuccess: () => emit('updated')
    })
}
</script>

<template>
    <div class="border p-4 rounded bg-white shadow">
        <h2 class="text-lg font-semibold mb-2">Assign / Update Langganan</h2>
        <div class="space-y-3">
            <div>
                <label class="block text-sm mb-1">Paket</label>
                <select v-model="form.package_id" class="w-full border rounded p-2">
                    <option disabled :value="null">-- Pilih Paket --</option>
                    <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                        {{ pkg.name }} - Rp{{ pkg.price_per_month }}/bulan
                    </option>
                </select>
                <div v-if="form.errors.package_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.package_id }}
                </div>
            </div>

            <div>
                <label class="block text-sm mb-1">Durasi (bulan)</label>
                <input type="number" v-model="form.duration_months" class="w-full border p-2 rounded" min="1" />
                <div v-if="form.errors.duration_months" class="text-red-500 text-sm mt-1">
                    {{ form.errors.duration_months }}
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <Button variant="outline" @click="$emit('updated')">Batal</Button>
                <Button @click="submit" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </Button>
            </div>
        </div>
    </div>
</template>