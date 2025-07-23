<script setup lang="ts">
import PageCreate from '@/pages/modules/base-page/PageCreate.vue';
import Form from './Form.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const pemeriksaan = computed(() => page.props.pemeriksaan || {});
const pemeriksaanId = computed(() => pemeriksaan.value.id);

const breadcrumbs = [
    { title: 'Pemeriksaan', href: '/pemeriksaan' },
    { title: pemeriksaan.value?.nama_pemeriksaan || 'Peserta', href: `/pemeriksaan/${pemeriksaanId.value}/peserta` },
    { title: 'Tambah Peserta', href: `/pemeriksaan/${pemeriksaanId.value}/peserta/create` },
];
</script>

<template>
    <PageCreate 
        title="Tambah Peserta" 
        :breadcrumbs="breadcrumbs" 
        :back-url="`/pemeriksaan/${pemeriksaanId}/peserta`"
    >
        <Form 
            mode="create" 
            :pemeriksaan="pemeriksaan"
        />
    </PageCreate>
</template>
