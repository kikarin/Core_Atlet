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

const posisiAtletOptions = ref<{ value: number; label: string }[]>([]);

onMounted(async () => {
    try {
        const res = await axios.get('/api/posisi-atlet-list');
        posisiAtletOptions.value = (res.data || []).map((item: { id: number; nama: string }) => ({ 
            value: item.id, 
            label: item.nama 
        }));
    } catch (e) {
        console.error('Gagal mengambil data posisi atlet', e);
        posisiAtletOptions.value = [];
    }
});

const formData = computed(() => ({
    posisi_atlet_id: props.initialData?.posisi_atlet_id || '',
    id: props.initialData?.id || undefined,
}));

const formInputs = computed(() => [
    {
        name: 'posisi_atlet_id',
        label: 'Posisi Atlet',
        type: 'select' as const,
        placeholder: 'Pilih posisi atlet',
        required: true,
        options: posisiAtletOptions.value,
    },
]);

const handleSave = (form: any) => {
    const formData: Record<string, any> = {
        posisi_atlet_id: form.posisi_atlet_id,
    };

    if (props.mode === 'edit' && props.initialData?.id) {
        formData.id = props.initialData.id;
    }

    save(formData, {
        url: '/cabor-kategori-atlet',
        mode: props.mode,
        id: props.initialData?.id,
        successMessage: props.mode === 'create' ? 'Posisi atlet berhasil ditambahkan!' : 'Posisi atlet berhasil diperbarui!',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan posisi atlet.' : 'Gagal memperbarui posisi atlet.',
         redirectUrl: `/cabor-kategori/${props.initialData?.cabor_kategori_id}/atlet`,
    });
};
</script>

<template>
    <FormInput :form-inputs="formInputs" :initial-data="formData" @save="handleSave" />
</template> 