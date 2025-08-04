<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const page = usePage();
const turnamenId = page.props.turnamen_id as string;
const turnamen = page.props.turnamen as any;
const jenisPeserta = ref(page.props.jenis_peserta || 'atlet');

const jenisLabel: Record<string, string> = {
    atlet: 'Atlet',
    pelatih: 'Pelatih',
    'tenaga-pendukung': 'Tenaga Pendukung',
};

const breadcrumbs = [
    { title: 'Turnamen', href: '/turnamen' },
    { title: 'Peserta', href: '#' },
];

const columns = computed(() => {
    const baseColumns = [
        { key: 'nama', label: 'Nama' },
        {
            key: 'foto',
            label: 'Foto',
            format: (row: any) => {
                if (row.foto) {
                    return `<div class='cursor-pointer' onclick="window.open('${row.foto}', '_blank')">
          <img src='${row.foto}' alt='Foto ${row.nama}' class='w-12 h-12 object-cover rounded-full border hover:shadow-md transition-shadow' />
        </div>`;
                }
                return '<div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs">No</div>';
            },
        },
        {
            key: 'jenis_kelamin',
            label: 'Jenis Kelamin',
            format: (row: any) => (row.jenis_kelamin === 'L' ? 'Laki-laki' : row.jenis_kelamin === 'P' ? 'Perempuan' : '-'),
        },
        { key: 'tempat_lahir', label: 'Tempat Lahir' },
        {
            key: 'tanggal_lahir',
            label: 'Tanggal Lahir',
            format: (row: any) =>
                row.tanggal_lahir
                    ? new Date(row.tanggal_lahir).toLocaleDateString('id-ID', { day: 'numeric', month: 'numeric', year: 'numeric' })
                    : '-',
        },
        { key: 'no_hp', label: 'No HP' },
    ];

    if (jenisPeserta.value === 'atlet') {
        return [
            { key: 'nama', label: 'Nama' },
            { key: 'posisi_atlet_nama', label: 'Posisi', format: (row: any) => row.posisi_atlet_nama || '-' },
            ...baseColumns.slice(1),
        ];
    } else if (jenisPeserta.value === 'pelatih') {
        return [
            { key: 'nama', label: 'Nama' },
            { key: 'jenis_pelatih_nama', label: 'Jenis Pelatih', format: (row: any) => row.jenis_pelatih_nama || '-' },
            ...baseColumns.slice(1),
        ];
    } else if (jenisPeserta.value === 'tenaga-pendukung') {
        return [
            { key: 'nama', label: 'Nama' },
            { key: 'jenis_tenaga_pendukung_nama', label: 'Jenis Tenaga Pendukung', format: (row: any) => row.jenis_tenaga_pendukung_nama || '-' },
            ...baseColumns.slice(1),
        ];
    }

    return baseColumns;
});

const selected = ref<number[]>([]);
const pageIndex = ref();
const { toast } = useToast();

const actions = (row: any) => [
    {
        label: 'Delete',
        onClick: () => pageIndex.value.handleDeleteRow(row),
        variant: 'destructive',
    },
];

const deleteSelected = async () => {
    if (!selected.value.length) {
        return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
    }
    try {
        await axios.post(`/api/turnamen/${turnamenId}/peserta/${jenisPeserta.value}/destroy-selected`, {
            ids: selected.value,
        });
        selected.value = [];
        if (pageIndex.value.fetchData) pageIndex.value.fetchData();
        toast({ title: 'Data berhasil dihapus', variant: 'success' });
    } catch {
        toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
    }
};

const deleteRow = async (row: any) => {
    try {
        await axios.delete(`/api/turnamen/${turnamenId}/peserta/${jenisPeserta.value}/${row.id}`);
        toast({ title: 'Data berhasil dihapus', variant: 'success' });
        if (pageIndex.value.fetchData) pageIndex.value.fetchData();
    } catch {
        toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
    }
};

// Watch untuk mendeteksi perubahan jenisPeserta
watch(jenisPeserta, (newValue, oldValue) => {
    if (newValue !== oldValue && pageIndex.value?.fetchData) {
        setTimeout(() => {
            pageIndex.value.fetchData();
        }, 100);
    }
});
</script>

<template>
    <div class="space-y-4">
        <PageIndex
            :title="`Peserta Turnamen (${jenisLabel[jenisPeserta] || jenisPeserta})`"
            :breadcrumbs="breadcrumbs"
            :columns="columns"
            :actions="actions"
            :selected="selected"
            @update:selected="(val: number[]) => (selected = val)"
            :on-delete-selected="deleteSelected"
            :on-delete-row="deleteRow"
            :show-import="false"
            :create-url="''"
            :api-endpoint="`/api/turnamen/${turnamenId}/peserta?jenis_peserta=${jenisPeserta}`"
            ref="pageIndex"
            :disable-length="true"
            :hide-search="false"
            :hide-pagination="true"
            :on-toast="toast"
        >
            <template #header-extra>
                <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Informasi Turnamen -->
                    <div class="bg-card rounded-lg border p-4">
                        <h3 class="mb-2 text-lg font-semibold">Informasi Turnamen</h3>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-muted-foreground text-sm font-medium">Nama Turnamen:</span>
                                <span class="text-sm font-medium">{{ turnamen?.nama }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-muted-foreground text-sm font-medium">Cabor Kategori:</span>
                                <span class="text-sm font-medium"
                                    >{{ turnamen?.cabor_kategori?.cabor?.nama }} - {{ turnamen?.cabor_kategori?.nama }}</span
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-muted-foreground text-sm font-medium">Periode:</span>
                                <span class="text-sm font-medium">
                                    {{
                                        turnamen?.tanggal_mulai
                                            ? new Date(turnamen.tanggal_mulai).toLocaleDateString('id-ID', {
                                                  day: 'numeric',
                                                  month: 'long',
                                                  year: 'numeric',
                                              })
                                            : '-'
                                    }}
                                    s/d
                                    {{
                                        turnamen?.tanggal_selesai
                                            ? new Date(turnamen.tanggal_selesai).toLocaleDateString('id-ID', {
                                                  day: 'numeric',
                                                  month: 'long',
                                                  year: 'numeric',
                                              })
                                            : '-'
                                    }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-muted-foreground text-sm font-medium">Lokasi:</span>
                                <span class="text-sm font-medium">{{ turnamen?.lokasi }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Jenis Peserta -->
                    <div class="bg-card rounded-lg border p-4">
                        <h3 class="mb-2 text-lg font-semibold">Filter Peserta</h3>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Jenis Peserta:</label>
                            <Select v-model="jenisPeserta">
                                <SelectTrigger class="w-full">
                                    <SelectValue :placeholder="jenisLabel[jenisPeserta] || 'Pilih jenis peserta'" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="atlet">Atlet</SelectItem>
                                    <SelectItem value="pelatih">Pelatih</SelectItem>
                                    <SelectItem value="tenaga-pendukung">Tenaga Pendukung</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>
            </template>
        </PageIndex>
    </div>
</template>
