<script setup lang="ts">
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { DatePicker } from "v-calendar";
import "v-calendar/dist/style.css";
import Navbar from '@/components/Navbar.vue';

interface BookingForm {
  date: string | null;
  service: "ps4" | "ps5";
  session: number;
  [key: string]: any;
}

const form = useForm<BookingForm>({
  date: null,
  service: "ps4",
  session: 1,
});

const basePrice = computed<number>(() => (form.service === "ps4" ? 30000 : 40000));

const isWeekend = computed<boolean>(() => {
  if (!form.date) return false;
  const day = new Date(form.date).getDay();
  return day === 6 || day === 0;
});

const surcharge = computed<number>(() => (isWeekend.value ? 50000 : 0));

const totalPrice = computed<number>(() => basePrice.value * form.session + surcharge.value);

const submit = () => {
  if (!form.date) {
    alert("Silakan pilih tanggal terlebih dahulu");
    return;
  }

  const formattedDate = new Date(form.date).toISOString().split("T")[0];
  form.date = formattedDate;

  form.post("/booking", {
    onSuccess: () => {
      alert("Booking berhasil! Silakan lakukan pembayaran.");
    },
    onError: (errors) => {
      console.error(errors);
      alert("Terjadi kesalahan saat booking: " + JSON.stringify(errors));
    },
  });
};
</script>

<template>
      <Head>
    <title>Buat Booking Baru - Rental PS</title>
  </Head>
  <div class="min-h-screen bg-black text-white flex flex-col">
    <Navbar />
    <div class="flex-grow flex items-center justify-center p-4">
      <div class="w-full max-w-lg bg-white text-gray-600 rounded-xl shadow-lg p-6 md:p-8">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">Booking Rental PlayStation</h2>
        
        <form @submit.prevent="submit" class="space-y-6">
          <div class="form-group">
            <label class="block font-medium mb-2 text-gray-700">Pilih Tanggal:</label>
            <DatePicker v-model="form.date" :model-config="{ type: 'string', mask: 'YYYY-MM-DD' }" />
            <p v-if="isWeekend" class="mt-1 text-gray-500 text-sm">
              *Biaya tambahan weekend Rp 50.000
            </p>
          </div>
          
          <div class="form-group">
            <label class="block font-medium mb-2 text-gray-700">Pilih Konsol:</label>
            <select
              v-model="form.service"
              class="w-full p-3 border rounded-lg bg-white text-gray-700 border-gray-300 focus:ring focus:ring-blue-200 focus:border-blue-500"
            >
              <option value="ps4">PlayStation 4 - Rp 30.000/sesi</option>
              <option value="ps5">PlayStation 5 - Rp 40.000/sesi</option>
            </select>
          </div>
          
          <div class="form-group">
            <label class="block font-medium mb-2 text-gray-700">Jumlah Sesi:</label>
            <input
              type="number"
              v-model.number="form.session"
              min="1"
              class="w-full p-3 border rounded-lg bg-white text-gray-700 border-gray-300 focus:ring focus:ring-blue-200 focus:border-blue-500"
            />
          </div>
          
          <div class="mt-6 p-4 bg-gray-100 rounded-lg transition-colors duration-200">
            <div class="flex justify-between text-sm text-gray-700 mb-2" v-if="form.date">
              <span>Harga {{ form.service.toUpperCase() }}:</span>
              <span>Rp {{ basePrice.toLocaleString() }} × {{ form.session }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-700 mb-2" v-if="isWeekend">
              <span>Biaya Weekend:</span>
              <span>Rp {{ surcharge.toLocaleString() }}</span>
            </div>
            <div class="flex justify-between font-bold text-lg text-gray-800 pt-2 border-t border-gray-300 mt-2">
              <span>Total:</span>
              <span class="text-gray-800">Rp {{ totalPrice.toLocaleString() }}</span>
            </div>
          </div>
          
          <button
            type="submit"
            class="w-full bg-gray-700 hover:bg-gray-800 text-white p-4 rounded-lg font-bold transition-colors duration-200 mt-4"
          >
            Booking Sekarang
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
