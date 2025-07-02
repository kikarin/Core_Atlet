<script setup lang="ts">
import PageEdit from '@/pages/modules/base-page/PageEdit.vue';
// import AppTabs from '@/components/AppTabs.vue'; // Remove direct import
import Form from './Form.vue';
import FormOrangTua from './FormOrangTua.vue';
import FormSertifikat from './FormSertifikat.vue';
import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps<{ item: Record<string, any> }>();

const atletId = ref<number | null>(props.item.id || null);

// Ambil tab dari query string
function getTabFromUrl(url: string, fallback = 'atlet-data') {
  if (url.includes('tab=')) {
    return new URLSearchParams(url.split('?')[1]).get('tab') || fallback;
  }
  return fallback;
}

const page = usePage();
const initialActiveTab = getTabFromUrl(page.url);
const activeTab = ref(initialActiveTab);

watch(activeTab, (val) => {
  const url = `/atlet/${props.item.id}/edit?tab=${val}`;
  router.visit(url, { replace: true, preserveState: true, preserveScroll: true, only: [] });});

watch(
  () => page.url,
  (newUrl) => {
    const tab = getTabFromUrl(newUrl);
    if (tab !== activeTab.value) {
      activeTab.value = tab;
    }
  }
);

// Computed property untuk judul dinamis
const dynamicTitle = computed(() => {
    if (activeTab.value === 'atlet-data') {
        return `Atlet : ${props.item.nama || '-'}`;
    } else if (activeTab.value === 'orang-tua-data') {
        return `Orang Tua/Wali : ${props.item.nama || '-'}`;
    }
    else if (activeTab.value === 'sertifikat-data') {
        return `Sertifikat : ${props.item.nama || '-'}`;
    }
    return 'Edit Atlet';
});

const breadcrumbs = computed(() => [
    { title: 'Atlet', href: '/atlet' },
    { title: 'Data Atlet', href: `/atlet/${props.item.id}/edit` },
]);

// Configuration for tabs
const tabsConfig = computed(() => [
    {
        value: 'atlet-data',
        label: 'Atlet',
        component: Form,
        props: { mode: 'edit', initialData: props.item },
    },
    {
        value: 'orang-tua-data',
        label: 'Orang Tua/Wali',
        component: FormOrangTua,
        props: { atletId: atletId.value, mode: 'edit' },
    },
    {
        value: 'sertifikat-data',
        label: 'Sertifikat',
        component: FormSertifikat,
        props: { atletId: atletId.value, mode: 'edit' },
    },
]);


</script>

<template>
    <PageEdit 
        :title="dynamicTitle" 
        :breadcrumbs="breadcrumbs" 
        back-url="/atlet"
        :tabs-config="tabsConfig"
        v-model:activeTabValue="activeTab"
        :show-edit-prefix="false"
    />
</template> 