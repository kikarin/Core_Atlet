<script setup lang="ts">
import PageCreate from '@/pages/modules/base-page/PageCreate.vue';
import FormSertifikat from './FormSertifikat.vue';
import { computed, ref } from 'vue';

const props = defineProps<{ atletId: number }>();

const breadcrumbs = computed(() => [
  { title: 'Atlet', href: '/atlet' },
  { title: 'Sertifikat', href: `/atlet/${props.atletId}/sertifikat` },
  { title: 'Create Sertifikat', href: `/atlet/${props.atletId}/sertifikat/create` },
]);

const tabsConfig = computed(() => [
  {
    value: 'sertifikat-data',
    label: 'Data Sertifikat',
    component: FormSertifikat,
    props: { atletId: props.atletId, mode: 'create', initialData: {} },
  },
]);

const activeTab = ref('sertifikat-data');
</script>

<template>
  <PageCreate 
    title="Create Sertifikat" 
    :breadcrumbs="breadcrumbs" 
    :back-url="`/atlet/${props.atletId}/sertifikat`"
  >
    <FormSertifikat :atlet-id="props.atletId" mode="create" :initial-data="{}" />
  </PageCreate>
</template> 