<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import AppTabs from '@/components/AppTabs.vue';
import HeaderForm from './HeaderForm.vue';
import { type BreadcrumbItem } from '@/types';

const props = withDefaults(defineProps<{
  title: string;
  breadcrumbs: BreadcrumbItem[];
  backUrl?: string;
  showEditPrefix?: boolean; 
  tabsConfig?: {
    value: string;
    label: string;
    component: any;
    props?: Record<string, any>;
    disabled?: boolean;
    onSave?: (form: any, setFormErrors: (errors: Record<string, string>) => void) => Promise<any>;
  }[];
  activeTabValue?: string;
}>(), {
  showEditPrefix: true,
});


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

          <div v-if="tabsConfig?.length" class="mb-4">
            <AppTabs
              :tabs="tabsConfig"
              :model-value="activeTabValue"
              @update:model-value="handleTabChange"
              :default-value="tabsConfig[0]?.value"
            />
          </div>

          <Card class="w-full">
            <HeaderForm
              :title="props.title"
              :back-url="props.backUrl"
              :is-edit="true"
              :show-edit-prefix="props.showEditPrefix"
            />
            <CardContent>
              <template v-if="tabsConfig && tabsConfig.length">
                <component
                  :is="tabsConfig.find(tab => tab.value === activeTabValue)?.component"
                  v-bind="tabsConfig.find(tab => tab.value === activeTabValue)?.props"
                  @save="tabsConfig.find(tab => tab.value === activeTabValue)?.onSave"
                />
              </template>
              <template v-else>
                <slot />
              </template>
            </CardContent>
          </Card>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

