<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ kesehatan: any }>();

const fields = computed(() => {
  const k = props.kesehatan || {};
  return [
    { label: 'Tinggi Badan', value: k.tinggi_badan ? `${k.tinggi_badan} cm` : '-' },
    { label: 'Berat Badan', value: k.berat_badan ? `${k.berat_badan} kg` : '-' },
    { label: 'Penglihatan', value: k.penglihatan || '-' },
    { label: 'Pendengaran', value: k.pendengaran || '-' },
    { label: 'Riwayat Penyakit', value: k.riwayat_penyakit || '-', className: 'sm:col-span-2' },
    { label: 'Alergi', value: k.alergi || '-', className: 'sm:col-span-2' },
  ];
});
</script>

<template>
  <div class="space-y-4 -mt-6">
    <div>
      <h3 class="font-semibold text-base mb-4 text-muted-foreground uppercase tracking-wide">Informasi Kesehatan</h3>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div v-for="field in fields" :key="field.label" :class="field.className">
          <div class="text-muted-foreground text-xs">{{ field.label }}</div>
          <div class="text-foreground text-sm font-semibold">{{ field.value }}</div>
        </div>
      </div>
    </div>
  </div>
</template> 