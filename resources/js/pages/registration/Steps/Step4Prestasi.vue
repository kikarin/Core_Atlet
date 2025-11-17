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

const step4Data = computed(() => props.registrationData?.step_4 || {});
const prestasiList = ref<
    Array<{
        tempId: number;
        nama_event: string;
        tingkat_id: number | null;
        tanggal: string;
        peringkat: string;
        keterangan: string;
        kategori_prestasi_pelatih_id?: number | null;
    }>
>(step4Data.value.prestasi || []);

const tingkatOptions = ref<{ value: number; label: string }[]>([]);
const kategoriPrestasiOptions = ref<{ value: number; label: string }[]>([]);

let tempIdCounter = 0;

onMounted(async () => {
    try {
        const res = await axios.get('/api/tingkat-list');
        tingkatOptions.value = res.data.map((item: { id: number; nama: string }) => ({ value: item.id, label: item.nama }));
        if (props.pesertaType === 'pelatih') {
            const kategoriRes = await axios.get('/api/kategori-prestasi-pelatih-list');
            kategoriPrestasiOptions.value = kategoriRes.data.map((item: { id: number; nama: string }) => ({
                value: item.id,
                label: item.nama,
            }));
        }
    } catch (e) {
        console.error('Gagal mengambil data tingkat', e);
    }
});

const addPrestasi = () => {
    prestasiList.value.push({
        tempId: ++tempIdCounter,
        nama_event: '',
        tingkat_id: null,
        tanggal: '',
        peringkat: '',
        keterangan: '',
        kategori_prestasi_pelatih_id: props.pesertaType === 'pelatih' ? null : undefined,
    });
};

const removePrestasi = (tempId: number) => {
    const index = prestasiList.value.findIndex((p) => p.tempId === tempId);
    if (index > -1) {
        prestasiList.value.splice(index, 1);
    }
};

const handleSubmit = () => {
    const data = {
        prestasi: prestasiList.value.map((p) => ({
            nama_event: p.nama_event,
            tingkat_id: p.tingkat_id,
            tanggal: p.tanggal,
            peringkat: p.peringkat,
            keterangan: p.keterangan,
            ...(props.pesertaType === 'pelatih' ? { kategori_prestasi_pelatih_id: p.kategori_prestasi_pelatih_id } : {}),
        })),
    };
    emit('submit', data);
};

if (prestasiList.value.length === 0) {
    addPrestasi();
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold">Prestasi (Opsional)</h2>
            <p class="text-muted-foreground mt-2">Tambahkan prestasi yang pernah Anda raih. Langkah ini dapat dilewati.</p>
        </div>

        <Card v-for="(prestasi, index) in prestasiList" :key="prestasi.tempId" class="mb-4">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>Prestasi {{ index + 1 }}</CardTitle>
                    <Button type="button" variant="ghost" size="icon" @click="removePrestasi(prestasi.tempId)" :disabled="prestasiList.length === 1">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-4">
                    <div>
                        <Label>Nama Event</Label>
                        <Input v-model="prestasi.nama_event" placeholder="Masukkan nama event" />
                    </div>
                    <div>
                        <Label>Tingkat</Label>
                        <Select v-model="prestasi.tingkat_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Tingkat" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null">Pilih Tingkat</SelectItem>
                                <SelectItem v-for="option in tingkatOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label>Tanggal</Label>
                        <Input v-model="prestasi.tanggal" type="date" />
                    </div>
                    <div>
                        <Label>Peringkat</Label>
                        <Input v-model="prestasi.peringkat" placeholder="Masukkan peringkat" />
                    </div>
                    <div>
                        <Label>Keterangan</Label>
                        <Input v-model="prestasi.keterangan" placeholder="Masukkan keterangan" />
                    </div>
                    <div v-if="props.pesertaType === 'pelatih'">
                        <Label>Kategori Prestasi Pelatih</Label>
                        <Select v-model="prestasi.kategori_prestasi_pelatih_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Kategori Prestasi" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null">Pilih Kategori Prestasi</SelectItem>
                                <SelectItem v-for="option in kategoriPrestasiOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Button type="button" variant="outline" @click="addPrestasi">
            <Plus class="mr-2 h-4 w-4" />
            Tambah Prestasi
        </Button>

        <div class="flex justify-end">
            <Button @click="handleSubmit" size="lg">
                Lanjutkan
                <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
