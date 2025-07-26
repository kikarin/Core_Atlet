<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { save } = useHandleFormSave();
const page = usePage();
const pemeriksaan = computed(() => page.props.pemeriksaan || {});
const pemeriksaanId = computed(() => pemeriksaan.value.id || (typeof window !== 'undefined' ? window.location.pathname.split('/')[2] : ''));

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: Record<string, any>;
}>();

const formInitialData = computed(() => ({
    nama_parameter: props.initialData?.nama_parameter || '',
    satuan: props.initialData?.satuan || '',
    id: props.initialData?.id || undefined,
}));

const formInputs = computed(() => [
    {
        name: 'nama_parameter',
        label: 'Nama Parameter',
        type: 'text' as const,
        required: true,
    },
    {
        name: 'satuan',
        label: 'Satuan',
        type: 'text' as const,
        required: false,
    },
]);

const handleSave = (form: any, setFormErrors: (errors: Record<string, string>) => void) => {
    const dataToSave: Record<string, any> = {
        ...form,
    };
    if (props.mode === 'edit' && props.initialData?.id) {
        dataToSave.id = props.initialData.id;
    }
    save(dataToSave, {
        url: `/pemeriksaan/${pemeriksaanId.value}/pemeriksaan-parameter`,
        mode: props.mode,
        id: props.initialData?.id,
        successMessage: props.mode === 'create' ? 'Parameter pemeriksaan berhasil ditambahkan' : 'Parameter pemeriksaan berhasil diperbarui',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan parameter pemeriksaan' : 'Gagal memperbarui parameter pemeriksaan',
        redirectUrl: `/pemeriksaan/${pemeriksaanId.value}/pemeriksaan-parameter`,
        onError: setFormErrors,
    });
};
</script>

<template>
    <FormInput :form-inputs="formInputs" :initial-data="formInitialData" @save="handleSave" />
</template>
