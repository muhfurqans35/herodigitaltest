<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
];

const props = defineProps<{
  stats: {
    bookings: number;
    paidBookings: number;
    services: number;
    users: number;
    reviews: number;
    
  };
  services: {
    id: number;
    name: string;
    total_units: number;
    bookings_count: number;
    booked_units: number
  }[];
}>();
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <div class="p-4">
            <h3 class="text-xl font-semibold">Total Booking</h3>
            <p class="text-3xl font-bold">{{ props.stats.bookings }}</p>
          </div>
          <PlaceholderPattern />
        </div>

        <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <div class="p-4">
            <h3 class="text-xl font-semibold">Paid Booking</h3>
            <p class="text-3xl font-bold">{{ props.stats.paidBookings }}</p>
          </div>
          <PlaceholderPattern />
        </div>

        <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <div class="p-4">
            <h3 class="text-xl font-semibold">Total Services</h3>
            <p class="text-3xl font-bold">{{ props.stats.services }}</p>
          </div>
          <PlaceholderPattern />
        </div>
      </div>
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mt-4">
        <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <div class="p-4">
            <h3 class="text-xl font-semibold">Total Users</h3>
            <p class="text-3xl font-bold">{{ props.stats.users }}</p>
          </div>
          <PlaceholderPattern />
        </div>

        <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <div class="p-4">
            <h3 class="text-xl font-semibold">Total Reviews</h3>
            <p class="text-3xl font-bold">{{ props.stats.reviews }}</p>
          </div>
          <PlaceholderPattern />
        </div>
      </div>


      <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">Service</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-left bg-white rounded shadow">
            <thead class="bg-gray-100 text-black">
              <tr>
                <th class="p-3">ID</th>
                <th class="p-3">Service Name</th>
                <th class="p-3">Total Units</th>
                <th class="p-3">Total Bookings</th>
              </tr>
            </thead>
            <tbody class="text-black">
              <tr v-for="(service, index) in props.services" :key="service.id" class="border-t">
                <td class="p-3">{{ index + 1 }}</td>
                <td class="p-3">{{ service.name }}</td>
                <td class="p-3">{{ service.total_units }}</td>
                <td class="p-3">{{ service.booked_units }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
