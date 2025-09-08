<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { computed } from 'vue';

const { save } = useHandleFormSave();

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: Record<string, any>;
}>();

const formData = computed(() => {
    const base = {
        nama: props.initialData?.nama || '',
        satuan: props.initialData?.satuan || '',
        id: props.initialData?.id || undefined,
    };
    return base;
});

const formInputs = [
    {
        name: 'nama',
        label: 'Nama Parameter',
        type: 'text' as const,
        placeholder: 'Masukkan nama Parameter',
        required: true,
    },
    {
        name: 'satuan',
        label: 'Satuan',
        type: 'text' as const,
        placeholder: 'Masukkan satuan',
        required: true,
    },
];

const handleSave = (form: any) => {
    const dataToSave: Record<string, any> = {
        nama: form.nama,
        satuan: form.satuan,
    };

    if (props.mode === 'edit' && props.initialData?.id) {
        dataToSave.id = props.initialData.id;
    }

    save(dataToSave, {
        url: '/data-master/parameter',
        mode: props.mode,
        id: props.initialData?.id,
        successMessage: props.mode === 'create' ? 'Data Parameter berhasil ditambahkan' : 'Data Parameter berhasil diperbarui',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan data Parameter' : 'Gagal memperbarui data Parameter',
        redirectUrl: '/data-master/parameter',
    });
};
</script>

<template>
    <FormInput :form-inputs="formInputs" :initial-data="formData" @save="handleSave" />
</template>
