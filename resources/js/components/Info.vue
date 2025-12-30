<template>
  <div class="flex flex-col sm:flex-row text-xs sm:text-sm">
    <span class="bg-ring/10 font-semibold px-2 py-1 sm:w-40 w-full">
      {{ label }}:
    </span>
    <span class="px-2 py-1 flex-1 break-words">
      {{ value || '' }}
    </span>
  </div>
</template>

<script setup>
defineProps({
  label: {
    required: true
  },
  value: {
  }
})
</script>
