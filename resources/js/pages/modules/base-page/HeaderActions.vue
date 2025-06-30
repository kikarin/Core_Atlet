<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    title: string;
    createUrl?: string; // ubah ke opsional
    selected: number[];
    onDeleteSelected: () => void;
}>();
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-semibold tracking-tight">
            {{ title }}
        </h1>

        <div class="flex flex-wrap items-center gap-2">
            <Link v-if="props.createUrl" :href="props.createUrl">
                <Button variant="outline" size="sm">+ Create</Button>
            </Link>

            <Button variant="destructive" size="sm" :disabled="selected.length === 0" @click="onDeleteSelected">
                Delete Selected ({{ selected.length }})
            </Button>
        </div>
    </div>
</template>
