<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import axios from 'axios';
import { ArrowRight, Plus, Trash2 } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    pesertaType?: string;
    registrationData?: Record<string, any>;
}>();

const emit = defineEmits<{
    submit: [data: any];
    skip: [];
}>();

const step5Data = computed(() => props.registrationData?.step_5 || {});
const dokumenList = ref<
    Array<{
        tempId: number;
        jenis_dokumen_id: number | null;
        nomor: string;
        file: File | null;
    }>
>(step5Data.value.dokumen || []);

const jenisDokumenOptions = ref<{ value: number; label: string }[]>([]);

let tempIdCounter = 0;

onMounted(async () => {
    try {
        const res = await axios.get('/api/jenis-dokumen-list');
        jenisDokumenOptions.value = res.data.map((item: { id: number; nama: string }) => ({ value: item.id, label: item.nama }));
    } catch (e) {
        console.error('Gagal mengambil data jenis dokumen', e);
    }
});

const addDokumen = () => {
    dokumenList.value.push({
        tempId: ++tempIdCounter,
        jenis_dokumen_id: null,
        nomor: '',
        file: null,
    });
};

const removeDokumen = (tempId: number) => {
    const index = dokumenList.value.findIndex((d) => d.tempId === tempId);
    if (index > -1) {
        dokumenList.value.splice(index, 1);
    }
};

const handleSubmit = () => {
    const data = {
        dokumen: dokumenList.value.map((d) => ({
            jenis_dokumen_id: d.jenis_dokumen_id,
            nomor: d.nomor,
            file: d.file,
        })),
    };
    emit('submit', data);
};

if (dokumenList.value.length === 0) {
    addDokumen();
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold">Dokumen (Opsional)</h2>
            <p class="text-muted-foreground mt-2">Tambahkan dokumen pendukung. Langkah ini dapat dilewati.</p>
        </div>

        <Card v-for="(dokumen, index) in dokumenList" :key="dokumen.tempId" class="mb-4">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>Dokumen {{ index + 1 }}</CardTitle>
                    <Button type="button" variant="ghost" size="icon" @click="removeDokumen(dokumen.tempId)" :disabled="dokumenList.length === 1">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-4">
                    <div>
                        <Label>Jenis Dokumen</Label>
                        <Select v-model="dokumen.jenis_dokumen_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Jenis Dokumen" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null">Pilih Jenis Dokumen</SelectItem>
                                <SelectItem v-for="option in jenisDokumenOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label>Nomor</Label>
                        <Input v-model="dokumen.nomor" placeholder="Masukkan nomor dokumen" />
                    </div>
                    <div>
                        <Label>File Dokumen</Label>
                        <Input type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" @change="(e: any) => (dokumen.file = e.target.files[0])" />
                    </div>
                </div>
            </CardContent>
        </Card>

        <Button type="button" variant="outline" @click="addDokumen">
            <Plus class="mr-2 h-4 w-4" />
            Tambah Dokumen
        </Button>

        <div class="flex justify-end">
            <Button @click="handleSubmit" size="lg">
                Ajukan Pendaftaran
                <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
