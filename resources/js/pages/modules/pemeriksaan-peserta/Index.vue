<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const page = usePage();
const pemeriksaan = computed(() => page.props.pemeriksaan as any);
const jenisPeserta = computed(() => {
  const ziggy: any = usePage().props.ziggy;
  const jenis = (ziggy?.query?.jenis_peserta || '').toString();
  if (['atlet', 'pelatih', 'tenaga-pendukung'].includes(jenis)) return jenis;
  return 'atlet';
});
const refStatusPemeriksaan = computed(() => page.props.ref_status_pemeriksaan as any[]);

const catatanUmum = ref('');
const selectedStatusId = ref('');

const jenisLabel: Record<string, string> = {
    atlet: 'Atlet',
    pelatih: 'Pelatih',
    'tenaga-pendukung': 'Tenaga Pendukung',
};

const breadcrumbs = computed(() => [
    { title: 'Pemeriksaan', href: `/pemeriksaan` },
    { title: pemeriksaan.value.nama_pemeriksaan, href: `/pemeriksaan/${pemeriksaan.value.id}` },
    { title: `Peserta (${jenisLabel[jenisPeserta.value] || jenisPeserta.value})`, href: '#' },
]);

const columns = computed(() => [
    { key: 'peserta.nama', label: 'Nama' },
    { key: 'status', label: 'Status', format: (row: any) => row.status || '-' },
    { key: 'catatan_umum', label: 'Catatan', format: (row: any) => row.catatan_umum || '-' },
    {
        key: 'foto',
        label: 'Foto',
        format: (row: any) => {
            const peserta = row.peserta;
            if(peserta && peserta.foto) {
                return `<div class='cursor-pointer' onclick=\"window.open('${peserta.foto}', '_blank')\"><img src='${peserta.foto}' alt='Foto ${peserta.nama}' class='w-12 h-12 object-cover rounded-full border hover:shadow-md transition-shadow' /></div>`
            }
            return '<div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs">No</div>';
        },
    },
    { key: 'peserta.jenis_kelamin', label: 'Jenis Kelamin', format: (row: any) => (row.peserta?.jenis_kelamin === 'L' ? 'Laki-laki' : row.peserta?.jenis_kelamin === 'P' ? 'Perempuan' : '-')},
    { key: 'peserta.tempat_lahir', label: 'Tempat Lahir' },
    { key: 'peserta.tanggal_lahir', label: 'Tanggal Lahir', format: (row: any) => row.peserta?.tanggal_lahir ? new Date(row.peserta.tanggal_lahir).toLocaleDateString('id-ID', { day: 'numeric', month: 'numeric', year: 'numeric' }) : '-'},
    { key: 'peserta.no_hp', label: 'No HP' },
]);

const selected = ref<number[]>([]);
const pageIndex = ref();
const { toast } = useToast();
const showConfirmDialog = ref(false);

const actions = (row: any) => [
    { label: 'Detail', onClick: () => router.get(`/pemeriksaan/${pemeriksaan.value.id}/peserta/${row.id}`), },
    { label: 'Edit', onClick: () => router.get(`/pemeriksaan/${pemeriksaan.value.id}/peserta/${row.id}/edit`), },
    { label: 'Delete', onClick: () => pageIndex.value.handleDeleteRow(row), variant: 'destructive' }
];

const deleteSelected = async () => {
    if (!selected.value.length) return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
    try {
        await axios.post(`/pemeriksaan/${pemeriksaan.value.id}/peserta/destroy-selected`, { ids: selected.value });
        selected.value = [];
        pageIndex.value.fetchData?.();
        toast({ title: 'Data berhasil dihapus', variant: 'success' });
    } catch {
        toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
    }
};

const deleteRow = async (row: any) => {
    try {
        await router.delete(`/pemeriksaan/${pemeriksaan.value.id}/peserta/${row.id}`, {
            onSuccess: () => {
                toast({ title: 'Data berhasil dihapus' });
            }
        });
    } catch {
        toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
    }
};

