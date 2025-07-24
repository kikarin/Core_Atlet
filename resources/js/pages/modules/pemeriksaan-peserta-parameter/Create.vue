<script setup lang="ts">
import PageCreate from '@/pages/modules/base-page/PageCreate.vue';
import Form from './Form.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const pemeriksaan = computed(() => page.props.pemeriksaan || {});
const peserta = computed(() => page.props.peserta || {});

const breadcrumbs = [
    { title: 'Pemeriksaan', href: '/pemeriksaan' },
    { title: 'Peserta Pemeriksaan', href: `/pemeriksaan/${pemeriksaan.value.id}/peserta` },
    { title: 'Parameter Peserta', href: `/pemeriksaan/${pemeriksaan.value.id}/peserta/${peserta.value.id}/parameter` },
    { title: 'Tambah Parameter Peserta', href: `/pemeriksaan/${pemeriksaan.value.id}/peserta/${peserta.value.id}/parameter/create` },
];
</script>

<template>
    <PageCreate title="Tambah Parameter Peserta" :breadcrumbs="breadcrumbs" :back-url="`/pemeriksaan/${pemeriksaan.id}/peserta/${peserta.id}/parameter`">
        <Form mode="create" :pemeriksaan="pemeriksaan" :peserta="peserta" :parameters="($page.props.parameters || [])" />
    </PageCreate>
</template> 