<script setup lang="ts">
import PageCreate from '@/pages/modules/base-page/PageCreate.vue';
// import AppTabs from '@/components/AppTabs.vue'; // Remove direct import
import Form from './Form.vue';
import FormOrangTua from './FormOrangTua.vue';
import FormSertifikat from './FormSertifikat.vue';
import { ref, watch, computed } from 'vue';

const activeTab = ref('atlet-data');

// Computed property untuk judul dinamis
const dynamicTitle = computed(() => {
    if (activeTab.value === 'atlet-data') {
        return 'Create Atlet';
    } else if (activeTab.value === 'orang-tua-data') {
        return 'Create Atlet Orang Tua/Wali';
    }
    return 'Create Atlet';
});

// Computed property untuk breadcrumbs dinamis
const breadcrumbs = computed(() => [
    { title: 'Atlet', href: '/atlet' },
    { title: dynamicTitle.value, href: '/atlet/create' },
]);

// Configuration for tabs
const tabsConfig = computed(() => [
    {
        value: 'atlet-data',
        label: 'Data Atlet',
        component: Form,
        props: { mode: 'create', initialData: {} },
    },
    {
        value: 'orang-tua-data',
        label: 'Data Orang Tua/Wali',
        component: FormOrangTua,
        props: { atletId: null, mode: 'create' },
        disabled: true,
    },
    {
        value: 'sertifikat-data',
        label: 'Sertifikat',
        component: FormSertifikat,
        props: { atletId: null, mode: 'create' },
        disabled: true,
    },
]);

</script>

<template>
    <PageCreate 
        :title="dynamicTitle" 
        :breadcrumbs="breadcrumbs" 
        back-url="/atlet"
        :tabs-config="tabsConfig"
        v-model:activeTabValue="activeTab"
    />
</template> 