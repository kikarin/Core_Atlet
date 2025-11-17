<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Trash2, Plus, ArrowRight } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps<{
    pesertaType?: string;
    selectedPesertaType?: string | null;
    registrationData?: Record<string, any>;
    kecamatanOptions?: Array<{ id: number; nama: string }>;
    kategoriPesertaOptions?: Array<{ id: number; nama: string }>;
    parameterUmumMaster?: Array<any>;
}>();

const emit = defineEmits<{
    submit: [data: any];
}>();

const step2Data = computed(() => props.registrationData?.step_2 || {});

// Form data berdasarkan jenis peserta - sync dengan FormInput
const formData = ref<Record<string, any>>({
    ...step2Data.value,
    file: null,
    is_delete_foto: 0,
});

// Ref untuk FormInput component
const formInputRef = ref<InstanceType<typeof FormInput> | null>(null);

const mapOptions = (items?: Array<{ id: number | string; nama: string }>) =>
    (items || []).map((item) => ({
        value: Number(item.id),
        label: item.nama,
    }));

const toOptionValueString = (value: number | string | null | undefined) =>
    value === null || value === undefined ? null : String(value);

const kecamatanOptions = ref<{ value: number; label: string }[]>(mapOptions(props.kecamatanOptions));
const kelurahanOptions = ref<{ value: number; label: string }[]>([]);
const kategoriPesertaOptions = ref<{ value: number; label: string }[]>(
    (props.kategoriPesertaOptions || []).map((item) => ({
        value: Number((item as any).value ?? item.id),
        label: (item as any).label ?? item.nama,
    })),
);

const currentPesertaType = computed(
    () =>
        props.selectedPesertaType ||
        props.pesertaType ||
        props.registrationData?.step_1?.peserta_type ||
        step2Data.value?.peserta_type ||
        'atlet',
);

// Multiple Kategori Peserta
const kategoriPesertas = ref<Array<{ id: string | null; tempId: number }>>([]);
let kategoriPesertaTempIdCounter = 0;

const initializeKategoriPeserta = (ids?: Array<number | string>) => {
    kategoriPesertaTempIdCounter = 0;
    if (ids && ids.length) {
        kategoriPesertas.value = ids.map((id) => ({
            id: toOptionValueString(id),
            tempId: ++kategoriPesertaTempIdCounter,
        }));
    } else {
        kategoriPesertas.value = [
            {
                id: null,
                tempId: ++kategoriPesertaTempIdCounter,
            },
        ];
    }
};

const initializeFormData = () => {
    const baseData =
        step2Data.value && step2Data.value.peserta_type === currentPesertaType.value ? { ...step2Data.value } : {};

    formData.value = {
        ...baseData,
        peserta_type: currentPesertaType.value,
        file: null,
        is_delete_foto: 0,
    };

    initializeKategoriPeserta(baseData.kategori_pesertas);

    if (formData.value.kecamatan_id) {
        axios
            .get(`/api/kelurahan-by-kecamatan/${formData.value.kecamatan_id}`)
            .then((res) => {
                kelurahanOptions.value = mapOptions(res.data);
            })
            .catch((e) => {
                console.error('Gagal mengambil data kelurahan', e);
                kelurahanOptions.value = [];
            });
    } else {
        kelurahanOptions.value = [];
        formData.value.kelurahan_id = '';
    }
};

onMounted(async () => {
    try {
        if (kecamatanOptions.value.length === 0) {
            const res = await axios.get('/api/kecamatan-list');
            kecamatanOptions.value = mapOptions(res.data);
        }

        if (kategoriPesertaOptions.value.length === 0) {
            const kategoriPesertaRes = await axios.get('/api/kategori-peserta-list');
            kategoriPesertaOptions.value = mapOptions(kategoriPesertaRes.data);
        }

        initializeFormData();
    } catch (e) {
        console.error('Gagal mengambil data', e);
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
                    kelurahanOptions.value = mapOptions(res.data);
                } catch (e) {
                    console.error('Gagal mengambil data kelurahan', e);
                }
            }
        }
    },
);

