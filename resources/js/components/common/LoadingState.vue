<template>
  <div class="flex flex-col items-center justify-center space-y-4" :class="padding">
    <div class="border-4 border-primary border-t-transparent rounded-full animate-spin" :class="spinnerSize"></div>
    <p class="text-slate-500 font-medium">Đang tải...</p>
  </div>
</template>

<script setup>
defineProps({
  padding: {
    type: String,
    default: 'py-20'
  },
  spinnerSize: {
    type: String,
    default: 'w-12 h-12'
  }
});
</script>
