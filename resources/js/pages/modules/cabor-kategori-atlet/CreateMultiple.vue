<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useToast } from '@/components/ui/toast/useToast';
import ButtonsForm from '@/pages/modules/base-page/ButtonsForm.vue';
import PageCreate from '@/pages/modules/base-page/PageCreate.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

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

const calculateAge = (birthDate: string | null | undefined): number | string => {
    if (!birthDate) return '-';
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
};

function getLamaBergabung(tanggalBergabung: string) {
    if (!tanggalBergabung) return '-';
    const start = new Date(tanggalBergabung);
    const now = new Date();
    let tahun = now.getFullYear() - start.getFullYear();
    let bulan = now.getMonth() - start.getMonth();
    if (bulan < 0) {
        tahun--;
        bulan += 12;
    }
    let result = '';
    if (tahun > 0) result += tahun + ' tahun ';
    if (bulan > 0) result += bulan + ' bulan';
    if (!result) result = 'Kurang dari 1 bulan';
    return result.trim();
}

const { toast } = useToast();

const breadcrumbs = [
    { title: 'Cabor Kategori', href: '/cabor-kategori' },
    { title: 'Daftar Atlet', href: `/cabor-kategori/${props.caborKategori.id}/atlet` },
    { title: 'Tambah Multiple Atlet', href: '#' },
];

const selectedAtletIds = ref<number[]>([]);
const atletList = ref<any[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);

// Columns untuk tabel atlet
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
    {
        key: 'usia',
        label: 'Usia',
        format: (row: any) => {
            return calculateAge(row.tanggal_lahir);
        },
    },
    {
        key: 'lama_bergabung',
        label: 'Lama Bergabung',
        format: (row: any) => getLamaBergabung(row.tanggal_bergabung),
    },
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

// Fetch atlet yang belum ada di kategori ini
const fetchAvailableAtlet = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/atlet', {
            params: {
                page: currentPage.value > 1 ? currentPage.value - 1 : 0,
                per_page: perPage.value,
                search: searchQuery.value,
                exclude_cabor_kategori_id: props.caborKategori.id,
                jenis_kelamin: props.caborKategori.jenis_kelamin !== 'C' ? props.caborKategori.jenis_kelamin : undefined,
            },
        });
        atletList.value = response.data.data || [];
        total.value = response.data.meta?.total || 0;
    } catch (error) {
        console.error('Gagal mengambil data atlet:', error);
        toast({ title: 'Gagal mengambil data atlet', variant: 'destructive' });
    } finally {
        loading.value = false;
    }
};

// Toggle selection atlet
const toggleSelect = (atletId: number) => {
    const index = selectedAtletIds.value.indexOf(atletId);
    if (index > -1) {
        selectedAtletIds.value.splice(index, 1);
    } else {
        selectedAtletIds.value.push(atletId);
    }
};

// Toggle select all
const toggleSelectAll = (checked: boolean) => {
    if (checked) {
        selectedAtletIds.value = atletList.value.map((atlet) => atlet.id);
    } else {
        selectedAtletIds.value = [];
    }
};

// Check if atlet is selected
const isAtletSelected = (atletId: number) => {
    return selectedAtletIds.value.includes(atletId);
};

// Handle search
const handleSearch = (value: string) => {
    searchQuery.value = value;
    currentPage.value = 1;
    fetchAvailableAtlet();
};

// Handle page change
const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchAvailableAtlet();
};

// Handle per page change
const handlePerPageChange = (value: number) => {
    perPage.value = value;
    currentPage.value = 1;
    fetchAvailableAtlet();
};

const selectedStatus = ref(1); // 1 = aktif, 0 = nonaktif
const posisiAtletOptions = ref<{ value: number; label: string }[]>([]);
const selectedPosisiAtletId = ref<number | null>(null);

// Fetch posisi atlet
const fetchPosisiAtlet = async () => {
    try {
        const res = await axios.get('/api/posisi-atlet-list');
        posisiAtletOptions.value = (res.data || []).map((item: any) => ({ value: item.id, label: item.nama }));
    } catch {
        posisiAtletOptions.value = [];
    }
};

fetchPosisiAtlet();

// Handle save
const handleSave = async () => {
    if (selectedAtletIds.value.length === 0) {
        toast({ title: 'Pilih minimal 1 atlet', variant: 'destructive' });
        return;
    }
    try {
        await router.post(
            `/cabor-kategori/${props.caborKategori.id}/atlet/store-multiple`,
            {
                atlet_ids: selectedAtletIds.value,
                is_active: selectedStatus.value,
                posisi_atlet_id: selectedPosisiAtletId.value,
            },
            {
                onSuccess: () => {
                    toast({ title: 'Atlet berhasil ditambahkan ke kategori', variant: 'success' });
                    router.visit(`/cabor-kategori/${props.caborKategori.id}/atlet`);
                },
                onError: () => {
                    toast({ title: 'Gagal menambahkan atlet ke kategori', variant: 'destructive' });
                },
            },
        );
    } catch {
        toast({ title: 'Gagal menambahkan atlet', variant: 'destructive' });
    }
};

