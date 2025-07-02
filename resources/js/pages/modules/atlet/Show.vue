<script setup lang="ts">
import { useToast } from '@/components/ui/toast/useToast';
import PageShow from '@/pages/modules/base-page/PageShow.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppTabs from '@/components/AppTabs.vue';
import ShowOrangTua from './ShowOrangTua.vue';

const { toast } = useToast();

const props = defineProps<{
    item: {
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
        created_by_user: {
            id: number;
            name: string;
        } | null;
        updated_at: string;
        updated_by_user: {
            id: number;
            name: string;
        } | null;
        atlet_orang_tua?: {
            id: number;
            atlet_id: number;
            created_at: string;
            updated_at: string;
            created_by_user: { name: string } | null;
            updated_by_user: { name: string } | null;
        } | null;
    };
}>();

const activeTab = ref('atlet-data');

const dynamicTitle = computed(() => {
  if (activeTab.value === 'atlet-data') {
    return `: Atlet ${props.item.nama}`;
  } else if (activeTab.value === 'orang-tua-data') {
    return `: Orang Tua/Wali ${props.item.nama}`;
  }
  return `Atlet: ${props.item.nama}`;
});

const breadcrumbs = [
    { title: 'Atlet', href: '/atlet' },
    { title: 'Detail Atlet', href: `/atlet/${props.item.id}` },
];

const fields = computed(() => {
    return [
        { label: 'NIK', value: props.item?.nik || '-' },
        { label: 'Nama', value: props.item?.nama || '-' },
        {
            label: 'Jenis Kelamin',
            value: props.item?.jenis_kelamin === 'L' ? 'Laki-laki' : props.item?.jenis_kelamin === 'P' ? 'Perempuan' : '-',
            className: props.item?.jenis_kelamin === 'L' ? 'text-blue-600' : props.item?.jenis_kelamin === 'P' ? 'text-pink-600' : '',
        },
        { label: 'Tempat Lahir', value: props.item?.tempat_lahir || '-' },
        {
            label: 'Tanggal Lahir',
            value: props.item?.tanggal_lahir
                ? new Date(props.item.tanggal_lahir).toLocaleDateString('id-ID', {
                      day: 'numeric',
                      month: 'numeric',
                      year: 'numeric',
                  })
                : '-',
        },
        { label: 'Alamat', value: props.item?.alamat || '-', className: 'sm:col-span-2' },
        { label: 'Kecamatan', value: props.item?.kecamatan_id ? `ID: ${props.item.kecamatan_id}` : '-' },
        { label: 'Kelurahan', value: props.item?.kelurahan_id ? `ID: ${props.item.kelurahan_id}` : '-' },
        { label: 'No HP', value: props.item?.no_hp || '-' },
        { label: 'Email', value: props.item?.email || '-' },
        {
            label: 'Status',
            value: props.item?.is_active ? 'Aktif' : 'Nonaktif',
            className: props.item?.is_active ? 'text-green-600' : 'text-red-600',
        },
        {
            label: 'Foto',
            value: props.item?.foto || '',
            type: 'image' as const,
            className: 'sm:col-span-2',
            imageConfig: {
                size: 'md' as const,
                labelText: 'Klik untuk melihat lebih besar'
            }
        },
    ];
});

const actionFields = computed(() => [
    { label: 'Created At', value: new Date(props.item.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) },
    { label: 'Created By', value: props.item.created_by_user?.name || '-' },
    { label: 'Updated At', value: new Date(props.item.updated_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) },
    { label: 'Updated By', value: props.item.updated_by_user?.name || '-' },
]);

const orangTuaActionFields = computed(() => {
  const o = props.item.atlet_orang_tua;
  return [
    {
      label: 'Created At',
      value: o?.created_at ? new Date(o.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) : '-',
    },
    {
      label: 'Created By',
      value: o?.created_by_user?.name || '-',
    },
    {
      label: 'Updated At',
      value: o?.updated_at ? new Date(o.updated_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) : '-',
    },
    {
      label: 'Updated By',
      value: o?.updated_by_user?.name || '-',
    },
  ];
});

const tabsConfig = [
    {
        value: 'atlet-data',
        label: 'Data Atlet',
    },
    {
        value: 'orang-tua-data',
        label: 'Data Orang Tua/Wali',
    },
];

const handleEditAtlet = () => {
    router.visit(`/atlet/${props.item.id}/edit`);
};

const handleDeleteAtlet = () => {
    router.delete(`/atlet/${props.item.id}`, {
        onSuccess: () => {
            toast({ title: 'Atlet berhasil dihapus', variant: 'success' });
            router.visit('/atlet');
        },
        onError: () => {
            toast({ title: 'Gagal menghapus atlet', variant: 'destructive' });
        },
    });
};

const handleEditOrangTua = () => {
    if (props.item.atlet_orang_tua) {
        router.visit(`/atlet/${props.item.id}/edit?tab=orang-tua-data`);
    }
};

const handleDeleteOrangTua = () => {
    if (props.item.atlet_orang_tua) {
        router.delete(`/atlet/${props.item.id}/orang-tua/${props.item.atlet_orang_tua.id}`, {
            onSuccess: () => {
                toast({ title: 'Data orang tua/wali berhasil dihapus', variant: 'success' });
                router.visit(`/atlet/${props.item.id}`, {
                    data: { tab: 'atlet-data' },
                    preserveState: true,
                    preserveScroll: true
                });
            },
            onError: () => {
                toast({ title: 'Gagal menghapus data orang tua/wali', variant: 'destructive' });
            },
        });
    }
};

const currentOnEditHandler = computed(() => {
    if (activeTab.value === 'atlet-data') {
        return handleEditAtlet;
    } else if (activeTab.value === 'orang-tua-data') {
        return props.item.atlet_orang_tua ? handleEditOrangTua : undefined;
    }
    return undefined;
});

const currentOnDeleteHandler = computed(() => {
    if (activeTab.value === 'atlet-data') {
        return handleDeleteAtlet;
    } else if (activeTab.value === 'orang-tua-data') {
        return props.item.atlet_orang_tua ? handleDeleteOrangTua : undefined;
    }
    return undefined;
});
</script>

<template>
    <PageShow
        :title="dynamicTitle"
        :breadcrumbs="breadcrumbs"
        :fields="activeTab === 'atlet-data' ? fields : []"
        :actionFields="activeTab === 'atlet-data' ? actionFields : orangTuaActionFields"
        :back-url="'/atlet'"
        :on-edit="currentOnEditHandler"
        :on-delete="currentOnDeleteHandler"
    >
        <template #tabs>
            <AppTabs
                :tabs="tabsConfig"
                :default-value="'atlet-data'"
                v-model="activeTab"
    />
        </template>
        <template #custom>
            <div v-if="activeTab === 'orang-tua-data'">
                <ShowOrangTua :orang-tua="props.item.atlet_orang_tua || null" />
            </div>
        </template>
    </PageShow>
</template>