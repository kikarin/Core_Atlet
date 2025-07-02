<script setup lang="ts">
import PageEdit from '@/pages/modules/base-page/PageEdit.vue';
// import AppTabs from '@/components/AppTabs.vue'; // Remove direct import
import Form from './Form.vue';
import FormOrangTua from './FormOrangTua.vue';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{ item: Record<string, any> }>();

const atletId = ref<number | null>(props.item.id || null);

// Menggunakan usePage untuk mengakses props Inertia, termasuk flash messages
const page = usePage();
const initialActiveTab = (page.props.flash as any)?.success ? 'orang-tua-data' : 'atlet-data';
const activeTab = ref(initialActiveTab);

// Computed property untuk judul dinamis
const dynamicTitle = computed(() => {
    if (activeTab.value === 'atlet-data') {
        return `: Atlet ${props.item.nama || '-'}`;
    } else if (activeTab.value === 'orang-tua-data') {
        return `: Orang Tua/Wali ${props.item.nama || '-'}`;
    }
    return 'Edit Atlet';
});

const breadcrumbs = computed(() => [
    { title: 'Atlet', href: '/atlet' },
    { title: 'Edit Atlet', href: `/atlet/${props.item.id}/edit` },
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
]);

</script>

<template>
    <PageEdit 
        :title="dynamicTitle" 
        :breadcrumbs="breadcrumbs" 
        back-url="/atlet"
        :tabs-config="tabsConfig"
        v-model:activeTabValue="activeTab"
    />
</template> 