const handleCancel = () => {
    router.visit(`/cabor-kategori/${props.caborKategori.id}/atlet`);
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
fetchAvailableAtlet();
</script>

<template>
    <PageCreate title="Tambah Multiple Atlet" :breadcrumbs="breadcrumbs" back-url="/cabor-kategori" :use-grid="true">
        <div class="space-y-6">
            <!-- Informasi Kategori -->
            <div class="bg-card rounded-lg border p-4">
                <h3 class="mb-2 text-lg font-semibold">Informasi Kategori</h3>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-muted-foreground text-sm font-medium">Kategori:</span>
                        <span class="text-sm font-medium">{{ caborKategori.nama }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-muted-foreground text-sm font-medium">Cabor:</span>
                        <span class="text-sm font-medium">{{ caborKategori.cabor.nama }}</span>
                    </div>
                </div>
            </div>

            <!-- Pilih Posisi Atlet (opsional) -->
            <div class="flex items-center gap-4">
                <span class="text-muted-foreground text-sm font-medium">Posisi Atlet (opsional):</span>
                <Select v-model="selectedPosisiAtletId" class="w-64">
                    <SelectTrigger>
                        <SelectValue placeholder="Pilih Posisi Atlet" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="null">- Tidak Ada -</SelectItem>
                        <SelectItem v-for="opt in posisiAtletOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Selection Counter -->
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Pilih Atlet</h3>
                <div class="flex items-center gap-4">
                    <Select v-model="selectedStatus" class="w-32">
                        <SelectTrigger>
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="1">Aktif</SelectItem>
                            <SelectItem :value="0">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>
                    <Badge variant="secondary"> {{ selectedAtletIds.length }} atlet dipilih </Badge>
                </div>
            </div>

            <!-- Search dan Length -->
            <div class="flex flex-col flex-wrap items-center justify-center gap-4 text-center sm:flex-row sm:justify-between">
                <!-- Length -->
                <div class="ml-2 flex items-center gap-2">
                    <span class="text-muted-foreground text-sm">Show</span>
                    <Select :model-value="perPage" @update:model-value="(val: any) => handlePerPageChange(val as number)">
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

                <!-- Search -->
                <div class="w-full sm:w-64">
                    <Input
                        :model-value="searchQuery"
                        @update:model-value="(val: any) => handleSearch(val as string)"
                        placeholder="Search..."
                        class="w-full"
                    />
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-8">
                <div class="border-primary h-8 w-8 animate-spin rounded-full border-b-2"></div>
                <span class="text-muted-foreground ml-2 text-sm">Memuat data atlet...</span>
            </div>

            <!-- Empty State -->
            <div v-else-if="atletList.length === 0" class="py-8 text-center">
                <p class="text-muted-foreground">Tidak ada atlet yang tersedia</p>
            </div>

            <!-- Table -->
            <div v-else class="rounded-md shadow-sm">
                <div class="w-full overflow-x-auto">
                    <Table class="min-w-max">
                        <TableHeader class="bg-muted">
                            <TableRow>
                                <TableHead class="w-12 text-center">No</TableHead>
                                <TableHead class="w-10 text-center">
                                    <label
                                        class="bg-background relative inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded border border-gray-500"
                                    >
                                        <input
                                            type="checkbox"
                                            class="peer sr-only"
                                            :checked="selectedAtletIds.length > 0 && selectedAtletIds.length === atletList.length"
                                            @change="(e) => toggleSelectAll((e.target as HTMLInputElement).checked)"
                                        />
                                        <div class="bg-primary h-3 w-3 scale-0 transform rounded-sm transition-all peer-checked:scale-100"></div>
                                    </label>
                                </TableHead>
                                <TableHead v-for="col in columns" :key="col.key" class="cursor-pointer select-none">
                                    <div class="flex items-center gap-1">
                                        {{ col.label }}
                                    </div>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(atlet, index) in atletList" :key="atlet.id" class="hover:bg-muted/40 border-t transition">
                                <TableCell class="px-2 text-center text-xs break-words whitespace-normal sm:px-4 sm:text-sm">
                                    {{ (currentPage - 1) * perPage + index + 1 }}
                                </TableCell>
                                <TableCell class="px-2 text-center text-xs break-words whitespace-normal sm:px-4 sm:text-sm">
                                    <label
                                        class="bg-background relative inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded border border-gray-500"
                                    >
                                        <input
                                            type="checkbox"
                                            class="peer sr-only"
                                            :checked="isAtletSelected(atlet.id)"
                                            @change="() => toggleSelect(atlet.id)"
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
                                <TableCell v-for="col in columns" :key="col.key">
                                    <span v-if="typeof col.format === 'function'" v-html="col.format(atlet)"></span>
                                    <span v-else>{{ atlet[col.key] }}</span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pagination Info -->
                <div
                    class="text-muted-foreground flex flex-col items-center justify-center gap-2 border-t p-4 text-center text-sm md:flex-row md:justify-between"
                >
                    <span>
                        Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} entries
                    </span>
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <Button
                            size="sm"
                            :disabled="currentPage === 1"
                            @click="handlePageChange(currentPage - 1)"
                            class="bg-muted/40 text-foreground"
                        >
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
                :save-text="`Tambah ${selectedAtletIds.length} Atlet`"
                :save-disabled="selectedAtletIds.length === 0"
                @save="handleSave"
                @cancel="handleCancel"
            />
        </div>
    </PageCreate>
</template>
