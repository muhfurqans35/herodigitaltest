<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import Navbar from '@/components/Navbar.vue';
import axios from 'axios';
import { ref, reactive } from 'vue';

interface Booking {
  id: number;
  date: string;
  service: string;
  session: number;
  total_price: number;
  status: string;
}

const props = defineProps<{ bookings: Booking[] }>();

// For update modal
const showUpdateModal = ref(false);
const selectedBooking = reactive({
  id: 0,
  date: '',
  service: '',
  session: 0,
  total_price: 0,
  status: ''
});

const openUpdateModal = (booking: Booking) => {
  selectedBooking.id = booking.id;
  selectedBooking.date = booking.date;
  selectedBooking.service = booking.service;
  selectedBooking.session = booking.session;
  selectedBooking.total_price = booking.total_price;
  selectedBooking.status = booking.status;
  showUpdateModal.value = true;
};

const closeUpdateModal = () => {
  showUpdateModal.value = false;
};

const updateBooking = () => {
  router.put(`/bookings/${selectedBooking.id}`, {
    date: selectedBooking.date,
    service: selectedBooking.service,
    session: selectedBooking.session,
  }, {
    onSuccess: () => {
      closeUpdateModal();
      alert('Booking berhasil diperbarui.');
    },
    onError: (errors) => {
      alert('Terjadi kesalahan saat memperbarui booking: ' + JSON.stringify(errors));
    },
  });
};

const deleteBooking = (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus booking ini?')) {
    router.delete(`/bookings/${id}`, {
      onSuccess: () => {
        alert('Booking berhasil dihapus.');
      },
      onError: (errors) => {
        alert('Terjadi kesalahan saat menghapus booking: ' + JSON.stringify(errors));
      },
    });
  }
};

const proceedToPayment = async (bookingId: number) => {
  try {
    const response = await axios.get<{ snap_token: string }>(`/booking/payment/${bookingId}`);
    const snapToken = response.data.snap_token;

    window.location.href = `https://app.sandbox.midtrans.com/snap/v2/vtweb/${snapToken}`;
  } catch (error) {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
  }
};
</script>

<template>
  <div class="min-h-screen bg-black text-white flex flex-col">
    <Navbar />
    <div class="flex-grow flex items-center justify-center p-4">
      <div class="w-full max-w-3xl bg-white rounded-xl shadow-md p-6 md:p-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">List Booking</h2>
          <Link
            href="/bookings/create"
            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-300"
          >
            Buat Booking Baru
          </Link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full border-collapse rounded-lg overflow-hidden shadow-sm">
            <thead>
              <tr class="bg-blue-50">
                <th class="p-2 md:p-4 text-left text-gray-700 font-semibold">Tanggal</th>
                <th class="p-2 md:p-4 text-left text-gray-700 font-semibold">Layanan</th>
                <th class="p-2 md:p-4 text-left text-gray-700 font-semibold">Sesi</th>
                <th class="p-2 md:p-4 text-left text-gray-700 font-semibold">Total Harga</th>
                <th class="p-2 md:p-4 text-left text-gray-700 font-semibold">Status</th>
                <th class="p-2 md:p-4 text-left text-gray-700 font-semibold">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="booking in bookings"
                :key="booking.id"
                class="border-b border-gray-200 hover:bg-gray-50 transition duration-200"
              >
                <td class="p-2 md:p-4 text-gray-700">{{ booking.date }}</td>
                <td class="p-2 md:p-4 text-gray-700 font-medium">{{ booking.service.toUpperCase() }}</td>
                <td class="p-2 md:p-4 text-gray-700">{{ booking.session }}</td>
                <td class="p-2 md:p-4 text-gray-700">Rp {{ booking.total_price.toLocaleString() }}</td>
                <td class="p-2 md:p-4">
                  <span
                    :class="{
                      'text-yellow-600': booking.status === 'pending',
                      'text-green-600': booking.status === 'completed',
                      'text-red-600': booking.status === 'canceled',
                    }"
                    class="font-semibold"
                  >
                    {{ booking.status }}
                  </span>
                </td>
                <td class="p-2 md:p-4 flex space-x-2">
                  <button
                    v-if="booking.status === 'pending'"
                    @click="proceedToPayment(booking.id)"
                    class="bg-green-500 text-white px-2 md:px-3 py-1 rounded-lg hover:bg-green-600 transition duration-300"
                  >
                    Bayar
                  </button>
                  <button
                    v-if="booking.status === 'pending'"
                    @click="openUpdateModal(booking)"
                    class="bg-blue-500 text-white px-2 md:px-3 py-1 rounded-lg hover:bg-blue-600 transition duration-300"
                  >
                    Update
                  </button>
                  <button
                    v-if="booking.status === 'pending'"
                    @click="deleteBooking(booking.id)"
                    class="bg-red-500 text-white px-2 md:px-3 py-1 rounded-lg hover:bg-red-600 transition duration-300"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Update Modal -->
    <div v-if="showUpdateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-gray-800">Update Booking</h3>
          <button @click="closeUpdateModal" class="text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="updateBooking" class="space-y-4">
          <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
            <input
              type="date"
              id="date"
              v-model="selectedBooking.date"
              class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
              required
            />
          </div>
          
          <div>
            <label for="service" class="block text-sm font-medium text-gray-700 mb-1">Layanan</label>
            <select
              id="service"
              v-model="selectedBooking.service"
              class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
              required
            >
              <option value="ps4">PS4</option>
              <option value="ps5">PS5</option>
            </select>
          </div>
          
          <div>
            <label for="session" class="block text-sm font-medium text-gray-700 mb-1">Sesi</label>
            <input
              type="number"
              id="session"
              v-model="selectedBooking.session"
              min="1"
              max="10"
              class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
              required
            />
          </div>
          
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeUpdateModal"
              class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition duration-300"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-300"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>