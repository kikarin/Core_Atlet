<script setup lang="ts">
import { ref, watch } from 'vue';
import DataTableShow from '@/pages/modules/components/DataTableShow.vue';

interface Sertifikat {
  id: number;
  nama_sertifikat: string;
  penyelenggara?: string;
  tanggal_terbit?: string;
  file_url?: string;
}

const props = defineProps<{
  sertifikatList: Sertifikat[];
  atletId?: number;
  selectedIds?: number[];
  onEditSertifikat?: (sertifikat: Sertifikat) => void;
  onDeleteSertifikat?: (sertifikat: Sertifikat) => void;
  onDeleteSelectedSertifikat?: (ids: number[]) => void;
}>();
const emit = defineEmits(['edit', 'delete', 'deleteSelected', 'update:selected', 'showCreator']);

const selected = ref<number[]>(props.selectedIds ? [...props.selectedIds] : []);

watch(() => props.selectedIds, (val) => {
  if (val && JSON.stringify(val) !== JSON.stringify(selected.value)) {
    selected.value = [...val];
  }
});

const columns = [
  { key: 'nama_sertifikat', label: 'Nama Sertifikat' },
  { key: 'penyelenggara', label: 'Penyelenggara' },
  {
    key: 'tanggal_terbit',
    label: 'Tanggal Terbit',
    format: (row: Sertifikat) =>
      row.tanggal_terbit
        ? new Date(row.tanggal_terbit).toLocaleDateString('id-ID', { day: 'numeric', month: 'numeric', year: 'numeric' })
        : '-',
  },
  {
    key: 'file_url',
    label: 'File',
    format: (row: Sertifikat) =>
      row.file_url
        ? `<a href="${row.file_url}" target="_blank" class="text-blue-600 underline">Lihat File</a>`
        : '<span class="text-muted-foreground">-</span>',
  },
];

const actions = (row: Sertifikat) => [
  {
    label: 'Edit',
    onClick: () => emit('edit', row),
  },
  {
    label: 'Delete',
    onClick: () => emit('delete', row),
  },
  {
    label: 'Lihat Pembuat',
    onClick: () => emit('showCreator', row),
  },
];

function handleDeleteSelected(ids: number[]) {
  if (props.onDeleteSelectedSertifikat) {
    props.onDeleteSelectedSertifikat(ids);
  } else {
    emit('deleteSelected', ids);
  }
}
</script>

<template>
  <DataTableShow
    :columns="columns"
    :rows="props.sertifikatList"
    :selected="selected"
    :actions="actions"
    @update:selected="val => emit('update:selected', val)"
    @edit="sertifikat => props.onEditSertifikat ? props.onEditSertifikat(sertifikat) : emit('edit', sertifikat)"
    @delete="sertifikat => props.onDeleteSertifikat ? props.onDeleteSertifikat(sertifikat) : emit('delete', sertifikat)"
    @deleteSelected="handleDeleteSelected"
  >
    <template #title>
      <h3 class="font-semibold text-base text-muted-foreground uppercase tracking-wide">
        Informasi Semua Sertifikat
        <span class="block mt-1 text-xs font-normal text-muted-foreground">
          (Shortcut: <kbd class="px-1 py-0.5 bg-muted rounded">Ctrl+A</kbd> / <kbd class="px-1 py-0.5 bg-muted rounded">Cmd+A</kbd> untuk Select All)
        </span>
      </h3>
    </template>
  </DataTableShow>
</template> 