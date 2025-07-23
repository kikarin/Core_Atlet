<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import SelectTableMultiple from '../components/SelectTableMultiple.vue';

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: any;
    pemeriksaan: any;
    jenisPeserta?: string;
}>();

function getJenisPeserta() {
    if (props.jenisPeserta && ["atlet", "pelatih", "tenaga_pendukung"].includes(props.jenisPeserta)) return props.jenisPeserta;
    const ziggy: any = usePage().props.ziggy;
    const jenis = (ziggy?.query?.jenis_peserta || '').toString();
    if (["atlet", "pelatih", "tenaga_pendukung"].includes(jenis)) return jenis;
    return "atlet";
}

const jenisPeserta = computed(() => getJenisPeserta());

const { save } = useHandleFormSave();

const selectionState = ref({
    atlet_ids: props.initialData?.atlets?.map((a: any) => a.id) || [],
    pelatih_ids: props.initialData?.pelatihs?.map((p: any) => p.id) || [],
    tenaga_pendukung_ids: props.initialData?.tenaga_pendukung?.map((t: any) => t.id) || [],
});

const formState = ref({
    ref_status_pemeriksaan_id: props.initialData?.ref_status_pemeriksaan_id || '',
    catatan_umum: props.initialData?.catatan_umum || '',
    peserta_nama: props.initialData?.peserta?.nama || '',
    peserta_tipe: props.initialData?.peserta_type?.split('\\').pop() || '',
});

const formInputs = computed(() => {
    const inputs: any[] = [
        { 
            name: 'ref_status_pemeriksaan_id', 
            label: 'Status Pemeriksaan', 
            type: 'select' as const, 
            required: true, 
            options: (usePage().props.ref_status_pemeriksaan as any[] || []).map(s => ({ value: s.id, label: s.nama })) 
        },
        { 
            name: 'catatan_umum', 
            label: 'Catatan Umum', 
            type: 'textarea' as const 
        },
    ];

    if (props.mode === 'edit') {
        inputs.unshift({ name: 'peserta_tipe', label: 'Tipe Peserta', type: 'text' as const, disabled: true });
        inputs.unshift({ name: 'peserta_nama', label: 'Nama Peserta', type: 'text' as const, disabled: true });
    }
    
    return inputs;
});

const handleSave = (form: any) => {
    let dataToSave;
    const url = `/pemeriksaan/${props.pemeriksaan.id}/peserta`;

    if (props.mode === 'create') {
        dataToSave = { ...form, ...selectionState.value };
    } else {
        dataToSave = {
            ref_status_pemeriksaan_id: form.ref_status_pemeriksaan_id,
            catatan_umum: form.catatan_umum,
            peserta_id: props.initialData.peserta_id,
            peserta_type: props.initialData.peserta_type,
        };
    }

    save(
        dataToSave,
        {
            url: props.mode === 'edit' ? `${url}/${props.initialData.id}` : url,
            mode: props.mode,
            redirectUrl: `/pemeriksaan/${props.pemeriksaan.id}/peserta?jenis_peserta=${jenisPeserta.value}`,
        },
    );
};

const columns = [
    {
        key: 'foto',
        label: 'Foto',
        format: (row: any) =>
            row.foto
                ? `<div class='cursor-pointer' onclick=\"window.open('${row.foto}', '_blank')\"><img src='${row.foto}' alt='Foto' class='w-12 h-12 object-cover rounded-full border hover:shadow-md transition-shadow' /></div>`
                : "<div class='w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs'>No</div>",
    },
    { key: 'nama', label: 'Nama' },
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
            row.tanggal_lahir ? new Date(row.tanggal_lahir).toLocaleDateString('id-ID', { day: 'numeric', month: 'numeric', year: 'numeric' }) : '-',
    },
    { key: 'no_hp', label: 'No HP' },
];
</script>

<template>
    <FormInput :form-inputs="formInputs" @save="handleSave" :initial-data="formState">
        <template #custom-fields v-if="mode === 'create'">
            <SelectTableMultiple
                v-if="jenisPeserta === 'atlet'"
                v-model:selected-ids="selectionState.atlet_ids"
                label="Atlet"
                :endpoint="`/api/cabor-kategori-atlet?cabor_kategori_id=${pemeriksaan.cabor_kategori_id}`"
                :columns="columns"
                id-key="atlet_id"
                name-key="atlet_nama"
                :auto-select-all="true"
            />
            <SelectTableMultiple
                v-if="jenisPeserta === 'pelatih'"
                v-model:selected-ids="selectionState.pelatih_ids"
                label="Pelatih"
                :endpoint="`/api/cabor-kategori-pelatih?cabor_kategori_id=${pemeriksaan.cabor_kategori_id}`"
                :columns="columns"
                id-key="pelatih_id"
                name-key="pelatih_nama"
                :auto-select-all="true"
            />
            <SelectTableMultiple
                v-if="jenisPeserta === 'tenaga_pendukung'"
                v-model:selected-ids="selectionState.tenaga_pendukung_ids"
                label="Tenaga Pendukung"
                :endpoint="`/api/cabor-kategori-tenaga-pendukung?cabor_kategori_id=${pemeriksaan.cabor_kategori_id}`"
                :columns="columns"
                id-key="tenaga_pendukung_id"
                name-key="tenaga_pendukung_nama"
                :auto-select-all="true"
            />
        </template>
    </FormInput>
</template>
