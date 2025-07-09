<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';

const { save } = useHandleFormSave();

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: Record<string, any>;
}>();

const caborOptions = ref<{ value: number; label: string }[]>([]);

const fetchCaborOptions = async () => {
    const res = await axios.get('/api/cabor-list');
    caborOptions.value = (res.data || []).map((item: any) => ({ value: item.id, label: item.nama }));
};

onMounted(fetchCaborOptions);

const formData = computed(() => {
    const base = {
        cabor_id: props.initialData?.cabor_id || '',
        nama: props.initialData?.nama || '',
        deskripsi: props.initialData?.deskripsi || '',
        id: props.initialData?.id || undefined,
    };
    return base;
});

const formInputs = computed(() => [
    {
        name: 'cabor_id',
        label: 'Cabor',
        type: 'select' as const,
        options: caborOptions.value,
        placeholder: 'Pilih Cabor',
        required: true,
    },
    {
        name: 'nama',
        label: 'Nama Kategori',
        type: 'text' as const,
        placeholder: 'Masukkan nama kategori',
        required: true,
    },
    {
        name: 'deskripsi',
        label: 'Deskripsi',
        type: 'textarea' as const,
        placeholder: 'Masukkan deskripsi (opsional)',
        required: false,
    },
]);

const handleSave = (form: any) => {
    const dataToSave: Record<string, any> = {
        cabor_id: form.cabor_id,
        nama: form.nama,
        deskripsi: form.deskripsi,
    };
    if (props.mode === 'edit' && props.initialData?.id) {
        dataToSave.id = props.initialData.id;
    }
    save(dataToSave, {
        url: '/cabor-kategori',
        mode: props.mode,
        id: props.initialData?.id,
        successMessage: props.mode === 'create' ? 'Kategori berhasil ditambahkan' : 'Kategori berhasil diperbarui',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan kategori' : 'Gagal memperbarui kategori',
        redirectUrl: '/cabor-kategori',
    });
};
</script>

<template>
    <FormInput :form-inputs="formInputs" :initial-data="formData" @save="handleSave" />
</template> 