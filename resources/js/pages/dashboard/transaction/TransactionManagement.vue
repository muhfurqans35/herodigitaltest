<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import Button from '@/components/ui/button/Button.vue'
import { Pencil, Trash2, Eye, Receipt } from 'lucide-vue-next'
import ReceiptGenerator from '@/components/ReceiptGenerator.vue'
import { Transaction } from '@/types';


// Properti untuk menerima data transaksi
const props = defineProps<{
  transactions: Transaction[]
}>()

// State untuk modal dan transaksi yang dipilih
const showModal = ref(false)
const selectedTransaction = ref<Transaction | null>(null)

// Form untuk menangani penghapusan transaksi
const form = useForm({
  customer_name: '',
  transaction_date: '',
  items: []
})


// Fungsi untuk membuka modal detail transaksi
const openDetailModal = (transaction: Transaction) => {
  selectedTransaction.value = transaction
  showModal.value = true
}

// Fungsi untuk menutup modal detail transaksi
const closeDetailModal = () => {
  showModal.value = false
  selectedTransaction.value = null
}

// Fungsi untuk menghapus transaksi
const deleteTransaction = (id: string) => {
  if (window.confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
    form.delete(route('transactions.destroy', id))
  }
}

const printReceipt = (id: string) => {
  form.get(`/transactions/${id}/receipt`, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Kwitansi berhasil dimuat')
    },
    onError: (errors) => {
      console.error('Gagal mencetak kwitansi:', errors)
    }
  })
}

// Fungsi untuk memformat tanggal menjadi format lokal Indonesia
const formatDate = (date: string | undefined): string => {
  return new Date(date?.toString() ?? "").toLocaleDateString("id-ID")  // Pastikan date adalah string yang valid
}


// Breadcrumb untuk navigasi
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Manajemen Transaksi', href: '/dashboard' },
]
</script>

<template>
  <Head title="Manajemen Transaksi" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen">
      <div class="p-4 max-w-6xl mx-auto">
        <div class="flex items-center md:justify-between justify-center mb-4">
          <Link :href="route('transactions.create')"
            class="inline-flex items-center justify-center gap-2 h-10 px-4 py-2 text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none transition-colors">
          + Tambah Transaksi </Link>
        </div>

        <!-- Header Grid -->
        <div class="hidden sm:grid grid-cols-3 bg-blue-100 text-gray-700 text-sm font-semibold rounded-t-xl px-4 py-2">
          <div class="text-center">Customer</div>
          <div class="text-center">Tanggal</div>
          <div class="text-center">Aksi</div>
        </div>

        <!-- Transaction List -->
        <div class="divide-y text-gray-700">
          <div v-for="transaction in transactions" :key="transaction.id"
            class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm px-4 py-3 bg-white sm:items-center">
            <!-- Customer Name -->
            <div class="text-center">
              <span class="sm:hidden text-gray-500">Customer:</span>
              {{ transaction.customer_name }}
            </div>

            <!-- Transaction Date -->
            <div class="text-center">
              <span class="sm:hidden text-gray-500">Tanggal:</span>
              {{ formatDate(transaction.transaction_date) }}
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-1 flex-wrap">
              <Link :href="route('transactions.edit', transaction.id)"
                class="bg-yellow-500 hover:bg-yellow-600 px-2 py-1 text-white rounded" title="Edit">
              <Pencil class="w-4 h-4" />
              </Link>
              <button @click="deleteTransaction(transaction.id)"
                class="bg-red-600 hover:bg-red-700 px-2 py-1 text-white rounded" title="Hapus">
                <Trash2 class="w-4 h-4" />
              </button>
              <button @click="openDetailModal(transaction)"
                class="bg-blue-600 hover:bg-blue-700 px-2 py-1 text-white rounded" title="Detail">
                <Eye class="w-4 h-4" />
              </button>

              <ReceiptGenerator :transaction="transaction" />
            </div>
          </div>

          <!-- No Transactions -->
          <div v-if="transactions.length === 0" class="text-center text-gray-500 dark:text-gray-400 px-4 py-4">
            Belum ada transaksi.
          </div>
        </div>


        <!-- Modal Detail -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div class="bg-white dark:bg-gray-900 p-4 md:p-6 rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">
              Detail Transaksi
            </h2>

            <div class="space-y-2 text-sm text-gray-800 dark:text-gray-100">
              <div><strong>Customer:</strong> {{ selectedTransaction?.customer_name }}</div>
              <div><strong>Tanggal Transaksi:</strong> {{ formatDate(selectedTransaction?.transaction_date) }}</div>
              <div><strong>Total Harga:</strong> {{ selectedTransaction?.total_price }}</div>

              <div>
                <strong>Layanan:</strong>
                <div v-for="item in selectedTransaction?.items" :key="item.id" class="mt-2">
                  <div>{{ item.name }} ({{ item.quantity }} x Rp{{ item.price_per_unit.toLocaleString() }})</div>
                  <div v-for="field in item.fields" :key="field.name" class="pl-4 text-xs">
                    - {{ field.name }}: Rp{{ field.price.toLocaleString() }} ({{ field.quantity }})
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
              <Button @click="closeDetailModal" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-sm rounded">
                Tutup
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
