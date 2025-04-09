<script setup lang="ts">
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { DatePicker } from "v-calendar";
import "v-calendar/dist/style.css";
import Navbar from "@/components/Navbar.vue";

const props = defineProps<{
  services: {
    id: number;
    name: string;
    price: number;
  }[];
}>();

interface BookingForm {
  date: string | null;
  service_id: number;
  session: number;
  units: number;
  start_time: string;
  [key: string]: any;
}

const form = useForm<BookingForm>({
  date: null,
  service_id: props.services[0]?.id || 1,
  session: 1,
  units: 1,
  start_time: "06:00",
});

const selectedService = computed(() =>
  props.services.find((s) => s.id === form.service_id)
);

const basePrice = computed(() => selectedService.value?.price || 0);

const isWeekend = computed(() => {
  if (!form.date) return false;
  const day = new Date(form.date).getDay();
  return day === 0 || day === 6;
});

const surcharge = computed(() => (isWeekend.value ? 50000 : 0));

const totalPrice = computed(() =>
  basePrice.value * form.session * form.units + surcharge.value
);

const submit = () => {
  if (!form.date) {
    alert("Silakan pilih tanggal terlebih dahulu");
    return;
  }

  if (form.session < 1 || form.units < 1) {
    alert("Jumlah sesi dan unit harus minimal 1");
    return;
  }

  const selectedDate = new Date(form.date);
  selectedDate.setMinutes(selectedDate.getMinutes() - selectedDate.getTimezoneOffset());
  form.date = selectedDate.toISOString().split("T")[0];

  form.post("/booking", {
    onSuccess: () => {
      alert("Booking berhasil! Silakan lakukan pembayaran.");
    },
    onError: (errors) => {
      alert("Terjadi kesalahan:\n" + Object.values(errors).join("\n"));
    },
  });
};
</script>

<template>
  <div class="min-h-screen bg-black text-white">
    <Navbar />
    <div class="flex justify-center items-center p-6 mt-8">
      <div class="w-full max-w-xl bg-white text-gray-800 p-6 rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold text-center mb-6">Booking Rental PlayStation</h2>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Tanggal -->
          <div>
            <label class="block font-semibold mb-2">Pilih Tanggal:</label>
            <DatePicker
              v-model="form.date"
              :model-config="{ type: 'string', mask: 'YYYY-MM-DD' }"
              is-required
              class="w-full"
            />
            <p v-if="isWeekend" class="text-sm text-gray-500 mt-1">*Biaya tambahan weekend Rp 50.000</p>
          </div>

          <!-- Jam Mulai -->
          <div>
            <label class="block font-semibold mb-2">Jam Mulai:</label>
            <input
              type="time"
              v-model="form.start_time"
              class="w-full p-3 border rounded-lg border-gray-300"
              required
            />
          </div>

          <!-- Konsol -->
          <div>
            <label class="block font-semibold mb-2">Pilih Konsol:</label>
            <select
              v-model.number="form.service_id"
              class="w-full p-3 border rounded-lg border-gray-300"
              required
            >
              <option
                v-for="service in props.services"
                :key="service.id"
                :value="service.id"
              >
                {{ service.name.toUpperCase() }} - Rp {{ service.price.toLocaleString() }}/sesi
              </option>
            </select>
          </div>

          <!-- Jumlah sesi -->
          <div>
            <label class="block font-semibold mb-2">Jumlah Sesi (1 Sesi/1 Jam):</label>
            <input
              type="number"
              min="1"
              v-model.number="form.session"
              class="w-full p-3 border rounded-lg border-gray-300"
              required
            />
          </div>

          <!-- Jumlah unit -->
          <div>
            <label class="block font-semibold mb-2">Jumlah Unit (Konsol):</label>
            <input
              type="number"
              min="1"
              v-model.number="form.units"
              class="w-full p-3 border rounded-lg border-gray-300"
              required
            />
          </div>

          <!-- Rincian harga -->
          <div class="mt-6 p-4 bg-gray-100 rounded-lg text-sm">
            <div class="flex justify-between mb-2">
              <span>Harga dasar:</span>
              <span>Rp {{ basePrice.toLocaleString() }} × {{ form.session }} × {{ form.units }}</span>
            </div>
            <div v-if="isWeekend" class="flex justify-between mb-2">
              <span>Biaya Weekend:</span>
              <span>Rp {{ surcharge.toLocaleString() }}</span>
            </div>
            <div class="flex justify-between font-bold text-lg border-t pt-3 mt-3">
              <span>Total:</span>
              <span>Rp {{ totalPrice.toLocaleString() }}</span>
            </div>
          </div>

          <!-- Tombol submit -->
          <button
            type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg font-bold transition"
          >
            Booking Sekarang
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
