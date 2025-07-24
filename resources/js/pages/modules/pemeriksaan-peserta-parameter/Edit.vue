<script setup lang="ts">
import PageEdit from '@/pages/modules/base-page/PageEdit.vue';
import Form from './Form.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const pemeriksaan = computed(() => page.props.pemeriksaan || {});
const peserta = computed(() => page.props.peserta || {});
const item = computed(() => page.props.item || {});

const breadcrumbs = [
    { title: 'Pemeriksaan', href: '/pemeriksaan' },
    { title: 'Peserta Pemeriksaan', href: `/pemeriksaan/${pemeriksaan.value.id}/peserta` },
    { title: 'Parameter Peserta', href: `/pemeriksaan/${pemeriksaan.value.id}/peserta/${peserta.value.id}/parameter` },
    { title: 'Edit Parameter Peserta', href: `/pemeriksaan/${pemeriksaan.value.id}/peserta/${peserta.value.id}/parameter/${item.value.id}/edit` },
];
</script>

<template>
    <PageEdit title="Edit Parameter Peserta" :breadcrumbs="breadcrumbs" :back-url="`/pemeriksaan/${pemeriksaan.id}/peserta/${peserta.id}/parameter`">
        <Form mode="edit" :pemeriksaan="pemeriksaan" :peserta="peserta" :initial-data="item" :parameters="($page.props.parameters || [])" />
    </PageEdit>
</template> 