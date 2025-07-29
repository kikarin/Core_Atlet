<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { computed } from 'vue';

const { save } = useHandleFormSave();

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: Record<string, any>;
    infoHeader?: any;
}>();

const formData = computed(() => ({
    deskripsi: props.initialData?.deskripsi || '',
    satuan: props.initialData?.satuan || '',
    nilai_target: props.initialData?.nilai_target || '',
    id: props.initialData?.id || undefined,
}));

const formInputs = [
    {
        name: 'deskripsi',
        label: 'Deskripsi Target',
        type: 'text' as const,
        placeholder: 'Masukkan deskripsi target',
        required: true,
    },
    {
        name: 'satuan',
        label: 'Satuan',
        type: 'text' as const,
        placeholder: 'Masukkan satuan (opsional)',
        required: false,
    },
    {
        name: 'nilai_target',
        label: 'Nilai Target',
        type: 'text' as const,
        placeholder: 'Masukkan nilai target (opsional)',
        required: false,
    },
];

const handleSave = (form: any) => {
    const dataToSave: Record<string, any> = {
        deskripsi: form.deskripsi,
        satuan: form.satuan,
        nilai_target: form.nilai_target,
        program_latihan_id: props.infoHeader?.program_latihan_id,
        jenis_target: props.infoHeader?.jenis_target,
    };
    if (props.mode === 'edit' && props.initialData?.id) {
        dataToSave.id = props.initialData.id;
    }
    save(dataToSave, {
        url: '/target-latihan',
        mode: props.mode,
        id: props.initialData?.id,
        successMessage: props.mode === 'create' ? 'Target latihan berhasil ditambahkan' : 'Target latihan berhasil diperbarui',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan target latihan' : 'Gagal memperbarui target latihan',
        redirectUrl: `/program-latihan/${props.infoHeader?.program_latihan_id}/target-latihan/${props.infoHeader?.jenis_target}`,
    });
};
</script>

<template>
    <FormInput :form-inputs="formInputs" :initial-data="formData" @save="handleSave" />
</template>
