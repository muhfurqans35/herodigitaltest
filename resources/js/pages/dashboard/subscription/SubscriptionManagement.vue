<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import Modal from '@/components/Modal.vue'
import AssignSubscriptionForm from './AssignSubscriptionForm.vue'
import ActiveSubscriptionCard from './ActiveSubscriptionCard.vue'
import ExtendSubscriptionForm from './ExtendSubscriptionForm.vue'
import { Trash2, CalendarPlus, Plus } from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
    users: any[],
    packages: any[]
}>()

const showAssignModal = ref(false)
const showCancelModal = ref(false)
const selectedUser = ref<any | null>(null)
const showExtendModal = ref(false)

function openAssignModal(user = null) {
    selectedUser.value = user
    showAssignModal.value = true
}

function openCancelModal(user: any) {
    selectedUser.value = user
    showCancelModal.value = true
}

function openExtendModal(user: any) {
    selectedUser.value = user
    showExtendModal.value = true
}

function onSubscriptionUpdated() {
    showAssignModal.value = false
    selectedUser.value = null
    // You might want to reload data here or emit an event
}

function onSubscriptionCancelled() {
    showCancelModal.value = false
    selectedUser.value = null
    // You might want to reload data here or emit an event
}

function onSubscriptionExtended() {
    showExtendModal.value = false
    selectedUser.value = null
    // You might want to reload data here or emit an event
}
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manajemen Subscription', href: '/subscriptions' }
]
</script>

<template>
    <Head title="Manajemen Subscription" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen">
            <div class="max-w-6xl mx-auto mt-8 p-4">

                <!-- Header Grid -->
                <div
                    class="hidden sm:grid grid-cols-4 bg-blue-100 text-gray-700 text-sm font-semibold rounded-t-xl px-4 py-2">
                    <div>User</div>
                    <div>Paket</div>
                    <div>Durasi</div>
                    <div>Aksi</div>
                </div>

                <!-- Daftar User -->
                <div class="divide-y text-gray-700">
                    <div v-for="user in users" :key="user.id"
                        class="grid grid-cols-1 sm:grid-cols-4 gap-2 text-sm px-4 py-3 bg-white sm:items-center">
                        <div>
                            <span class="sm:hidden text-gray-500">User:</span> {{ user.name }}
                        </div>
                        <div>
                            <span class="sm:hidden text-gray-500">Paket:</span>
                            {{ user.subscription?.package?.name || '-' }}
                        </div>
                        <div>
                            <span class="sm:hidden text-gray-500">Durasi:</span>
                            {{ user.subscription ? `${user.subscription.starts_at} - ${user.subscription.ends_at}` : '-' }}
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <button @click="openAssignModal(user)"
                                class="bg-green-600 hover:bg-green-700 px-2 py-1 text-white rounded">
                                <Plus class="w-4 h-4" />
                            </button>
                            <button v-if="user.subscription" @click="openExtendModal(user)"
                                class="bg-yellow-500 hover:bg-yellow-600 px-2 py-1 text-white rounded" title="Edit">
                                <CalendarPlus class="w-4 h-4" />
                            </button>
                            <button v-if="user.subscription" @click="openCancelModal(user)"
                                class="bg-red-600 hover:bg-red-700 px-2 py-1 text-white rounded">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Assign -->
                <Modal v-model:open="showAssignModal">
                    <AssignSubscriptionForm :user-id="selectedUser?.id" :packages="packages"
                        @updated="onSubscriptionUpdated" />
                </Modal>

                <!-- Modal Cancel -->
                <Modal v-model:open="showCancelModal">
                    <ActiveSubscriptionCard :subscription="selectedUser?.subscription"
                        @cancelled="onSubscriptionCancelled" />
                </Modal>

                <!-- Modal Extend -->
                <Modal v-model:open="showExtendModal">
                    <ExtendSubscriptionForm :subscription="selectedUser?.subscription" @extended="onSubscriptionExtended" />
                </Modal>
            </div>
        </div>
    </AppLayout>
</template>