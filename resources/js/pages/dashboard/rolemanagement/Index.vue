<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/components/Modal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Pencil, Trash2, Plus } from 'lucide-vue-next';

interface Role {
  id: number;
  name: string;
  users_count: number;
}

const props = defineProps<{
  roles: Role[];
}>();

const showModal = ref(false);
const isEditMode = ref(false);
const form = reactive({ id: null, name: '' });
const formErrors = ref<{ name?: string }>({});

const openCreateModal = () => {
  isEditMode.value = false;
  form.id = null;
  form.name = '';
  showModal.value = true;
};

const openEditModal = (role: Role) => {
  isEditMode.value = true;
  form.id = role.id;
  form.name = role.name;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  formErrors.value = {};
};

const submitRole = () => {
  const payload = { name: form.name };
  const url = isEditMode.value
    ? `/dashboard/roles/${form.id}`
    : `/dashboard/roles`;

  const method = isEditMode.value ? 'put' : 'post';

  router[method](url, payload, {
    onSuccess: () => closeModal(),
    onError: (errors) => (formErrors.value = errors),
  });
};

const deleteRole = (id: number) => {
  if (confirm('Yakin ingin menghapus role ini?')) {
    router.delete(`/dashboard/roles/${id}`);
  }
};
</script>

<template>
  <Head title="Role Management" />
  <AppLayout :breadcrumbs="[{ title: 'Role Management', href: '/dashboard/roles' }]">
    <div class="p-6 max-w-4xl mx-auto">
      <div class="flex justify-between items-center mb-4">
        <button class="bg-blue-600 text-white px-4 py-2 rounded" @click="openCreateModal">
          <Plus class="w-4 h-4" />
        </button>
      </div>

      <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="w-full table-auto text-sm text-left">
          <thead class="bg-blue-100 text-xs uppercase">
            <tr>
              <th class="p-3 text-black">Nama</th>
              <th class="p-3 text-black">Total User</th>
              <th class="p-3 text-black">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="role in roles" :key="role.id" class="border-b hover:bg-gray-50">
              <td class="p-3 text-black">{{ role.name }}</td>
              <td class="p-3 text-black">{{ role.users_count }}</td>
              <td class="p-3 flex gap-2">

                <button class="bg-blue-500 px-2 py-1 text-white rounded" @click="openEditModal(role)">
                  <Pencil class="w-4 h-4" />
                </button>
                <button class="bg-red-500 px-2 py-1 text-white rounded" @click="deleteRole(role.id)">
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Modal :show="showModal" title="Form Role" @update:show="closeModal">
        <form @submit.prevent="submitRole" class="space-y-4">
          <div>
            <label class="block text-sm">Nama Role</label>
            <input v-model="form.name" class="w-full p-2 border rounded text-black" />
            <p v-if="formErrors.name" class="text-red-500 text-xs">{{ formErrors.name }}</p>
          </div>

          <div class="text-right">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              Simpan
            </button>
          </div>
        </form>
      </Modal>
    </div>
  </AppLayout>
</template>
