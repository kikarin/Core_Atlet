<script setup lang="ts">
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';

const { save } = useHandleFormSave();

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: Record<string, any>;
}>();

const formData = ref({
    nik: props.initialData?.nik || '',
    nisn: props.initialData?.nisn || '',
    nama: props.initialData?.nama || '',
    jenis_kelamin: props.initialData?.jenis_kelamin || '',
    tempat_lahir: props.initialData?.tempat_lahir || '',
    tanggal_lahir: props.initialData?.tanggal_lahir || '',
    agama: props.initialData?.agama || '',
    tanggal_bergabung: props.initialData?.tanggal_bergabung || '',
    alamat: props.initialData?.alamat || '',
    sekolah: props.initialData?.sekolah || '',
    kelas_sekolah: props.initialData?.kelas_sekolah || '',
    ukuran_baju: props.initialData?.ukuran_baju || '',
    ukuran_celana: props.initialData?.ukuran_celana || '',
    ukuran_sepatu: props.initialData?.ukuran_sepatu || '',
    kecamatan_id: props.initialData?.kecamatan_id || '',
    kelurahan_id: props.initialData?.kelurahan_id || '',
    kategori_atlet_id: props.initialData?.kategori_atlet_id || '',
    no_hp: props.initialData?.no_hp || '',
    email: props.initialData?.email || '',
    is_active: props.initialData?.is_active !== undefined ? props.initialData.is_active : 1,
    foto: props.initialData?.foto || '',
    id: props.initialData?.id || undefined,
    file: null,
    is_delete_foto: 0,
});

const kecamatanOptions = ref<{ value: number; label: string }[]>([]);
const kelurahanOptions = ref<{ value: number; label: string }[]>([]);
const kategoriAtletOptions = ref<{ value: number; label: string }[]>([]);

onMounted(async () => {
    try {
        const res = await axios.get('/api/kecamatan-list');
        kecamatanOptions.value = (res.data || []).map((item: { id: number; nama: string }) => ({ value: item.id, label: item.nama }));

        if (props.mode === 'edit' && formData.value.kecamatan_id) {
            const kelurahanRes = await axios.get(`/api/kelurahan-by-kecamatan/${formData.value.kecamatan_id}`);
            kelurahanOptions.value = kelurahanRes.data.map((item: { id: number; nama: string }) => ({ value: item.id, label: item.nama }));
        }

        const kategoriAtletRes = await axios.get('/api/kategori-atlet-list');
        kategoriAtletOptions.value = (kategoriAtletRes.data || []).map((item: { id: number; nama: string }) => ({ value: item.id, label: item.nama }));
    } catch (e) {
        console.error('Gagal mengambil data kecamatan/kelurahan/kategori atlet', e);
        kecamatanOptions.value = [];
        kategoriAtletOptions.value = [];
    }
});

watch(
    () => formData.value.kecamatan_id,
    async (newVal, oldVal) => {
        if (newVal !== oldVal) {
            kelurahanOptions.value = [];
            formData.value.kelurahan_id = '';
            if (newVal) {
                try {
                    const res = await axios.get(`/api/kelurahan-by-kecamatan/${newVal}`);
                    kelurahanOptions.value = res.data.map((item: { id: number; nama: string }) => ({ value: item.id, label: item.nama }));
                } catch (e) {
                    console.error('Gagal mengambil data kelurahan', e);
                    kelurahanOptions.value = [];
                }
            }
        }
    },
);

const formInputs = computed(() => [
    {
        name: 'nik',
        label: 'NIK/NISN',
        type: 'text' as const,
        placeholder: 'Masukkan NIK',
        required: false,
    },
    {
        name: 'nisn',
        label: 'NISN',
        type: 'text' as const,
        placeholder: 'Masukkan NISN',
        required: false,
    },
    { name: 'nama', label: 'Nama', type: 'text' as const, placeholder: 'Masukkan nama', required: true },
    {
        name: 'jenis_kelamin',
        label: 'Jenis Kelamin',
        type: 'select' as const,
        required: true,
        options: [
            { value: 'L', label: 'Laki-laki' },
            { value: 'P', label: 'Perempuan' },
        ],
    },
    { name: 'tempat_lahir', label: 'Tempat Lahir', type: 'text' as const, placeholder: 'Masukkan tempat lahir' },
    { name: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date' as const, placeholder: 'Pilih tanggal lahir' },
    { name: 'agama', label: 'Agama', type: 'text' as const, placeholder: 'Masukkan agama' },
    { name: 'alamat', label: 'Alamat', type: 'textarea' as const, placeholder: 'Masukkan alamat' },
    { name: 'sekolah', label: 'Sekolah', type: 'text' as const, placeholder: 'Masukkan sekolah' },
    { name: 'kelas_sekolah', label: 'Kelas Sekolah', type: 'text' as const, placeholder: 'Masukkan kelas sekolah' },
    { name: 'ukuran_baju', label: 'Ukuran Baju', type: 'text' as const, placeholder: 'Masukkan ukuran baju' },
    { name: 'ukuran_celana', label: 'Ukuran Celana', type: 'text' as const, placeholder: 'Masukkan ukuran celana' },
    { name: 'ukuran_sepatu', label: 'Ukuran Sepatu', type: 'text' as const, placeholder: 'Masukkan ukuran sepatu' },
    { name: 'kecamatan_id', label: 'Kecamatan', type: 'select' as const, placeholder: 'Pilih Kecamatan', options: kecamatanOptions.value },
    { name: 'kelurahan_id', label: 'Kelurahan', type: 'select' as const, placeholder: 'Pilih Kelurahan', options: kelurahanOptions.value },
    { name: 'kategori_atlet_id', label: 'Kategori Atlet', type: 'select' as const, placeholder: 'Pilih Kategori Atlet', options: kategoriAtletOptions.value },
    { name: 'no_hp', label: 'No HP', type: 'text' as const, placeholder: 'Masukkan nomor HP' },
    { name: 'email', label: 'Email', type: 'email' as const, placeholder: 'Masukkan email' },
    { name: 'tanggal_bergabung', label: 'Tanggal Bergabung', type: 'date' as const, placeholder: 'Pilih tanggal bergabung' },
    {
        name: 'is_active',
        label: 'Status',
        type: 'select' as const,
        required: true,
        options: [
            { value: 1, label: 'Aktif' },
            { value: 0, label: 'Nonaktif' },
        ],
    },
    { name: 'file', label: 'Foto', type: 'file' as const, placeholder: 'Upload foto' },
]);

function handleFieldUpdate({ field, value }: { field: string; value: any }) {
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
        onSuccess: (page: any) => {
            const id = page?.props?.item?.id || page?.props?.item?.data?.id;
            if (props.mode === 'create') {
                if (id) {
                    window.location.href = `/atlet/${id}/edit`;
                } else {
                    window.location.href = '/atlet';
                }
            } else if (props.mode === 'edit') {
                if (id) {
                    window.location.href = `/atlet/${id}/edit`;
                } else {
                    window.location.href = '/atlet';
                }
            }
        },
    });
};
</script>

<template>
    <div>
        <FormInput
            :form-inputs="formInputs"
            :initial-data="formData"
            :disable-auto-reset="props.mode === 'create'"
            @save="handleSave"
            @field-updated="handleFieldUpdate"
        />
    </div>
</template>
