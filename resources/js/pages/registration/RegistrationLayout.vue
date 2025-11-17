<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    currentStep: number;
    totalSteps?: number;
}>();

const totalSteps = props.totalSteps ?? 5;

const steps = computed(() => {
    const stepLabels = ['Pilih Jenis Peserta', 'Data Diri', 'Sertifikat', 'Prestasi', 'Dokumen'];

    return Array.from({ length: totalSteps }, (_, i) => ({
        number: i + 1,
        label: stepLabels[i] || `Step ${i + 1}`,
        completed: i + 1 < props.currentStep,
        current: i + 1 === props.currentStep,
    }));
});
</script>

<template>
    <div class="bg-background min-h-screen">
        <div class="mx-auto max-w-4xl px-4 py-8">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div v-for="(step, index) in steps" :key="step.number" class="flex flex-1 items-center">
                        <div class="flex flex-col items-center">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full border-2 transition-colors"
                                :class="
                                    step.completed
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : step.current
                                          ? 'border-primary bg-primary/10 text-primary'
                                          : 'border-muted bg-muted text-muted-foreground'
                                "
                            >
                                <Check v-if="step.completed" class="h-5 w-5" />
                                <span v-else class="text-sm font-semibold">{{ step.number }}</span>
                            </div>
                            <span class="mt-2 text-xs font-medium" :class="step.current ? 'text-primary' : 'text-muted-foreground'">
                                {{ step.label }}
                            </span>
                        </div>
                        <div v-if="index < steps.length - 1" class="mx-2 h-0.5 flex-1" :class="step.completed ? 'bg-primary' : 'bg-muted'" />
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="bg-card border-border rounded-xl border p-6 shadow-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
