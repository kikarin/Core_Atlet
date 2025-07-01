<script setup lang="ts">
import { ref, watch } from 'vue';
import Tabs from "../components/ui/tabs/Tabs.vue";
import TabsContent from "../components/ui/tabs/TabsContent.vue";
import TabsList from "../components/ui/tabs/TabsList.vue";
import TabsTrigger from "../components/ui/tabs/TabsTrigger.vue";

const props = defineProps<{
    tabs: {
        value: string;
        label: string;
        component?: any;
        props?: Record<string, any>;
        disabled?: boolean;
        onSave?: (form: any, setFormErrors: (errors: Record<string, string>) => void) => Promise<any>;
    }[];
    defaultValue: string;
    modelValue?: string; 
}>();

const emit = defineEmits(['update:modelValue', 'tab-change']);

const activeTab = ref(props.modelValue || props.defaultValue);

watch(() => props.modelValue, (newVal) => {
    if (newVal && newVal !== activeTab.value) {
        activeTab.value = newVal;
    }
});

watch(activeTab, (newVal) => {
    emit('update:modelValue', newVal);
    emit('tab-change', newVal);
});

const handleTabChange = (tabValue: string) => {
    if (!props.tabs.find(t => t.value === tabValue)?.disabled) {
        activeTab.value = tabValue;
    }
};
</script>

<template>
  <Tabs :model-value="activeTab" @update:model-value="handleTabChange" class="w-full">
    <!-- Tabs List -->
    <TabsList
      class="flex w-full rounded-md border bg-muted overflow-x-auto flex-nowrap"
    >
      <TabsTrigger
        v-for="tab in tabs"
        :key="tab.value"
        :value="tab.value"
        :disabled="tab.disabled"
        class="flex-shrink-0" 
      >
        {{ tab.label }}
      </TabsTrigger>
    </TabsList>

    <!-- Tabs Content -->
    <TabsContent
      v-for="tab in tabs"
      :key="tab.value"
      :value="tab.value"
    >
      <div class="mt-4 border-t pt-4">
        <component :is="tab.component" v-bind="tab.props" @save="tab.onSave" />
      </div>
    </TabsContent>
  </Tabs>
</template>