const updateStatus = async () => {
    if (!selected.value.length) return;
    try {
        await axios.post(`/pemeriksaan/${pemeriksaan.value.id}/update-status`, {
            ids: selected.value,
            ref_status_pemeriksaan_id: selectedStatusId.value,
            catatan_umum: catatanUmum.value,
        });
        toast({ title: 'Status Peserta berhasil diupdate', variant: 'success' });
        selected.value = [];
        catatanUmum.value = '';
        selectedStatusId.value = '';
        showConfirmDialog.value = false;
        pageIndex.value.fetchData?.();
    } catch (error: any) {
        toast({ title: error.response?.data?.message || 'Gagal update status', variant: 'destructive' });
    }
};

const handleUpdateStatus = () => {
    if (!selected.value.length) {
        toast({ title: 'Pilih peserta terlebih dahulu', variant: 'destructive' });
        return;
    }
    showConfirmDialog.value = true;
};
</script>

<template>
    <div class="space-y-4">
        <PageIndex
            :title="`Peserta (${jenisLabel[jenisPeserta.value] || jenisPeserta.value})`"
            :breadcrumbs="breadcrumbs"
            :columns="columns"
            :actions="actions"
            :selected="selected"
            @update:selected="(val: any) => (selected = val)"
            :on-delete-selected="deleteSelected"
            :on-delete-row="deleteRow"
            :show-import="false"
            :create-url="`/pemeriksaan/${pemeriksaan.id}/peserta/create?jenis_peserta=${jenisPeserta.value}`"
            :api-endpoint="`/api/pemeriksaan/${pemeriksaan.id}/peserta/${jenisPeserta.value}`"
            ref="pageIndex"
            :on-toast="toast"
            :key="jenisPeserta.value"
        >
            <template #header-extra>
                <div class="bg-card mb-4 rounded-lg border p-4">
                    <h3 class="mb-2 text-lg font-semibold">Informasi Pemeriksaan</h3>
                     <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-muted-foreground text-sm font-medium">Nama:</span>
                            <Badge variant="secondary">{{ pemeriksaan.nama_pemeriksaan }}</Badge>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-muted-foreground text-sm font-medium">Cabor:</span>
                            <Badge variant="outline">{{ pemeriksaan.cabor?.nama }}<template v-if="pemeriksaan.cabor_kategori?.nama"> - {{ pemeriksaan.cabor_kategori?.nama }}</template></Badge>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-muted-foreground text-sm font-medium">Tanggal:</span>
                            <Badge variant="outline">{{ pemeriksaan.tanggal_pemeriksaan ? new Date(pemeriksaan.tanggal_pemeriksaan).toLocaleDateString('id-ID') : '-' }}</Badge>
                        </div>
                    </div>
                </div>
            </template>
            <template #table-actions>
                <Button @click="handleUpdateStatus" :disabled="!selected.length">Update Status Terpilih</Button>
            </template>
        </PageIndex>
        <Dialog v-model:open="showConfirmDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Update Status Peserta</DialogTitle>
                    <DialogDescription class="space-y-4 pt-2">
                        <div>Update status untuk {{ selected.length }} peserta terpilih?</div>
                        <div>
                            <label for="status" class="mb-1 block text-sm font-medium">Status Pemeriksaan</label>
                            <select v-model="selectedStatusId" class="w-full rounded border p-2 text-sm">
                                <option disabled value="">Pilih status</option>
                                <option v-for="status in refStatusPemeriksaan" :key="status.id" :value="status.id">{{ status.nama }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="catatan" class="mb-1 block text-sm font-medium">Catatan (opsional)</label>
                            <textarea id="catatan" v-model="catatanUmum" rows="3" class="w-full rounded border p-2 text-sm" placeholder="Catatan untuk semua peserta terpilih"/>
                        </div>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showConfirmDialog = false">Batal</Button>
                    <Button variant="default" @click="updateStatus" :disabled="!selectedStatusId">Ya, Update</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template> 