<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import axios from 'axios';

const props = defineProps<{ infoHeader: any; data: any[]; total: number; currentPage: number; perPage: number; search: string; sort: string; order: string; }>();

const info = computed(() => props.infoHeader || {});
const breadcrumbs = [
    { title: 'Program Latihan', href: '/program-latihan' },
    { title: 'Rencana Latihan', href: `/program-latihan/${info.value.program_latihan_id}/rencana-latihan` },
];

const columns = [
    { key: 'tanggal', label: 'Tanggal', format: (row: any) => row.tanggal ? new Date(row.tanggal).toLocaleDateString('id-ID') : '-' },
    { key: 'materi', label: 'Materi' },
    { key: 'lokasi_latihan', label: 'Lokasi Latihan' },
    {
        key: 'peserta',
        label: 'Peserta',
        format: (row: any) => {
            const atlet = row.atlets?.length || 0;
            const pelatih = row.pelatihs?.length || 0;
            const pendukung = row.tenaga_pendukung?.length || 0;
            return `<span class='flex flex-col gap-1 items-start'>
                <span class='px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs'>Atlet: ${atlet}</span>
                <span class='px-2 py-0.5 rounded-full bg-green-100 text-green-800 text-xs'>Pelatih: ${pelatih}</span>
                <span class='px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 text-xs'>Pendukung: ${pendukung}</span>
            </span>`;
        },
    },
    {
        key: 'target_latihan',
        label: 'Target Latihan',
        format: (row: any) => row.target_latihan?.map((t: any) => t.deskripsi).join(', ') || '-',
    },
    { key: 'catatan', label: 'Catatan' },
];

const selected = ref<number[]>([]);
const pageIndex = ref();
const { toast } = useToast();

const actions = (row: any) => [
    { label: 'Detail', onClick: () => router.visit(`/program-latihan/${info.value.program_latihan_id}/rencana-latihan/${row.id}`) },
    { label: 'Edit', onClick: () => router.visit(`/program-latihan/${info.value.program_latihan_id}/rencana-latihan/${row.id}/edit`) },
    { label: 'Delete', onClick: () => pageIndex.value.handleDeleteRow(row) },
];

const deleteSelected = async () => {
    if (!selected.value.length) {
        return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
    }
    try {
        const response = await axios.post(`/program-latihan/${info.value.program_latihan_id}/rencana-latihan/destroy-selected`, { ids: selected.value });
        selected.value = [];
        pageIndex.value.fetchData();
        toast({ title: response.data?.message, variant: 'success' });
    } catch (error: any) {
        const message = error.response?.data?.message;
        toast({ title: message, variant: 'destructive' });
    }
};

const deleteRow = async (row: any) => {
    console.log('Delete URL:', `/program-latihan/${info.value.program_latihan_id}/rencana-latihan/${row.id}`);
    console.log('program_latihan_id:', info.value.program_latihan_id, 'row.id:', row.id);
    await router.delete(`/program-latihan/${info.value.program_latihan_id}/rencana-latihan/${row.id}`, {
        onSuccess: () => {
            toast({ title: 'Data berhasil dihapus', variant: 'success' });
            pageIndex.value.fetchData();
        },
        onError: () => {
            toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
        },
    });
};
</script>

<template>
    <div class="space-y-4">
        <PageIndex
            title="Rencana Latihan"
            :breadcrumbs="breadcrumbs"
            :columns="columns"
            :create-url="`/program-latihan/${info.program_latihan_id}/rencana-latihan/create`"
            :actions="actions"
            :selected="selected"
            @update:selected="(val) => (selected = val)"
            :on-delete-selected="deleteSelected"
            :api-endpoint="`/api/rencana-latihan?program_latihan_id=${info.program_latihan_id}`"
            ref="pageIndex"
            :on-toast="toast"
            :on-delete-row="deleteRow"
            :show-import="false"
        >
            <template #header-extra>
                <div class="bg-card border rounded-lg p-4 mb-4">
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
            </template>
        </PageIndex>
    </div>
</template> 