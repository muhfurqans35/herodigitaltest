<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/components/Modal.vue';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Pencil, Trash2, Eye, Plus, ChevronDown, ChevronUp, Info } from 'lucide-vue-next';

interface Service {
  id: string;
  name: string;
  price: number;
  total_units: number;
  manual_book_path: string;
  extra_info?: Record<string, string> | string; // Bisa berupa objek atau string JSON
}

interface AuditLog {
  event: string;
  user?: { name: string };
  created_at: string;
  old_values: Record<string, any>;
  new_values: Record<string, any>;
}

interface FormErrors {
  name?: string;
  price?: string;
  total_units?: string;
  manual_book?: string;
  [key: string]: string | undefined;
}

const props = defineProps<{ services: Service[] }>();

const showModal = ref(false);
const isEditMode = ref(false);
const formErrors = ref<FormErrors>({});
const isSubmitting = ref(false);
const showExtraInfo = ref(false);
const showExtraInfoDetailsModal = ref(false);
const selectedServiceExtraInfo = ref<Record<string, string>>({});
const selectedServiceName = ref('');

const form = reactive({
  id: '',
  name: '',
  price: 0,
  total_units: 1,
  extra_info: {} as Record<string, string>,
});

const file = ref<File | null>(null);
const showAuditModal = ref(false);
const auditLogs = ref<AuditLog[]>([]);
const selectedServiceId = ref<string | null>(null);
const newExtraKey = ref('');
const newExtraValue = ref('');

// Helper untuk menangani extra_info yang mungkin string atau objek
const parseExtraInfo = (extraInfo: any): Record<string, string> => {
  if (!extraInfo) return {};
  
  if (typeof extraInfo === 'string') {
    try {
      return JSON.parse(extraInfo);
    } catch (e) {
      console.error('Failed to parse extra_info string:', e);
      return {};
    }
  }
  
  return { ...extraInfo }; // Clone jika sudah berupa objek
};

const addExtraInfo = () => {
  if (newExtraKey.value && newExtraValue.value) {
    form.extra_info[newExtraKey.value] = newExtraValue.value;
    newExtraKey.value = '';
    newExtraValue.value = '';
  }
};

const resetForm = () => {
  form.id = '';
  form.name = '';
  form.price = 0;
  form.total_units = 1;
  form.extra_info = {};
  file.value = null;
  formErrors.value = {};
  showExtraInfo.value = false;
};

const openCreateModal = () => {
  isEditMode.value = false;
  resetForm();
  showModal.value = true;
};

const openUpdateModal = (service: Service) => {
  isEditMode.value = true;
  resetForm();
  form.id = service.id;
  form.name = service.name;
  form.price = service.price;
  form.total_units = service.total_units;
  
  // Parsing extra_info dengan benar
  form.extra_info = parseExtraInfo(service.extra_info);
  
  console.log('Loaded extra_info:', form.extra_info); // Debug
  showModal.value = true;
};

const showExtraInfoDetails = (service: Service) => {
  selectedServiceName.value = service.name;
  selectedServiceExtraInfo.value = parseExtraInfo(service.extra_info);
  showExtraInfoDetailsModal.value = true;
};

const toggleExtraInfo = () => {
  showExtraInfo.value = !showExtraInfo.value;
};

const showAuditFor = async (serviceId: string) => {
  try {
    selectedServiceId.value = serviceId;
    const response = await axios.get(`/dashboard/services/${serviceId}/audits`);
    auditLogs.value = response.data;
    showAuditModal.value = true;
  } catch (e) {
    alert('Gagal mengambil audit log.');
  }
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    file.value = target.files[0];
  } else {
    file.value = null;
  }
};

const validateForm = () => {
  const errors: FormErrors = {};
  if (!form.name || form.name.trim() === '') {
    errors.name = 'Nama layanan harus diisi';
  }
  if (typeof form.price !== 'number' || form.price < 0) {
    errors.price = 'Harga harus berupa angka positif';
  }
  if (typeof form.total_units !== 'number' || form.total_units < 1) {
    errors.total_units = 'Jumlah unit harus minimal 1';
  }
  formErrors.value = errors;
  return Object.keys(errors).length === 0;
};

