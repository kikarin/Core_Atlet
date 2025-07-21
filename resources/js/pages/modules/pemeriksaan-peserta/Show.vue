<script setup lang="ts">
import PageShow from '@/pages/modules/base-page/PageShow.vue';
import { usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const pemeriksaan = computed(() => page.props.pemeriksaan as any);
const item = computed(() => page.props.item as any);

const breadcrumbs = computed(() => [
    { title: 'Pemeriksaan', href: '/pemeriksaan' },
    { title: pemeriksaan.value.nama_pemeriksaan, href: `/pemeriksaan/${pemeriksaan.value.id}/peserta` },
    { title: 'Detail Peserta', href: '#' },
]);

const fields = computed(() => [
    { label: 'Nama Peserta', value: item.value.peserta?.nama || '-' },
    { label: 'Tipe Peserta', value: item.value.peserta_type?.split('\\').pop() || '-' },
    { label: 'Status Pemeriksaan', value: item.value.status?.nama || '-' },
    { label: 'Catatan Umum', value: item.value.catatan_umum || '-' },
]);

const actionFields = computed(() => [
    { label: 'Created At', value: item.value.created_at ? new Date(item.value.created_at).toLocaleString('id-ID') : '-' },
    { label: 'Created By', value: item.value.created_by_user?.name || '-' },
    { label: 'Updated At', value: item.value.updated_at ? new Date(item.value.updated_at).toLocaleString('id-ID') : '-' },
    { label: 'Updated By', value: item.value.updated_by_user?.name || '-' },
]);

const handleEdit = () => {
    router.visit(`/pemeriksaan/${pemeriksaan.value.id}/peserta/${item.value.id}/edit`);
};

const handleDelete = () => {
    router.delete(`/pemeriksaan/${pemeriksaan.value.id}/peserta/${item.value.id}`, {
        onSuccess: () => {
            router.visit(`/pemeriksaan/${pemeriksaan.value.id}/peserta`);
        },
    });
};

</script>

<template>
    <PageShow
        title="Detail Peserta Pemeriksaan"
        :breadcrumbs="breadcrumbs"
        :fields="fields"
        :actionFields="actionFields"
        :back-url="`/pemeriksaan/${pemeriksaan.id}/peserta`"
        :on-edit="handleEdit"
        :on-delete="handleDelete"
    />
</template> 