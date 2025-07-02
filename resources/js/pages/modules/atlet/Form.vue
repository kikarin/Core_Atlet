<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';

const { save } = useHandleFormSave();

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: Record<string, any>;
}>();

const formData = ref({
    nik: props.initialData?.nik || '',
    nama: props.initialData?.nama || '',
    jenis_kelamin: props.initialData?.jenis_kelamin || '',
    tempat_lahir: props.initialData?.tempat_lahir || '',
    tanggal_lahir: props.initialData?.tanggal_lahir || '',
    alamat: props.initialData?.alamat || '',
    kecamatan_id: props.initialData?.kecamatan_id || '',
    kelurahan_id: props.initialData?.kelurahan_id || '',
    no_hp: props.initialData?.no_hp || '',
    email: props.initialData?.email || '',
    is_active: props.initialData?.is_active !== undefined ? props.initialData.is_active : 1,
    foto: props.initialData?.foto || '',
    id: props.initialData?.id || undefined,
    file: null,
    is_delete_foto: 0, 
});

const kecamatanOptions = ref<{ value: number; label: string; }[]>([]);
const kelurahanOptions = ref<{ value: number; label: string; }[]>([]);

onMounted(async () => {
    try {
        const res = await axios.get('/api/kecamatan');
        kecamatanOptions.value = res.data.map((item: { id: number; nama: string }) => ({ value: item.id, label: item.nama }));

        if (props.mode === 'edit' && formData.value.kecamatan_id) {
            const kelurahanRes = await axios.get(`/api/kelurahan-by-kecamatan/${formData.value.kecamatan_id}`);
            kelurahanOptions.value = kelurahanRes.data.map((item: { id: number; nama: string }) => ({ value: item.id, label: item.nama }));
        }
    } catch (e) {
        console.error("Gagal mengambil data kecamatan/kelurahan", e);
        kecamatanOptions.value = [];
    }
});

watch(() => formData.value.kecamatan_id, async (newVal, oldVal) => {
    if (newVal !== oldVal) { 
        kelurahanOptions.value = []; 
        formData.value.kelurahan_id = ''; 
        if (newVal) {
            try {
                const res = await axios.get(`/api/kelurahan-by-kecamatan/${newVal}`);
                kelurahanOptions.value = res.data.map((item: { id: number; nama: string }) => ({ value: item.id, label: item.nama }));
            } catch (e) {
                console.error("Gagal mengambil data kelurahan", e);
                kelurahanOptions.value = [];
            }
        }
    }
});

const formInputs = computed(() => [
    { 
        name: 'nik', 
        label: 'NIK', 
        type: 'text' as const, 
        placeholder: 'Masukkan NIK (16 digit)', 
        required: true,
    },
    { name: 'nama', label: 'Nama', type: 'text' as const, placeholder: 'Masukkan nama', required: true },
    { name: 'jenis_kelamin', label: 'Jenis Kelamin', type: 'select' as const, required: true, options: [ { value: 'L', label: 'Laki-laki' }, { value: 'P', label: 'Perempuan' } ] },
    { name: 'tempat_lahir', label: 'Tempat Lahir', type: 'text' as const, placeholder: 'Masukkan tempat lahir' },
    { name: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date' as const, placeholder: 'Pilih tanggal lahir' },
    { name: 'alamat', label: 'Alamat', type: 'textarea' as const, placeholder: 'Masukkan alamat' },
    { name: 'kecamatan_id', label: 'Kecamatan', type: 'select' as const, placeholder: 'Pilih Kecamatan', options: kecamatanOptions.value },
    { name: 'kelurahan_id', label: 'Kelurahan', type: 'select' as const, placeholder: 'Pilih Kelurahan', options: kelurahanOptions.value },
    { name: 'no_hp', label: 'No HP', type: 'text' as const, placeholder: 'Masukkan nomor HP' },
    { name: 'email', label: 'Email', type: 'email' as const, placeholder: 'Masukkan email' },
    { name: 'is_active', label: 'Status', type: 'select' as const, required: true, options: [ { value: 1, label: 'Aktif' }, { value: 0, label: 'Nonaktif' } ] },
    { name: 'file', label: 'Foto', type: 'file' as const, placeholder: 'Upload foto' },
]);

function handleFieldUpdate({ field, value }: { field: string, value: any }) {
    if (field === 'kecamatan_id') {
        formData.value.kecamatan_id = value;
    }
}

const handleSave = (dataFromFormInput: any, setFormErrors: (errors: Record<string, string>) => void) => {
    const formFields = { ...formData.value, ...dataFromFormInput }; 
    
    const url = '/atlet';
    
    console.log('Atlet/Form.vue: Form fields to send:', formFields);
    console.log('Atlet/Form.vue: Determined base URL:', url);
    
    save(formFields, {
        url: url,
        mode: props.mode,
        id: props.initialData?.id, 
        successMessage: props.mode === 'create' ? 'Atlet berhasil ditambahkan!' : 'Atlet berhasil diperbarui!',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan atlet.' : 'Gagal memperbarui atlet.',
        onError: (errors: Record<string, string>) => {
            setFormErrors(errors);
        },
        redirectUrl: '/atlet', 
    });
};
</script>

<template>
    <div>
        <FormInput 
            :form-inputs="formInputs" 
            :initial-data="formData" 
            @save="handleSave"
            @field-updated="handleFieldUpdate"
        />
    </div>
</template> 