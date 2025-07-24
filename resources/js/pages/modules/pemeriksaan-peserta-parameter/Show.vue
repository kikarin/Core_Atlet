<script setup lang="ts">
import PageShow from '@/pages/modules/base-page/PageShow.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useToast } from '@/components/ui/toast/useToast';

const { toast } = useToast();
const page = usePage();
const pemeriksaan = computed(() => page.props.pemeriksaan || {});
const peserta = computed(() => page.props.peserta || {});
const item = computed(() => page.props.item || {});

const breadcrumbs = [
    { title: 'Pemeriksaan', href: '/pemeriksaan' },
    { title: 'Peserta Pemeriksaan', href: `/pemeriksaan/${pemeriksaan.value.id}/peserta` },
    { title: 'Parameter Peserta', href: `/pemeriksaan/${pemeriksaan.value.id}/peserta/${peserta.value.id}/parameter` },
    { title: 'Detail Parameter Peserta', href: `/pemeriksaan/${pemeriksaan.value.id}/peserta/${peserta.value.id}/parameter/${item.value.id}` },
];

const fields = computed(() => [
    { label: 'Parameter', value: item.value?.parameter || '-' },
    { label: 'Nilai', value: item.value?.nilai ?? '-' },
    { label: 'Trend', value: item.value?.trend || '-' },
    { label: 'Pemeriksaan', value: pemeriksaan.value?.nama_pemeriksaan || '-' },
    { label: 'Peserta', value: peserta.value?.peserta?.nama || '-' },
]);

const actionFields = [
    { label: 'Created At', value: item.value.created_at ? new Date(item.value.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) : '-' },
    { label: 'Updated At', value: item.value.updated_at ? new Date(item.value.updated_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) : '-' },
];

const handleDelete = () => {
    router.delete(`/pemeriksaan/${pemeriksaan.value.id}/peserta/${peserta.value.id}/parameter/${item.value.id}`, {
        onSuccess: () => {
            toast({ title: 'Data parameter peserta berhasil dihapus', variant: 'success' });
            router.visit(`/pemeriksaan/${pemeriksaan.value.id}/peserta/${peserta.value.id}/parameter`);
        },
        onError: () => {
            toast({ title: 'Gagal menghapus data parameter peserta', variant: 'destructive' });
        },
    });
};
</script>

<template>
    <PageShow
        title="Parameter Peserta"
        :breadcrumbs="breadcrumbs"
        :fields="fields"
        :actionFields="actionFields"
        :back-url="`/pemeriksaan/${pemeriksaan.id}/peserta/${peserta.id}/parameter`"
        :on-edit="() => router.visit(`/pemeriksaan/${pemeriksaan.id}/peserta/${peserta.id}/parameter/${item.id}/edit`)"
        :on-delete="handleDelete"
    />
</template> 