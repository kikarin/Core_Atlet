<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import ConfirmDialog from '@/pages/modules/components/ConfirmDialog.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps<{
    caborKategori: {
        id: number;
        nama: string;
        cabor: {
            id: number;
            nama: string;
        };
    };
}>();

const breadcrumbs = [
    { title: 'Cabor Kategori', href: '/cabor-kategori' },
    { title: 'Daftar Tenaga Pendukung', href: `/cabor-kategori/${props.caborKategori.id}/tenaga-pendukung` },
];

const showConfirmDelete = ref(false);
const rowToDelete = ref<any>(null);

const columns = [
    { key: 'tenaga_pendukung_nama', label: 'Nama' },
    {
        key: 'foto',
        label: 'Foto',
        format: (row: any) => {
            if (row.foto) {
                return `<div class="cursor-pointer" onclick="window.open('${row.foto}', '_blank')">
                    <img src="${row.foto}" alt="Foto ${row.nama}" class="w-12 h-12 object-cover rounded-full border hover:shadow-md transition-shadow" />
                </div>`;
            }
            return '<div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs">No</div>';
        },
    },
    { key: 'jenis_tenaga_pendukung_nama', label: 'Jenis Tenaga Pendukung' },

    {
        key: 'jenis_kelamin',
        label: 'Jenis Kelamin',
        format: (row: any) => {
            return row.jenis_kelamin === 'L' ? 'Laki-laki' : row.jenis_kelamin === 'P' ? 'Perempuan' : '-';
        },
    },
    { key: 'tempat_lahir', label: 'Tempat Lahir' },
    {
        key: 'tanggal_lahir',
        label: 'Tanggal Lahir',
        format: (row: any) => {
            return row.tanggal_lahir
                ? new Date(row.tanggal_lahir).toLocaleDateString('id-ID', {
                      day: 'numeric',
                      month: 'numeric',
                      year: 'numeric',
                  })
                : '-';
        },
    },
    {
        key: 'is_active',
        label: 'Status',
        format: (row: any) => {
            return row.is_active
                ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>'
                : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Nonaktif</span>';
        },
    },
    {
        key: 'created_at',
        label: 'Tanggal Ditambahkan',
        format: (row: any) => {
            if (!row.created_at) return '-';
            const date = new Date(row.created_at);
            return (
                date.toLocaleString('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                }) + ' WIB'
            );
        },
    },
];

const selected = ref<number[]>([]);

const pageIndex = ref();

const { toast } = useToast();

const actions = (row: any) => [
    {
        label: 'Detail Tenaga Pendukung',
        onClick: () => router.visit(`/tenaga-pendukung/${row.tenaga_pendukung_id}`),
    },
    {
        label: 'Edit Jenis Tenaga Pendukung',
        onClick: () => router.visit(`/cabor-kategori-tenaga-pendukung/${row.id}/edit`),
    },
    {
        label: 'Hapus',
        onClick: () => {
            rowToDelete.value = row;
            showConfirmDelete.value = true;
        },
        variant: 'destructive',
    },
];

const deleteSelected = async () => {
    if (!selected.value.length) {
        return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
    }
    try {
        const response = await axios.post('/cabor-kategori-tenaga-pendukung/destroy-selected', {
            ids: selected.value,
        });
        selected.value = [];
        pageIndex.value.fetchData();
        toast({
            title: response.data?.message || 'Data berhasil dihapus',
            variant: 'success',
        });
    } catch (error: any) {
        const message = error.response?.data?.message || 'Gagal menghapus data';
        toast({
            title: message,
            variant: 'destructive',
        });
    }
};

const deleteTenagaPendukung = async (row: any) => {
    try {
        await axios.delete(`/cabor-kategori-tenaga-pendukung/${row.id}`);
        toast({ title: 'Data berhasil dihapus', variant: 'success' });
        pageIndex.value.fetchData();
    } catch {
        toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
    }
    showConfirmDelete.value = false;
    rowToDelete.value = null;
};
</script>

<template>
    <div class="space-y-4">
        <PageIndex
            title="Daftar Tenaga Pendukung"
            :breadcrumbs="breadcrumbs"
            :columns="columns"
            :actions="actions"
            :selected="selected"
            @update:selected="(val: number[]) => (selected = val)"
            :on-delete-selected="deleteSelected"
            :api-endpoint="`/api/cabor-kategori-tenaga-pendukung?cabor_kategori_id=${caborKategori.id}`"
            ref="pageIndex"
            :on-toast="toast"
            :on-delete-row="deleteTenagaPendukung"
            :show-import="false"
            :show-create="false"
            :show-multiple-button="true"
            :create-multiple-url="`/cabor-kategori/${caborKategori.id}/tenaga-pendukung/create-multiple`"
            create-url=""
        >
            <template #header-extra>
                <div class="bg-card mb-4 rounded-lg border p-4">
                    <h3 class="mb-2 text-lg font-semibold">Informasi Kategori</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-muted-foreground text-sm font-medium">Kategori:</span>
                            <span class="text-sm font-medium">{{ caborKategori.nama }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-muted-foreground text-sm font-medium">Cabor:</span>
                            <span class="text-sm font-medium">{{ caborKategori.cabor.nama }}</span>
                        </div>
                    </div>
                </div>
            </template>
        </PageIndex>
        <ConfirmDialog
            :show="showConfirmDelete"
            title="Konfirmasi Hapus"
            description="Yakin ingin menghapus tenaga pendukung ini dari kategori?"
            @confirm="() => deleteTenagaPendukung(rowToDelete)"
            @cancel="
                () => {
                    showConfirmDelete = false;
                    rowToDelete = null;
                }
            "
        />
    </div>
</template>