watch([step2Data, currentPesertaType], () => {
    initializeFormData();
});

const addKategoriPeserta = () => {
    kategoriPesertas.value.push({
        id: null,
        tempId: ++kategoriPesertaTempIdCounter,
    });
};

const removeKategoriPeserta = (tempId: number) => {
    const index = kategoriPesertas.value.findIndex((k) => k.tempId === tempId);
    if (index > -1) {
        kategoriPesertas.value.splice(index, 1);
    }
};

const updateKategoriPeserta = (tempId: number, kategoriId: number | string | null) => {
    const item = kategoriPesertas.value.find((k) => k.tempId === tempId);
    if (item) {
        if (kategoriId === null || kategoriId === undefined || kategoriId === '') {
            item.id = null;
        } else {
            item.id = String(kategoriId);
        }
    }
};

const genderOptions = [
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' },
];

const buildCommonInputs = () => [
    { name: 'nama', label: 'Nama', type: 'text' as const, placeholder: 'Masukkan nama', required: true },
    {
        name: 'jenis_kelamin',
        label: 'Jenis Kelamin',
        type: 'select' as const,
        required: true,
        options: genderOptions,
    },
    { name: 'tempat_lahir', label: 'Tempat Lahir', type: 'text' as const, placeholder: 'Masukkan tempat lahir' },
    { name: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date' as const, placeholder: 'Pilih tanggal lahir' },
    { name: 'alamat', label: 'Alamat', type: 'textarea' as const, placeholder: 'Masukkan alamat' },
    {
        name: 'kecamatan_id',
        label: 'Kecamatan',
        type: 'select' as const,
        placeholder: 'Pilih Kecamatan',
        options: kecamatanOptions.value,
    },
    {
        name: 'kelurahan_id',
        label: 'Kelurahan',
        type: 'select' as const,
        placeholder: 'Pilih Kelurahan',
        options: kelurahanOptions.value,
    },
    { name: 'no_hp', label: 'No HP', type: 'text' as const, placeholder: 'Masukkan nomor HP' },
    { name: 'email', label: 'Email', type: 'email' as const, placeholder: 'Masukkan email' },
    { name: 'tanggal_bergabung', label: 'Tanggal Bergabung', type: 'date' as const, placeholder: 'Pilih tanggal bergabung' },
];

const formInputs = computed(() => {
    const common = buildCommonInputs();
    const fotoInput = [{ name: 'file', label: 'Foto', type: 'file' as const, placeholder: 'Upload foto' }];

    switch (currentPesertaType.value) {
        case 'pelatih':
            return [
                { name: 'nik', label: 'NIK', type: 'text' as const, placeholder: 'Masukkan NIK (16 digit)', required: true },
                ...common,
                { name: 'pekerjaan_selain_melatih', label: 'Pekerjaan Selain Melatih', type: 'text' as const, placeholder: 'Masukkan pekerjaan selain melatih' },
                ...fotoInput,
            ];
        case 'tenaga_pendukung':
            return [
                { name: 'nik', label: 'NIK', type: 'text' as const, placeholder: 'Masukkan NIK (16 digit)', required: true },
                ...common,
                ...fotoInput,
            ];
        case 'atlet':
        default:
            return [
                { name: 'nik', label: 'NIK', type: 'text' as const, placeholder: 'Masukkan NIK (16 digit)', required: true },
                { name: 'nisn', label: 'NISN', type: 'text' as const, placeholder: 'Masukkan NISN' },
                ...common,
                { name: 'agama', label: 'Agama', type: 'text' as const, placeholder: 'Masukkan agama' },
                { name: 'sekolah', label: 'Sekolah', type: 'text' as const, placeholder: 'Masukkan sekolah' },
                { name: 'kelas_sekolah', label: 'Kelas Sekolah', type: 'text' as const, placeholder: 'Masukkan kelas sekolah' },
                { name: 'ukuran_baju', label: 'Ukuran Baju', type: 'text' as const, placeholder: 'Masukkan ukuran baju' },
                { name: 'ukuran_celana', label: 'Ukuran Celana', type: 'text' as const, placeholder: 'Masukkan ukuran celana' },
                { name: 'ukuran_sepatu', label: 'Ukuran Sepatu', type: 'text' as const, placeholder: 'Masukkan ukuran sepatu' },
                ...fotoInput,
            ];
    }
});

const handleFieldUpdate = ({ field, value }: { field: string; value: any }) => {
    formData.value[field] = value;
    // Trigger kelurahan load sudah di-handle di watch
};

// Sync formData dengan FormInput via v-model
const formModelValue = computed({
    get: () => formData.value,
    set: (val) => {
        formData.value = { ...formData.value, ...val };
    },
});

const handleSave = (dataFromFormInput?: any, setFormErrors?: (errors: Record<string, string>) => void) => {
    console.log('Step2DataDiri: handleSave called', { dataFromFormInput, formData: formData.value });
    
    // Jika dataFromFormInput tidak ada, ambil dari formData yang sudah ter-update
    const formFields = dataFromFormInput || formData.value;
    
    // Collect kategori peserta IDs
    const kategoriPesertaIds = kategoriPesertas.value
        .map((k) => (k.id !== null && k.id !== undefined ? Number(k.id) : null))
        .filter((id) => id !== null) as number[];

    const finalData = {
        ...formFields,
        peserta_type: currentPesertaType.value,
        kategori_pesertas: kategoriPesertaIds,
    };

    console.log('Step2DataDiri: Submitting data', finalData);
    emit('submit', finalData);
};

// Handler untuk trigger submit dari FormInput
const handleFormSubmit = async () => {
    await nextTick();
    
    console.log('Step2DataDiri: handleFormSubmit called');
    
    // Coba trigger submit dari FormInput component via exposed method
    if (formInputRef.value && typeof (formInputRef.value as any).submit === 'function') {
        console.log('Step2DataDiri: Calling FormInput.submit()');
        (formInputRef.value as any).submit();
    } else {
        // Fallback: langsung submit dengan formData yang sudah ter-update
        console.warn('Step2DataDiri: FormInput ref not available, submitting with current formData');
        handleSave(formData.value);
    }
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold">Data Diri</h2>
            <p class="text-muted-foreground mt-2">Lengkapi data diri Anda</p>
        </div>

        <FormInput
            ref="formInputRef"
            :form-inputs="formInputs"
            :initial-data="formData"
            v-model="formModelValue"
            @save="handleSave"
            @field-updated="handleFieldUpdate"
            :hide-buttons="true"
            :disable-auto-reset="true"
            id="step2-form-input"
        />

        <!-- Multiple Kategori Peserta -->
        <Card>
            <CardHeader>
                <CardTitle>Kategori Peserta</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-for="(kategori, index) in kategoriPesertas" :key="kategori.tempId" class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="mb-2 block text-sm font-medium">Kategori Peserta {{ index + 1 }}</label>
                        <Select
                            :model-value="kategori.id"
                            @update:model-value="(value: string | null) => updateKategoriPeserta(kategori.tempId, value)"
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Kategori Peserta" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null">Pilih Kategori Peserta</SelectItem>
                                <template v-if="kategoriPesertaOptions.length">
                                    <SelectItem
                                    v-for="option in kategoriPesertaOptions"
                                    :key="option.value"
                                    :value="String(option.value)"
                                >
                                    {{ option.label }}
                                    </SelectItem>
                                </template>
                                <template v-else>
                                    <div class="px-3 py-2 text-sm text-muted-foreground">
                                        Tidak ada data kategori peserta.
                                    </div>
                                </template>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        @click="removeKategoriPeserta(kategori.tempId)"
                        :disabled="kategoriPesertas.length === 1"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
                <Button type="button" variant="outline" size="sm" @click="addKategoriPeserta">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Kategori
                </Button>
            </CardContent>
        </Card>

        <div class="flex justify-end">
            <Button @click="handleFormSubmit" size="lg">
                Lanjutkan
                <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
        </div>
    </div>
</template>

