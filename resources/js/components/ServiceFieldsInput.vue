<template>
  <div class="space-y-4">
    <div v-for="(options, category) in localFields" :key="category"
      class="border p-3 rounded-md  bg-gray-50 dark:bg-background">
      <div class="flex flex-wrap justify-between items-center mb-2 gap-2">
        <strong class="text-sm text-gray-700 dark:text-gray-100">{{ category }}</strong>
        <button type="button" @click="removeCategory(category)" class="text-xs text-red-500 hover:underline">Hapus
          Kategori</button>
      </div>

      <div v-for="(option, idx) in options" :key="idx" class="flex flex-wrap gap-2 mb-2">
        <input v-model="localFields[category][idx].name" type="text" placeholder="Nama opsi"
          class="input flex-1 min-w-[180px] bg-gray-50 dark:bg-background border" required />
        <input v-model.number="localFields[category][idx].price" type="number" placeholder="Harga" min="0"
          class="input w-24 md:w-32 bg-gray-50 dark:bg-background border" required />
        <button type="button" @click="removeOption(category, idx)"
          class="text-xs text-red-500 hover:underline flex items-center">Hapus</button>
      </div>

      <button type="button" @click="addOption(category)" class="text-xs text-blue-500 hover:underline">+ Tambah
        Opsi</button>
    </div>

    <!-- Add new category section -->
    <div class="border p-3 rounded-md  bg-gray-50 dark:bg-background">
      <div class="mb-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tambah Kategori Baru</label>
        <input v-model="newCategory" type="text" placeholder="Nama Kategori"
          class="input w-full mt-1 bg-gray-50 dark:bg-background border" />
      </div>
      <Button type="button" @click="addCategory"
        class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700" :disabled="!newCategory.trim()">
        Tambah Kategori
      </Button>
    </div>
  </div>
</template>
  
<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import Button from '@/components/ui/button/Button.vue'

type FieldOption = { name: string; price: number }

const props = defineProps<{
  modelValue: Record<string, FieldOption[]>
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: Record<string, FieldOption[]>): void
}>()

const localFields = ref<Record<string, FieldOption[]>>({})
const newCategory = ref('')

// Initialize local fields from props
onMounted(() => {
  if (props.modelValue) {
    localFields.value = JSON.parse(JSON.stringify(props.modelValue))
  }
})

// Watch for changes in props
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue && Object.keys(newValue).length > 0) {
      localFields.value = JSON.parse(JSON.stringify(newValue))
    } else {
      localFields.value = {}
    }
  },
  { deep: true }
)

// Watch for changes in local fields
watch(
  localFields,
  (newValue) => {
    console.log('Fields updated:', JSON.stringify(newValue))
    emit('update:modelValue', JSON.parse(JSON.stringify(newValue)))
  },
  { deep: true }
)

function addCategory() {
  const category = newCategory.value.trim()
  if (category && !localFields.value[category]) {
    localFields.value = {
      ...localFields.value,
      [category]: [{ name: '', price: 0 }]
    }
    newCategory.value = ''
  }
}

function removeCategory(category: string) {
  const updatedFields = { ...localFields.value }
  delete updatedFields[category]
  localFields.value = updatedFields
}

function addOption(category: string) {
  if (!localFields.value[category]) {
    localFields.value[category] = []
  }

  localFields.value = {
    ...localFields.value,
    [category]: [...localFields.value[category], { name: '', price: 0 }]
  }
}

function removeOption(category: string, index: number) {
  if (localFields.value[category]) {
    const updatedOptions = [...localFields.value[category]]
    updatedOptions.splice(index, 1)

    if (updatedOptions.length === 0) {
      removeCategory(category)
    } else {
      localFields.value = {
        ...localFields.value,
        [category]: updatedOptions
      }
    }
  }
}
</script>
  