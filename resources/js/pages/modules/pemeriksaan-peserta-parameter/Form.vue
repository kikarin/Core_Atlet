<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { computed, ref } from 'vue';

const { save } = useHandleFormSave();

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: Record<string, any>;
    pemeriksaan: any;
    peserta: any;
    parameters: any[];
}>();

const selectedParameterId = ref(props.initialData?.pemeriksaan_parameter_id || '');

const formInitialData = computed(() => ({
    pemeriksaan_parameter_id: selectedParameterId.value,
    nilai: props.initialData?.nilai ?? '',
    trend: props.initialData?.trend || 'stabil',
    id: props.initialData?.id || undefined,
}));

const formInputs = computed(() => [
    {
        name: 'pemeriksaan_parameter_id',
        label: 'Parameter',
        type: 'select' as const,
        options: (props.parameters || []).map((item: any) => ({ value: item.id, label: item.nama_parameter })),
        placeholder: 'Pilih Parameter',
        required: true,
        modelValue: selectedParameterId.value,
        onUpdateModelValue: (val: any) => (selectedParameterId.value = val),
    },
    {
        name: 'nilai',
        label: 'Nilai',
        type: 'number' as const,
        required: true,
    },
    {
        name: 'trend',
        label: 'Trend',
        type: 'select' as const,
        required: true,
        options: [
            { value: 'stabil', label: 'Stabil' },
            { value: 'penurunan', label: 'Penurunan' },
            { value: 'kenaikan', label: 'Kenaikan' },
        ],
    },
]);

const handleSave = (form: any, setFormErrors: (errors: Record<string, string>) => void) => {
    const dataToSave: Record<string, any> = {
        ...form,
        pemeriksaan_id: props.pemeriksaan.id,
        pemeriksaan_peserta_id: props.peserta.id,
    };
    if (props.mode === 'edit' && props.initialData?.id) {
        dataToSave.id = props.initialData.id;
    }
    save(dataToSave, {
        url: `/pemeriksaan/${props.pemeriksaan.id}/peserta/${props.peserta.id}/parameter`,
        mode: props.mode,
        id: props.initialData?.id,
        successMessage: props.mode === 'create' ? 'Parameter peserta berhasil ditambahkan' : 'Parameter peserta berhasil diperbarui',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan parameter peserta' : 'Gagal memperbarui parameter peserta',
        redirectUrl: `/pemeriksaan/${props.pemeriksaan.id}/peserta/${props.peserta.id}/parameter`,
        onError: setFormErrors,
    });
};
</script>

<template>
    <FormInput :form-inputs="formInputs" :initial-data="formInitialData" @save="handleSave" />
</template> 