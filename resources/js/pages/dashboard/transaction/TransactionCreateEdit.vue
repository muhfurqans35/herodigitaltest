<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { v4 as uuid } from 'uuid'
import Button from '@/components/ui/button/Button.vue'

const props = defineProps({
  services: Array,
  transaction: Object,
})

const form = useForm({
  transaction_date: new Date().toISOString().slice(0, 10),
  customer_name: '',
  items: [],
})

const addItem = () => {
  form.items.push({
    temp_id: uuid(),
    service_id: '',
    service: null,
    name: '',
    quantity: 1,
    base_price: 0,
    price_per_unit: 0,
    subtotal: 0,
    selectedFields: {},
    selectedQuantities: {},
    fields: [],
    notes: '',
  })
}

const removeItem = (index) => {
  form.items.splice(index, 1)
}

const onServiceChange = (item) => {
  const service = props.services.find((s) => s.id === item.service_id)
  if (!service) return

  item.service = service
  item.name = service.name
  item.base_price = service.base_price || 0
  item.selectedFields = {}
  item.selectedQuantities = {}
  item.fields = []
  updatePrice(item)
}

const onFieldChange = (item, fieldName, option) => {
  if (!item.selectedQuantities[fieldName]) {
    item.selectedQuantities[fieldName] = 1
  }
  updatePrice(item)
}

const updatePrice = (item) => {
  let extra = 0
  item.fields = []

  for (const fieldName in item.selectedFields) {
    const optionName = item.selectedFields[fieldName]
    const option = item.service?.fields?.[fieldName]?.find((o) => o.name === optionName)

    if (option) {
      const qty = Number(item.selectedQuantities[fieldName]) || 1
      const price = Number(option.price) || 0
      extra += price * qty
      item.fields.push({
        name: option.name,
        price,
        quantity: qty,
      })
    }
  }

  const base = Number(item.base_price) || 0
  const quantity = Number(item.quantity) || 1

  item.price_per_unit = base + extra
  item.subtotal = item.price_per_unit * quantity
}

const submit = () => {
  if (props.transaction) {
    form.put(route('transactions.update', props.transaction.id))
  } else {
    form.post(route('transactions.store'))
  }
}

onMounted(() => {
  if (props.transaction) {
    form.transaction_date = props.transaction.transaction_date.slice(0, 10)
    form.customer_name = props.transaction.customer_name
    form.items = props.transaction.items.map((item) => {
      const selectedFields = {}
      const selectedQuantities = {}
      const service = props.services.find((s) => s.id === item.service_id)

      if (item.fields && Array.isArray(item.fields)) {
        item.fields.forEach((field) => {
          for (const fieldName in service?.fields || {}) {
            const match = service.fields[fieldName].find((f) => f.name === field.name)
            if (match) {
              selectedFields[fieldName] = field.name
              selectedQuantities[fieldName] = field.quantity
              break
            }
          }
        })
      }

      return {
        ...item,
        temp_id: uuid(),
        selectedFields,
        selectedQuantities,
        service,
        name: item.name,
      }
    })

    form.items.forEach(updatePrice)
  } else {
    addItem()
  }
})
</script>

<template>
  <form @submit.prevent="submit">
    <div class="space-y-6 p-4 md:p-6">
      <!-- Customer Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Customer</label>
        <input v-model="form.customer_name"
          class="w-full mt-1 border rounded px-3 py-2 bg-white dark:bg-background  text-sm text-gray-900 dark:text-gray-100" />
      </div>

      <!-- Transaction Date -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tanggal Transaksi</label>
        <input type="date" v-model="form.transaction_date"
          class="w-full mt-1 border rounded px-3 py-2 bg-white dark:bg-background text-sm text-gray-900 dark:text-gray-100" />
      </div>

      <!-- Items Loop -->
      <div v-for="(item, index) in form.items" :key="item.temp_id"
        class="p-4 rounded border bg-gray-50 dark:bg-background  space-y-4">
        <!-- Service & Quantity -->
        <div class="flex flex-col md:flex-row gap-4">
          <div class="md:w-1/2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Layanan</label>
            <select v-model="item.service_id" @change="onServiceChange(item)"
              class="w-full mt-1 border px-2 py-1 rounded bg-white dark:bg-background before:text-sm">
              <option disabled value="">Pilih Layanan</option>
              <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <div class="md:w-1/2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Jumlah</label>
            <input type="number" v-model.number="item.quantity" min="1" @input="updatePrice(item)"
              class="w-full mt-1 border px-2 py-1 rounded bg-white dark:bg-background  text-sm" />
          </div>
        </div>

        <!-- Optional Fields -->
        <div v-if="item.service?.fields">
          <div v-for="(options, fieldName) in item.service.fields" :key="fieldName" class="mt-4 space-y-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ fieldName }}</label>
            <div class="flex flex-col gap-2">
              <div v-for="option in options" :key="option.name" class="flex items-center gap-3">
                <input type="radio" :name="`field-${item.temp_id}-${fieldName}`" :id="`${fieldName}-${option.name}`"
                  :value="option.name" v-model="item.selectedFields[fieldName]"
                  @change="onFieldChange(item, fieldName, option)" />
                <label :for="`${fieldName}-${option.name}`" class="flex-1 text-sm text-gray-800 dark:text-gray-100">
                  {{ option.name }} (Rp{{ option.price.toLocaleString() }})
                </label>
                <input v-if="item.selectedFields[fieldName] === option.name" type="number" min="1"
                  class="w-20 border px-2 py-1 rounded text-sm bg-white dark:bg-background"
                  v-model.number="item.selectedQuantities[fieldName]" @input="updatePrice(item)" />
              </div>
            </div>
          </div>
        </div>

        <!-- Harga dan Hapus -->
        <div class="text-sm text-gray-700 dark:text-gray-200 mt-4">
          Harga Satuan: <strong>Rp{{ item.price_per_unit.toLocaleString() }}</strong><br />
          Subtotal: <strong>Rp{{ item.subtotal.toLocaleString() }}</strong>
        </div>

        <Button type="button" @click="removeItem(index)" class="text-sm text-red-600 hover:underline">
          Hapus Item
        </Button>
      </div>

      <!-- Tambah Item -->
      <Button type="button" @click="addItem">
        + Tambah Item
      </Button>

      <!-- Submit -->
      <div class="text-right mt-6">
        <Button type="submit">
          Simpan Transaksi
        </Button>
      </div>
    </div>
  </form>
</template>
