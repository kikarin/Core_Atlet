<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { ref, computed, watch } from 'vue';

const { save } = useHandleFormSave();

const props = defineProps<{
    atletId: number | null;
    mode: 'create' | 'edit';
    initialData?: any;
    redirectUrl?: string;
}>();

const formData = ref<Record<string, any>>({
    id: props.initialData?.id || undefined,
    atlet_id: props.atletId,
    jenis_dokumen_id: props.initialData?.jenis_dokumen_id || '',
    nomor: props.initialData?.nomor || '',
    file: null,
});

const formInputInitialData = computed(() => ({ ...formData.value }));

watch(() => props.initialData, (newVal) => {
    if (newVal) {
        Object.assign(formData.value, newVal);
        if (props.atletId) {
            formData.value.atlet_id = props.atletId;
        }
    }
}, { immediate: true, deep: true });

const formInputs = computed(() => [
    { name: 'jenis_dokumen_id', label: 'Jenis Dokumen', type: 'number' as const, placeholder: 'Masukkan jenis dokumen' }, // Temporary as text, will be select
    { name: 'nomor', label: 'Nomor Dokumen', type: 'number' as const, placeholder: 'Masukkan nomor dokumen' },
    { name: 'file', label: 'File Dokumen', type: 'file' as const, placeholder: 'Upload file dokumen (pdf/gambar)' },
]);

const handleSave = (dataFromFormInput: any, setFormErrors: (errors: Record<string, string>) => void) => {
    const formFields = { ...formData.value, ...dataFromFormInput };
    if (props.atletId && !formFields.atlet_id) {
        formFields.atlet_id = props.atletId;
    }
    const baseUrl = `/atlet/${props.atletId}/dokumen`;
    save(formFields, {
        url: baseUrl,
        mode: formData.value.id ? 'edit' : 'create',
        id: formData.value.id,
        successMessage: formData.value.id ? 'Dokumen berhasil diperbarui!' : 'Dokumen berhasil ditambahkan!',
        errorMessage: formData.value.id ? 'Gagal memperbarui dokumen.' : 'Gagal menambah dokumen.',
        onError: (errors: Record<string, string>) => {
            setFormErrors(errors);
        },
        redirectUrl: props.redirectUrl ?? `/atlet/${props.atletId}/dokumen`
    });
};
</script>

<template>
    <FormInput
        :form-inputs="formInputs"
        :initial-data="formInputInitialData"
        @save="handleSave"
    />
</template> 