const submitService = () => {
  if (!validateForm() || isSubmitting.value) return;
  isSubmitting.value = true;

  const formData = new FormData();
  formData.append('name', form.name);
  formData.append('price', form.price.toString());
  formData.append('total_units', form.total_units.toString());
  
  // Pastikan extra_info dikirim sebagai JSON string
  const extraInfoJson = JSON.stringify(form.extra_info);
  formData.append('extra_info', extraInfoJson);
  console.log('Submitting extra_info:', extraInfoJson); // Debug

  if (file.value) {
    formData.append('manual_book', file.value);
  }

  const routeUrl = isEditMode.value && form.id
    ? `/dashboard/services/${form.id}`
    : '/dashboard/services';

  router.post(routeUrl, formData, {
    forceFormData: true,
    onFinish: () => { isSubmitting.value = false; },
    onSuccess: () => { closeModal(); },
    onError: (errors) => {
      formErrors.value = errors;
    },
    headers: isEditMode.value ? { 'X-HTTP-Method-Override': 'PATCH' } : {},
  });
};

const deleteService = (id: string) => {
  if (confirm('Hapus layanan ini?')) {
    router.delete(`/dashboard/services/${id}`);
  }
};

// Computed property untuk mendapatkan jumlah extra info dari setiap layanan
const getExtraInfoCount = (service: Service): number => {
  const extraInfo = parseExtraInfo(service.extra_info);
  return Object.keys(extraInfo).length;
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Services', href: '/dashboard/services' },
];
</script>

