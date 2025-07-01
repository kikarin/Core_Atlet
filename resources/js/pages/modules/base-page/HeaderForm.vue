<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { CardHeader, CardTitle } from '@/components/ui/card';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    title: string;
    backUrl?: string;
    isEdit?: boolean; 
}>();

const handleBack = () => {
    if (props.backUrl) {
        router.visit(props.backUrl);
    } else {
        router.visit('/');
    }
};
</script>

<template>
    <CardHeader class="flex items-center justify-between">
        <CardTitle class="text-xl">
            <span v-if="isEdit">Edit </span>{{ title }}
        </CardTitle>
        <Button variant="secondary" @click="handleBack"> ← Kembali </Button>
    </CardHeader>
</template> 