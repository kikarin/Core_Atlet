<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const page = usePage();
const routeParams = computed(() => page.props.ziggy?.route_parameters || {});
const programId = computed(() => routeParams.value.program_id || (typeof window !== 'undefined' ? window.location.pathname.split('/')[2] : ''));
const jenisTarget = computed(() => routeParams.value.jenis_target || (typeof window !== 'undefined' ? window.location.pathname.split('/')[4] : ''));

const props = defineProps<{
    infoHeader?: any;
}>();

const breadcrumbs = [
    { title: 'Program Latihan', href: '/program-latihan' },
    { title: 'Target Latihan', href: `/program-latihan/${programId.value}/target-latihan/${jenisTarget.value}` },
];

const columns = [
    { key: 'deskripsi', label: 'Deskripsi Target' },
    { key: 'satuan', label: 'Satuan' },
    { key: 'nilai_target', label: 'Nilai Target' },
];

const selected = ref<number[]>([]);
const pageIndex = ref();
const { toast } = useToast();

const actions = (row: any) => [
    { label: 'Detail', onClick: () => router.visit(`/program-latihan/${programId.value}/target-latihan/${jenisTarget.value}/${row.id}`) },
    { label: 'Edit', onClick: () => router.visit(`/program-latihan/${programId.value}/target-latihan/${jenisTarget.value}/${row.id}/edit`) },
    { label: 'Delete', onClick: () => pageIndex.value.handleDeleteRow(row) },
];

const deleteSelected = async () => {
    if (!selected.value.length) {
        return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
    }
    try {
        const response = await axios.post(`/target-latihan/destroy-selected`, { ids: selected.value });
        selected.value = [];
        pageIndex.value.fetchData();
        toast({ title: response.data?.message, variant: 'success' });
    } catch (error: any) {
        const message = error.response?.data?.message;
        toast({ title: message, variant: 'destructive' });
    }
};

const deleteRow = async (row: any) => {
    await router.delete(`/program-latihan/${info.value.program_latihan_id}/target-latihan/${info.value.jenis_target}/${row.id}`, {
        onSuccess: () => {
            toast({ title: 'Data berhasil dihapus', variant: 'success' });
            pageIndex.value.fetchData();
        },
        onError: () => {
            toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
        },
    });
};

const info = computed(() => props.infoHeader || {});
</script>

<template>
    <div class="space-y-4">
        <PageIndex
            title="Target Latihan"
            :breadcrumbs="breadcrumbs"
            :columns="columns"
            :create-url="`/program-latihan/${info.program_latihan_id}/target-latihan/${info.jenis_target}/create`"
            :actions="actions"
            :selected="selected"
            @update:selected="(val) => (selected = val)"
            :on-delete-selected="deleteSelected"
            :api-endpoint="`/api/target-latihan?program_latihan_id=${info.program_latihan_id}&jenis_target=${info.jenis_target}`"
            ref="pageIndex"
            :on-toast="toast"
            :on-delete-row-confirm="deleteRow"
            :show-import="false"
        >
            <template #header-extra>
                <div class="bg-card mb-4 rounded-lg border p-4">
                    <h3 class="mb-2 text-lg font-semibold">Informasi Program Latihan</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-muted-foreground text-sm font-medium">Nama Program:</span>
                            <Badge variant="secondary">{{ info.nama_program }}</Badge>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-muted-foreground text-sm font-medium">Cabor:</span>
                            <Badge variant="outline">
                                {{ info.cabor_nama }}<template v-if="info.cabor_kategori_nama"> - {{ info.cabor_kategori_nama }}</template>
                            </Badge>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-muted-foreground text-sm font-medium">Periode:</span>
                            <Badge variant="secondary">{{
                                info.periode_mulai && info.periode_selesai ? `${info.periode_mulai} s/d ${info.periode_selesai}` : '-'
                            }}</Badge>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-muted-foreground text-sm font-medium">Jenis Target:</span>
                            <Badge variant="outline">{{ info.jenis_target }}</Badge>
                        </div>
                    </div>
                </div>
            </template>
        </PageIndex>
    </div>
</template>
