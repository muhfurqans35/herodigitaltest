<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import ServiceFieldsInput from '@/components/ServiceFieldsInput.vue'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { Pencil, Trash2 } from 'lucide-vue-next'


type FieldOption = { name: string; price: number }
type Service = {
  id: string
  name: string
  base_price: number
  fields: Record<string, FieldOption[]>
}

const props = defineProps<{ services: Service[] }>()

const showModal = ref(false);
const isEditing = ref(false);
const selectedService = ref<Service | null>(null);

// Initialize form with proper typing
const form = useForm({
  id: '',
  name: '',
  base_price: 0,
  fields: {} as Record<string, FieldOption[]>
})

// Debug form submissions
watch(() => form.processing, (isProcessing) => {
  if (isProcessing) {
    console.log('Submitting form data:', JSON.stringify(form.data()))
  }
})

const openCreateModal = () => {
  form.reset()
  form.clearErrors()
  // Make sure fields is initialized as an empty object
  form.fields = {}
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false;
  form.reset();
  selectedService.value = null;
};

const openEditModal = (service: Service) => {
  form.reset()
  form.clearErrors()

  form.id = service.id
  form.name = service.name
  form.base_price = service.base_price

  // Use a safer way to copy the fields
  try {
    form.fields = JSON.parse(JSON.stringify(service.fields || {}))
  } catch (e) {
    console.error('Error copying fields:', e)
    form.fields = {}
  }

  showModal.value = true
}

const submit = () => {
  // Validate that all field options have names
  let isValid = true
  Object.entries(form.fields).forEach(([category, options]) => {
    options.forEach(option => {
      if (!option.name.trim()) isValid = false
    })
  })

  if (!isValid) {
    alert('Semua opsi harus memiliki nama')
    return
  }

  // Ensure fields data is properly formatted and included
  const data = {
    name: form.name,
    base_price: form.base_price,
    fields: form.fields
  }

  console.log('Submitting service data:', JSON.stringify(data))

  if (form.id) {
    form.put(`/services/${form.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        console.log('Service updated successfully')
        showModal.value = false
      },
      onError: (errors) => {
        console.error('Error updating service:', errors)
      }
    })
  } else {
    form.post('/services', {
      preserveScroll: true,
      onSuccess: () => {
        console.log('Service created successfully')
        showModal.value = false
      },
      onError: (errors) => {
        console.error('Error creating service:', errors)
      }
    })
  }
}

const deleteService = (id: string) => {
  if (confirm('Yakin ingin menghapus layanan ini?')) {
    form.delete(`/services/${id}`, {
      preserveScroll: true,
      onSuccess: () => console.log('Service deleted successfully'),
      onError: (errors) => console.error('Error deleting service:', errors)
    })
  }
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Manajemen Transaksi', href: '/dashboard' },
]
</script>

<template>
  <Head title="Manajemen Layanan" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen">
      <div class="mx-auto♣ max-w-6xl p-4">
        <div class="flex items-center md:justify-between justify-center mb-4">
          <Button @click="openCreateModal">+ Tambah Layanan</Button>
        </div>

        <!-- Header Grid -->
        <div class="hidden sm:grid grid-cols-4 bg-blue-100 text-gray-700 text-sm font-semibold rounded-t-xl px-4 py-2">
          <div>Nama</div>
          <div>Opsi Tambahan</div>
          <div>Harga Dasar</div>
          <div>Aksi</div>
        </div>

        <!-- List of Services -->
        <div class="divide-y text-gray-700">
          <div v-for="service in props.services" :key="service.id"
            class="grid grid-cols-1 sm:grid-cols-4 gap-2 text-sm px-4 py-3 bg-white sm:items-center">
            <div>
              <span class="sm:hidden text-gray-500">Nama:</span> {{ service.name }}
            </div>

            <div>
              <span class="sm:hidden text-gray-500">Opsi Tambahan:</span>
              <div v-for="(options, key) in service.fields" :key="key" class="mb-1">
                <strong>{{ key }}:</strong>
                <span class="text-xs">
                  {{ options.map(opt => `${opt.name} (+Rp ${opt.price.toLocaleString()})`).join(', ') }}
                </span>
              </div>
            </div>

            <div>
              <span class="sm:hidden text-gray-500">Harga Dasar:</span>
              Rp{{ service.base_price.toLocaleString() }}
            </div>

            <div class="flex gap-1 flex-wrap">
              <button @click="openEditModal(service)"
                class="bg-yellow-500 hover:bg-yellow-600 px-2 py-1 text-white rounded" title="Edit">
                <Pencil class="w-4 h-4" />
              </button>
              <button @click="deleteService(service.id)" class="bg-red-600 hover:bg-red-700 px-2 py-1 text-white rounded"
                title="Hapus">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Jika tidak ada layanan -->
          <div v-if="props.services.length === 0" class="text-center text-gray-500 dark:text-gray-400 px-4 py-4">
            Belum ada layanan.
          </div>
        </div>


        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-background max-h-screen overflow-auto">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
              {{ isEditing ? 'Edit Layanan' : 'Tambah Layanan' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-4">
              <div>
                <label class="mb-1 block text-sm text-gray-700 dark:text-gray-300">Nama</label>
                <input v-model="form.name" type="text"
                  class="w-full rounded border p-2 dark:border-gray-700 dark:bg-background dark:text-white" />
              </div>
              <div>
                <label class="mb-1 block text-sm text-gray-700 dark:text-gray-300">Harga Dasar</label>
                <input v-model.number="form.base_price" type="number"
                  class="w-full rounded border p-2 dark:border-gray-700 dark:bg-background dark:text-white" />
              </div>
              <div>
                <label class="mb-1 block text-sm text-gray-700 dark:text-gray-300">Opsi Tambahan</label>
                <ServiceFieldsInput v-model="form.fields" />
              </div>
              <div class="flex justify-end space-x-2">
                <Button type="button" @click="closeModal" class="bg-primary/50 hover:bg-primary/40">
                  Batal
                </Button>
                <Button type="submit">Simpan</Button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
