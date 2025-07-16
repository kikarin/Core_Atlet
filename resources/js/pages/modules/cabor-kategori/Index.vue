<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const breadcrumbs = [{ title: 'Cabor Kategori', href: '/cabor-kategori' }];

const columns = [
    {
        key: 'peserta',
        label: 'Peserta',
        sortable: false,
        format: (row: any) => {
            const atlet = row.jumlah_atlet || 0;
            const pelatih = row.jumlah_pelatih || 0;
            const tenaga = row.jumlah_tenaga_pendukung || 0;
            return `
<div class="flex flex-col gap-1 items-start">
            <button
                class="w-fit inline-flex items-center px-2 py-0.5 text-[11px] font-medium bg-blue-100 text-blue-800 rounded-full hover:bg-blue-200 cursor-pointer"
                onclick="window.location.href='/cabor-kategori/${row.id}/atlet'"
            >
                <span class="font-semibold mr-1">${atlet}</span> Atlet
            </button>
            <button
                class="w-fit inline-flex items-center px-2 py-0.5 text-[11px] font-medium bg-green-100 text-green-800 rounded-full hover:bg-green-200 cursor-pointer"
                onclick="window.location.href='/cabor-kategori/${row.id}/pelatih'"
            >
                <span class="font-semibold mr-1">${pelatih}</span> Pelatih
            </button>
            <button
                class="w-fit inline-flex items-center px-2 py-0.5 text-[11px] font-medium bg-yellow-100 text-yellow-800 rounded-full hover:bg-yellow-200 cursor-pointer"
                onclick="window.location.href='/cabor-kategori/${row.id}/tenaga-pendukung'"
            >
                <span class="font-semibold mr-1">${tenaga}</span> Tenaga Pendukung
            </button>
        </div>
            `;
        },
    },
    { key: 'cabor_nama', label: 'Cabor' },
    { key: 'nama', label: 'Nama' },
    { key: 'deskripsi', label: 'Deskripsi' },
];

const selected = ref<number[]>([]);

const pageIndex = ref();

const { toast } = useToast();

const actions = (row: any) => [
    {
        label: 'Detail',
        onClick: () => router.visit(`/cabor-kategori/${row.id}`),
    },
    {
        label: 'Edit',
        onClick: () => router.visit(`/cabor-kategori/${row.id}/edit`),
    },
    {
        label: 'Delete',
        onClick: () => pageIndex.value.handleDeleteRow(row),
    },
    {
        label: 'Daftar Atlet',
        onClick: () => router.visit(`/cabor-kategori/${row.id}/atlet`),
    },
    {
        label: 'Daftar Pelatih',
        onClick: () => router.visit(`/cabor-kategori/${row.id}/pelatih`),
    },
    {
        label: 'Daftar Tenaga Pendukung',
        onClick: () => router.visit(`/cabor-kategori/${row.id}/tenaga-pendukung`),
    },
];

const deleteSelected = async () => {
    if (!selected.value.length) {
        return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
    }

    try {
        const response = await axios.post('/cabor-kategori/destroy-selected', {
            ids: selected.value,
        });

        selected.value = [];
        pageIndex.value.fetchData();

        toast({
            title: response.data?.message,
            variant: 'success',
        });
    } catch (error: any) {
        console.error('Gagal menghapus data:', error);

        const message = error.response?.data?.message;
        toast({
            title: message,
            variant: 'destructive',
        });
    }
};

const deleteKategori = async (row: any) => {
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
        <PageIndex title="Cabor Kategori" :breadcrumbs="breadcrumbs" :columns="columns"
            :create-url="'/cabor-kategori/create'" :actions="actions" :selected="selected"
            @update:selected="(val) => (selected = val)" :on-delete-selected="deleteSelected"
            api-endpoint="/api/cabor-kategori" ref="pageIndex" :on-toast="toast" :on-delete-row="deleteKategori"
            :show-import="false" />
    </div>
</template>