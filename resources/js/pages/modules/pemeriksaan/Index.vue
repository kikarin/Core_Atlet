<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import BadgeGroup from '../components/BadgeGroup.vue';

const { toast } = useToast();
const breadcrumbs = [{ title: 'Pemeriksaan', href: '/pemeriksaan' }];

const columns = [
    { key: 'peserta', label: 'Peserta' },
    { key: 'parameter', label: 'Parameter' },
    { key: 'cabor', label: 'Cabor' },
    { key: 'cabor_kategori', label: 'Kategori' },
    { key: 'tenaga_pendukung', label: 'Tenaga Pendukung' },
    { key: 'nama_pemeriksaan', label: 'Nama Pemeriksaan' },
    { key: 'tanggal_pemeriksaan', label: 'Tanggal Pemeriksaan' },
    {
        key: 'status',
        label: 'Status',
        format: (row: any) => {
            if (row.status === 'belum') return '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-300 rounded-full">Belum</span>';
            if (row.status === 'sebagian')
                return '<span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Sebagian</span>';
            if (row.status === 'selesai')
                return '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Selesai</span>';
            return row.status;
        },
    },
];

const selected = ref<number[]>([]);

const actions = (row: any) => [
    {
        label: 'Detail',
        onClick: () => router.visit(`/pemeriksaan/${row.id}`),
    },
    {
        label: 'Edit',
        onClick: () => router.visit(`/pemeriksaan/${row.id}/edit`),
    },
    {
        label: 'Delete',
        onClick: () => pageIndex.value.handleDeleteRow(row),
    },
];

const pageIndex = ref();

const deleteSelected = async () => {
    if (!selected.value.length) {
        return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
    }
    try {
        const response = await axios.post('/pemeriksaan/destroy-selected', { ids: selected.value });
        selected.value = [];
        pageIndex.value.fetchData();
        toast({ title: response.data?.message || 'Data berhasil dihapus', variant: 'success' });
    } catch (error: any) {
        toast({ title: error.response?.data?.message || 'Gagal menghapus data', variant: 'destructive' });
    }
};
</script>

<template>
    <PageIndex
        title="Pemeriksaan"
        :breadcrumbs="breadcrumbs"
        :columns="columns"
        :create-url="'/pemeriksaan/create'"
        :actions="actions"
        :selected="selected"
        @update:selected="(val) => (selected = val)"
        :on-delete-selected="deleteSelected"
        api-endpoint="/api/pemeriksaan"
        ref="pageIndex"
        :showImport="false"
    >
        <template #cell-parameter="{ row }">
            <div class="flex justify-center">
                <BadgeGroup
                    :badges="[
                        {
                            value: row.jumlah_parameter || 0,
                            colorClass: 'bg-indigo-100 text-indigo-800 hover:bg-indigo-200',
                            onClick: () => router.visit(`/pemeriksaan/${row.id}/pemeriksaan-parameter`),
                        },
                    ]"
                />
            </div>
        </template>
        <template #cell-peserta="{ row }">
            <BadgeGroup
                :badges="[
                    {
                        label: 'Atlet',
                        value: row.jumlah_atlet || 0,
                        colorClass: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
                        onClick: () => router.visit(`/pemeriksaan/${row.id}/peserta?jenis_peserta=atlet`),
                    },
                    {
                        label: 'Pelatih',
                        value: row.jumlah_pelatih || 0,
                        colorClass: 'bg-green-100 text-green-800 hover:bg-green-200',
                        onClick: () => router.visit(`/pemeriksaan/${row.id}/peserta?jenis_peserta=pelatih`),
                    },
                    {
                        label: 'Tenaga Pendukung',
                        value: row.jumlah_tenaga_pendukung || 0,
                        colorClass: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
                        onClick: () => router.visit(`/pemeriksaan/${row.id}/peserta?jenis_peserta=tenaga-pendukung`),
                    },
                ]"
            />
        </template>
    </PageIndex>
</template>
