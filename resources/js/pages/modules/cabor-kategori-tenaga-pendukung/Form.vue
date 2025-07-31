<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

const { save } = useHandleFormSave();

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: Record<string, any>;
}>();

const jenisTenagaPendukungOptions = ref<{ value: number; label: string }[]>([]);

onMounted(async () => {
    try {
        const res = await axios.get('/api/jenis-tenaga-pendukung-list');
        jenisTenagaPendukungOptions.value = (res.data || []).map((item: { id: number; nama: string }) => ({
            value: item.id,
            label: item.nama,
        }));
    } catch (e) {
        console.error('Gagal mengambil data jenis tenaga pendukung', e);
        jenisTenagaPendukungOptions.value = [];
    }
});

const formData = computed(() => ({
    jenis_tenaga_pendukung_id: props.initialData?.jenis_tenaga_pendukung_id || '',
    id: props.initialData?.id || undefined,
}));

const formInputs = computed(() => [
    {
        name: 'jenis_tenaga_pendukung_id',
        label: 'Jenis Tenaga Pendukung',
        type: 'select' as const,
        placeholder: 'Pilih jenis tenaga pendukung',
        required: true,
        options: jenisTenagaPendukungOptions.value,
    },
]);

const handleSave = (form: any) => {
    const formData: Record<string, any> = {
        jenis_tenaga_pendukung_id: form.jenis_tenaga_pendukung_id,
    };

    if (props.mode === 'edit' && props.initialData?.id) {
        formData.id = props.initialData.id;
    }

    save(formData, {
        url: '/cabor-kategori-tenaga-pendukung',
        mode: props.mode,
        id: props.initialData?.id,
        successMessage: props.mode === 'create' ? 'Jenis tenaga pendukung berhasil ditambahkan!' : 'Jenis tenaga pendukung berhasil diperbarui!',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan jenis tenaga pendukung.' : 'Gagal memperbarui jenis tenaga pendukung.',
        redirectUrl: `/cabor-kategori/${props.initialData?.cabor_kategori_id}/tenaga-pendukung`,
    });
};
</script>

<template>
    <FormInput :form-inputs="formInputs" :initial-data="formData" @save="handleSave" />
</template>
