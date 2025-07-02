<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageShow from '@/pages/modules/base-page/PageShow.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppTabs from '@/components/AppTabs.vue';
import ShowOrangTua from './ShowOrangTua.vue';
import ShowSertifikat from './ShowSertifikat.vue';
import EditSertifikatModal from './EditSertifikatModal.vue';
import { useSertifikatEdit } from './useSertifikatEdit';
import { PropType } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Pencil, Plus } from 'lucide-vue-next';

const { toast } = useToast();

interface Sertifikat {
  id: number;
  atlet_id: number;
  nama_sertifikat: string;
  penyelenggara?: string;
  tanggal_terbit?: string;
  file_url?: string;
  created_at: string;
  updated_at: string;
  created_by_user?: { name: string } | null;
  updated_by_user?: { name: string } | null;
}

const props = defineProps<{
    item: {
        id: number;
        nik: string;
        nama: string;
        jenis_kelamin: string;
        tempat_lahir: string;
        tanggal_lahir: string;
        alamat: string;
        kecamatan_id: number | null;
        kelurahan_id: number | null;
        no_hp: string;
        email: string;
        is_active: number;
        foto: string;
        created_at: string;
        created_by_user: { id: number; name: string } | null;
        updated_at: string;
        updated_by_user: { id: number; name: string } | null;
        atlet_orang_tua?: {
            id: number;
            atlet_id: number;
            created_at: string;
            updated_at: string;
            created_by_user: { name: string } | null;
            updated_by_user: { name: string } | null;
        } | null;
        sertifikat?: Sertifikat[];
    };
}>();

// Ambil tab dari query string
function getTabFromUrl(url: string, fallback = 'atlet-data') {
  if (url.includes('tab=')) {
    return new URLSearchParams(url.split('?')[1]).get('tab') || fallback;
  }
  return fallback;
}

const page = usePage();
const initialTab = getTabFromUrl(page.url);
const activeTab = ref(initialTab);

watch(activeTab, (val) => {
  console.log('Tab berubah:', val);
  const url = `/atlet/${props.item.id}?tab=${val}`;
  router.visit(url, { replace: true, preserveState: true, preserveScroll: true, only: [] });});

watch(
  () => page.url,
  (newUrl) => {
    const tab = getTabFromUrl(newUrl);
    if (tab !== activeTab.value) {
      activeTab.value = tab;
    }
  }
);

const dynamicTitle = computed(() => {
  if (activeTab.value === 'atlet-data') {
    return `Atlet : ${props.item.nama}`;
  } else if (activeTab.value === 'orang-tua-data') {
    return `Orang Tua/Wali : ${props.item.nama}`;
  } else if (activeTab.value === 'sertifikat-data') {
    return `Sertifikat : ${props.item.nama}`;
  }
  return `Atlet: ${props.item.nama}`;
});

const breadcrumbs = [
    { title: 'Atlet', href: '/atlet' },
    { title: 'Detail Atlet', href: `/atlet/${props.item.id}` },
];

const fields = computed(() => {
    return [
        { label: 'NIK', value: props.item?.nik || '-' },
        { label: 'Nama', value: props.item?.nama || '-' },
        {
            label: 'Jenis Kelamin',
            value: props.item?.jenis_kelamin === 'L' ? 'Laki-laki' : props.item?.jenis_kelamin === 'P' ? 'Perempuan' : '-',
            className: props.item?.jenis_kelamin === 'L' ? 'text-blue-600' : props.item?.jenis_kelamin === 'P' ? 'text-pink-600' : '',
        },
        { label: 'Tempat Lahir', value: props.item?.tempat_lahir || '-' },
        {
            label: 'Tanggal Lahir',
            value: props.item?.tanggal_lahir
                ? new Date(props.item.tanggal_lahir).toLocaleDateString('id-ID', {
                      day: 'numeric',
                      month: 'numeric',
                      year: 'numeric',
                  })
                : '-',
        },
        { label: 'Alamat', value: props.item?.alamat || '-', className: 'sm:col-span-2' },
        { label: 'Kecamatan', value: props.item?.kecamatan_id ? `ID: ${props.item.kecamatan_id}` : '-' },
        { label: 'Kelurahan', value: props.item?.kelurahan_id ? `ID: ${props.item.kelurahan_id}` : '-' },
        { label: 'No HP', value: props.item?.no_hp || '-' },
        { label: 'Email', value: props.item?.email || '-' },
        {
            label: 'Status',
            value: props.item?.is_active ? 'Aktif' : 'Nonaktif',
            className: props.item?.is_active ? 'text-green-600' : 'text-red-600',
        },
        {
            label: 'Foto',
            value: props.item?.foto || '',
            type: 'image' as const,
            className: 'sm:col-span-2',
            imageConfig: {
                size: 'md' as const,
                labelText: 'Klik untuk melihat lebih besar'
            }
        },
    ];
});

