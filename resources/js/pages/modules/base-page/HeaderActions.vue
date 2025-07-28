<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    title: string;
    createUrl?: string;
    createMultipleUrl?: string;
    showMultipleButton?: boolean;
    selected: number[];
    onDeleteSelected: () => void;
    showImport: boolean;
    showKehadiran?: boolean;
    showKelola?: boolean;
    kelolaUrl?: string;
}>();
</script>

<template>
    <div class="mx-auto flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-xl font-semibold tracking-tight dark:text-white">
            {{ title }}
        </h1>

        <div class="flex flex-wrap items-center gap-2">
            <Button v-if="props.showImport" variant="secondary" size="sm" @click="$emit('import')"> Import Excel </Button>

            <!-- Button Tambah Multiple -->
            <Link v-if="props.showMultipleButton && props.createMultipleUrl" :href="props.createMultipleUrl">
                <Button variant="outline" size="sm">+ Tambah Multiple</Button>
            </Link>

            <!-- Button Kelola -->
            <Link v-if="props.showKelola && props.kelolaUrl" :href="props.kelolaUrl">
                <Button variant="outline" size="sm">Pemetaan</Button>
            </Link>

            <Link v-if="props.createUrl" :href="props.createUrl">
                <Button variant="outline" size="sm">+ Create</Button>
            </Link>

            <!-- Dropdown Set Kehadiran pakai shadcn-vue -->
            <DropdownMenu v-if="props.showKehadiran">
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" size="sm" :disabled="selected.length === 0" class="mr-2" type="button"> Set Kehadiran </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="$emit('setKehadiran', 'Hadir')">Hadir</DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('setKehadiran', 'Tidak Hadir')">Tidak Hadir</DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('setKehadiran', 'Izin')">Izin</DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('setKehadiran', 'Sakit')">Sakit</DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <Button variant="destructive" size="sm" :disabled="selected.length === 0" @click="onDeleteSelected">
                Delete Selected ({{ selected.length }})
            </Button>
        </div>
    </div>
</template>
