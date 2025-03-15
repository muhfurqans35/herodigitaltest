<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const isAuthenticated = computed(() => page.props.auth);
</script>
<template>
        <!-- Navbar -->
  <nav class="bg-black border-b border-red-700 p-4 shadow-md flex justify-between items-center">
    <h1 class="text-2xl font-bold text-red-600">
  <Link href="/">RENTAL PS</Link>
</h1>
  <div class="flex items-center">
    <template v-if="isAuthenticated">
        <Link href="/bookings" class="px-4 py-2 bg-gray-700 rounded-lg text-white font-semibold hover:bg-gray-800 transition">
          My Bookings
        </Link>
        <Link href="/logout" method="post" as="button" class="ml-2 px-4 py-2 bg-red-600 rounded-lg text-white font-semibold hover:bg-red-700 transition">
          Logout
        </Link>
    </template>
    <template v-else>
      <Link href="/login" class="px-4 py-2 bg-red-600 rounded-lg text-white font-semibold hover:bg-red-700 transition">
        Login
      </Link>
      <Link href="/register" class="ml-2 px-4 py-2 bg-white rounded-lg text-black font-semibold hover:bg-gray-200 transition">
        Register
      </Link>
    </template>
  </div>
</nav>
</template>