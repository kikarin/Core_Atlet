<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageShow from '@/pages/modules/base-page/PageShow.vue';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const { toast } = useToast();

const props = defineProps<{
    item: {
        id: number;
        nik: string;
        nama: string;
        jenis_kelamin: string;
        tempat_lahir: string;
        tanggal_lahir: string;
        alamat: string;
        kecamatan_id: number | null;
        kelurahan_id: number | null;
        no_hp: string;
        email: string;
        is_active: number;
        foto: string;
        created_at: string;
        created_by_user: {
            id: number;
            name: string;
        } | null;
        updated_at: string;
        updated_by_user: {
            id: number;
            name: string;
        } | null;
    };
}>();

const atlet = computed(() => props.item);

const breadcrumbs = [
    { title: 'Atlet', href: '/atlet' },
    { title: 'Detail Atlet', href: `/atlet/${props.item.id}` },
];

const fields = computed(() => {
    return [
        { label: 'NIK', value: atlet.value?.nik || '-' },
        { label: 'Nama', value: atlet.value?.nama || '-' },
        {
            label: 'Jenis Kelamin',
            value: atlet.value?.jenis_kelamin === 'L' ? 'Laki-laki' : atlet.value?.jenis_kelamin === 'P' ? 'Perempuan' : '-',
            className: atlet.value?.jenis_kelamin === 'L' ? 'text-blue-600' : atlet.value?.jenis_kelamin === 'P' ? 'text-pink-600' : '',
        },
        { label: 'Tempat Lahir', value: atlet.value?.tempat_lahir || '-' },
        {
            label: 'Tanggal Lahir',
            value: atlet.value?.tanggal_lahir
                ? new Date(atlet.value.tanggal_lahir).toLocaleDateString('id-ID', {
                      day: 'numeric',
                      month: 'numeric',
                      year: 'numeric',
                  })
                : '-',
        },
        { label: 'Alamat', value: atlet.value?.alamat || '-', className: 'sm:col-span-2' },
        { label: 'Kecamatan', value: atlet.value?.kecamatan_id ? `ID: ${atlet.value.kecamatan_id}` : '-' },
        { label: 'Kelurahan', value: atlet.value?.kelurahan_id ? `ID: ${atlet.value.kelurahan_id}` : '-' },
        { label: 'No HP', value: atlet.value?.no_hp || '-' },
        { label: 'Email', value: atlet.value?.email || '-' },
        {
            label: 'Status',
            value: atlet.value?.is_active ? 'Aktif' : 'Nonaktif',
            className: atlet.value?.is_active ? 'text-green-600' : 'text-red-600',
        },
        {
            label: 'Foto',
            value: atlet.value?.foto || '',
            type: 'image' as const,
            className: 'sm:col-span-2',
            imageConfig: {
                size: 'md' as const,
                labelText: 'Klik untuk melihat lebih besar'
            }
        },
    ];
});

const actionFields = [
    { label: 'Created At', value: new Date(props.item.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) },
    { label: 'Created By', value: props.item.created_by_user?.name || '-' },
    { label: 'Updated At', value: new Date(props.item.updated_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) },
    { label: 'Updated By', value: props.item.updated_by_user?.name || '-' },
];

const handleEdit = () => {
    router.visit(`/atlet/${props.item.id}/edit`);
};

const handleDelete = () => {
    router.delete(`/atlet/${props.item.id}`, {
        onSuccess: () => {
            toast({ title: 'Atlet berhasil dihapus', variant: 'success' });
            router.visit('/atlet');
        },
        onError: () => {
            toast({ title: 'Gagal menghapus atlet', variant: 'destructive' });
        },
    });
};

console.log('Show data:', props.item);
</script>

<template>
    <PageShow
        title="Atlet"
        :breadcrumbs="breadcrumbs"
        :fields="fields"
        :actionFields="actionFields"
        :back-url="'/atlet'"
        :on-edit="handleEdit"
        :on-delete="handleDelete"
    />
</template>
