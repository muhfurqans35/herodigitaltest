<script setup lang="ts"> 
import { ref, reactive, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { type BreadcrumbItem } from '@/types';
import Modal from '@/components/Modal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Pencil, Trash2, Eye, BadgeCheck, Plus, DownloadCloud } from 'lucide-vue-next';

interface Booking {
  id: number;
  date: string;
  service: Service;
  session: number;
  units: number;
  total_price: number;
  status: string;
  start_time: string; 
  end_time: string; 
  service_id: number;
}

interface Service {
  id: string;
  name: string;
}

interface AuditLog {
  event: string;
  user?: { name: string };
  created_at: string;
  old_values: Record<string, any>;
  new_values: Record<string, any>;
}

const props = defineProps<{
  bookings: Booking[];
  services: Service[];
}>()

const showUpdateModal = ref(false);
const showStatusModal = ref(false);
const showAuditModal = ref(false);
const auditLogs = ref<AuditLog[]>([]);
const selectedBooking = reactive<Booking>({
  id: 0,
  date: '',
  service: { id: '', name: '' },
  session: 1,
  units: 1,
  total_price: 0,
  status: '',
  start_time: '',
  end_time: '',
  service_id: 0,
});

const selectedStatus = ref('pending');
const searchQuery = ref('');
const selectedFilterStatus = ref('');  

const resetFilter = () => {
  searchQuery.value = ''
  selectedFilterStatus.value = ''
}


const filteredBookings = computed(() => {
  return props.bookings.filter(b => {
    const matchesStatus = selectedFilterStatus.value ? b.status === selectedFilterStatus.value : true;
    const matchesQuery = b.id.toString().includes(searchQuery.value) || b.service?.name.toLowerCase().includes(searchQuery.value.toLowerCase());
    return matchesStatus && matchesQuery;
  });
});

const openUpdateModal = (booking: Booking) => {
  Object.assign(selectedBooking, booking);
  showUpdateModal.value = true;
};

const closeUpdateModal = () => {
  showUpdateModal.value = false;
};

const updateBooking = () => {
  router.put(`/bookings/${selectedBooking.id}`, {
    date: selectedBooking.date,
    service_id: selectedBooking.service_id,
    session: selectedBooking.session,
    start_time: selectedBooking.start_time,
    units: selectedBooking.units,
  }, {
    onSuccess: () => {
      closeUpdateModal();
      alert('Booking berhasil diperbarui.');
    },
  });
};

const openStatusModal = (booking: Booking) => {
  selectedBooking.id = booking.id;
  selectedStatus.value = booking.status;
  showStatusModal.value = true;
};

const closeStatusModal = () => {
  showStatusModal.value = false;
};

const updateStatus = () => {
  router.patch(`/dashboard/bookings/${selectedBooking.id}/status`, {
    status: selectedStatus.value
  }, {
    onSuccess: () => {
      closeStatusModal();
      alert('Status berhasil diperbarui.');
    },
    onError: () => {
      alert('Gagal memperbarui status.');
    },
  });
};

const deleteBooking = (id: number) => {
  if (confirm('Hapus booking ini?')) {
    router.delete(`/bookings/${id}`, {
      onSuccess: () => alert('Booking dihapus.'),
    });
  }
};

const showAuditFor = async (bookingId: number) => {
  const response = await axios.get(`/dashboard/bookings/${bookingId}/audits`);
  auditLogs.value = response.data;
  showAuditModal.value = true;
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Bookings', href: '/dashboard/bookings' },
];
</script>

<template>
  <Head title="Dashboard Bookings" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen">
      <div class="p-4 max-w-6xl mx-auto mt-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
          <Link href="/bookings/create" class="bg-blue-600 px-4 py-2 rounded-lg text-white hover:bg-blue-700">
            <Plus class="w-4 h-4" />
          </Link>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4 mb-4 w-full text-gray-700">
          

          <div class="w-full sm:flex-1">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari booking..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200"
            />
          </div>

          <div class="w-full sm:w-48">
            <select
              v-model="selectedFilterStatus"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200"
            >
              <option value="">Semua Status</option>
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <div class="w-full sm:w-auto">
            <button
              @click="resetFilter"
              class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg shadow"
            >
              Reset
            </button>
          </div>
        </div>
    </div>

        <div class="hidden sm:grid grid-cols-9 bg-blue-100 text-gray-700 text-sm font-semibold rounded-t-xl px-4 py-2 ">
          <div>ID</div>
          <div>Tanggal</div>
          <div>Layanan</div>
          <div>Sesi</div>
          <div>Unit</div>
          <div>Waktu</div>
          <div>Total</div>
          <div>Status</div>
          <div>Aksi</div>
        </div>

        <div class="divide-y text-gray-700">
          <div
            v-for="b in filteredBookings"
            :key="b.id"
            class="grid grid-cols-1 sm:grid-cols-9 gap-2 text-sm px-4 py-3 bg-white sm:items-center"
          >
            <div>
              <span class="sm:hidden text-gray-500">ID:</span> {{ b.id }}
            </div>
            <div>
              <span class="sm:hidden text-gray-500">Tanggal:</span> {{ b.date }}
            </div>
            <div>
              <span class="sm:hidden text-gray-500">Layanan:</span> {{ b.service?.name }}
            </div>
            <div>
              <span class="sm:hidden text-gray-500">Sesi:</span> {{ b.session }}
            </div>
            <div>
              <span class="sm:hidden text-gray-500">Unit:</span> {{ b.units }}
            </div>
            <div>
              <span class="sm:hidden text-gray-500">Waktu:</span> {{ b.start_time }} - {{ b.end_time }}
            </div>
            <div>
              <span class="sm:hidden text-gray-500">Total:</span> Rp {{ b.total_price.toLocaleString() }}
            </div>
            <div>
              <span class="sm:hidden text-gray-500">Status:</span>
              <span :class="{
                'text-yellow-500': b.status === 'pending',
                'text-yellow-700': b.status === 'processing',
                'text-green-500': b.status === 'paid',
                'text-green-700': b.status === 'finished',
                'text-red-600': b.status === 'canceled',
              }">{{ b.status }}</span>
            </div>
            <div class="flex gap-1 flex-wrap">
              <button @click="openUpdateModal(b)" class="bg-blue-500 px-2 py-1 text-white rounded" title="Update">
                <Pencil class="w-4 h-4" />
              </button>
              <button @click="openStatusModal(b)" class="bg-teal-500 px-2 py-1 text-white rounded" title="Status">
                <BadgeCheck class="w-4 h-4" />
              </button>
              <button @click="deleteBooking(b.id)" class="bg-red-500 px-2 py-1 text-white rounded" title="Delete">
                <Trash2 class="w-4 h-4" />
              </button>
              <button @click="showAuditFor(b.id)" class="bg-purple-500 px-2 py-1 text-white rounded" title="Audit">
                <Eye class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <Modal :show="showUpdateModal" title="Update Booking" @update:show="showUpdateModal = false">
          <form @submit.prevent="updateBooking" class="space-y-3 text-sm">
            <input
              v-model="selectedBooking.date"
              type="date"
              required
              class="w-full border p-2 rounded bg-white text-black"
            />
            <select
              v-model="selectedBooking.service_id"
              required
              class="w-full border p-2 rounded bg-white text-black"
            >
              <option disabled value="">Pilih layanan</option>
              <option
                v-for="service in props.services"
                :key="service.id"
                :value="service.id"
                class="bg-white text-black"
              >
                {{ service.name }}
              </option>
            </select>

            <input
              v-model="selectedBooking.session"
              type="number"
              min="1"
              required
              class="w-full border p-2 rounded bg-white text-black"
            />
            <input
              v-model="selectedBooking.start_time"
              type="time"
              required
              class="w-full border p-2 rounded bg-white text-black"
            />
            <div class="text-right">
              <button type="submit" class="bg-blue-600 px-4 py-2 text-white rounded hover:bg-blue-700">
                Simpan
              </button>
            </div>
          </form>
        </Modal>
        <Modal :show="showStatusModal" title="Ubah Status Booking" @update:show="showStatusModal = false">
          <form @submit.prevent="updateStatus" class="space-y-3 text-sm">
            <select
              v-model="selectedStatus"
              required
              class="w-full border p-2 rounded bg-white text-black"
            >
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="processing">Processing</option>
              <option value="finished">Finished</option>
              <option value="canceled">Canceled</option>
            </select>
            <div class="text-right">
              <button type="submit" class="bg-teal-600 px-4 py-2 text-white rounded hover:bg-teal-700">
                Simpan Status
              </button>
            </div>
          </form>
        </Modal>
        <Modal :show="showAuditModal" title="Audit Log Review" @update:show="showAuditModal = false">
          <div class="space-y-4 max-h-[60vh] overflow-y-auto">
            <div v-if="auditLogs.length === 0">Tidak ada audit log.</div>
            <div v-for="(log, index) in auditLogs" :key="index" class="border-b pb-2">
              <p><strong>Event:</strong> {{ log.event }}</p>
              <p><strong>User:</strong> {{ log.user?.name ?? 'System' }}</p>
              <p><strong>Waktu:</strong> {{ new Date(log.created_at).toLocaleString() }}</p>
              <div v-if="log.old_values && log.new_values">
                <p class="mt-1"><strong>Perubahan:</strong></p>
                <ul class="ml-4 list-disc text-sm">
                  <li v-for="(value, key) in log.new_values" :key="key">
                    <span class="text-gray-700">{{ key }}:</span>
                    <span v-if="log.old_values[key] !== undefined" class="text-red-500 line-through">
                      {{ log.old_values[key] }}
                    </span>
                    →
                    <span class="text-green-600">{{ value }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </Modal>
      </div>
    </div>
  </AppLayout>
</template>
