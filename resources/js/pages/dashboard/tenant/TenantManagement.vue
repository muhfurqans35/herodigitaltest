<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { Pencil, Trash2, UserPlus, Users } from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types'
import axios from 'axios'

const props = defineProps<{
    tenants: {
        data: { id: string; name: string; description: string; logo?: string }[]
        links: any[]
    }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manajemen Tenant', href: '/tenants' }
]

// Modal States
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const showInviteModal = ref(false)
const isEditing = ref(false)
const selectedTenant = ref<any>(null)
const showUserModal = ref(false)
const tenantUsers = ref<{ id: number; name: string; email: string; role: string }[]>([])

// Form Tenant
const form = useForm({
    id: '',
    name: '',
    description: '',
    logo: null as File | null
})

// Form Undangan
const inviteForm = useForm({
    email: '',
    role: 'employee',
    tenant_id: ''
})

// Open Modal
const openCreateModal = () => {
    form.reset()
    form.clearErrors()
    isEditing.value = false
    showFormModal.value = true
}

const openEditModal = (tenant: any) => {

    selectedTenant.value = tenant
    form.id = tenant.id
    form.name = tenant.name
    form.description = tenant.description
    form.logo = null
    isEditing.value = true
    showFormModal.value = true
}

const openDeleteModal = (tenant: any) => {
    selectedTenant.value = tenant
    showDeleteModal.value = true
}

const openInviteModal = (tenant: any) => {
    selectedTenant.value = tenant
    inviteForm.reset()
    inviteForm.role = 'employee'
    inviteForm.tenant_id = tenant.id
    showInviteModal.value = true
}

const openUserModal = async (tenant: any) => {
    selectedTenant.value = tenant

    try {
        const res = await axios.get(`/tenants/${tenant.id}/users`)
        tenantUsers.value = res.data.users ?? []
        showUserModal.value = true
    } catch (e) {
        console.error('Gagal mengambil user:', e)
    }
}

// Submit Action
const submitTenant = () => {
    if (isEditing.value && form.id) {
        // For PATCH requests, explicitly set transform function
        form.transform((data) => {
            // Ensure name is explicitly included
            const formData = new FormData()
            formData.append('_method', 'PATCH')
            formData.append('name', form.name)
            formData.append('description', form.description || '')

            if (form.logo instanceof File) {
                formData.append('logo', form.logo)
            }

            return formData
        }).post(`/tenants/${form.id}`, {
            preserveScroll: true,
            onSuccess: () => showFormModal.value = false
        })
    } else {
        form.post('/tenants', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => showFormModal.value = false
        })
    }
}

const deleteTenant = () => {
    form.delete(`/tenants/${selectedTenant.value.id}`, {
        preserveScroll: true,
        onSuccess: () => showDeleteModal.value = false
    })
}

const submitInvite = () => {
    inviteForm.post('/tenants/invite', {
        preserveScroll: true,
        onSuccess: () => showInviteModal.value = false
    })
}


</script>

