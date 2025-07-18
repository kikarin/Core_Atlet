<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

const breadcrumbs = [{ title: 'Atlet', href: '/atlet' }];

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

const selected = ref<number[]>([]);
const { toast } = useToast();

const actions = (row: any) => [
    {
        label: 'Detail',
        onClick: () => router.visit(`/atlet/${row.id}`),
    },
    {
        label: 'Lihat',
        onClick: () => router.visit(`/atlet/${row.id}/edit`),
    },
    {
        label: 'Delete',
        onClick: () => pageIndex.value.handleDeleteRow(row),
    },
];

const pageIndex = ref();

const deleteSelected = async () => {
    if (!selected.value.length) {
        return toast({ title: 'Pilih data yang akan dihapus', variant: 'destructive' });
    }
    try {
        const response = await axios.post('/atlet/destroy-selected', { ids: selected.value });
        selected.value = [];
        pageIndex.value.fetchData();
        toast({ title: response.data?.message || 'Data berhasil dihapus', variant: 'success' });
    } catch (error: any) {
        toast({ title: error.response?.data?.message || 'Gagal menghapus data', variant: 'destructive' });
    }
};

const deleteAtlet = async (row: any) => {
    await router.delete(`/atlet/${row.id}`, {
        onSuccess: () => {
            toast({ title: 'Data berhasil dihapus', variant: 'success' });
            pageIndex.value.fetchData();
        },
        onError: () => {
            toast({ title: 'Gagal menghapus data.', variant: 'destructive' });
        },
    });
};

const showImportModal = ref(false);
const importFile = ref<File|null>(null);
const importLoading = ref(false);
const fileName = ref<string>('');
const fileInput = ref<HTMLInputElement>();

function openImportModal() {
    showImportModal.value = true;
    importFile.value = null;
    fileName.value = '';
}

function closeImportModal() {
    showImportModal.value = false;
    importFile.value = null;
    fileName.value = '';
}

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
        if (!validTypes.includes(file.type)) {
            toast({ title: 'File harus berformat Excel (.xlsx atau .xls)', variant: 'destructive' });
            target.value = '';
            return;
        }
        importFile.value = file;
        fileName.value = file.name;
        console.log('File selected:', file.name, 'Import button should be enabled:', !!importFile.value);
    }
}

async function handleImport() {
    console.log('handleImport called, importFile:', importFile.value);
    if (!importFile.value) return toast({ title: 'Pilih file Excel terlebih dahulu', variant: 'destructive' });
    importLoading.value = true;
    const formData = new FormData();
    formData.append('file', importFile.value);
    try {
        const response = await axios.post('/atlet/import', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
        
        if (response.data.success) {
            const data = response.data.data;
            let message = response.data.message;
            
            if (data.error_count > 0) {
                console.log('Import errors:', data.errors);
                // Group errors by type for cleaner display
                const errorGroups: Record<string, number[]> = {};
                data.errors.forEach((err: any) => {
                    if (!errorGroups[err.error]) {
                        errorGroups[err.error] = [];
                    }
                    errorGroups[err.error].push(err.row);
                });
                
                message += '\n\nError yang ditemukan:';
                Object.entries(errorGroups).forEach(([errorType, rows]) => {
                    if (rows.length === 1) {
                        message += `\n• Baris ${rows[0]}: ${errorType}`;
                    } else {
                        message += `\n• Baris ${rows.join(', ')}: ${errorType}`;
                    }
                });
            }
            
            toast({ title: message, variant: 'success' });
        } else {
            toast({ title: response.data.message, variant: 'destructive' });
        }
        
        closeImportModal();
        pageIndex.value.fetchData();
    } catch (e: any) {
        console.error('Import error:', e.response?.data || e.message);
        const errorMessage = e.response?.data?.message || e.response?.data?.error || e.message || 'Gagal import';
        toast({ title: errorMessage, variant: 'destructive' });
    } finally {
        importLoading.value = false;
    }
}
</script>

<template>
    <div class="space-y-4">
        <PageIndex
            title="Atlet"
            :breadcrumbs="breadcrumbs"
            :columns="columns"
            :create-url="'/atlet/create'"
            :actions="actions"
            :selected="selected"
            @update:selected="(val) => (selected = val)"
            :on-delete-selected="deleteSelected"
            api-endpoint="/api/atlet"
            ref="pageIndex"
            :on-toast="toast"
            :on-delete-row="deleteAtlet"
            @import="openImportModal"
            :showImport="true"
        />
        <Dialog v-model:open="showImportModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Import Data Atlet</DialogTitle>
                    <DialogDescription>
                        Upload file Excel (.xlsx atau .xls) yang berisi data atlet.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">File Excel</label>
                        <div class="flex items-center gap-2">
                            <input 
                                ref="fileInput"
                                type="file" 
                                accept=".xlsx,.xls" 
                                @change="handleFileChange"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            />
                        </div>
                        <div v-if="fileName" class="text-sm text-muted-foreground">
                            File: {{ fileName }}
                        </div>
                    </div>
                    
                    <div class="rounded-lg border bg-muted p-3">
                        <div class="text-sm font-medium mb-2">Format kolom yang didukung:</div>
                        <div class="text-xs text-muted-foreground space-y-1">
                            <div><strong>Atlet:</strong> nik, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, kecamatan_id, kelurahan_id, no_hp, email, is_active</div>
                            <div><strong>Orang Tua:</strong> nama_ibu_kandung, nama_ayah_kandung, nama_wali, dst</div>
                            <div><strong>Kesehatan:</strong> tinggi_badan, berat_badan, penglihatan, pendengaran, riwayat_penyakit, alergi</div>
                        </div>
                        <div class="mt-2">
                            <a
                                href="/template-import/template_import_atlet.xlsx"
                                target="_blank"
                                class="text-blue-600 hover:underline text-sm"
                                download
                            >
                                Unduh Format Excel Atlet
                            </a>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="closeImportModal" :disabled="importLoading">
                        Batal
                    </Button>
                    <Button 
                        @click="handleImport" 
                        :disabled="importLoading || !importFile"
                    >
                        {{ importLoading ? 'Mengimpor...' : 'Import' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template> 