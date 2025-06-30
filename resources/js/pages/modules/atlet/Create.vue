<script setup lang="ts">
import PageCreate from '@/pages/modules/base-page/PageCreate.vue';
import Form from './Form.vue';

const breadcrumbs = [
    { title: 'Atlet', href: '/atlet' },
    { title: 'Create Atlet', href: '/atlet/create' },
];
</script>

<template>
    <PageCreate title="Create Atlet" :breadcrumbs="breadcrumbs" back-url="/atlet">
        <Form mode="create" />
    </PageCreate>
</template> 