<template>
    <Head title="Manajemen Tenant" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen">
            <div class="max-w-8xl mx-auto p-4">
                <div class="flex justify-between items-center mb-4">
                    <Button @click="openCreateModal">+ Tambah Tenant</Button>
                </div>

                <!-- Header -->
                <div
                    class="hidden sm:grid grid-cols-4 gap-4 bg-blue-100 text-sm text-gray-700 font-semibold rounded-t-xl px-4 py-2">
                    <div>Nama</div>
                    <div>Deskripsi</div>
                    <div>Logo</div>
                    <div>Aksi</div>
                </div>

                <!-- Data List -->
                <div v-for="tenant in props.tenants.data" :key="tenant.id"
                    class="grid grid-cols-1 sm:grid-cols-4 gap-2 px-4 py-3 bg-white border-b text-sm sm:items-center">
                    <div>
                        <span class="sm:hidden text-gray-500">Nama:</span> {{ tenant.name }}
                    </div>
                    <div>
                        <span class="sm:hidden text-gray-500">Deskripsi:</span> {{ tenant.description }}
                    </div>
                    <div>
                        <span class="sm:hidden text-gray-500">Logo:</span>
                        <img v-if="tenant.logo" :src="tenant.logo" class="w-12 h-12 object-contain rounded" />
                        <span v-else class="text-gray-400">-</span>
                    </div>
                    <div class="flex gap-1 flex-wrap">
                        <button @click="openEditModal(tenant)"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded">
                            <Pencil class="w-4 h-4" />
                        </button>
                        <button @click="openInviteModal(tenant)"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded">
                            <UserPlus class="w-4 h-4" />
                        </button>
                        <button @click="openDeleteModal(tenant)"
                            class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded">
                            <Trash2 class="w-4 h-4" />
                        </button>
                        <button @click="openUserModal(tenant)"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded">
                            <Users class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex justify-center">
                    <Component v-for="link in props.tenants.links" :is="link.url ? 'a' : 'span'" :href="link.url"
                        class="px-3 py-1 text-sm" :class="{
                            'text-blue-600 font-bold': link.active,
                            'text-gray-400': !link.url
                        }" v-html="link.label" />
                </div>

                <!-- Modals -->
                <!-- Form Modal -->
                <div v-if="showFormModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                    <div class="w-full max-w-md bg-white rounded-lg p-6">
                        <h2 class="text-lg font-semibold mb-4">{{ isEditing ? 'Edit Tenant' : 'Tambah Tenant' }}</h2>
                        <form @submit.prevent="submitTenant" class="space-y-4">
                            <div>
                                <label class="block text-sm mb-1">Nama</label>
                                <input v-model="form.name" type="text" class="w-full border p-2 rounded" />
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Deskripsi</label>
                                <textarea v-model="form.description" class="w-full border p-2 rounded"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Logo</label>
                                <input type="file" @change="(e: any) => form.logo = e.target.files[0]" accept="image/*" />
                            </div>
                            <div class="flex justify-end gap-2">
                                <Button type="button" @click="showFormModal = false"
                                    class="bg-gray-400 hover:bg-gray-500">Batal</Button>
                                <Button type="submit">Simpan</Button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                    <div class="w-full max-w-md bg-white rounded-lg p-6">
                        <h2 class="text-lg font-semibold mb-4">Hapus Tenant</h2>
                        <p>Yakin ingin menghapus tenant <strong>{{ selectedTenant?.name }}</strong>?</p>
                        <div class="flex justify-end gap-2 mt-4">
                            <Button @click="showDeleteModal = false" class="bg-gray-400 hover:bg-gray-500">Batal</Button>
                            <Button @click="deleteTenant" class="bg-red-600 hover:bg-red-700">Hapus</Button>
                        </div>
                    </div>
                </div>

                <!-- Invite Modal -->
                <div v-if="showInviteModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                    <div class="w-full max-w-md bg-white rounded-lg p-6">
                        <h2 class="text-lg font-semibold mb-4">Undang User ke {{ selectedTenant?.name }}</h2>
                        <form @submit.prevent="submitInvite" class="space-y-4">
                            <div>
                                <label class="block text-sm mb-1">Email</label>
                                <input v-model="inviteForm.email" type="email" class="w-full border p-2 rounded" />
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Role</label>
                                <select v-model="inviteForm.role" class="w-full border p-2 rounded">
                                    <option value="employee">Employee</option>
                                    <option value="owner">Owner</option>
                                </select>
                            </div>
                            <div class="flex justify-end gap-2">
                                <Button type="button" @click="showInviteModal = false"
                                    class="bg-gray-400 hover:bg-gray-500">Batal</Button>
                                <Button type="submit">Kirim Undangan</Button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- User Modal -->
                <div v-if="showUserModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                    <div class="w-full max-w-md bg-white rounded-lg p-6">
                        <h2 class="text-lg font-semibold mb-4">User pada {{ selectedTenant?.name }}</h2>
                        <ul class="space-y-2 max-h-64 overflow-y-auto">
                            <li v-for="user in tenantUsers" :key="user.id" class="border rounded p-2">
                                <div class="font-medium">{{ user.name }}</div>
                                <div class="text-sm text-gray-600">{{ user.email }}</div>
                                <div class="text-xs text-blue-600">Role: {{ user.role }}</div>
                            </li>
                            <li v-if="!tenantUsers.length" class="text-sm text-gray-500">Belum ada user yang terhubung.</li>
                        </ul>
                        <div class="flex justify-end mt-4">
                            <Button @click="showUserModal = false" class="bg-gray-400 hover:bg-gray-500">Tutup</Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