<template>
  <Head title="Dashboard Services" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen p-4 sm:p-6 lg:p-8">
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
          <button @click="openCreateModal" class="bg-blue-600 px-4 py-2 rounded-lg hover:bg-blue-700 text-white w-full sm:w-auto flex items-center justify-center gap-2">
            <Plus class="w-4 h-4" />
            <span>Tambah Layanan</span>
          </button>
        </div>

        <div class="hidden sm:grid grid-cols-6 bg-blue-100 text-gray-700 text-sm font-semibold rounded-t-xl px-4 py-2">
          <div>Nama</div>
          <div>Harga</div>
          <div>Unit</div>
          <div>Manual</div>
          <div>Extra Info</div>
          <div>Aksi</div>
        </div>
        <div class="divide-y text-gray-700">
          <div v-for="s in services" :key="s.id" class="grid grid-cols-1 sm:grid-cols-6 gap-2 px-4 py-3 text-sm bg-white sm:items-center">
            <div>{{ s.name }}</div>
            <div>Rp {{ s.price.toLocaleString() }}</div>
            <div>{{ s.total_units }}</div>
            <div>
              <template v-if="s.manual_book_path">
                <a :href="`/storage/${s.manual_book_path}`" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
              </template>
              <template v-else>
                <span class="text-gray-500">Tidak ada</span>
              </template>
            </div>
            <div>
              <button v-if="getExtraInfoCount(s) > 0" 
                      @click="showExtraInfoDetails(s)"
                      class="bg-green-500 px-2 py-1 text-white rounded flex items-center gap-1">
                <Info class="w-4 h-4" />
                <span>{{ getExtraInfoCount(s) }}</span>
              </button>
              <span v-else class="text-gray-500">Tidak ada</span>
            </div>
            <div class="flex flex-wrap gap-2">
              <button @click="openUpdateModal(s)" class="bg-blue-500 px-2 py-1 text-white rounded"><Pencil class="w-4 h-4" /></button>
              <button @click="deleteService(s.id)" class="bg-red-500 px-2 py-1 text-white rounded"><Trash2 class="w-4 h-4" /></button>
              <button @click="showAuditFor(s.id)" class="bg-purple-500 px-2 py-1 text-white rounded"><Eye class="w-4 h-4" /></button>
            </div>
          </div>
        </div>

        <Modal :show="showModal" title="Form Layanan" @update:show="showModal = false">
          <form @submit.prevent="submitService" class="space-y-4">
            <div>
              <label class="block text-sm font-medium">Nama Layanan</label>
              <input v-model="form.name" type="text" class="w-full border p-2 rounded text-gray-700" />
              <p v-if="formErrors.name" class="text-red-500 text-xs">{{ formErrors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium">Harga</label>
              <input v-model.number="form.price" type="number" class="w-full border p-2 rounded text-gray-700" />
              <p v-if="formErrors.price" class="text-red-500 text-xs">{{ formErrors.price }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium">Jumlah Unit</label>
              <input v-model.number="form.total_units" type="number" class="w-full border p-2 rounded text-gray-700" />
              <p v-if="formErrors.total_units" class="text-red-500 text-xs">{{ formErrors.total_units }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium">Manual Book (PDF)</label>
              <input type="file" accept=".pdf" @change="handleFileChange" class="w-full border p-2 rounded text-gray-700" />
              <p v-if="formErrors.manual_book" class="text-red-500 text-xs">{{ formErrors.manual_book }}</p>
            </div>
            
            
            <div>
              <div class="flex justify-between items-center mb-2">
                <label class="block text-sm font-medium">Extra Info</label>
                <button type="button" @click="toggleExtraInfo" class="bg-gray-200 px-2 py-1 rounded flex items-center gap-1 text-gray-700">
                  <span>{{ showExtraInfo ? 'Sembunyikan' : 'Tampilkan' }}</span>
                  <ChevronUp v-if="showExtraInfo" class="w-4 h-4" />
                  <ChevronDown v-else class="w-4 h-4" />
                </button>
              </div>
              
              <div v-if="showExtraInfo" class="bg-gray-50 p-3 rounded border">
                <div v-if="Object.keys(form.extra_info).length === 0" class="text-gray-500 text-sm mb-2">
                  Belum ada data extra info
                </div>
                
                <div v-for="(value, key) in form.extra_info" :key="key" class="flex gap-2 mb-2 text-gray-700">
                  <input :value="key" disabled class="border p-1 rounded w-1/3 bg-gray-100" placeholder="Key" />
                  <input v-model="form.extra_info[key]" class="border p-1 rounded w-2/3" placeholder="Value" />
                  <button type="button" @click="delete form.extra_info[key]" class="text-red-500">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
                
                <div class="flex gap-2 mt-2 text-gray-700">
                  <input v-model="newExtraKey" placeholder="Key" class="border p-1 rounded w-1/3" />
                  <input v-model="newExtraValue" placeholder="Value" class="border p-1 rounded w-2/3" />
                  <button type="button" @click="addExtraInfo" class="bg-blue-500 text-white px-2 py-1 rounded">
                    <Plus class="w-4 h-4" />
                  </button>
                </div>
              </div>
              
              <div v-else-if="Object.keys(form.extra_info).length > 0" class="text-sm text-blue-600">
                {{ Object.keys(form.extra_info).length }} data extra info tersedia
              </div>
            </div>
            
            <div class="text-right">
              <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" :disabled="isSubmitting">
                {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </Modal>

        <!-- Modal Audit -->
        <Modal :show="showAuditModal" title="Audit Log" @update:show="showAuditModal = false">
          <div class="max-h-96 overflow-y-auto space-y-2">
            <div v-if="auditLogs.length === 0">Tidak ada audit log.</div>
            <div v-for="(log, index) in auditLogs" :key="index" class="border-b pb-2">
              <p><strong>Event:</strong> {{ log.event }}</p>
              <p><strong>User:</strong> {{ log.user?.name || 'System' }}</p>
              <p><strong>Waktu:</strong> {{ new Date(log.created_at).toLocaleString() }}</p>
              <ul class="ml-4 list-disc text-sm">
                <li v-for="(value, key) in log.new_values" :key="key">
                  {{ key }}:
                  <span v-if="log.old_values[key]" class="line-through text-red-500">{{ log.old_values[key] }}</span>
                  →
                  <span class="text-green-600">{{ value }}</span>
                </li>
              </ul>
            </div>
          </div>
        </Modal>

        <!-- Modal Detail Extra Info -->
        <Modal :show="showExtraInfoDetailsModal" :title="`Extra Info: ${selectedServiceName}`" @update:show="showExtraInfoDetailsModal = false">
          <div class="max-h-96 overflow-y-auto">
            <div v-if="Object.keys(selectedServiceExtraInfo).length === 0" class="text-gray-500">
              Tidak ada data extra info
            </div>
            <table v-else class="w-full text-sm">
              <thead>
                <tr class="bg-gray-100">
                  <th class="py-2 px-4 text-left">Key</th>
                  <th class="py-2 px-4 text-left">Value</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(value, key) in selectedServiceExtraInfo" :key="key" class="border-b">
                  <td class="py-2 px-4 font-semibold">{{ key }}</td>
                  <td class="py-2 px-4">{{ value }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </Modal>
      </div>
    </div>
  </AppLayout>
</template>