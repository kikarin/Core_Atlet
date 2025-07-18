<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const page = usePage();
const programId = page.props.program_id as string;
const rencanaId = page.props.rencana_id as string;
const jenisPeserta = page.props.jenis_peserta as string;
const infoHeader = page.props.infoHeader as any;
const info = computed(() => infoHeader || {});
const infoRencana = page.props.infoRencana as any;

const jenisLabel: Record<string, string> = {
  'atlet': 'Atlet',
  'pelatih': 'Pelatih',
  'tenaga-pendukung': 'Tenaga Pendukung',
};

const breadcrumbs = [
  { title: 'Program Latihan', href: `/program-latihan/${programId}` },
  { title: 'Rencana Latihan', href: `/program-latihan/${programId}/rencana-latihan` },
  { title: `Peserta (${jenisLabel[jenisPeserta] || jenisPeserta})`, href: '#' },
];

const columns = [
  { key: 'nama', label: 'Nama' },
  {
    key: 'foto',
    label: 'Foto',
    format: (row: any) => {
      if (row.foto) {
        return `<div class='cursor-pointer' onclick="window.open('${row.foto}', '_blank')">
          <img src='${row.foto}' alt='Foto ${row.nama}' class='w-12 h-12 object-cover rounded-full border hover:shadow-md transition-shadow' />
        </div>`;
      }
      return '<div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs">No</div>';
    },
  },
  { key: 'posisi_atlet_nama', label: 'Posisi', format: (row: any) => row.posisi_atlet_nama || '-' },
  {
    key: 'jenis_kelamin',
    label: 'Jenis Kelamin',
    format: (row: any) => row.jenis_kelamin === 'L' ? 'Laki-laki' : row.jenis_kelamin === 'P' ? 'Perempuan' : '-',
  },
  { key: 'tempat_lahir', label: 'Tempat Lahir' },
  {
    key: 'tanggal_lahir',
    label: 'Tanggal Lahir',
    format: (row: any) => row.tanggal_lahir ? new Date(row.tanggal_lahir).toLocaleDateString('id-ID', { day: 'numeric', month: 'numeric', year: 'numeric' }) : '-',
  },
  { key: 'no_hp', label: 'No HP' },
  {
    key: 'kategori_is_active',
    label: 'Status',
    format: (row: any) => row.kategori_is_active == 1
      ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>'
      : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Nonaktif</span>',
  },
];

const selected = ref<number[]>([]);
const pageIndex = ref();
const { toast } = useToast();

const actions = (row: any) => [
  {
    label: 'Delete',
    onClick: () => pageIndex.value.handleDeleteRow(row),
    variant: 'destructive',
  },
];

const deleteSelected = async () => {
  if (!selected.value.length) {
    return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
  }
  try {
    await axios.post(`/api/rencana-latihan/${rencanaId}/peserta/${jenisPeserta}/destroy-selected`, {
      ids: selected.value,
    });
    selected.value = [];
    pageIndex.value.fetchData && pageIndex.value.fetchData();
    toast({ title: 'Data berhasil dihapus', variant: 'success' });
  } catch (error) {
    toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
  }
};

const deleteRow = async (row: any) => {
  try {
    await axios.delete(`/api/rencana-latihan/${rencanaId}/peserta/${jenisPeserta}/${row.id}`);
    toast({ title: 'Data berhasil dihapus', variant: 'success' });
    pageIndex.value.fetchData && pageIndex.value.fetchData();
  } catch (error) {
    toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
  }
};
</script>

<template>
  <div class="space-y-4">
    <PageIndex
      :title="`Peserta (${jenisLabel[jenisPeserta] || jenisPeserta})`"
      :breadcrumbs="breadcrumbs"
      :columns="columns"
      :actions="actions"
      :selected="selected"
      @update:selected="(val) => (selected = val)"
      :on-delete-selected="deleteSelected"
      :on-delete-row="deleteRow"
      :show-import="false"
      :create-url="''"
      :api-endpoint="`/api/rencana-latihan/${rencanaId}/peserta/${jenisPeserta}`"
      ref="pageIndex"
      :disable-length="true"
      :hide-search="true"
      :hide-pagination="true"
      :on-toast="toast"
    >
      <template #header-extra>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <!-- Kolom kiri: Informasi Program Latihan -->
          <div class="bg-card border rounded-lg p-4">
            <h3 class="text-lg font-semibold mb-2">Informasi Program Latihan</h3>
            <div class="space-y-2">
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-muted-foreground">Nama Program:</span>
                <Badge variant="secondary">{{ info.nama_program }}</Badge>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-muted-foreground">Cabor:</span>
                <Badge variant="outline">{{ info.cabor_nama }}<template v-if="info.cabor_kategori_nama"> - {{ info.cabor_kategori_nama }}</template></Badge>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-muted-foreground">Periode:</span>
                <Badge variant="outline">{{ info.periode_mulai }} s/d {{ info.periode_selesai }}</Badge>
              </div>
            </div>
          </div>
          <!-- Kolom kanan: Informasi Rencana Latihan -->
          <div class="bg-card border rounded-lg p-4">
            <h3 class="text-lg font-semibold mb-2">Informasi Rencana Latihan</h3>
            <div class="space-y-2">
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-muted-foreground">Tanggal:</span>
                <Badge variant="secondary">{{ infoRencana.tanggal ? new Date(infoRencana.tanggal).toLocaleDateString('id-ID') : '-' }}</Badge>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-muted-foreground">Materi:</span>
                <Badge variant="outline">{{ infoRencana.materi }}</Badge>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-muted-foreground">Lokasi:</span>
                <Badge variant="outline">{{ infoRencana.lokasi_latihan }}</Badge>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-muted-foreground">Target Latihan:</span>
                <Badge variant="outline">{{ infoRencana.target_latihan && infoRencana.target_latihan.length ? infoRencana.target_latihan.join(', ') : '-' }}</Badge>
              </div>
            </div>
          </div>
        </div>
      </template>
    </PageIndex>
  </div>
</template> 