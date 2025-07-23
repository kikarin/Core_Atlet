<!-- resources/js/pages/modules/pemeriksaan-peserta/Index.vue -->
<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { useToast } from '@/components/ui/toast/useToast';
import PageIndex from '@/pages/modules/base-page/PageIndex.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';
import axios from 'axios';

interface Pemeriksaan {
    id: number;
    nama_pemeriksaan: string;
    cabor: { nama: string };
    cabor_kategori: { nama: string };
    tenaga_pendukung: { nama: string };
}

const page = usePage();
const jenisPeserta = computed(() => page.props.jenis_peserta as string || 'atlet');
const pemeriksaan = computed(() => page.props.pemeriksaan as Pemeriksaan || {} as Pemeriksaan);

const props = defineProps<{
    pemeriksaan: any;
    items?: any;
}>();

// Debugging: Memeriksa data yang diterima dari server
onMounted(() => {
    console.log('Page props:', page.props);
    console.log('Items from props:', props.items);
    if (props.items?.data && props.items.data.length > 0) {
        console.log('First item:', props.items.data[0]);
        console.log('Peserta in first item:', props.items.data[0].peserta);
    }
});

const { toast } = useToast();

const breadcrumbs = [
    { title: 'Pemeriksaan', href: '/pemeriksaan' },
    { title: 'Peserta Pemeriksaan', href: `/pemeriksaan/${props.pemeriksaan.id}/peserta` },
];

