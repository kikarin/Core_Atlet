<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { ref } from 'vue';

const { save } = useHandleFormSave();

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: Record<string, any>;
}>();

// Gunakan ref, bukan computed, agar reactive dan bisa diubah
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
    file: null, // file baru
    is_delete_foto: 0, // flag hapus foto
});

const formInputs = [
    { 
        name: 'nik', 
        label: 'NIK', 
        type: 'text' as const, 
        placeholder: 'Masukkan NIK (16 digit)', 
        required: true,
        help: 'NIK harus tepat 16 digit angka'
    },
    { name: 'nama', label: 'Nama', type: 'text' as const, placeholder: 'Masukkan nama', required: true },
    { name: 'jenis_kelamin', label: 'Jenis Kelamin', type: 'select' as const, required: true, options: [ { value: 'L', label: 'Laki-laki' }, { value: 'P', label: 'Perempuan' } ] },
    { name: 'tempat_lahir', label: 'Tempat Lahir', type: 'text' as const, placeholder: 'Masukkan tempat lahir' },
    { name: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date' as const, placeholder: 'Pilih tanggal lahir' },
    { name: 'alamat', label: 'Alamat', type: 'textarea' as const, placeholder: 'Masukkan alamat' },
    { name: 'kecamatan_id', label: 'Kecamatan', type: 'select' as const, placeholder: 'Pilih Kecamatan', options: [] },
    { name: 'kelurahan_id', label: 'Kelurahan', type: 'select' as const, placeholder: 'Pilih Kelurahan', options: [] },
    { name: 'no_hp', label: 'No HP', type: 'text' as const, placeholder: 'Masukkan nomor HP' },
    { name: 'email', label: 'Email', type: 'email' as const, placeholder: 'Masukkan email' },
    { name: 'is_active', label: 'Status', type: 'select' as const, required: true, options: [ { value: 1, label: 'Aktif' }, { value: 0, label: 'Nonaktif' } ] },
    { name: 'file', label: 'Foto', type: 'file' as const, placeholder: 'Upload foto' },
];

const handleSave = async (form: any) => {
    // Gabungkan data dari formData dan form
    const formFields = { ...formData.value, ...form };
    
    // For edit mode, make sure to include the ID
    if (props.mode === 'edit' && props.initialData?.id) {
        formFields.id = props.initialData.id;
    }
    
    console.log('Form fields to send:', formFields);
    
    save(formFields, {
        url: '/atlet',
        mode: props.mode,
        id: props.initialData?.id,
        successMessage: props.mode === 'create' ? 'Atlet berhasil ditambahkan' : 'Atlet berhasil diperbarui',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan atlet' : 'Gagal memperbarui atlet',
        redirectUrl: '/atlet',
    });
};
</script>

<template>
    <div>
        <FormInput :form-inputs="formInputs" :initial-data="formData" @save="handleSave" />
    </div>
</template> 