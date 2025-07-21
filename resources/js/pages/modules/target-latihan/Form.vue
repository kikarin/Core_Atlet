<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { useHandleFormSave } from '@/composables/useHandleFormSave';
import FormInput from '@/pages/modules/base-page/FormInput.vue';
import { computed } from 'vue';

const { save } = useHandleFormSave();

const props = defineProps<{
    mode: 'create' | 'edit';
    initialData?: Record<string, any>;
    infoHeader?: any;
}>();

const info = computed(() => {
    if (props.infoHeader) return props.infoHeader;
    return {
        nama_program: props.initialData?.program_latihan?.nama_program || '-',
        cabor_nama: props.initialData?.program_latihan?.cabor?.nama || '-',
        periode_mulai: props.initialData?.program_latihan?.periode_mulai || '-',
        periode_selesai: props.initialData?.program_latihan?.periode_selesai || '-',
        jenis_target: props.initialData?.jenis_target || '-',
    };
});

const formData = computed(() => ({
    deskripsi: props.initialData?.deskripsi || '',
    satuan: props.initialData?.satuan || '',
    nilai_target: props.initialData?.nilai_target || '',
    id: props.initialData?.id || undefined,
}));

const formInputs = [
    {
        name: 'deskripsi',
        label: 'Deskripsi Target',
        type: 'text' as const,
        placeholder: 'Masukkan deskripsi target',
        required: true,
    },
    {
        name: 'satuan',
        label: 'Satuan',
        type: 'text' as const,
        placeholder: 'Masukkan satuan (opsional)',
        required: false,
    },
    {
        name: 'nilai_target',
        label: 'Nilai Target',
        type: 'text' as const,
        placeholder: 'Masukkan nilai target (opsional)',
        required: false,
    },
];

const handleSave = (form: any) => {
    const dataToSave: Record<string, any> = {
        deskripsi: form.deskripsi,
        satuan: form.satuan,
        nilai_target: form.nilai_target,
        program_latihan_id: props.infoHeader?.program_latihan_id,
        jenis_target: props.infoHeader?.jenis_target,
    };
    if (props.mode === 'edit' && props.initialData?.id) {
        dataToSave.id = props.initialData.id;
    }
    save(dataToSave, {
        url: '/target-latihan',
        mode: props.mode,
        id: props.initialData?.id,
        successMessage: props.mode === 'create' ? 'Target latihan berhasil ditambahkan' : 'Target latihan berhasil diperbarui',
        errorMessage: props.mode === 'create' ? 'Gagal menyimpan target latihan' : 'Gagal memperbarui target latihan',
        redirectUrl: `/program-latihan/${props.infoHeader?.program_latihan_id}/target-latihan/${props.infoHeader?.jenis_target}`,
    });
};
</script>

<template>
    <div class="bg-card mb-4 rounded-lg border p-4">
        <h3 class="mb-2 text-lg font-semibold">Informasi Program Latihan</h3>
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <span class="text-muted-foreground text-sm font-medium">Nama Program:</span>
                <Badge variant="secondary">{{ info.nama_program }}</Badge>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-muted-foreground text-sm font-medium">Cabor:</span>
                <Badge variant="outline">
                    {{ info.cabor_nama }}<template v-if="info.cabor_kategori_nama"> - {{ info.cabor_kategori_nama }}</template>
                </Badge>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-muted-foreground text-sm font-medium">Periode:</span>
                <Badge variant="secondary">{{
                    info.periode_mulai && info.periode_selesai ? `${info.periode_mulai} s/d ${info.periode_selesai}` : '-'
                }}</Badge>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-muted-foreground text-sm font-medium">Jenis Target:</span>
                <Badge variant="outline">{{ info.jenis_target }}</Badge>
            </div>
        </div>
    </div>
    <FormInput :form-inputs="formInputs" :initial-data="formData" @save="handleSave" />
</template>
