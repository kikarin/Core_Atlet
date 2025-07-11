<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import ConfirmDialog from '@/pages/modules/components/ConfirmDialog.vue';

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
    { title: 'Daftar Pelatih', href: `/cabor-kategori/${props.caborKategori.id}/pelatih` },
];

const showConfirmDelete = ref(false);
const rowToDelete = ref<any>(null);

const columns = [
    { key: 'pelatih_nama', label: 'Nama Pelatih' },
    { key: 'pelatih_nik', label: 'NIK' },
    { key: 'jenis_pelatih_nama', label: 'Jenis Pelatih' },
    { key: 'cabor_nama', label: 'Cabor' },
    { key: 'cabor_kategori_nama', label: 'Kategori' },
    {
        key: 'created_at',
        label: 'Tanggal Ditambahkan',
        format: (row: any) => {
            if (!row.created_at) return '-';
            const date = new Date(row.created_at);
            return date.toLocaleString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
        },
    },
];

const selected = ref<number[]>([]);

const pageIndex = ref();

const { toast } = useToast();

const actions = (row: any) => [
    {
        label: 'Detail Pelatih',
        onClick: () => router.visit(`/pelatih/${row.pelatih_id}`),
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
        const response = await axios.post('/cabor-kategori-pelatih/destroy-selected', {
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

const deletePelatih = async (row: any) => {
    try {
        await axios.delete(`/cabor-kategori-pelatih/${row.id}`);
        toast({ title: 'Data berhasil dihapus', variant: 'success' });
        pageIndex.value.fetchData();
    } catch  {
        toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
    }
    showConfirmDelete.value = false;
    rowToDelete.value = null;
};
</script>

<template>

        <PageIndex
            title="Daftar Pelatih"
            :breadcrumbs="breadcrumbs"
            :columns="columns"
            :actions="actions"
            :selected="selected"
            @update:selected="(val) => (selected = val)"
            :on-delete-selected="deleteSelected"
            :api-endpoint="`/api/cabor-kategori-pelatih?cabor_kategori_id=${caborKategori.id}`"
            ref="pageIndex"
            :on-toast="toast"
            :on-delete-row="deletePelatih"
            :show-import="false"
            :show-create="false"
            :show-multiple-button="true"
            :create-multiple-url="`/cabor-kategori/${caborKategori.id}/pelatih/create-multiple`"
            create-url=""
        />
    <ConfirmDialog
        :show="showConfirmDelete"
        title="Konfirmasi Hapus"
        description="Yakin ingin menghapus pelatih ini dari kategori?"
        @confirm="() => deletePelatih(rowToDelete)"
        @cancel="() => { showConfirmDelete = false; rowToDelete = null; }"
    />
</template> 