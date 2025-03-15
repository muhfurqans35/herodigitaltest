<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import Navbar from '@/components/Navbar.vue';
import axios from 'axios';

interface Booking {
  id: number;
  date: string;
  service: string;
  session: number;
  total_price: number;
  status: string;
}

defineProps<{ bookings: Booking[] }>();

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
  <Navbar />
  <div class="p-4 md:p-6 max-w-3xl mx-auto bg-white rounded-xl shadow-md m-4 md:m-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6">
      <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-0">Daftar Booking</h2>
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
</template>