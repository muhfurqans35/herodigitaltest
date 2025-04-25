<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emitClose">
      <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-background max-h-screen overflow-auto">
        <slot />
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
const props = defineProps<{
  open: boolean
}>()

const emit = defineEmits(['update:open'])

function emitClose() {
  emit('update:open', false)
}
</script>
