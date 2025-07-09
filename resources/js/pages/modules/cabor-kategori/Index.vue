<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const breadcrumbs = [
    { title: 'Cabor', href: '/cabor' },
    { title: 'Kategori', href: '/cabor-kategori' },
];

const columns = [
    { key: 'cabor_nama', label: 'Cabor' },
    { key: 'nama', label: 'Nama Kategori' },
    { key: 'deskripsi', label: 'Deskripsi' },
];

const selected = ref<number[]>([]);
const pageIndex = ref();
const { toast } = useToast();

const actions = (row: any) => [
    { label: 'Detail', onClick: () => router.visit(`/cabor-kategori/${row.id}`) },
    { label: 'Edit', onClick: () => router.visit(`/cabor-kategori/${row.id}/edit`) },
    { label: 'Delete', onClick: () => pageIndex.value.handleDeleteRow(row) },
];

const deleteSelected = async () => {
    if (!selected.value.length) {
        return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
    }
    try {
        const response = await axios.post('/cabor-kategori/destroy-selected', { ids: selected.value });
        selected.value = [];
        pageIndex.value.fetchData();
        toast({ title: response.data?.message, variant: 'success' });
    } catch (error: any) {
        const message = error.response?.data?.message;
        toast({ title: message, variant: 'destructive' });
    }
};

const deleteRow = async (row: any) => {
    await router.delete(`/cabor-kategori/${row.id}`, {
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
            title="Kategori"
            :breadcrumbs="breadcrumbs"
            :columns="columns"
            :create-url="'/cabor-kategori/create'"
            :actions="actions"
            :selected="selected"
            @update:selected="(val) => (selected = val)"
            :on-delete-selected="deleteSelected"
            api-endpoint="/api/cabor-kategori"
            ref="pageIndex"
            :on-toast="toast"
            :on-delete-row-confirm="deleteRow"
            :show-import="false"
        />
    </div>
</template> 