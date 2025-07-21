<script setup lang="ts">
import PageEdit from '@/pages/modules/base-page/PageEdit.vue';
import Form from './Form.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const pemeriksaan = computed(() => page.props.pemeriksaan as any);
const item = computed(() => page.props.item as any);

const breadcrumbs = computed(() => [
    { title: 'Pemeriksaan', href: '/pemeriksaan' },
    { title: pemeriksaan.value.nama_pemeriksaan, href: `/pemeriksaan/${pemeriksaan.value.id}/peserta` },
    { title: 'Edit Peserta', href: '#' },
]);

</script>

<template>
    <PageEdit
        title="Edit Peserta Pemeriksaan"
        :breadcrumbs="breadcrumbs"
        :back-url="`/pemeriksaan/${pemeriksaan.id}/peserta`"
    >
        <Form mode="edit" :pemeriksaan="pemeriksaan" :initial-data="item"/>
    </PageEdit>
</template> 