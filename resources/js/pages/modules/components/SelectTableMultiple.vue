<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import axios from 'axios';

const props = defineProps<{
    label: string;
    endpoint: string;
    columns: { key: string; label: string; format?: (row: any) => string }[];
    idKey: string;
    nameKey: string;
    selectedIds?: number[];
}>();

const emit = defineEmits(['update:selectedIds']);

const items = ref<any[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);
const localSelected = ref<number[]>(props.selectedIds ? [...props.selectedIds] : []);

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get(props.endpoint, {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchQuery.value,
            },
        });
        items.value = response.data.data || [];
        total.value = response.data.meta?.total || 0;
    } catch {
        items.value = [];
        total.value = 0;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);
watch([searchQuery, perPage], () => {
    currentPage.value = 1;
    fetchData();
});
watch(currentPage, fetchData);
watch(() => props.endpoint, fetchData);

watch(localSelected, (val) => {
    emit('update:selectedIds', [...val]);
});
watch(() => props.selectedIds, (val) => {
    if (val && JSON.stringify(val) !== JSON.stringify(localSelected.value)) {
        localSelected.value = [...val];
    }
});

const toggleSelect = (id: number) => {
    const idx = localSelected.value.indexOf(id);
    if (idx > -1) {
        localSelected.value = localSelected.value.filter((v) => v !== id);
    } else {
        localSelected.value = [...localSelected.value, id];
    }
};
const toggleSelectAll = (checked: boolean) => {
    if (checked) {
        localSelected.value = items.value.map((item) => item[props.idKey]);
    } else {
        localSelected.value = [];
    }
};
const isSelected = (id: number) => localSelected.value.includes(id);

const totalPages = computed(() => Math.ceil(total.value / perPage.value));
const getPageNumbers = () => {
    const pages = [];
    const maxPages = 5;
    let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
    const end = Math.min(totalPages.value, start + maxPages - 1);
    if (end - start + 1 < maxPages) {
        start = Math.max(1, end - maxPages + 1);
    }
    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    return pages;
};
</script>
<template>
    <div class="space-y-6">
        <div class="flex items-center gap-2 mb-1">
            <span class="font-semibold">{{ label }}</span>
            <Badge variant="secondary">{{ localSelected.length }} dipilih</Badge>
        </div>
        <div class="flex flex-col flex-wrap items-center justify-center gap-4 text-center sm:flex-row sm:justify-between mb-2">
            <div class="ml-2 flex items-center gap-2">
                <span class="text-muted-foreground text-sm">Show</span>
                <Select v-model="perPage">
                    <SelectTrigger class="w-24">
                        <SelectValue :placeholder="String(perPage)" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="10">10</SelectItem>
                        <SelectItem :value="25">25</SelectItem>
                        <SelectItem :value="50">50</SelectItem>
                        <SelectItem :value="100">100</SelectItem>
                    </SelectContent>
                </Select>
                <span class="text-muted-foreground text-sm">entries</span>
            </div>
            <div class="w-full sm:w-64">
                <Input v-model="searchQuery" placeholder="Search..." class="w-full" />
            </div>
        </div>
        <div v-if="loading" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <span class="ml-2 text-sm text-muted-foreground">Memuat data...</span>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-8">
            <p class="text-muted-foreground">Tidak ada data tersedia</p>
        </div>
        <div v-else class="rounded-md shadow-sm">
            <div class="w-full overflow-x-auto">
                <Table class="min-w-max">
                    <TableHeader class="bg-muted">
                        <TableRow>
                            <TableHead class="w-12 text-center">No</TableHead>
                            <TableHead class="w-10 text-center">
                                <label class="bg-background relative inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded border border-gray-500">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        :checked="localSelected.length > 0 && localSelected.length === items.length"
                                        @change="(e) => toggleSelectAll((e.target as HTMLInputElement).checked)"
                                    />
                                    <div class="bg-primary h-3 w-3 scale-0 transform rounded-sm transition-all peer-checked:scale-100"></div>
                                </label>
                            </TableHead>
                            <TableHead v-for="col in props.columns" :key="col.key" class="cursor-pointer select-none">
                                <div class="flex items-center gap-1">{{ col.label }}</div>
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(item, index) in items" :key="item[props.idKey]" class="hover:bg-muted/40 border-t transition">
                            <TableCell class="text-center text-xs sm:text-sm px-2 sm:px-4 whitespace-normal break-words">
                                {{ (currentPage - 1) * perPage + index + 1 }}
                            </TableCell>
                            <TableCell class="text-center text-xs sm:text-sm px-2 sm:px-4 whitespace-normal break-words">
                                <label class="bg-background relative inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded border border-gray-500">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        :checked="isSelected(item[props.idKey])"
                                        @change="() => toggleSelect(item[props.idKey])"
                                    />
                                    <svg
                                        class="text-primary h-4 w-4 scale-75 opacity-0 transition-all duration-200 peer-checked:scale-100 peer-checked:opacity-100"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </label>
                            </TableCell>
                            <TableCell v-for="col in props.columns" :key="col.key">
                                <span v-if="typeof col.format === 'function'" v-html="col.format(item)"></span>
                                <span v-else>{{ item[col.key] }}</span>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div class="text-muted-foreground flex flex-col items-center justify-center gap-2 border-t p-4 text-center text-sm md:flex-row md:justify-between">
                <span>
                    Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of
                    {{ total }} entries
                </span>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <Button size="sm" :disabled="currentPage === 1" @click="currentPage--" class="bg-muted/40 text-foreground">
                        Previous
                    </Button>
                    <div class="flex flex-wrap items-center gap-1">
                        <Button
                            v-for="page in getPageNumbers()"
                            :key="page"
                            size="sm"
                            class="rounded-md border px-3 py-1.5 text-sm"
                            :class="[
                                currentPage === page
                                    ? 'bg-primary text-primary-foreground border-primary'
                                    : 'bg-muted border-input text-black dark:text-white',
                            ]"
                            @click="currentPage = page"
                        >
                            {{ page }}
                        </Button>
                    </div>
                    <Button
                        size="sm"
                        :disabled="currentPage === totalPages"
                        @click="currentPage++"
                        class="bg-muted/40 text-foreground"
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template> 