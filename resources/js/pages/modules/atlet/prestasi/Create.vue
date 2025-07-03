<script setup lang="ts">
import PageCreate from '@/pages/modules/base-page/PageCreate.vue';
import FormPrestasi from './Form.vue';
import { computed, ref } from 'vue';

const props = defineProps<{ atletId: number }>();

const breadcrumbs = computed(() => [
  { title: 'Atlet', href: '/atlet' },
  { title: 'Prestasi', href: `/atlet/${props.atletId}/prestasi` },
  { title: 'Create Prestasi', href: `/atlet/${props.atletId}/prestasi/create` },
]);

const tabsConfig = computed(() => [
  {
    value: 'prestasi-data',
    label: 'Data Prestasi',
    component: FormPrestasi,
    props: { atletId: props.atletId, mode: 'create', initialData: {} },
  },
]);

const activeTab = ref('prestasi-data');
</script>

<template>
  <PageCreate 
    title="Create Prestasi" 
    :breadcrumbs="breadcrumbs" 
    :back-url="`/atlet/${props.atletId}/prestasi`"
  >
    <FormPrestasi :atlet-id="props.atletId" mode="create" :initial-data="{}" />
  </PageCreate>
</template> 