<script setup lang="ts">
import PageCreate from '@/pages/modules/base-page/PageCreate.vue';
import Form from './Form.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const pemeriksaan = computed(() => page.props.pemeriksaan as any);
const jenisPeserta = computed((): string => {
  const jenis = (page.props.jenis_peserta as string) || '';
  if (["atlet", "pelatih", "tenaga-pendukung"].includes(jenis)) return jenis;
  return 'atlet';
});

const breadcrumbs = computed(() => [
    { title: 'Pemeriksaan', href: '/pemeriksaan' },
    { title: pemeriksaan.value.nama_pemeriksaan, href: `/pemeriksaan/${pemeriksaan.value.id}/peserta` },
    { title: 'Tambah Peserta', href: '#' },
]);

</script>

<template>
    <PageCreate
        title="Tambah Peserta Pemeriksaan"
        :breadcrumbs="breadcrumbs"
        :back-url="`/pemeriksaan/${pemeriksaan.id}/peserta`"
    >
        <Form mode="create" :pemeriksaan="pemeriksaan" :jenis-peserta="jenisPeserta" />
    </PageCreate>
</template> 