<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useToast } from '@/components/ui/toast/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import debounce from 'lodash.debounce';
import { onMounted, ref, watch } from 'vue';
import DataTable from '../components/DataTable.vue';
import HeaderActions from './HeaderActions.vue';

const { toast } = useToast();

const tableRows = ref<any[]>([]);
const total = ref(0);
const loading = ref(false);

const page = ref(1);
const localLimit = ref(10);
const search = ref('');
const sort = ref<{ key: string; order: 'asc' | 'desc' }>({ key: '', order: 'asc' });

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get(props.apiEndpoint, {
            params: {
                search: search.value,
                page: localLimit.value === -1 ? undefined : page.value > 1 ? page.value - 1 : 0,
                per_page: props.limit !== undefined ? props.limit : localLimit.value,
                sort: sort.value.key,
                order: sort.value.order,
            },
        });

        tableRows.value = response.data.data;
        const meta = response.data.meta || {};
        total.value = Number(meta.total) || 0;
        page.value = Number(meta.current_page) || 1;
        localLimit.value = Number(meta.per_page) || 10;
        search.value = meta.search || '';
        sort.value.key = meta.sort || '';
        sort.value.order = meta.order || 'asc';
    } catch (error) {
        console.error('Gagal fetch data:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);

watch([page, localLimit, () => sort.value.key, () => sort.value.order], (vals, oldVals) => {
    if (vals[2] !== oldVals[2] || vals[3] !== oldVals[3]) {
        fetchData();
    } else {
        fetchData();
    }
});

const props = defineProps<{
    title: string;
    breadcrumbs: BreadcrumbItem[];
    columns: { key: string; label: string }[];
    actions?: (row: any) => { label: string; onClick: () => void }[];
    createUrl: string;
    selected?: number[];
    onDeleteSelected?: () => void;
    apiEndpoint: string;
    onDeleteRowConfirm?: (row: any) => Promise<void>;
    hidePagination?: boolean;
    limit?: number;
    disableLength?: boolean;
    hideSearch?: boolean;
    showImport: boolean;
    showMultipleButton?: boolean;
    createMultipleUrl?: string;
}>();

const emit = defineEmits(['search', 'update:selected', 'import']);

const localSelected = ref<number[]>([]);

watch(
    () => props.selected,
    (val) => {
        if (val) localSelected.value = val;
    },
);

watch(localSelected, (val) => {
    emit('update:selected', val);
});

const showConfirm = ref(false);
const showDeleteDialog = ref(false);
const rowToDelete = ref<any>(null);

const handleSearch = (params: { search?: string; sortKey?: string; sortOrder?: 'asc' | 'desc'; page?: number; limit?: number }) => {
    if (params.search !== undefined) search.value = params.search;
    if (params.sortKey) sort.value.key = params.sortKey;
    if (params.sortOrder) sort.value.order = params.sortOrder;
    if (params.page) page.value = params.page;
    if (params.limit) localLimit.value = params.limit;
};

const handleDeleteSelected = () => {
    if (!localSelected.value.length) return;
    if (props.onDeleteSelected) {
        props.onDeleteSelected();
    }
    showConfirm.value = false;
};

const handleDeleteRow = (row: any) => {
    rowToDelete.value = row;
    showDeleteDialog.value = true;
};

const confirmDeleteRow = async () => {
    if (!rowToDelete.value) return;

    if (props.onDeleteRowConfirm) {
        await props.onDeleteRowConfirm(rowToDelete.value);
        showDeleteDialog.value = false;
        rowToDelete.value = null;
        fetchData();
        return;
    }

    try {
        const module = props.apiEndpoint.split('/').pop();
        await router.delete(`/${module}/${rowToDelete.value.id}`, {
            onSuccess: () => {
                toast({ title: 'Data berhasil dihapus', variant: 'success' });
                fetchData();
            },
            onError: () => {
                toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
            },
        });
    } finally {
        showDeleteDialog.value = false;
        rowToDelete.value = null;
    }
};

const localActions = (row: any) => {
    const base = props.actions ? props.actions(row) : [];
    return base.map((action) => {
        if (action.label === 'Delete') {
            return {
                ...action,
                onClick: () => handleDeleteRow(row),
            };
        }
        return action;
    });
};

const handleSearchDebounced = debounce((val: string) => {
    search.value = val;
    fetchData();
}, 400);

const handleSort = debounce((val: { key: string; order: 'asc' | 'desc' }) => {
    sort.value.key = val.key;
    sort.value.order = val.order;
    page.value = 1;
    fetchData();
}, 300);

defineExpose({ fetchData });
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen w-full bg-gray-100 dark:bg-neutral-950 py-4">
            <div class="max-w-5xl mx-auto">
                <div class="mx-auto p-2 py-4">
                    <HeaderActions
                        :title="title"
                        :selected="localSelected"
                        :on-delete-selected="() => (showConfirm = true)"
                        v-bind="createUrl ? { createUrl } : {}"
                        :showImport="props.showImport"
                        :showMultipleButton="props.showMultipleButton"
                        :createMultipleUrl="props.createMultipleUrl"
                        @import="$emit('import')"
                    />
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-2 py-4">
                    <DataTable
                        :columns="columns"
                        :rows="tableRows"
                        :actions="localActions"
                        :total="total"
                        :loading="loading"
                        v-model:selected="localSelected"
                        :search="search"
                        :sort="sort"
                        :page="page"
                        :per-page="props.limit !== undefined ? props.limit : localLimit"
                        @update:search="handleSearchDebounced"
                        @update:sort="handleSort"
                        @update:page="(val) => handleSearch({ page: val })"
                        @update:perPage="(val) => handleSearch({ limit: Number(val), page: 1 })"
                        @deleted="fetchData()"
                        :on-delete-row="handleDeleteRow"
                        :hide-pagination="props.hidePagination"
                        :disable-length="props.disableLength"
                        :hide-search="props.hideSearch"
                    />
                </div>
            </div>
            <Dialog v-model:open="showConfirm">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Hapus data terpilih?</DialogTitle>
                        <DialogDescription>
                            Anda akan menghapus {{ localSelected.length }} data. Tindakan ini tidak dapat dibatalkan.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" @click="showConfirm = false">Batal</Button>
                        <Button variant="destructive" @click="handleDeleteSelected">Hapus</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            <Dialog v-model:open="showDeleteDialog">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Hapus data ini!</DialogTitle>
                        <DialogDescription> Apakah Anda yakin? </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" @click="showDeleteDialog = false">Batal</Button>
                        <Button variant="destructive" @click="confirmDeleteRow">Hapus</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
