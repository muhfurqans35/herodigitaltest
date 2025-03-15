<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { DatePicker } from "v-calendar";
import "v-calendar/dist/style.css";

const form = useForm({
  date: null,
  service: "ps4",
  session: 1,
});

const basePrice = computed(() => (form.service === "ps4" ? 30000 : 40000));
const isWeekend = computed(() => {
  if (!form.date) return false;
  const day = new Date(form.date).getDay();
  return day === 6 || day === 0;
});
const surcharge = computed(() => (isWeekend.value ? 50000 : 0));
const totalPrice = computed(() => basePrice.value * form.session + surcharge.value);

const submit = () => {
  if (!form.date) {
    alert("Silakan pilih tanggal terlebih dahulu");
    return;
  }

  const formattedDate = new Date(form.date).toISOString().split('T')[0];
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
  <div class="p-8 max-w-lg mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg transition-colors duration-200">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800 dark:text-gray-100">Booking Rental PlayStation</h2>
    
    <form @submit.prevent="submit" class="space-y-6">
      <div class="form-group">
        <label class="block font-medium mb-2 text-gray-700 dark:text-gray-200">Pilih Tanggal:</label>
        <DatePicker v-model="form.date" :model-config="{ type: 'string', mask: 'YYYY-MM-DD' }" />
        <p v-if="isWeekend" class="mt-1 text-amber-600 dark:text-amber-400 text-sm">
          *Biaya tambahan weekend Rp 50.000
        </p>
      </div>
      
      <div class="form-group">
        <label class="block font-medium mb-2 text-gray-700 dark:text-gray-200">Pilih Konsol:</label>
        <select
          v-model="form.service"
          class="w-full p-3 border rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring focus:ring-blue-200 focus:border-blue-500"
        >
          <option value="ps4">PlayStation 4 - Rp 30.000/sesi</option>
          <option value="ps5">PlayStation 5 - Rp 40.000/sesi</option>
        </select>
      </div>
      
      <div class="form-group">
        <label class="block font-medium mb-2 text-gray-700 dark:text-gray-200">Jumlah Sesi:</label>
        <input
          type="number"
          v-model.number="form.session"
          min="1"
          class="w-full p-3 border rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring focus:ring-blue-200 focus:border-blue-500"
        />
      </div>
      
      <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg transition-colors duration-200">
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300 mb-2" v-if="form.date">
          <span>Harga {{ form.service.toUpperCase() }}:</span>
          <span>Rp {{ basePrice.toLocaleString() }} × {{ form.session }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300 mb-2" v-if="isWeekend">
          <span>Biaya Weekend:</span>
          <span>Rp {{ surcharge.toLocaleString() }}</span>
        </div>
        <div class="flex justify-between font-bold text-lg text-gray-800 dark:text-gray-100 pt-2 border-t dark:border-gray-600 mt-2">
          <span>Total:</span>
          <span class="text-blue-600 dark:text-blue-400">Rp {{ totalPrice.toLocaleString() }}</span>
        </div>
      </div>
      
      <button
        type="submit"
        class="w-full bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg font-bold transition-colors duration-200 mt-4 dark:bg-green-600 dark:hover:bg-green-700"
      >
        Booking Sekarang
      </button>
    </form>
  </div>
</template>