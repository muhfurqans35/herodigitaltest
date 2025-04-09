<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';  
import { type BreadcrumbItem } from '@/types';

const form = reactive({
  model: 'users',  
  fields: [] as string[],  
  file: null as File | null,  
});

const availableFields = ref<string[]>([]);  

const fetchFields = async () => {
  try {
    const response = await axios.get(`/dashboard/get-columns?model=${form.model}s`);
    availableFields.value = response.data;
    form.fields = [];  
  } catch (error) {
    console.error('Gagal mengambil kolom:', error);
  }
};


watch(() => form.model, fetchFields);

const handleFileChange = (event: Event) => {
  const fileInput = event.target as HTMLInputElement;
  if (fileInput?.files) {
    form.file = fileInput.files[0];  
  }
};

const submit = async () => {
  const formData = new FormData();
  formData.append('model', form.model);


  form.fields.forEach(field => {
    formData.append('fields[]', field);
  });

  if (form.file) {
    formData.append('file', form.file);  
  }

  try {

    if (form.file) {
      await axios.post('/dashboard/excel/import', formData);  
      alert('Impor berhasil!');
    } else {
      
      await axios.post('/dashboard/excel/export', formData);  
      alert('Ekspor berhasil!');
    }
  } catch (error) {
    alert('Terjadi kesalahan saat memproses data');
    console.error(error); 
  }
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Excel Import/Export', href: '/dashboard/excel' },
];
</script>

<template>
  <Head title="Excel Import/Export" />
  <AppLayout :breadcrumbs="breadcrumbs">

    <div class="min-h-screen p-4 sm:p-6 lg:p-8">
      <div class="max-w-7xl mx-auto">
        <div class="bg-white shadow rounded-xl p-6">

          <form @submit.prevent="submit" enctype="multipart/form-data">
            <!-- Model Selection -->
            <div class="mb-4">
              <label for="model" class="block text-sm font-medium text-black">Pilih Model</label>
              <select v-model="form.model" @change="fetchFields" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-black">
                <option value="user">User</option>
                <option value="service">Service</option>
                <option value="booking">Booking</option>
                <option value="review">Review</option>
              </select>
            </div>

            <!-- Field Selection -->
            <div class="mb-4">
              <label for="fields" class="block text-sm font-medium text-black">Pilih Kolom</label>
              <div class="mt-2">
                <div v-for="field in availableFields" :key="field" class="flex items-center mb-2">
                  <input
                    type="checkbox"
                    :id="field"
                    :value="field"
                    v-model="form.fields"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                  <label :for="field" class="ml-2 text-sm text-black">{{ field }}</label>
                </div>
              </div>
            </div>

            <!-- File Input -->
            <div class="mb-4">
              <label for="file" class="block text-sm font-medium text-gray-700">Pilih File Excel (Untuk Impor)</label>
              <input type="file" @change="handleFileChange" class="mt-1 block w-full text-sm border border-gray-300 rounded-md shadow-sm" />
            </div>

            <!-- Dynamic Button Text -->
            <div class="flex gap-4 mt-6">
              <button 
                type="submit" 
                :class="form.file ? 'bg-green-600' : 'bg-blue-600'" 
                class="px-4 py-2 text-white rounded hover:bg-blue-700">
                {{ form.file ? 'Impor' : 'Ekspor' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>


