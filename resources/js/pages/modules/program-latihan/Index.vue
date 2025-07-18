<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import BadgeGroup from '../components/BadgeGroup.vue';

const breadcrumbs = [{ title: 'Program Latihan', href: '/program-latihan' }];

const columns = [
    { key: 'nama_program', label: 'Nama Program' },
    { key: 'rencana_latihan', label: 'Rencana Latihan' },
    { key: 'cabor_nama', label: 'Cabor' },
    {
        key: 'cabor_kategori_nama',
        label: 'Kategori',
        format: (row: any) => row.cabor_kategori_nama || row.cabor_kategori?.nama || '-'
    },
    {
        key: 'periode',
        label: 'Periode',
        format: (row: any) => {
            return row.periode_mulai && row.periode_selesai
                ? `${row.periode_mulai} s/d ${row.periode_selesai}`
                : '-';
        },
    },
    { key: 'target_individu', label: 'Target Individu' },
    { key: 'target_kelompok', label: 'Target Kelompok' },
];

const selected = ref<number[]>([]);
const pageIndex = ref();
const { toast } = useToast();

const actions = (row: any) => [
    {
        label: 'Detail',
        onClick: () => router.visit(`/program-latihan/${row.id}`),
    },
    {
        label: 'Edit',
        onClick: () => router.visit(`/program-latihan/${row.id}/edit`),
    },
    {
        label: 'Delete',
        onClick: () => pageIndex.value.handleDeleteRow(row),
    },
];

const deleteSelected = async () => {
    if (!selected.value.length) {
        return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
    }
    try {
        const response = await axios.post('/program-latihan/destroy-selected', { ids: selected.value });
        selected.value = [];
        pageIndex.value.fetchData();
        toast({ title: response.data?.message || 'Data berhasil dihapus', variant: 'success' });
    } catch (error: any) {
        toast({ title: error.response?.data?.message || 'Gagal menghapus data', variant: 'destructive' });
    }
};

const deleteProgram = async (row: any) => {
    await router.delete(`/program-latihan/${row.id}`, {
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
            title="Program Latihan"
            :breadcrumbs="breadcrumbs"
            :columns="columns"
            :create-url="'/program-latihan/create'"
            :actions="actions"
            :selected="selected"
            @update:selected="(val) => (selected = val)"
            :on-delete-selected="deleteSelected"
            api-endpoint="/api/program-latihan"
            ref="pageIndex"
            :on-toast="toast"
            :on-delete-row="deleteProgram"
            :show-import="false"
        >
            <template #cell-rencana_latihan="{ row }">
                <BadgeGroup
                    :badges="[
                        {
                            label: 'Rencana Latihan',
                            value: row.jumlah_rencana_latihan || 0,
                            colorClass: 'bg-purple-100 text-purple-800 hover:bg-purple-200',
                            onClick: () => router.visit(`/program-latihan/${row.id}/rencana-latihan`)
                        }
                    ]"
                />
            </template>

            <template #cell-target_individu="{ row }">
                <BadgeGroup
                    :badges="[
                        {
                            label: 'Target Individu',
                            value: row.jumlah_target_individu || 0,
                            colorClass: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
                            onClick: () => router.visit(`/program-latihan/${row.id}/target-latihan/individu`)
                        }
                    ]"
                />
            </template>

            <template #cell-target_kelompok="{ row }">
                <BadgeGroup
                    :badges="[
                        {
                            label: 'Target Kelompok',
                            value: row.jumlah_target_kelompok || 0,
                            colorClass: 'bg-green-100 text-green-800 hover:bg-green-200',
                            onClick: () => router.visit(`/program-latihan/${row.id}/target-latihan/kelompok`)
                        }
                    ]"
                />
            </template>
        </PageIndex>
    </div>
</template>