<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/components/Modal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Pencil, Trash2, Plus } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';

interface Role {
  id: number;
  name: string;
}

interface User {
  id: string;
  name: string;
  email: string;
  roles: Role[]; 
}

interface FormErrors {
  name?: string;
  email?: string;
  password?: string;
  role_id?: string;
  [key: string]: string | undefined;
}

const props = defineProps<{
  users: User[];
  roles: Role[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'User Management', href: '/dashboard/usermanagement' },
];

const showModal = ref(false);
const isEditMode = ref(false);
const formErrors = ref<FormErrors>({});
const isSubmitting = ref(false);

const form = reactive({
  id: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: null as number | null,
});

const resetForm = () => {
  form.id = '';
  form.name = '';
  form.email = '';
  form.password = '';
  form.password_confirmation = '';
  form.role_id = null;
  formErrors.value = {};
};

const openCreateModal = () => {
  isEditMode.value = false;
  resetForm();
  showModal.value = true;
};

const openUpdateModal = (user: User) => {
  isEditMode.value = true;
  form.id = user.id;
  form.name = user.name;
  form.email = user.email;
  form.password = '';
  form.password_confirmation = '';
  form.role_id = user.roles[0]?.id ?? null;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const submitUser = () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  const payload: any = {
    name: form.name,
    email: form.email,
    role_id: form.role_id,
  };

  if (form.password) {
    payload.password = form.password;
    payload.password_confirmation = form.password_confirmation;
  }

  if (isEditMode.value && form.id) {
    router.patch(`/dashboard/usermanagement/${form.id}`, payload, {
      onFinish: () => (isSubmitting.value = false),
      onSuccess: () => closeModal(),
      onError: (errors) => (formErrors.value = errors),
    });
  } else {
    payload.password = form.password;
    payload.password_confirmation = form.password_confirmation;

    router.post('/dashboard/usermanagement', payload, {
      onFinish: () => (isSubmitting.value = false),
      onSuccess: () => closeModal(),
      onError: (errors) => (formErrors.value = errors),
    });
  }
};

const deleteUser = (id: string) => {
  if (confirm('Hapus user ini?')) {
    router.delete(`/dashboard/usermanagement/${id}`);
  }
};
</script>

<template>
  <Head title="User Management" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen p-4 sm:p-6 lg:p-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
          <button
            @click="openCreateModal"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          >
          <Plus class="w-4 h-4" />
          </button>
        </div>

        <!-- Header -->
        <div class="hidden sm:grid grid-cols-4 bg-blue-100 text-gray-700 text-sm font-semibold rounded-t-xl px-4 py-2">
          <div>Nama</div>
          <div>Email</div>
          <div>Role</div>
          <div>Aksi</div>
        </div>

        <!-- Rows -->
        <div class="divide-y text-gray-700">
          <div
            v-for="user in users"
            :key="user.id"
            class="grid grid-cols-1 sm:grid-cols-4 gap-2 px-4 py-3 text-sm bg-white sm:items-center"
          >
            <div><span class="sm:hidden text-gray-500">Nama:</span> {{ user.name }}</div>
            <div><span class="sm:hidden text-gray-500">Email:</span> {{ user.email }}</div>
            <div><span class="sm:hidden text-gray-500">Role:</span> {{ user.roles[0]?.name || '-' }}</div>
            <div class="flex flex-wrap gap-2">
              <button @click="openUpdateModal(user)" class="bg-blue-500 px-2 py-1 text-white rounded" title="Edit">
                <Pencil class="w-5 h-5" />
              </button>
              <button @click="deleteUser(user.id)" class="bg-red-500 px-2 py-1 text-white rounded" title="Hapus">
                <Trash2 class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>


        <!-- Modal -->
        <Modal :show="showModal" title="Form User" @update:show="showModal = false">
          <form @submit.prevent="submitUser" class="space-y-4">
            <div>
              <label class="text-sm">Nama</label>
              <input v-model="form.name" type="text" class="w-full p-2 border rounded text-black" />
              <p v-if="formErrors.name" class="text-red-500 text-xs">{{ formErrors.name }}</p>
            </div>

            <div>
              <label class="text-sm">Email</label>
              <input v-model="form.email" type="email" class="w-full p-2 border rounded text-black" />
              <p v-if="formErrors.email" class="text-red-500 text-xs">{{ formErrors.email }}</p>
            </div>

            <div>
              <label class="text-sm">Password</label>
              <input v-model="form.password" type="password" class="w-full p-2 border rounded text-black" />
              <p v-if="formErrors.password" class="text-red-500 text-xs">{{ formErrors.password }}</p>
            </div>

            <div>
              <label class="text-sm">Konfirmasi Password</label>
              <input v-model="form.password_confirmation" type="password" class="w-full p-2 border rounded text-black" />
            </div>

            <div>
              <label class="text-sm">Role</label>
              <select v-model="form.role_id" class="w-full p-2 border rounded text-black">
                <option value="">Pilih Role</option>
                <option v-for="role in props.roles" :key="role.id" :value="role.id">
                  {{ role.name }}
                </option>
              </select>
              <p v-if="formErrors.role_id" class="text-red-500 text-xs">{{ formErrors.role_id }}</p>
            </div>

            <div class="text-right space-x-2">
              <button @click="closeModal" type="button" class="px-4 py-2 border rounded">
                Batal
              </button>
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded" :disabled="isSubmitting">
                {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </Modal>
      </div>
    </div>
  </AppLayout>
</template>
