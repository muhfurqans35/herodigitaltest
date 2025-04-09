<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Modal from '@/components/Modal.vue';
import { Eye, Trash2, Pencil } from 'lucide-vue-next';
import axios from 'axios';
import { type BreadcrumbItem } from '@/types';

interface Review {
  id: string;
  booking_id: string;
  rating: number;
  comment: string;
  created_at: string;
}

interface AuditLog {
  event: string;
  user?: { name: string };
  created_at: string;
  old_values: Record<string, any>;
  new_values: Record<string, any>;
}

const props = defineProps<{
  reviews: Review[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Reviews',
    href: '/dashboard/reviews',
  },
];

const showAuditModal = ref(false);
const auditLogs = ref<AuditLog[]>([]);
const selectedReviewId = ref<string | null>(null);
const showEditModal = ref(false);
const editReview = ref<Review | null>(null);

const showAuditFor = async (reviewId: string) => {
  try {
    selectedReviewId.value = reviewId;
    const response = await axios.get(`/dashboard/reviews/${reviewId}/audits`);
    auditLogs.value = response.data;
    showAuditModal.value = true;
  } catch (error) {
    alert('Gagal mengambil audit log.');
  }
};

const openEditModal = (review: Review) => {
  editReview.value = { ...review };
  showEditModal.value = true;
};

const deleteReview = (id: string) => {
  if (confirm('Yakin ingin menghapus review ini?')) {
    router.delete(`/dashboard/reviews/${id}`);
  }
};

const submitEdit = () => {
  if (!editReview.value) return;

  router.put(`/dashboard/reviews/${editReview.value.id}`, {
    rating: editReview.value.rating,
    comment: editReview.value.comment,
  }, {
    onSuccess: () => {
      showEditModal.value = false;
      editReview.value = null;
    },
    onError: () => alert('Gagal update review.'),
  });
};
</script>

<template>
  <Head title="Dashboard Reviews" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="max-w-7xl mx-auto">

      <!-- Header (hanya desktop) -->
      <div class="hidden sm:grid grid-cols-5 bg-blue-100 text-gray-700 text-sm font-semibold rounded-t-xl px-4 py-2">
        <div>Booking</div>
        <div>Rating</div>
        <div>Komentar</div>
        <div>Waktu</div>
        <div>Aksi</div>
      </div>

      <!-- Rows -->
      <div class="divide-y text-gray-700">
        <div
          v-for="review in reviews"
          :key="review.id"
          class="grid grid-cols-1 sm:grid-cols-5 gap-2 px-4 py-3 text-sm bg-white sm:items-center"
        >
          <div><span class="sm:hidden text-gray-500">Booking:</span> {{ review.booking_id }}</div>
          <div><span class="sm:hidden text-gray-500">Rating:</span> {{ review.rating }} ⭐</div>
          <div><span class="sm:hidden text-gray-500">Komentar:</span> {{ review.comment || '-' }}</div>
          <div><span class="sm:hidden text-gray-500">Waktu:</span> {{ new Date(review.created_at).toLocaleString() }}</div>
          <div class="flex flex-wrap gap-2">
            <button @click="showAuditFor(review.id)" class="bg-purple-500 px-2 py-1 text-white rounded" title="Audit">
              <Eye class="w-5 h-5" />
            </button>
            <button @click="deleteReview(review.id)" class="bg-red-500 px-2 py-1 text-white rounded" title="Delete">
              <Trash2 class="w-5 h-5" />
            </button>
            <button @click="openEditModal(review)" class="bg-yellow-500 px-2 py-1 text-white rounded" title="Edit">
              <Pencil class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>


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

        <Modal :show="showEditModal" title="Edit Review" @update:show="showEditModal = false">
        <form v-if="editReview" @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Rating (1–5)</label>
            <div class="flex space-x-1 text-2xl">
              <span
                v-for="n in 5"
                :key="n"
                @click="editReview.rating = n"
                class="cursor-pointer"
                :class="n <= editReview.rating ? 'text-yellow-500' : 'text-gray-300'"
              >★</span>
            </div>
          </div>

          <div>
            <label for="comment" class="block text-sm font-medium mb-1">Komentar</label>
            <textarea
              id="comment"
              v-model="editReview.comment"
              class="w-full border rounded p-2 bg-white text-black"
              rows="3"
            ></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showEditModal = false" class="bg-gray-300 px-4 py-2 rounded">
              Batal
            </button>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
              Simpan
            </button>
          </div>
        </form>
      </Modal>
      </div>
    </div>
  </AppLayout>
</template>