const actionFields = computed(() => [
    { label: 'Created At', value: new Date(props.item.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) },
    { label: 'Created By', value: props.item.created_by_user?.name || '-' },
    { label: 'Updated At', value: new Date(props.item.updated_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) },
    { label: 'Updated By', value: props.item.updated_by_user?.name || '-' },
]);

const orangTuaActionFields = computed(() => {
  const o = props.item.atlet_orang_tua;
  return [
    {
      label: 'Created At',
      value: o?.created_at ? new Date(o.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) : '-',
    },
    {
      label: 'Created By',
      value: o?.created_by_user?.name || '-',
    },
    {
      label: 'Updated At',
      value: o?.updated_at ? new Date(o.updated_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) : '-',
    },
    {
      label: 'Updated By',
      value: o?.updated_by_user?.name || '-',
    },
  ];
});

const tabsConfig = [
    {
        value: 'atlet-data',
        label: 'Atlet',
    },
    {
        value: 'orang-tua-data',
        label: 'Orang Tua/Wali',
    },
    {
        value: 'sertifikat-data',
        label: 'Sertifikat',
    },
];

const handleEditAtlet = () => {
    router.visit(`/atlet/${props.item.id}/edit`);
};

const handleDeleteAtlet = () => {
    router.delete(`/atlet/${props.item.id}`, {
        onSuccess: () => {
            toast({ title: 'Atlet berhasil dihapus', variant: 'success' });
            router.visit('/atlet');
        },
        onError: () => {
            toast({ title: 'Gagal menghapus atlet', variant: 'destructive' });
        },
    });
};

const handleEditOrangTua = () => {
    router.visit(`/atlet/${props.item.id}/edit?tab=orang-tua-data`);
};

const handleDeleteOrangTua = () => {
    if (props.item.atlet_orang_tua) {
        router.delete(`/atlet/${props.item.id}/orang-tua/${props.item.atlet_orang_tua.id}`, {
            onSuccess: () => {
                toast({ title: 'Data orang tua/wali berhasil dihapus', variant: 'success' });
                router.visit(`/atlet/${props.item.id}?tab=orang-tua-data`, { replace: true, preserveState: true, preserveScroll: true, only: [] });
            },
            onError: () => {
                toast({ title: 'Gagal menghapus data orang tua/wali', variant: 'destructive' });
            },
        });
    }
};

const currentOnEditHandler = computed(() => {
    if (activeTab.value === 'atlet-data') {
        return handleEditAtlet;
    } else if (activeTab.value === 'orang-tua-data') {
        return handleEditOrangTua;
    } else if (activeTab.value === 'sertifikat-data') {
        return undefined;
    }
    return undefined;
});

const currentOnDeleteHandler = computed(() => {
    if (activeTab.value === 'atlet-data') {
        return handleDeleteAtlet;
    } else if (activeTab.value === 'orang-tua-data') {
        return props.item.atlet_orang_tua ? handleDeleteOrangTua : undefined;
    } else if (activeTab.value === 'sertifikat-data') {
        return undefined;
    }
    return undefined;
});

// Sertifikat edit modal logic
const {
  showEditModal,
  sertifikatToEdit,
  openEditSertifikat,
  closeEditModal,
  onSavedSertifikat,
} = useSertifikatEdit();

function handleEditSertifikat(sertifikat: any) {
  openEditSertifikat(sertifikat);
}

function handleSertifikatSaved() {
  onSavedSertifikat();
  router.reload();
}

// State untuk modal konfirmasi delete
const showDeleteModal = ref(false);
const sertifikatToDelete = ref<any | null>(null);
const showDeleteSelectedModal = ref(false);
const idsToDelete = ref<number[]>([]);
const selectedSertifikat = ref<number[]>([]);

function handleDeleteSertifikat(sertifikat: any) {
  sertifikatToDelete.value = sertifikat;
  showDeleteModal.value = true;
}

function confirmDeleteSertifikat() {
  if (!sertifikatToDelete.value) return;
  router.delete(`/atlet/${props.item.id}/sertifikat/${sertifikatToDelete.value.id}`, {
    onSuccess: () => {
      toast({ title: 'Sertifikat berhasil dihapus', variant: 'success' });
      router.reload();
    },
    onError: () => {
      toast({ title: 'Gagal menghapus sertifikat', variant: 'destructive' });
    },
  });
  showDeleteModal.value = false;
  sertifikatToDelete.value = null;
}

function handleDeleteSelectedSertifikat(ids: number[]) {
  idsToDelete.value = ids;
  showDeleteSelectedModal.value = true;
}

function handleUpdateSelectedSertifikat(val: number[]) {
  selectedSertifikat.value = val;
}

function confirmDeleteSelectedSertifikat() {
  if (!idsToDelete.value.length) return;
  Promise.all(idsToDelete.value.map(id => router.delete(`/atlet/${props.item.id}/sertifikat/${id}`)))
    .then(() => {
      toast({ title: 'Sertifikat terpilih berhasil dihapus', variant: 'success' });
      router.reload();
    })
    .catch(() => {
      toast({ title: 'Gagal menghapus beberapa sertifikat', variant: 'destructive' });
    });
  showDeleteSelectedModal.value = false;
  idsToDelete.value = [];
}

