<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import PageCreate from '@/pages/modules/base-page/PageCreate.vue';
import ButtonsForm from '@/pages/modules/base-page/ButtonsForm.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps<{
    caborKategori: {
        id: number;
        nama: string;
        cabor: {
            id: number;
            nama: string;
        };
    };
}>();

const { toast } = useToast();

const breadcrumbs = [
    { title: 'Cabor Kategori', href: '/cabor-kategori' },
    { title: 'Daftar Pelatih', href: `/cabor-kategori/${props.caborKategori.id}/pelatih` },
    { title: 'Tambah Multiple Pelatih', href: '#' },
];

const selectedPelatihIds = ref<number[]>([]);
const pelatihList = ref<any[]>([]);
const jenisPelatihList = ref<any[]>([]);
const selectedJenisPelatihId = ref<number | null>(null);
const loading = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);

// Columns untuk tabel pelatih
const columns = [
    { key: 'nama', label: 'Nama' },
    {
        key: 'foto',
        label: 'Foto',
        format: (row: any) => {
            if (row.foto) {
                return `<div class="cursor-pointer" onclick="window.open('${row.foto}', '_blank')">
                    <img src="${row.foto}" alt="Foto ${row.nama}" class="w-12 h-12 object-cover rounded-full border hover:shadow-md transition-shadow" />
                </div>`;
            }
            return '<div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs">No</div>';
        },
    },
    {
        key: 'jenis_kelamin',
        label: 'Jenis Kelamin',
        format: (row: any) => {
            return row.jenis_kelamin === 'L' ? 'Laki-laki' : row.jenis_kelamin === 'P' ? 'Perempuan' : '-';
        },
    },
    { key: 'tempat_lahir', label: 'Tempat Lahir' },
    {
        key: 'tanggal_lahir',
        label: 'Tanggal Lahir',
        format: (row: any) => {
            return row.tanggal_lahir ? new Date(row.tanggal_lahir).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'numeric',
                year: 'numeric'
            }) : '-';
        },
    },
    { key: 'no_hp', label: 'No HP' },
    {
        key: 'is_active',
        label: 'Status',
        format: (row: any) => {
            return row.is_active
                ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>'
                : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Nonaktif</span>';
        },
    },
];

// Fetch pelatih yang belum ada di kategori ini
const fetchAvailablePelatih = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/pelatih', {
            params: {
                page: currentPage.value > 1 ? currentPage.value - 1 : 0,
                per_page: perPage.value,
                search: searchQuery.value,
                exclude_cabor_kategori_id: props.caborKategori.id,
            },
        });
        pelatihList.value = response.data.data || [];
        total.value = response.data.meta?.total || 0;
    } catch (error) {
        console.error('Gagal mengambil data pelatih:', error);
        toast({ title: 'Gagal mengambil data pelatih', variant: 'destructive' });
    } finally {
        loading.value = false;
    }
};

// Fetch jenis pelatih
const fetchJenisPelatih = async () => {
    try {
        const response = await axios.get('/api/jenis-pelatih-list');
        jenisPelatihList.value = response.data || [];
    } catch (error) {
        console.error('Gagal mengambil data jenis pelatih:', error);
        toast({ title: 'Gagal mengambil data jenis pelatih', variant: 'destructive' });
    }
};

// Toggle selection pelatih
const toggleSelect = (pelatihId: number) => {
    const index = selectedPelatihIds.value.indexOf(pelatihId);
    if (index > -1) {
        selectedPelatihIds.value.splice(index, 1);
    } else {
        selectedPelatihIds.value.push(pelatihId);
    }
};

// Toggle select all
const toggleSelectAll = (checked: boolean) => {
    if (checked) {
        selectedPelatihIds.value = pelatihList.value.map(pelatih => pelatih.id);
    } else {
        selectedPelatihIds.value = [];
    }
};

// Check if pelatih is selected
const isPelatihSelected = (pelatihId: number) => {
    return selectedPelatihIds.value.includes(pelatihId);
};

// Handle search
const handleSearch = (value: string) => {
    searchQuery.value = value;
    currentPage.value = 1;
    fetchAvailablePelatih();
};

// Handle page change
const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchAvailablePelatih();
};

// Handle per page change
const handlePerPageChange = (value: number) => {
    perPage.value = value;
    currentPage.value = 1;
    fetchAvailablePelatih();
};

// Handle save
const handleSave = async () => {
    console.log('handleSave called, selectedPelatihIds:', selectedPelatihIds.value);
    console.log('selectedJenisPelatihId:', selectedJenisPelatihId.value);
    
    if (selectedPelatihIds.value.length === 0) {
        toast({ title: 'Pilih minimal 1 pelatih', variant: 'destructive' });
        return;
    }

    if (!selectedJenisPelatihId.value) {
        toast({ title: 'Pilih jenis pelatih', variant: 'destructive' });
        return;
    }

    try {
        console.log('Sending request to:', `/cabor-kategori/${props.caborKategori.id}/pelatih/store-multiple`);
        console.log('Data being sent:', { 
            pelatih_ids: selectedPelatihIds.value,
            jenis_pelatih_id: selectedJenisPelatihId.value
        });
        
        const response = await router.post(`/cabor-kategori/${props.caborKategori.id}/pelatih/store-multiple`, {
            pelatih_ids: selectedPelatihIds.value,
            jenis_pelatih_id: selectedJenisPelatihId.value,
        });
        
        console.log('Response received:', response);
        toast({ title: 'Pelatih berhasil ditambahkan ke kategori', variant: 'success' });
        router.visit(`/cabor-kategori/${props.caborKategori.id}/pelatih`);
    } catch (error: any) {
        console.error('Gagal menambahkan pelatih:', error);
        console.error('Error details:', error.response?.data);
        toast({ 
            title: error.response?.data?.message || 'Gagal menambahkan pelatih ke kategori', 
            variant: 'destructive' 
        });
    }
};

