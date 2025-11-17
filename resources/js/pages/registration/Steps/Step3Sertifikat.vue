<script setup lang="ts">
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Trash2, ArrowRight } from 'lucide-vue-next';

const props = defineProps<{
    pesertaType?: string;
    registrationData?: Record<string, any>;
}>();

const emit = defineEmits<{
    submit: [data: any];
    skip: [];
}>();

const step3Data = computed(() => props.registrationData?.step_3 || {});
const sertifikatList = ref<Array<{
    tempId: number;
    nama_sertifikat: string;
    penyelenggara: string;
    tanggal_terbit: string;
    file: File | null;
}>>(step3Data.value.sertifikat || []);

let tempIdCounter = 0;

const addSertifikat = () => {
    sertifikatList.value.push({
        tempId: ++tempIdCounter,
        nama_sertifikat: '',
        penyelenggara: '',
        tanggal_terbit: '',
        file: null,
    });
};

const removeSertifikat = (tempId: number) => {
    const index = sertifikatList.value.findIndex((s) => s.tempId === tempId);
    if (index > -1) {
        sertifikatList.value.splice(index, 1);
    }
};

const handleSubmit = () => {
    const data = {
        sertifikat: sertifikatList.value.map((s) => ({
            nama_sertifikat: s.nama_sertifikat,
            penyelenggara: s.penyelenggara,
            tanggal_terbit: s.tanggal_terbit,
            file: s.file,
        })),
    };
    emit('submit', data);
};

if (sertifikatList.value.length === 0) {
    addSertifikat();
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold">Sertifikat (Opsional)</h2>
            <p class="text-muted-foreground mt-2">Tambahkan sertifikat yang Anda miliki. Langkah ini dapat dilewati.</p>
        </div>

        <Card v-for="(sertifikat, index) in sertifikatList" :key="sertifikat.tempId" class="mb-4">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>Sertifikat {{ index + 1 }}</CardTitle>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        @click="removeSertifikat(sertifikat.tempId)"
                        :disabled="sertifikatList.length === 1"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-4">
                    <div>
                        <Label>Nama Sertifikat</Label>
                        <Input v-model="sertifikat.nama_sertifikat" placeholder="Masukkan nama sertifikat" />
                    </div>
                    <div>
                        <Label>Penyelenggara</Label>
                        <Input v-model="sertifikat.penyelenggara" placeholder="Masukkan nama penyelenggara" />
                    </div>
                    <div>
                        <Label>Tanggal Terbit</Label>
                        <Input v-model="sertifikat.tanggal_terbit" type="date" />
                    </div>
                    <div>
                        <Label>File Sertifikat</Label>
                        <Input
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                            @change="(e: any) => sertifikat.file = e.target.files[0]"
                        />
                    </div>
                </div>
            </CardContent>
        </Card>

        <Button type="button" variant="outline" @click="addSertifikat">
            <Plus class="mr-2 h-4 w-4" />
            Tambah Sertifikat
        </Button>

        <div class="flex justify-end">
            <Button @click="handleSubmit" size="lg">
                Lanjutkan
                <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
        </div>
    </div>
</template>

