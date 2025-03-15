<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Navbar from '@/components/Navbar.vue';

</script>

<template>
  <div class="min-h-screen bg-black text-white">
  
    <Navbar/>

    <!-- Hero Section -->
    <section class="text-center py-20 bg-gradient-to-b from-black to-gray-900">
      <h2 class="text-5xl font-extrabold mb-4 text-white">
        Mainkan Game Favoritmu <span class="text-red-600">dengan Mudah</span>
      </h2>
      <p class="text-gray-300 text-lg mb-10">
        Sewa PlayStation 4 & 5 dengan harga terbaik dan pembayaran mudah.
      </p>
      <Link href="/bookings/create" class="m-6 px-8 py-3 bg-red-600 rounded-lg text-white text-lg font-semibold hover:bg-red-700 transition shadow-lg">
        Mulai Booking
      </Link>
      <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
        <div class="bg-gray-900 p-6 rounded-lg border-l-4 border-red-600">
          <h3 class="text-xl font-bold mb-2">Harga Terjangkau</h3>
          <p class="text-gray-400">Nikmati game premium tanpa harus membeli konsol mahal.</p>
        </div>
        <div class="bg-gray-900 p-6 rounded-lg border-l-4 border-red-600">
          <h3 class="text-xl font-bold mb-2">Konsol Terbaru</h3>
          <p class="text-gray-400">PS4 dan PS5 dengan kondisi prima dan terawat.</p>
        </div>
        <div class="bg-gray-900 p-6 rounded-lg border-l-4 border-red-600">
          <h3 class="text-xl font-bold mb-2">Pengiriman Cepat</h3>
          <p class="text-gray-400">Layanan antar-jemput konsol ke lokasi Anda.</p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="text-center py-6 bg-black border-t border-red-700 text-gray-400">
      <p>&copy; 2025 Sewa PS4 & PS5 | All Rights Reserved</p>
    </footer>
  </div>
</template>