<script setup lang="ts">
import PageEdit from '@/pages/modules/base-page/PageEdit.vue';
import FormPrestasi from './Form.vue';
import { computed, ref } from 'vue';

const props = defineProps<{
  atletId: number;
  item: Record<string, any>;
}>();

const breadcrumbs = computed(() => [
  { title: 'Atlet', href: '/atlet' },
  { title: 'Prestasi', href: `/atlet/${props.atletId}/prestasi` },
  { title: 'Edit Prestasi', href: `/atlet/${props.atletId}/prestasi/${props.item.id}/edit` },
]);

const tabsConfig = computed(() => [
  {
    value: 'prestasi-data',
    label: 'Data Prestasi',
    component: FormPrestasi,
    props: { atletId: props.atletId, mode: 'edit', initialData: props.item },
  },
]);

const activeTab = ref('prestasi-data');
</script>

<template>
  <PageEdit 
    title="Prestasi" 
    :breadcrumbs="breadcrumbs" 
    :back-url="`/atlet/${props.atletId}/prestasi`"
  >
    <FormPrestasi :atlet-id="props.atletId" mode="edit" :initial-data="props.item" />
  </PageEdit>
</template> 