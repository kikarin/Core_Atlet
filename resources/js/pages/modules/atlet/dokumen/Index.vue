<script setup lang="ts">
import AppTabs from '@/components/AppTabs.vue';
import HeaderActions from '@/pages/modules/base-page/HeaderActions.vue';
import DataTable from '@/pages/modules/components/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import debounce from 'lodash.debounce';
import { router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { useToast } from '@/components/ui/toast/useToast';
import axios from 'axios';

const props = defineProps<{ atletId: number }>();
const { toast } = useToast();

const breadcrumbs = computed(() => [
  { title: 'Atlet', href: '/atlet' },
  { title: 'Dokumen', href: `/atlet/${props.atletId}/dokumen` },
]);

const columns = [
  { key: 'jenis_dokumen', label: 'Jenis Dokumen', format: (row: any) => row.jenis_dokumen?.nama || '-' },
  { key: 'nomor', label: 'Nomor' },
  {
    key: 'file_url',
    label: 'File',
    format: (row: any) =>
      row.file_url
        ? `<a href="${row.file_url}" target="_blank" class="text-blue-600 underline">Lihat File</a>`
        : '<span class="text-muted-foreground">-</span>',
  },
];

const selected = ref<number[]>([]);
const rows = ref<any[]>([]);
const total = ref(0);
const loading = ref(false);
const search = ref('');
const page = ref(1);
const perPage = ref(10);
const sort = ref<{ key: string; order: 'asc' | 'desc' }>({ key: '', order: 'asc' });
const handleSearchDebounced = debounce((val: string) => {
  search.value = val;
  fetchData();
}, 400);

const handleSort = debounce((val: { key: string; order: 'asc' | 'desc' }) => {
  sort.value.key = val.key;
  sort.value.order = val.order;
  page.value = 1;
  fetchData();
}, 400);

const handleSearch = (params: { search?: string; sortKey?: string; sortOrder?: 'asc' | 'desc'; page?: number; limit?: number }) => {
  if (params.search !== undefined) search.value = params.search;
  if (params.sortKey) sort.value.key = params.sortKey;
  if (params.sortOrder) sort.value.order = params.sortOrder;
  if (params.page) page.value = params.page;
  if (params.limit) perPage.value = params.limit;
};

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/atlet/${props.atletId}/dokumen`, {
      params: {
        search: search.value,
        page: page.value,
        per_page: perPage.value,
        sort: sort.value.key,
        order: sort.value.order,
      },
    });
    rows.value = response.data.data;
    const meta = response.data.meta || {};
    total.value = Number(meta.total) || 0;
    page.value = Number(meta.current_page) || 1;
    perPage.value = Number(meta.per_page) || 10;
    search.value = meta.search || '';
    sort.value.key = meta.sort || '';
    sort.value.order = meta.order || 'asc';
  } catch {
    toast({ title: 'Gagal memuat data', variant: 'destructive' });
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

watch([page, perPage, () => sort.value.key, () => sort.value.order], () => {
  fetchData();
});

const actions = (row: any) => [
  {
    label: 'Detail',
    onClick: () => router.visit(`/atlet/${props.atletId}/dokumen/${row.id}`),
  },
  {
    label: 'Edit',
    onClick: () => router.visit(`/atlet/${props.atletId}/dokumen/${row.id}/edit`),
  },
  {
    label: 'Delete',
    onClick: () => handleDeleteRow(row),
  },
];

const handleDeleteRow = async (row: any) => {
  rowToDelete.value = row;
  showDeleteDialog.value = true;
};

const confirmDeleteRow = async () => {
  if (!rowToDelete.value) return;

  router.delete(`/atlet/${props.atletId}/dokumen/${rowToDelete.value.id}`, {
    onSuccess: () => {
      toast({ title: 'Dokumen berhasil dihapus', variant: 'success' });
      fetchData();
    },
    onError: () => {
      toast({ title: 'Gagal menghapus dokumen.', variant: 'destructive' });
    },
  });
  showDeleteDialog.value = false;
  rowToDelete.value = null;
};

const deleteSelected = async () => {
  if (!selected.value.length) {
    return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
  }
  idsToDelete.value = [...selected.value];
  showDeleteSelectedDialog.value = true;
};

const confirmDeleteSelected = async () => {
  try {
    const response = await axios.post(`/atlet/${props.atletId}/dokumen/destroy-selected`, { ids: idsToDelete.value });
    selected.value = [];
    fetchData();
    toast({ title: response.data?.message || 'Dokumen terpilih berhasil dihapus', variant: 'success' });
  } catch (error: any) {
    toast({ title: error.response?.data?.message || 'Gagal menghapus dokumen terpilih', variant: 'destructive' });
  }
  showDeleteSelectedDialog.value = false;
  idsToDelete.value = [];
};

// Tabs config
const tabsConfig = [
  {
    value: 'atlet-data',
    label: 'Atlet',
    onClick: () => router.visit(`/atlet/${props.atletId}/edit?tab=atlet-data`),
  },
  {
    value: 'orang-tua-data',
    label: 'Orang Tua/Wali',
    onClick: () => router.visit(`/atlet/${props.atletId}/edit?tab=orang-tua-data`),
  },
  {
    value: 'sertifikat-data',
    label: 'Sertifikat',
    onClick: () => router.visit(`/atlet/${props.atletId}/sertifikat`),
  },
  {
    value: 'prestasi-data',
    label: 'Prestasi',
    onClick: () => router.visit(`/atlet/${props.atletId}/prestasi`),
  },
  {
    value: 'dokumen-data',
    label: 'Dokumen',
    // Aktif
  },
  {
    value: 'kesehatan-data',
    label: 'Kesehatan',
    onClick: () => router.visit(`/atlet/${props.atletId}/edit?tab=kesehatan-data`),
  },
];
const activeTab = ref('dokumen-data');

function handleTabChange(val: string) {
  if (val === 'dokumen-data') return;
  const tab = tabsConfig.find(t => t.value === val);
  if (tab && tab.onClick) tab.onClick();
}

const showDeleteDialog = ref(false);
const showDeleteSelectedDialog = ref(false);
const rowToDelete = ref<any>(null);
const idsToDelete = ref<number[]>([]);
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
      <AppTabs :tabs="tabsConfig" :model-value="activeTab" @update:model-value="handleTabChange"
        :default-value="'dokumen-data'" />
      <HeaderActions title="Dokumen" :create-url="`/atlet/${props.atletId}/dokumen/create`" :selected="selected"
        :on-delete-selected="deleteSelected" />
      <DataTable :columns="columns" :rows="rows" v-model:selected="selected" :total="total" :search="search"
        :sort="sort" :page="page" :per-page="perPage" :loading="loading" :actions="actions"
        @update:search="handleSearchDebounced" @update:sort="handleSort"
        @update:page="(val) => handleSearch({ page: val })"
        @update:per-page="(val) => handleSearch({ limit: Number(val), page: 1 })" @deleted="fetchData()" />
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Hapus data ini!</DialogTitle>
          <DialogDescription>
            Apakah Anda yakin?
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="showDeleteDialog = false">Batal</Button>
          <Button variant="destructive" @click="confirmDeleteRow">Hapus</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Delete Selected Confirmation Dialog -->
    <Dialog v-model:open="showDeleteSelectedDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Hapus data terpilih?</DialogTitle>
          <DialogDescription>
            Anda akan menghapus <b>{{ idsToDelete.length }}</b> data. Tindakan ini tidak
            dapat dibatalkan.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="showDeleteSelectedDialog = false">Batal</Button>
          <Button variant="destructive" @click="confirmDeleteSelected">Hapus</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template> 