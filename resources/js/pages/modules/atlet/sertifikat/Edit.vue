<script setup lang="ts">
import PageEdit from '@/pages/modules/base-page/PageEdit.vue';
import FormSertifikat from './FormSertifikat.vue';
import { computed, ref } from 'vue';

const props = defineProps<{ 
  atletId: number;
  item: Record<string, any>;
}>();

const breadcrumbs = computed(() => [
  { title: 'Atlet', href: '/atlet' },
  { title: 'Sertifikat', href: `/atlet/${props.atletId}/sertifikat` },
  { title: 'Edit Sertifikat', href: `/atlet/${props.atletId}/sertifikat/${props.item.id}/edit` },
]);

const tabsConfig = computed(() => [
  {
    value: 'sertifikat-data',
    label: 'Data Sertifikat',
    component: FormSertifikat,
    props: { atletId: props.atletId, mode: 'edit', initialData: props.item },
  },
]);

const activeTab = ref('sertifikat-data');
</script>

<template>
  <PageEdit 
    title="Edit Sertifikat" 
    :breadcrumbs="breadcrumbs" 
    :back-url="`/atlet/${props.atletId}/sertifikat`"
    :tabs-config="tabsConfig"
    v-model:activeTabValue="activeTab"
  />
</template> 