<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import AppTabs from '@/components/AppTabs.vue';
import HeaderForm from './HeaderForm.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    title: string;
    breadcrumbs: BreadcrumbItem[];
    backUrl?: string;
    tabsConfig?: {
        value: string;
        label: string;
        component: any;
        props?: Record<string, any>;
        disabled?: boolean;
    }[];
    activeTabValue?: string;
}>();

const emit = defineEmits(['cancel', 'update:activeTabValue']);

const handleTabChange = (value: string) => {
    emit('update:activeTabValue', value);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="grid grid-cols-1 lg:grid-cols-12">
                <div class="col-span-1 lg:col-span-7 lg:col-start-1">
                    <Card class="w-full">
                        <HeaderForm :title="props.title" :back-url="props.backUrl" :is-edit="true" />
                        <CardContent>
                            <div v-if="tabsConfig && tabsConfig.length > 0">
                                <AppTabs
                                    :tabs="tabsConfig"
                                    :model-value="activeTabValue"
                                    @update:model-value="handleTabChange"
                                    :default-value="tabsConfig[0]?.value"
                                />
                            </div>
                            <div v-else>
                                <slot />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
