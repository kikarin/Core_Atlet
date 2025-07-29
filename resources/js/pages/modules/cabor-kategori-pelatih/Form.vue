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

const jenisPelatihOptions = ref<{ value: number; label: string }[]>([]);

onMounted(async () => {
    try {
        const res = await axios.get('/api/jenis-pelatih-list');
        jenisPelatihOptions.value = (res.data || []).map((item: { id: number; nama: string }) => ({ 
            value: item.id, 
            label: item.nama 
        }));
    } catch (e) {
        console.error('Gagal mengambil data jenis pelatih', e);
        jenisPelatihOptions.value = [];
    }
});

const formData = computed(() => ({
    jenis_pelatih_id: props.initialData?.jenis_pelatih_id || '',
    id: props.initialData?.id || undefined,
}));

const formInputs = computed(() => [
    {
        name: 'jenis_pelatih_id',
        label: 'Jenis Pelatih',
        type: 'select' as const,
        placeholder: 'Pilih jenis pelatih',
        required: true,
        options: jenisPelatihOptions.value,
    },
]);

const handleSave = (form: any) => {
    const formData: Record<string, any> = {
        jenis_pelatih_id: form.jenis_pelatih_id,
    };

    if (props.mode === 'edit' && props.initialData?.id) {
        formData.id = props.initialData.id;
    }

    save(formData, {
        url: '/cabor-kategori-pelatih',
        mode: props.mode,
        id: props.initialData?.id,
        successMessage: props.mode === 'create' ? 'Jenis pelatih berhasil ditambahkan!' : 'Jenis pelatih berhasil diperbarui!',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan jenis pelatih.' : 'Gagal memperbarui jenis pelatih.',
        redirectUrl: `/cabor-kategori/${props.initialData?.cabor_kategori_id}/pelatih`,
    });
};
</script>

<template>
    <FormInput :form-inputs="formInputs" :initial-data="formData" @save="handleSave" />
</template> 