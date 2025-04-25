<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Pencil, Trash2, Eye } from 'lucide-vue-next'
import Button from '@/components/ui/button/Button.vue'
import { type BreadcrumbItem } from '@/types'
import { User } from '@/types'

const props = defineProps<{
    users: {
        data: User[]
        links: any[]
        current_page: number
        total: number
        [key: string]: any
    }
    roles: {
        id: number
        name: string
    }[]
}>()

const showModal = ref(false)
const selectedUser = ref<User | null>(null)

const form = useForm({
    name: '',
    email: '',
    phone: '',
    global_role_id: 0,
})

const openDetailModal = (user: User) => {
    selectedUser.value = user
    form.name = user.name
    form.email = user.email
    form.phone = user.phone || ''
    form.global_role_id = user.global_role?.id || 0
    showModal.value = true
}

const closeDetailModal = () => {
    showModal.value = false
    selectedUser.value = null
}

const deleteUser = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
        form.delete(route('users.destroy', id), {
            preserveScroll: true,
        })
    }
}

const updateUser = () => {
    if (!selectedUser.value) return
    form.patch(route('users.update', selectedUser.value.id), {
        onSuccess: () => {
            closeDetailModal()
            alert('Pengguna berhasil diperbarui.')
        },
        onError: (errors) => {
            console.error('Terjadi kesalahan:', errors)
        }
    })
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Manajemen Pengguna', href: '/dashboard' },
]
</script>

<template>
    <Head title="Manajemen Pengguna" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen">
            <div class="p-4 max-w-6xl mx-auto mt-8">

                <!-- Header Grid -->
                <div
                    class="hidden sm:grid grid-cols-4 bg-blue-100 text-gray-700 text-sm font-semibold rounded-t-xl px-4 py-2">
                    <div class="text-center">Nama Pengguna</div>
                    <div class="text-center">Email</div>
                    <div class="text-center">Role</div>
                    <div class="text-center">Aksi</div>
                </div>

                <!-- User List -->
                <div class="divide-y text-gray-700">
                    <div v-for="user in users.data" :key="user.id"
                        class="grid grid-cols-1 sm:grid-cols-4 gap-2 text-sm px-4 py-3 bg-white sm:items-center">
                        <div class="text-center">
                            <span class="sm:hidden text-gray-500">Nama:</span>
                            {{ user.name }}
                        </div>
                        <div class="text-center">
                            <span class="sm:hidden text-gray-500">Email:</span>
                            {{ user.email }}
                        </div>
                        <div class="text-center">
                            <span class="sm:hidden text-gray-500">Role:</span>
                            {{ user.global_role ? user.global_role.name : '-' }}
                        </div>
                        <div class="flex justify-center gap-1 flex-wrap">
                            <button @click="openDetailModal(user)"
                                class="bg-blue-600 hover:bg-blue-700 px-2 py-1 text-white rounded" title="Detail">
                                <Eye class="w-4 h-4" />
                            </button>
                            <button @click="deleteUser(user.id)"
                                class="bg-red-600 hover:bg-red-700 px-2 py-1 text-white rounded" title="Hapus">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div v-if="users.data.length === 0" class="text-center text-gray-500 px-4 py-4">
                        Belum ada pengguna.
                    </div>
                </div>

                <!-- Modal -->
                <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white p-6 rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                        <h2 class="text-lg font-bold mb-4">Detail Pengguna</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama</label>
                                <input v-model="form.name" class="w-full border border-gray-300 rounded-md p-2 mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" v-model="form.email"
                                    class="w-full border border-gray-300 rounded-md p-2 mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telepon</label>
                                <input v-model="form.phone" class="w-full border border-gray-300 rounded-md p-2 mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Role</label>
                                <select v-model="form.global_role_id"
                                    class="w-full border border-gray-300 rounded-md p-2 mt-1">
                                    <option disabled value="0">Pilih Role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-4">
                            <Button @click="updateUser" class="px-4 py-2 bg-green-600 text-white text-sm rounded">
                                Simpan Perubahan
                            </Button>
                            <Button @click="closeDetailModal" class="px-4 py-2 bg-gray-300 text-sm rounded">
                                Tutup
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