const showCreatorModal = ref(false);
const sertifikatCreator = ref<any | null>(null);
function handleShowCreator(sertifikat: any) {
  sertifikatCreator.value = sertifikat;
  showCreatorModal.value = true;
}
</script>

<template>
    <PageShow
        :title="dynamicTitle"
        :breadcrumbs="breadcrumbs"
        :fields="activeTab === 'atlet-data' ? fields : []"
        :actionFields="activeTab === 'sertifikat-data' ? [] : (activeTab === 'atlet-data' ? actionFields : orangTuaActionFields)"
        :back-url="'/atlet'"
        :on-edit="currentOnEditHandler"
        :on-delete="currentOnDeleteHandler"
        :on-edit-label="activeTab === 'orang-tua-data' && !props.item.atlet_orang_tua ? 'Create' : 'Edit'"
        :on-edit-icon="activeTab === 'orang-tua-data' && !props.item.atlet_orang_tua ? Plus : Pencil"
    >
        <template #tabs>
            <AppTabs
                :tabs="tabsConfig"
                :default-value="'atlet-data'"
                v-model="activeTab"
            />
        </template>
        <template #custom-action>
            <div v-if="activeTab === 'sertifikat-data'">
                <button
                  class="border-input bg-background hover:bg-accent hover:text-accent-foreground inline-flex items-center gap-1 rounded-md border px-3 py-2 text-sm transition-colors"
                  @click="() => router.visit(`/atlet/${props.item.id}/edit?tab=sertifikat-data`)"
                >
                  <Plus class="h-4 w-4" />
                  Create Sertifikat
                </button>
            </div>
        </template>
        <template #custom>
            <div v-if="activeTab === 'orang-tua-data'">
                <ShowOrangTua :orang-tua="props.item.atlet_orang_tua || null" />
            </div>
            <div v-if="activeTab === 'sertifikat-data'">
                <ShowSertifikat
                  :sertifikat-list="props.item.sertifikat || []"
                  :atlet-id="props.item.id"
                  :selected-ids="selectedSertifikat"
                  @edit="handleEditSertifikat"
                  @delete="handleDeleteSertifikat"
                  @deleteSelected="handleDeleteSelectedSertifikat"
                  @update:selected="handleUpdateSelectedSertifikat"
                  @showCreator="handleShowCreator"
                />
                <EditSertifikatModal
                  :visible="showEditModal"
                  :sertifikat="sertifikatToEdit"
                  :atlet-id="props.item.id"
                  :onClose="closeEditModal"
                  :onSaved="handleSertifikatSaved"
                />
                <!-- Modal konfirmasi delete single -->
                <Dialog v-model:open="showDeleteModal">
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Hapus Sertifikat</DialogTitle>
                    </DialogHeader>
                    <div>Yakin ingin menghapus sertifikat <b>{{ sertifikatToDelete?.nama_sertifikat }}</b>?</div>
                    <DialogFooter>
                      <Button variant="outline" @click="showDeleteModal = false">Batal</Button>
                      <Button variant="destructive" @click="confirmDeleteSertifikat">Hapus</Button>
                    </DialogFooter>
                  </DialogContent>
                </Dialog>
                <!-- Modal konfirmasi delete selected -->
                <Dialog v-model:open="showDeleteSelectedModal">
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Hapus Sertifikat Terpilih</DialogTitle>
                    </DialogHeader>
                    <div>Yakin ingin menghapus <b>{{ idsToDelete.length }}</b> sertifikat terpilih?</div>
                    <DialogFooter>
                      <Button variant="outline" @click="showDeleteSelectedModal = false">Batal</Button>
                      <Button variant="destructive" @click="confirmDeleteSelectedSertifikat">Hapus</Button>
                    </DialogFooter>
                  </DialogContent>
                </Dialog>
                <!-- Modal info pembuat sertifikat -->
                <Dialog v-model:open="showCreatorModal">
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Info Pembuat Sertifikat</DialogTitle>
                    </DialogHeader>
                    <div v-if="sertifikatCreator">
                      <div class="mb-2"><b>Dibuat:</b> {{ sertifikatCreator.created_at ? new Date(sertifikatCreator.created_at).toLocaleString('id-ID') : '-' }}</div>
                      <div class="mb-2"><b>Oleh:</b> {{ sertifikatCreator.created_by_user?.name || '-' }}</div>
                      <div class="mb-2"><b>Diupdate:</b> {{ sertifikatCreator.updated_at ? new Date(sertifikatCreator.updated_at).toLocaleString('id-ID') : '-' }}</div>
                      <div class="mb-2"><b>Oleh:</b> {{ sertifikatCreator.updated_by_user?.name || '-' }}</div>
                    </div>
                    <DialogFooter>
                      <Button variant="outline" @click="showCreatorModal = false">Tutup</Button>
                    </DialogFooter>
                  </DialogContent>
                </Dialog>
            </div>
        </template>
    </PageShow>
</template>