const handleCancel = () => {
    router.visit(`/cabor-kategori/${props.caborKategori.id}/pelatih`);
};

// Computed untuk pagination
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

// Load data saat komponen dimount
fetchAvailablePelatih();
fetchJenisPelatih();
</script>

<template>
    <PageCreate title="Tambah Multiple Pelatih" :breadcrumbs="breadcrumbs" back-url="/cabor-kategori">
        <div class="space-y-6">
            <!-- Informasi Kategori -->
            <div class="bg-card border rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-2">Informasi Kategori</h3>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-muted-foreground">Nama Kategori:</span>
                        <Badge variant="secondary">{{ caborKategori.nama }}</Badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-muted-foreground">Cabor:</span>
                        <Badge variant="outline">{{ caborKategori.cabor.nama }}</Badge>
                    </div>
                </div>
            </div>

            <!-- Jenis Pelatih Selection -->
            <div class="space-y-2">
                <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                    Jenis Pelatih *
                </label>
                <Select :model-value="selectedJenisPelatihId" @update:model-value="(val) => selectedJenisPelatihId = val">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Pilih Jenis Pelatih" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="jenis in jenisPelatihList" :key="jenis.id" :value="jenis.id">
                            {{ jenis.nama }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Selection Counter -->
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Pilih Pelatih</h3>
                <Badge variant="secondary">
                    {{ selectedPelatihIds.length }} pelatih dipilih
                </Badge>
            </div>

            <!-- Search dan Length -->
            <div class="flex flex-col flex-wrap items-center justify-center gap-4 text-center sm:flex-row sm:justify-between">
                <!-- Length -->
                <div class="ml-2 flex items-center gap-2">
                    <span class="text-muted-foreground text-sm">Show</span>
                    <Select :model-value="perPage" @update:model-value="handlePerPageChange">
                        <SelectTrigger class="w-24">
                            <SelectValue :placeholder="perPage" />
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

                <!-- Search -->
                <div class="w-full sm:w-64">
                    <Input 
                        :model-value="searchQuery" 
                        @update:model-value="handleSearch" 
                        placeholder="Search..." 
                        class="w-full" 
                    />
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                <span class="ml-2 text-sm text-muted-foreground">Memuat data pelatih...</span>
            </div>

            <!-- Empty State -->
            <div v-else-if="pelatihList.length === 0" class="text-center py-8">
                <p class="text-muted-foreground">Tidak ada pelatih yang tersedia</p>
            </div>

            <!-- Table -->
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
                                            :checked="selectedPelatihIds.length > 0 && selectedPelatihIds.length === pelatihList.length"
                                            @change="(e) => toggleSelectAll((e.target as HTMLInputElement).checked)"
                                        />
                                        <div class="bg-primary h-3 w-3 scale-0 transform rounded-sm transition-all peer-checked:scale-100"></div>
                                    </label>
                                </TableHead>
                                <TableHead
                                    v-for="col in columns"
                                    :key="col.key"
                                    class="cursor-pointer select-none"
                                >
                                    <div class="flex items-center gap-1">
                                        {{ col.label }}
                                    </div>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(pelatih, index) in pelatihList" :key="pelatih.id" class="hover:bg-muted/40 border-t transition">
                                <TableCell class="text-center text-xs sm:text-sm px-2 sm:px-4 whitespace-normal break-words">
                                    {{ (currentPage - 1) * perPage + index + 1 }}
                                </TableCell>
                                <TableCell class="text-center text-xs sm:text-sm px-2 sm:px-4 whitespace-normal break-words">
                                    <label class="bg-background relative inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded border border-gray-500">
                                        <input
                                            type="checkbox"
                                            class="peer sr-only"
                                            :checked="isPelatihSelected(pelatih.id)"
                                            @change="() => toggleSelect(pelatih.id)"
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
                                <TableCell
                                    v-for="col in columns"
                                    :key="col.key"
                                >
                                    <span v-if="typeof col.format === 'function'" v-html="col.format(pelatih)"></span>
                                    <span v-else>{{ pelatih[col.key] }}</span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                
                <!-- Pagination Info -->
                <div class="text-muted-foreground flex flex-col items-center justify-center gap-2 border-t p-4 text-center text-sm md:flex-row md:justify-between">
                    <span>
                        Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of
                        {{ total }} entries
                    </span>
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <Button size="sm" :disabled="currentPage === 1" @click="handlePageChange(currentPage - 1)" class="bg-muted/40 text-foreground">
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
                                @click="handlePageChange(page)"
                            >
                                {{ page }}
                            </Button>
                        </div>
                        <Button
                            size="sm"
                            :disabled="currentPage === totalPages"
                            @click="handlePageChange(currentPage + 1)"
                            class="bg-muted/40 text-foreground"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <ButtonsForm
                :show-save="true"
                :show-cancel="true"
                :save-text="`Tambah ${selectedPelatihIds.length} Pelatih`"
                :save-disabled="selectedPelatihIds.length === 0 || !selectedJenisPelatihId"
                @save="handleSave"
                @cancel="handleCancel"
            />
        </div>
    </PageCreate>
</template> 