<script setup lang="ts">
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    pemeriksaan: any;
    peserta: any;
    data: any[];
    total: number;
    currentPage: number;
    perPage: number;
    search: string;
}>();

const breadcrumbs = [
    { title: 'Pemeriksaan', href: '/pemeriksaan' },
    { title: 'Peserta Pemeriksaan', href: `/pemeriksaan/${props.pemeriksaan.id}/peserta` },
    { title: 'Parameter Peserta', href: `/pemeriksaan/${props.pemeriksaan.id}/peserta/${props.peserta.id}/parameter` },
];

const columns = [
    { key: 'parameter', label: 'Parameter' },
    { key: 'nilai', label: 'Nilai' },
    { key: 'trend', label: 'Trend', format: (row: any) => {
        if (row.trend === 'stabil') return '<span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Stabil</span>';
        if (row.trend === 'penurunan') return '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Penurunan</span>';
        if (row.trend === 'kenaikan') return '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Kenaikan</span>';
        return row.trend;
    }},
];

const selected = ref<number[]>([]);

const actions = (row: any) => [
    { label: 'Detail', onClick: () => router.visit(`/pemeriksaan/${props.pemeriksaan.id}/peserta/${props.peserta.id}/parameter/${row.id}`) },
    { label: 'Edit', onClick: () => router.visit(`/pemeriksaan/${props.pemeriksaan.id}/peserta/${props.peserta.id}/parameter/${row.id}/edit`) },
    { label: 'Delete', onClick: () => pageIndex.value.handleDeleteRow(row) },
];

const pageIndex = ref();
</script>

<template>
    <PageIndex
        title="Parameter Peserta"
        :breadcrumbs="breadcrumbs"
        :columns="columns"
        :create-url="`/pemeriksaan/${pemeriksaan.id}/peserta/${peserta.id}/parameter/create`"
        :actions="actions"
        :selected="selected"
        @update:selected="(val) => (selected = val)"
        :on-delete-selected="() => {}"
        :api-endpoint="`/api/pemeriksaan/${pemeriksaan.id}/peserta/${peserta.id}/parameter`"
        ref="pageIndex"
        :showImport="false"
    >
        <template #header-extra>
            <div class="bg-card mb-4 rounded-lg border p-4">
                <h3 class="mb-2 text-lg font-semibold">Informasi Pemeriksaan & Peserta</h3>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-muted-foreground text-sm font-medium">Nama Pemeriksaan:</span>
                        <Badge variant="secondary">{{ pemeriksaan.nama_pemeriksaan }}</Badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-muted-foreground text-sm font-medium">Cabor:</span>
                        <Badge variant="outline">{{ pemeriksaan.cabor?.nama }}</Badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-muted-foreground text-sm font-medium">Kategori:</span>
                        <Badge variant="outline">{{ pemeriksaan.cabor_kategori?.nama }}</Badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-muted-foreground text-sm font-medium">Tenaga Pendukung:</span>
                        <Badge variant="outline">{{ pemeriksaan.tenaga_pendukung?.nama }}</Badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-muted-foreground text-sm font-medium">Peserta:</span>
                        <Badge variant="outline">{{ peserta.peserta?.nama || '-' }}</Badge>
                    </div>
                </div>
            </div>
        </template>
    </PageIndex>
</template> 