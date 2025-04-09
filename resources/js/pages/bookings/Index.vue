<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import Navbar from '@/components/Navbar.vue';
import Modal from '@/components/Modal.vue';
import axios from 'axios';
import { ref, reactive, computed } from 'vue';
import { Pencil, Trash2, CreditCard, Star } from 'lucide-vue-next'

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
  id: string
  name: string
}

const props = defineProps<{
  bookings: Booking[]
  services: Service[]
}>()

const showUpdateModal = ref(false);
const selectedBooking = reactive<Booking>({
  id: 0,
  date: '',
  service: {
    id: '',
    name: '',
  },
  session: 0,
  units:1,
  total_price: 0,
  status: '',
  start_time: '',
  end_time: '',
  service_id: 0,
});
const showReviewModal = ref(false);
const selectedBookingId = ref<number | null>(null);

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
    units: selectedBooking.units
  }, {
    onSuccess: () => {
      closeUpdateModal();
      alert('Booking berhasil diperbarui.');
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

const proceedToPayment = async (bookingId: number) => {
  try {
    const response = await axios.get<{ snap_token: string }>(`/booking/payment/${bookingId}`);
    window.location.href = `https://app.sandbox.midtrans.com/snap/v2/vtweb/${response.data.snap_token}`;
  } catch (error) {
    alert('Gagal memproses pembayaran.');
  }
};


const reviewForm = reactive({
  rating: 0,
  comment: '',
});

const openReviewModal = (bookingId: number) => {
  selectedBookingId.value = bookingId;
  reviewForm.rating = 0;
  reviewForm.comment = '';
  showReviewModal.value = true;
};

const selectedBookingData = computed(() =>
  props.bookings.find(b => b.id === selectedBookingId.value) ?? null
);

const submitReview = async () => {
  if (!selectedBookingId.value || reviewForm.rating === 0) {
    alert('Berikan rating terlebih dahulu.');
    return;
  }

  try {
    await axios.post('/reviews', {
      booking_id: selectedBookingId.value,
      rating: reviewForm.rating,
      comment: reviewForm.comment,
    });

    showReviewModal.value = false;
    alert('Review berhasil disimpan.');
  } catch (error) {
    alert('Gagal menyimpan review.');
  }
};

</script>



<template>
  <div class="min-h-screen bg-black text-white">
    <Navbar />
    <div class="p-4 max-w-6xl mx-auto mt-8">
      <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold">Booking Saya</h2>
        <Link href="/bookings/create" class="bg-blue-600 px-4 py-2 rounded-lg hover:bg-blue-700 text-sm sm:text-base">
          Booking Baru
        </Link>
      </div>

      <div class="space-y-4 text-gray-700">
      <div class="hidden sm:grid grid-cols-9 bg-blue-100 text-gray-800 text-sm font-semibold rounded-t-xl px-4 py-2">
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

      <div
        v-for="b in bookings"
        :key="b.id"
        class="grid grid-cols-1 sm:grid-cols-9 gap-2 text-sm px-4 py-3 bg-white rounded-xl shadow-sm sm:items-center"
      >
        <div>
          <span class="sm:hidden font-semibold text-gray-500">ID: </span>{{ b.id }}
        </div>
        <div>
          <span class="sm:hidden font-semibold text-gray-500">Tanggal: </span>{{ b.date }}
        </div>
        <div>
          <span class="sm:hidden font-semibold text-gray-500">Layanan: </span>{{ b.service?.name }}
        </div>
        <div>
          <span class="sm:hidden font-semibold text-gray-500">Sesi: </span>{{ b.session }}
        </div>
        <div>
          <span class="sm:hidden font-semibold text-gray-500">Unit: </span>{{ b.units }}
        </div>
        <div>
          <span class="sm:hidden font-semibold text-gray-500">Waktu: </span>{{ b.start_time }} - {{ b.end_time }}
        </div>
        <div>
          <span class="sm:hidden font-semibold text-gray-500">Total: </span>Rp {{ b.total_price.toLocaleString() }}
        </div>
        <div>
          <span class="sm:hidden font-semibold text-gray-500">Status: </span>
          <span :class="{
            'text-yellow-500': b.status === 'pending',
            'text-yellow-700': b.status === 'processing',
            'text-green-500': b.status === 'paid',
            'text-green-700': b.status === 'finished',
            'text-red-600': b.status === 'canceled',
          }">{{ b.status }}</span>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            v-if="b.status === 'pending'"
            @click="proceedToPayment(b.id)"
            class="text-green-500 hover:bg-green-100 p-2 rounded"
            title="Bayar"
          >
            <CreditCard class="w-4 h-4" />
          </button>
          <button
            v-if="b.status === 'pending'"
            @click="openUpdateModal(b)"
            class="text-blue-500 hover:bg-blue-100 p-2 rounded"
            title="Edit"
          >
            <Pencil class="w-4 h-4" />
          </button>
          <button
            v-if="b.status === 'pending'"
            @click="deleteBooking(b.id)"
            class="text-red-500 hover:bg-red-100 p-2 rounded"
            title="Hapus"
          >
            <Trash2 class="w-4 h-4" />
          </button>
          <button
            v-if="b.status === 'finished'"
            @click="openReviewModal(b.id)"
            class="text-yellow-500 hover:bg-yellow-100 p-2 rounded"
            title="Review"
          >
            <Star class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>


      <!-- Modal Update -->
      <Modal :show="showUpdateModal" title="Update Booking" @update:show="showUpdateModal = false">
        <form @submit.prevent="updateBooking" class="space-y-4 p-2 sm:p-4">
          <div class="grid sm:grid-cols-2 gap-3">
            <input
              v-model="selectedBooking.date"
              type="date"
              required
              class="w-full border p-2 rounded bg-white text-black"
            />
            <input
              v-model="selectedBooking.start_time"
              type="time"
              required
              class="w-full border p-2 rounded bg-white text-black"
            />
          </div>
          <select
            v-model="selectedBooking.service_id"
            required
            class="w-full border p-2 rounded bg-white text-black"
          >
            <option disabled value="">Pilih layanan</option>
            <option
              v-for="service in services"
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
          <div class="text-right">
            <button type="submit" class="bg-blue-600 px-4 py-2 text-white rounded text-sm sm:text-base">
              Simpan
            </button>
          </div>
        </form>
      </Modal>

      <!-- Modal Review -->
      <Modal :show="showReviewModal" title="Beri Review" @update:show="showReviewModal = false">
        <div class="space-y-4 p-2 sm:p-4">
          <div>
            <label class="block font-medium mb-2">Rating:</label>
            <div class="flex space-x-1">
              <span
                v-for="n in 5"
                :key="n"
                @click="reviewForm.rating = n"
                class="cursor-pointer text-2xl"
                :class="n <= reviewForm.rating ? 'text-yellow-500' : 'text-gray-300'"
              >★</span>
            </div>
          </div>
          <div>
            <label class="block font-medium mb-2">Komentar:</label>
            <textarea
              v-model="reviewForm.comment"
              rows="3"
              class="w-full border rounded p-2 bg-white text-black"
            ></textarea>
          </div>
          <div class="text-right">
            <button @click="submitReview" class="bg-blue-600 px-4 py-2 text-white rounded text-sm sm:text-base">
              Kirim
            </button>
          </div>
        </div>
      </Modal>
    </div>
  </div>
</template>

