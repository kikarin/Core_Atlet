<script setup lang="ts">
import DataTableShow from '@/pages/modules/components/DataTableShow.vue';

interface Prestasi {
  id: number;
  nama_event: string;
  tingkat_id?: number;
  tanggal?: string;
  peringkat?: string;
  keterangan?: string;
}

const props = defineProps<{
  prestasiList: Prestasi[];
  atletId?: number;
}>();

const columns = [
  { key: 'nama_event', label: 'Nama Event' },
  { key: 'tingkat_id', label: 'Tingkat' },
  {
    key: 'tanggal',
    label: 'Tanggal',
    format: (row: Prestasi) =>
      row.tanggal
        ? new Date(row.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'numeric', year: 'numeric' })
        : '-',
  },
  { key: 'peringkat', label: 'Peringkat' },
  { key: 'keterangan', label: 'Keterangan' },
];
</script>

<template>
  <DataTableShow
    :columns="columns"
    :rows="props.prestasiList"
  >
    <template #title>
      <h3 class="font-semibold text-base text-muted-foreground uppercase tracking-wide">
        Informasi Semua Prestasi
      </h3>
    </template>
  </DataTableShow>
</template> 