<script setup lang="ts">
import { computed, type PropType } from 'vue';
import DataTableShow from '@/pages/modules/components/DataTableShow.vue';

interface Dokumen {
  id: number;
  jenis_dokumen_id?: number;
  nomor?: string;
  file_url?: string;
}

const props = defineProps<{
  dokumenList: Dokumen[];
  atletId?: number;
  selectedIds?: number[];
}>();

const columns = [
  { key: 'jenis_dokumen_id', label: 'Jenis Dokumen' },
  { key: 'nomor', label: 'Nomor Dokumen' },
  {
    key: 'file_url',
    label: 'File',
    format: (row: Dokumen) =>
      row.file_url
        ? `<a href="${row.file_url}" target="_blank" class="text-blue-600 underline">Lihat File</a>`
        : '<span class="text-muted-foreground">-</span>',
  },
];
</script>

<template>
  <DataTableShow
    :columns="columns"
    :rows="props.dokumenList"
  >
    <template #title>
      <h3 class="font-semibold text-base text-muted-foreground uppercase tracking-wide">
        Informasi Semua Dokumen
      </h3>
    </template>
  </DataTableShow>
</template> 