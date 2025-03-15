<!-- eslint-disable-next-line vue/block-lang -->
<script setup lang="js">
import { usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const isAuthenticated = computed(() => page.props.auth?.user);
</script>

<template>
  <nav class="bg-black border-b border-red-700 p-3 md:p-4 shadow-md">
    <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">

      <h1 class="text-xl md:text-2xl font-bold text-red-600 mb-2 md:mb-0">
        <Link href="/">RENTAL PS</Link>
      </h1>

  
      <div class="flex items-center space-x-2 md:space-x-4">
        <template v-if="isAuthenticated">

          <Link
            href="/bookings"
            class="px-3 py-1 md:px-4 md:py-2 bg-gray-700 rounded-lg text-white font-semibold hover:bg-gray-800 transition text-sm md:text-base"
          >
            List Booking
          </Link>

          <Link
            href="/logout"
            method="post"
            as="button"
            class="px-3 py-1 md:px-4 md:py-2 bg-red-600 rounded-lg text-white font-semibold hover:bg-red-700 transition text-sm md:text-base"
          >
            Logout
          </Link>
        </template>
        <template v-else>
   
          <Link
            href="/login"
            class="px-3 py-1 md:px-4 md:py-2 bg-red-600 rounded-lg text-white font-semibold hover:bg-red-700 transition text-sm md:text-base"
          >
            Login
          </Link>

          <Link
            href="/register"
            class="px-3 py-1 md:px-4 md:py-2 bg-white rounded-lg text-black font-semibold hover:bg-gray-200 transition text-sm md:text-base"
          >
            Register
          </Link>
        </template>
      </div>
    </div>
  </nav>
</template>