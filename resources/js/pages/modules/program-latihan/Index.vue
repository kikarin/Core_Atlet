<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const breadcrumbs = [{ title: 'Program Latihan', href: '/program-latihan' }];

const columns = [
    { key: 'nama_program', label: 'Nama Program' },
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
    { key: 'keterangan', label: 'Keterangan' },
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
        />
    </div>
</template> 