// Kolom dinamis berdasarkan jenis peserta
const columns = computed(() => {
    const baseColumns = [
        { 
          key: 'status', 
          label: 'Status', 
          format: (row: any) => row.status?.nama || '-'
        },
        { 
          key: 'catatan_umum', 
          label: 'Catatan',
          format: (row: any) => row.catatan_umum || '-'
        },
    ];

    // Debugging untuk melihat struktur data
    console.log('Row data example:', props.items?.data?.[0]);
    
    // Fungsi untuk mendapatkan data peserta dari row
    const getPesertaData = (row: any) => {
        // Jika peserta adalah boolean true, coba ambil dari peserta_type dan peserta_id
        if (row.peserta === true && row.peserta_type && row.peserta_id) {
            // Log untuk debugging
            console.log('Peserta is boolean true, trying to get data from API');
            
            // Tentukan jenis peserta dari peserta_type
            let jenisPeserta = '';
            if (row.peserta_type.includes('Atlet')) {
                jenisPeserta = 'atlet';
            } else if (row.peserta_type.includes('Pelatih')) {
                jenisPeserta = 'pelatih';
            } else if (row.peserta_type.includes('TenagaPendukung')) {
                jenisPeserta = 'tenaga-pendukung';
            }
            
            // Ambil data peserta dari cache jika sudah ada
            const cacheKey = `${jenisPeserta}_${row.peserta_id}`;
            if (pesertaCache.value[cacheKey]) {
                console.log('Using cached peserta data:', pesertaCache.value[cacheKey]);
                return pesertaCache.value[cacheKey];
            }
            
            // Jika tidak ada di cache, ambil data dari API
            // Ini akan dijalankan secara asinkron, jadi mungkin tidak langsung tersedia
            fetchPesertaData(jenisPeserta, row.peserta_id, cacheKey);
            
            // Sementara menunggu data, tampilkan placeholder
            return { nama: 'Loading...', nik: 'Loading...', tempat_lahir: 'Loading...' };
        }
        
        // Jika peserta adalah objek, gunakan langsung
        return row.peserta || null;
    };
    
    // Cache untuk menyimpan data peserta yang sudah diambil
    const pesertaCache = ref({});
    
    // Fungsi untuk mengambil data peserta dari API
    const fetchPesertaData = async (jenisPeserta: string, pesertaId: number, cacheKey: string) => {
        try {
            // Tentukan endpoint API berdasarkan jenis peserta
            const endpoint = `/api/${jenisPeserta}/${pesertaId}`;
            console.log('Fetching peserta data from:', endpoint);
            
            // Panggil API untuk mendapatkan data peserta
            const response = await fetch(endpoint);
            if (!response.ok) {
                throw new Error(`Failed to fetch peserta data: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('Fetched peserta data:', data);
            
            // Simpan data ke cache
            pesertaCache.value[cacheKey] = data.data || data;
            
            // Paksa komponen untuk dirender ulang
            forceUpdate.value++;
        } catch (error) {
            console.error('Error fetching peserta data:', error);
        }
    };
    
    // Untuk memaksa komponen dirender ulang saat data peserta sudah diambil
    const forceUpdate = ref(0);

    const specificColumns = {
        'atlet': [
            { 
                key: 'peserta.nama', 
                label: 'Nama Atlet',
                format: (row: any) => {
                    console.log('Atlet row:', row);
                    const peserta = getPesertaData(row);
                    return peserta?.nama || '-';
                }
            },
            { 
                key: 'peserta.nik', 
                label: 'NIK',
                format: (row: any) => {
                    const peserta = getPesertaData(row);
                    return peserta?.nik || '-';
                }
            },
            { 
                key: 'peserta.tempat_lahir', 
                label: 'Tempat Lahir',
                format: (row: any) => {
                    const peserta = getPesertaData(row);
                    return peserta?.tempat_lahir || '-';
                }
            },
        ],
        'pelatih': [
            { 
                key: 'peserta.nama', 
                label: 'Nama Pelatih',
                format: (row: any) => {
                    console.log('Pelatih row:', row);
                    const peserta = getPesertaData(row);
                    return peserta?.nama || '-';
                }
            },
            { 
                key: 'peserta.nik', 
                label: 'NIK',
                format: (row: any) => {
                    const peserta = getPesertaData(row);
                    return peserta?.nik || '-';
                }
            },
            { 
                key: 'peserta.jenis_pelatih.nama', 
                label: 'Jenis Pelatih',
                format: (row: any) => {
                    const peserta = getPesertaData(row);
                    return peserta?.jenis_pelatih?.nama || '-';
                }
            },
        ],
        'tenaga-pendukung': [
            { 
                key: 'peserta.nama', 
                label: 'Nama Tenaga Pendukung',
                format: (row: any) => {
                    console.log('Tenaga Pendukung row:', row);
                    const peserta = getPesertaData(row);
                    return peserta?.nama || '-';
                }
            },
            { 
                key: 'peserta.nik', 
                label: 'NIK',
                format: (row: any) => {
                    const peserta = getPesertaData(row);
                    return peserta?.nik || '-';
                }
            },
            { 
                key: 'peserta.jenis_tenaga_pendukung.nama', 
                label: 'Jenis',
                format: (row: any) => {
                    const peserta = getPesertaData(row);
                    return peserta?.jenis_tenaga_pendukung?.nama || '-';
                }
            },
        ],
    };

    // Get columns based on current participant type
    const pesertaColumns = specificColumns[jenisPeserta.value] || specificColumns['atlet'];
    return [...pesertaColumns, ...baseColumns];
});

const selected = ref<number[]>([]);
const pageIndex = ref();

const actions = (row: any) => [
    {
        label: 'Detail',
        icon: 'eye',
        onClick: () => router.visit(`/pemeriksaan/${props.pemeriksaan.id}/peserta/${row.id}`),
    },
    {
        label: 'Edit',
        icon: 'pencil',
        onClick: () => router.visit(`/pemeriksaan/${props.pemeriksaan.id}/peserta/${row.id}/edit`),
    },
    {
        label: 'Hapus',
        icon: 'trash',
        onClick: () => pageIndex.value.handleDeleteRow(row),
    },
];

const deleteSelected = async () => {
    try {
        await axios.post(`/pemeriksaan/${props.pemeriksaan.id}/peserta/destroy-selected`, {
            ids: selected.value,
        });
        selected.value = [];
        pageIndex.value?.refreshData();
        toast({ title: 'Data berhasil dihapus', variant: 'success' });
    } catch (error: any) {
        toast({ title: error.response?.data?.message || 'Terjadi kesalahan', variant: 'destructive' });
    }
};

// Helper function untuk mendapatkan label jenis peserta
const getPesertaLabel = computed(() => {
    switch(jenisPeserta.value) {
        case 'atlet':
            return 'Atlet';
        case 'pelatih':
            return 'Pelatih';
        case 'tenaga_pendukung':
            return 'Tenaga Pendukung';
        default:
            return 'Atlet';
    }
});
</script>

<template>
    <PageIndex
        :title="`Peserta ${getPesertaLabel}`"
        :breadcrumbs="breadcrumbs"
        :columns="columns"
        :create-url="`/pemeriksaan/${pemeriksaan.id}/peserta/create`"
        :actions="actions"
        :selected="selected"
        @update:selected="(val) => (selected = val)"
        :on-delete-selected="deleteSelected"
        :api-endpoint="`/api/pemeriksaan/${pemeriksaan.id}/peserta/${jenisPeserta}`"
        ref="pageIndex"
        :on-toast="toast"
        :showImport="false"
    >
        <template #header-extra>
            <div class="bg-card mb-4 rounded-lg border p-4">
                <h3 class="mb-2 text-lg font-semibold">Informasi Pemeriksaan</h3>
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
                </div>
            </div>
        </template>
    </PageIndex>
</template>