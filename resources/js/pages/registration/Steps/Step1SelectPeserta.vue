<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { UserCircle2, HandHeart, HeartHandshake, ArrowRight } from 'lucide-vue-next';

const props = defineProps<{
    registrationData?: Record<string, any>;
}>();

const emit = defineEmits<{
    submit: [data: { peserta_type: string }];
}>();

const selectedType = ref<string>(props.registrationData?.step_1?.peserta_type || '');

watch(
    () => props.registrationData?.step_1?.peserta_type,
    (val) => {
        if (val && val !== selectedType.value) {
            selectedType.value = val;
        }
    },
);

const pesertaTypes = [
    {
        value: 'atlet',
        label: 'Atlet',
        description: 'Daftar sebagai atlet/peserta olahraga',
        icon: UserCircle2,
        color: 'bg-blue-50 border-blue-200 hover:bg-blue-100',
    },
    {
        value: 'pelatih',
        label: 'Pelatih',
        description: 'Daftar sebagai pelatih/coach',
        icon: HandHeart,
        color: 'bg-green-50 border-green-200 hover:bg-green-100',
    },
    {
        value: 'tenaga_pendukung',
        label: 'Tenaga Pendukung',
        description: 'Daftar sebagai tenaga pendukung',
        icon: HeartHandshake,
        color: 'bg-yellow-50 border-yellow-200 hover:bg-yellow-100',
    },
];

const canSubmit = computed(() => selectedType.value !== '');

const handleSubmit = () => {
    if (canSubmit.value) {
        emit('submit', { peserta_type: selectedType.value });
    }
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold">Pilih Jenis Peserta</h2>
            <p class="text-muted-foreground mt-2">Pilih jenis peserta yang sesuai dengan Anda</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <Card
                v-for="type in pesertaTypes"
                :key="type.value"
                class="cursor-pointer transition-all"
                :class="
                    selectedType === type.value
                        ? 'border-primary ring-2 ring-primary'
                        : type.color
                "
                @click="selectedType = type.value"
            >
                <CardHeader class="text-center">
                    <component :is="type.icon" class="mx-auto mb-2 h-12 w-12" :class="selectedType === type.value ? 'text-primary' : 'text-muted-foreground'" />
                    <CardTitle class="text-gray-500">{{ type.label }}</CardTitle>
                    <CardDescription>{{ type.description }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-center">
                        <div
                            class="flex h-5 w-5 items-center justify-center rounded-full border-2"
                            :class="selectedType === type.value ? 'border-primary bg-primary' : 'border-muted'"
                        >
                            <div v-if="selectedType === type.value" class="h-2 w-2 rounded-full bg-white" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="flex justify-end">
            <Button @click="handleSubmit" :disabled="!canSubmit" size="lg">
                Lanjutkan
                <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
        </div>
    </div>
</template>

