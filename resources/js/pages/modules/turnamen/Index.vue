<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import BadgeGroup from '../components/BadgeGroup.vue';

const breadcrumbs = [{ title: 'Turnamen', href: '/turnamen' }];

const columns = [
    { key: 'nama', label: 'Nama Turnamen' },
    { key: 'cabor_kategori_nama', label: 'Cabor Kategori', orderable: false },
    {
        key: 'tanggal_mulai',
        label: 'Tanggal Mulai',
        format: (row: any) => {
            if (!row.tanggal_mulai) return '-';
            const date = new Date(row.tanggal_mulai);
            const options: Intl.DateTimeFormatOptions = {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            };
            return date.toLocaleDateString('id-ID', options);
        },
    },
    {
        key: 'tanggal_selesai',
        label: 'Tanggal Selesai',
        format: (row: any) => {
            if (!row.tanggal_selesai) return '-';
            const date = new Date(row.tanggal_selesai);
            const options: Intl.DateTimeFormatOptions = {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            };
            return date.toLocaleDateString('id-ID', options);
        },
    },
    { key: 'tingkat_nama', label: 'Tingkat', orderable: false },
    { key: 'lokasi', label: 'Lokasi' },
    { key: 'juara_nama', label: 'Juara', orderable: false },
    { key: 'hasil', label: 'Hasil', orderable: false },
    {
        key: 'peserta',
        label: 'Peserta',
        orderable: false,
    },
];

const selected = ref<number[]>([]);

const pageIndex = ref();

const { toast } = useToast();

const actions = (row: any) => [
    {
        label: 'Detail',
        onClick: () => router.visit(`/turnamen/${row.id}`),
    },
    {
        label: 'Edit',
        onClick: () => router.visit(`/turnamen/${row.id}/edit`),
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
        const response = await axios.post('/turnamen/destroy-selected', {
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

const deleteRow = async (row: any) => {
    await router.delete(`/turnamen/${row.id}`, {
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
            title="Turnamen"
            :breadcrumbs="breadcrumbs"
            :columns="columns"
            :create-url="'/turnamen/create'"
            :actions="actions"
            :selected="selected"
            @update:selected="(val: number[]) => (selected = val)"
            :on-delete-selected="deleteSelected"
            api-endpoint="/api/turnamen"
            ref="pageIndex"
            :on-toast="toast"
            :on-delete-row="deleteRow"
            :show-import="false"
        >
            <template #cell-peserta="{ row }">
                <BadgeGroup
                    :badges="[
                        {
                            label: 'Atlet',
                            value: row.peserta_counts?.atlet || 0,
                            colorClass: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
                            onClick: () => router.visit(`/turnamen/${row.id}/peserta?jenis_peserta=atlet`),
                        },
                        {
                            label: 'Pelatih',
                            value: row.peserta_counts?.pelatih || 0,
                            colorClass: 'bg-green-100 text-green-800 hover:bg-green-200',
                            onClick: () => router.visit(`/turnamen/${row.id}/peserta?jenis_peserta=pelatih`),
                        },
                        {
                            label: 'Tenaga Pendukung',
                            value: row.peserta_counts?.tenaga_pendukung || 0,
                            colorClass: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
                            onClick: () => router.visit(`/turnamen/${row.id}/peserta?jenis_peserta=tenaga-pendukung`),
                        },
                    ]"
                />
            </template>
        </PageIndex>
    </div>
</template>
