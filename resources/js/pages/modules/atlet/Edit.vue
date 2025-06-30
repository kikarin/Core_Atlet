<script setup lang="ts">
import PageEdit from '@/pages/modules/base-page/PageEdit.vue';
import Form from './Form.vue';

const props = defineProps<{ item: Record<string, any> }>();

const breadcrumbs = [
    { title: 'Atlet', href: '/atlet' },
    { title: 'Edit Atlet', href: `/atlet/${props.item.id}/edit` },
];
</script>

<template>
    <PageEdit title="Atlet" :breadcrumbs="breadcrumbs" back-url="/atlet">
        <Form mode="edit" :initial-data="item" />
    </PageEdit>
</template> 