<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { MoreVertical } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    id: string | number;
    baseUrl: string;
    actions?: { label: string; onClick: () => void; permission?: string }[];
    show?: boolean;
    edit?: boolean;
    delete?: boolean;
    onDelete?: () => void;
}>();

const items = computed(() => {
    if (props.actions && props.actions.length > 0) {
        return props.actions.map((action) => ({
            label: action.label,
            action: action.onClick,
            icon: undefined,
        }));
    }

    const links: { label: string; action: () => void; icon?: any }[] = [];

    return links;
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" size="icon">
                <MoreVertical class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent class="w-40">
            <DropdownMenuItem v-for="item in items" :key="item.label" @click="item.action" class="flex items-center gap-2">
                <component :is="item.icon" class="h-4 w-4" v-if="item.icon" />
                {{ item.label }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
