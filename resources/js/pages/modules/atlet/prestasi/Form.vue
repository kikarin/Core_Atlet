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
    nama_event: props.initialData?.nama_event || '',
    tingkat_id: props.initialData?.tingkat_id || null,
    tanggal: props.initialData?.tanggal || '',
    peringkat: props.initialData?.peringkat || '',
    keterangan: props.initialData?.keterangan || '',
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
    { name: 'nama_event', label: 'Nama Event', type: 'text' as const, placeholder: 'Masukkan nama event', required: true },
    { name: 'tingkat_id', label: 'Tingkat', type: 'number' as const, placeholder: 'Masukkan tingkat (opsional)' }, // Menggunakan type number, nanti bisa diganti select jika ada data master tingkat
    { name: 'tanggal', label: 'Tanggal', type: 'date' as const, placeholder: 'Pilih tanggal' },
    { name: 'peringkat', label: 'Peringkat', type: 'text' as const, placeholder: 'Masukkan peringkat (misal: Juara 1, Finalis)' },
    { name: 'keterangan', label: 'Keterangan', type: 'textarea' as const, placeholder: 'Masukkan keterangan tambahan (opsional)' },
]);

const handleSave = (dataFromFormInput: any, setFormErrors: (errors: Record<string, string>) => void) => {
    const formFields = { ...formData.value, ...dataFromFormInput };
    if (props.atletId && !formFields.atlet_id) {
        formFields.atlet_id = props.atletId;
    }
    const baseUrl = `/atlet/${props.atletId}/prestasi`;
    save(formFields, {
        url: baseUrl,
        mode: formData.value.id ? 'edit' : 'create',
        id: formData.value.id,
        successMessage: formData.value.id ? 'Prestasi berhasil diperbarui!' : 'Prestasi berhasil ditambahkan!',
        errorMessage: formData.value.id ? 'Gagal memperbarui prestasi.' : 'Gagal menambah prestasi.',
        onError: (errors: Record<string, string>) => {
            setFormErrors(errors);
        },
        redirectUrl: props.redirectUrl ?? `/atlet/${props.atletId}/prestasi`
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