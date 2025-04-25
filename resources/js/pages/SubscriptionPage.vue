<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3'
import { Subscription, SubscriptionPackage } from '@/types'

interface Props {
    packages: SubscriptionPackage[]
    activeSubscription: Subscription | null
}

const props = defineProps<Props>()

const form = useForm({
    package_id: null as number | null,
})

function selectPackage(packageId: number) {
    form.package_id = packageId
    form.post('/subscription/select')
}

function formatRupiah(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value)
}

function formatDate(date: string): string {
    const parsedDate = new Date(date)
    if (isNaN(parsedDate.getTime())) return '-'
    return parsedDate.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}
</script>

<template>
    <div class="p-4 max-w-5xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Pilih Paket Langganan</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="pkg in packages" :key="pkg.id" :class="[
                'border rounded p-4 shadow hover:shadow-md transition',
                activeSubscription?.package?.id === pkg.id
                    ? 'border-blue-500 ring-1 ring-blue-300'
                    : 'border-gray-300',
            ]">
                <h2 class="text-xl font-semibold">{{ pkg.name }}</h2>
                <p class="text-sm mt-1 text-gray-700">
                    {{ formatRupiah(pkg.price_per_month) }}/bulan
                </p>
                <p class="text-xs mt-2">Max Tenant: {{ pkg.max_tenants }}</p>
                <p class="text-xs">Max User/Tenant: {{ pkg.max_users_per_tenant }}</p>

                <button @click="selectPackage(pkg.id)" :disabled="form.processing"
                    class="mt-4 w-full bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                    Pilih Paket
                </button>
            </div>
        </div>

        <div class="mt-10">
            <h2 class="text-xl font-bold">Langganan Aktif</h2>
            <div v-if="activeSubscription && activeSubscription.package" class="mt-2 text-sm bg-gray-100 p-4 rounded">
                <p><strong>Paket:</strong> {{ activeSubscription.package.name }}</p>
                <p><strong>Mulai:</strong> {{ formatDate(activeSubscription.starts_at) }}</p>
                <p><strong>Berakhir:</strong> {{ formatDate(activeSubscription.ends_at) }}</p>
            </div>
            <p v-else class="text-gray-500 text-sm">Belum ada langganan aktif.</p>
        </div>

    </div>
</template>
