<script setup lang="ts">
defineProps<{
  badges: Array<{
    label: string,
    value: number | string,
    colorClass: string,
    onClick?: () => void
  }>
}>();
</script>

<template>
  <div class="flex flex-col gap-1 items-start">
    <button
      v-for="(badge, i) in badges"
      :key="i"
      type="button"
      :class="`w-fit inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full cursor-pointer ${badge.colorClass}`"
      @click="badge.onClick"
    >
      <span class="font-semibold mr-1">{{ badge.value }}</span> {{ badge.label }}
    </button>
  </div>
</template> 