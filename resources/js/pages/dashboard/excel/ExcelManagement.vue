<template>
    <div class="p-6 max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Excel Import/Export</h1>

        <!-- Select Model -->
        <div class="mb-4">
            <label class="block font-medium mb-1">Pilih Model:</label>
            <select v-model="selectedModel" class="border rounded px-3 py-2 w-full">
                <option v-for="model in models" :key="model" :value="model">{{ model }}</option>
            </select>
        </div>

        <!-- Export Button -->
        <div class="mb-4">
            <button @click="exportModel" :disabled="!selectedModel || loading"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Export
            </button>
        </div>

        <!-- Import Form -->
        <div class="mb-6">
            <label class="block font-medium mb-1">Import File Excel:</label>
            <input type="file" @change="onFileChange" accept=".xlsx,.xls,.csv" />
            <button @click="importModel" :disabled="!file || !selectedModel || loading"
                class="ml-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Import
            </button>
        </div>

        <!-- Exported Files (simulasi) -->
        <div class="mt-6">
            <h2 class="font-semibold text-lg mb-2">File Export (sementara)</h2>
            <ul class="list-disc ml-5">
                <li v-for="file in exportedFiles" :key="file">
                    <a :href="`/excel/download/${file}`" target="_blank" class="text-blue-600 underline">
                        {{ file }}
                    </a>
                </li>
            </ul>
        </div>

        <!-- Loader -->
        <div v-if="loading" class="mt-4 text-gray-600">Loading...</div>
    </div>
</template>
  
<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const models = ref([])
const selectedModel = ref('')
const file = ref(null)
const loading = ref(false)
const exportedFiles = ref([])

const getAvailableModels = async () => {
    const { data } = await axios.get('/excel/models')
    models.value = data
}

const exportModel = async () => {
    loading.value = true
    try {
        const { data } = await axios.post('/excel/export', { model: selectedModel.value })
        exportedFiles.value.unshift(data.file) // Simpan nama file untuk simulasi
        alert('Export sedang diproses, silakan cek link setelah beberapa saat.')
    } catch (e) {
        alert('Gagal export: ' + e.message)
    } finally {
        loading.value = false
    }
}

const onFileChange = (e) => {
    file.value = e.target.files[0]
}

const importModel = async () => {
    if (!file.value) return
    loading.value = true
    const formData = new FormData()
    formData.append('file', file.value)
    formData.append('model', selectedModel.value)

    try {
        await axios.post('/excel/import', formData)
        alert('Import sedang diproses di background.')
    } catch (e) {
        alert('Gagal import: ' + e.message)
    } finally {
        loading.value = false
        file.value = null
    }
}

onMounted(() => {
    getAvailableModels()
})
</script>
  