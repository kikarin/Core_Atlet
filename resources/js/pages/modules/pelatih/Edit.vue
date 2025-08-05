<script setup lang="ts">
import PageEdit from '@/pages/modules/base-page/PageEdit.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Form from './Form.vue';
import FormKesehatan from './FormKesehatan.vue';
import FormAkun from './FormAkun.vue';

interface PelatihItem {
    id: number;
    nik: string;
    nama: string;
    jenis_kelamin: string;
    tempat_lahir: string;
    tanggal_lahir: string;
    alamat: string;
    kecamatan_id: number | null;
    kelurahan_id: number | null;
    no_hp: string;
    email: string;
    is_active: number;
    foto: string;
    created_at: string;
    created_by_user: { id: number; name: string } | null;
    updated_at: string;
    updated_by_user: { id: number; name: string } | null;
}

const props = defineProps<{
    item: PelatihItem;
}>();

const pelatihId = ref<number | null>(props.item.id || null);

// Ambil tab dari query string
function getTabFromUrl(url: string, fallback = 'pelatih-data') {
    if (url.includes('tab=')) {
        return new URLSearchParams(url.split('?')[1]).get('tab') || fallback;
    }
    return fallback;
}

const page = usePage();
const initialActiveTab = getTabFromUrl(page.url);
const activeTab = ref(initialActiveTab);

watch(activeTab, (val) => {
    const url = `/pelatih/${props.item.id}/edit?tab=${val}`;
    router.visit(url, { replace: true, preserveState: true, preserveScroll: true, only: [] });
});

watch(
    () => page.url,
    (newUrl) => {
        const tab = getTabFromUrl(newUrl);
        if (tab !== activeTab.value) {
            activeTab.value = tab;
        }
    },
);

// Computed property untuk judul dinamis
const dynamicTitle = computed(() => {
    if (activeTab.value === 'pelatih-data') {
        return `Pelatih : ${props.item.nama || '-'}`;
    } else if (activeTab.value === 'sertifikat-data') {
        return `Sertifikat : ${props.item.nama || '-'}`;
    } else if (activeTab.value === 'prestasi-data') {
        return `Prestasi : ${props.item.nama || '-'}`;
    } else if (activeTab.value === 'kesehatan-data') {
        return `Kesehatan : ${props.item.nama || '-'}`;
    } else if (activeTab.value === 'dokumen-data') {
        return `Dokumen : ${props.item.nama || '-'}`;
    } else if (activeTab.value === 'akun-data') {
        return `Akun : ${props.item.nama || '-'}`;
    }
    return 'Edit Pelatih';
});

const breadcrumbs = computed(() => [
    { title: 'Pelatih', href: '/pelatih' },
    { title: 'Data Pelatih', href: `/pelatih/${props.item.id}/edit` },
]);

// Configuration for tabs
const tabsConfig = computed(() => [
    {
        value: 'pelatih-data',
        label: 'Pelatih',
        component: Form,
        props: { mode: 'edit', initialData: props.item },
    },
    {
        value: 'sertifikat-data',
        label: 'Sertifikat',
        isRedirectTab: true,
        onClick: () => router.visit(`/pelatih/${props.item.id}/sertifikat`),
    },
    {
        value: 'prestasi-data',
        label: 'Prestasi',
        isRedirectTab: true,
        onClick: () => router.visit(`/pelatih/${props.item.id}/prestasi`),
    },
    {
        value: 'kesehatan-data',
        label: 'Kesehatan',
        component: FormKesehatan,
        props: { pelatihId: pelatihId.value, mode: 'edit' },
    },
    {
        value: 'dokumen-data',
        label: 'Dokumen',
        isRedirectTab: true,
        onClick: () => router.visit(`/pelatih/${props.item.id}/dokumen`),
    },
    {
        value: 'akun-data',
        label: 'Akun',
        component: FormAkun,
        props: { mode: 'edit', initialData: props.item },
    },
]);
</script>

<template>
    <PageEdit
        :title="dynamicTitle"
        :breadcrumbs="breadcrumbs"
        back-url="/pelatih"
        :tabs-config="tabsConfig"
        v-model:activeTabValue="activeTab"
        :show-edit-prefix="false"
    >
        <template #default>
            <div class="mt-4 flex justify-end">
                <button
                    class="border-input bg-background hover:bg-accent hover:text-accent-foreground inline-flex items-center gap-1 rounded-md border px-3 py-2 text-sm transition-colors"
                    @click="() => router.visit(`/pelatih/${props.item.id}/sertifikat`)"
                >
                    Lihat Sertifikat
                </button>
                <button
                    class="border-input bg-background hover:bg-accent hover:text-accent-foreground inline-flex items-center gap-1 rounded-md border px-3 py-2 text-sm transition-colors"
                    @click="() => router.visit(`/pelatih/${props.item.id}/prestasi`)"
                >
                    Lihat Prestasi
                </button>
                <button
                    class="border-input bg-background hover:bg-accent hover:text-accent-foreground inline-flex items-center gap-1 rounded-md border px-3 py-2 text-sm transition-colors"
                    @click="() => router.visit(`/pelatih/${props.item.id}/dokumen`)"
                >
                    Lihat Dokumen
                </button>
            </div>
        </template>
    </PageEdit>
</template>
