<template>
    <transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-lg mx-4 relative">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ title }}</h2>
            <button @click="$emit('update:show', false)" class="text-gray-500 hover:text-gray-700 dark:hover:text-white">
              ✕
            </button>
          </div>
          <div class="px-6 py-4">
            <slot />
          </div>
        </div>
      </div>
    </transition>
  </template>
  
  <script setup lang="ts">
  defineProps<{
    show: boolean
    title: string
  }>()
  
  defineEmits(['update:show'])
  </script>
  
  <style scoped>
  .modal-enter-active,
  .modal-leave-active {
    transition: opacity 0.3s ease;
  }
  .modal-enter-from,
  .modal-leave-to {
    opacity: 0;
  }
  </style>
  