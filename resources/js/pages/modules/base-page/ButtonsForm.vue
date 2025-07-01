<script setup lang="ts">
import { Button } from '@/components/ui/button';

const emit = defineEmits(['save', 'cancel']);

</script>

<template>
    <div class="mt-4 flex justify-end gap-2">
        <Button type="submit" variant="default"> Save </Button>
        <Button type="button" variant="outline" @click="emit('cancel')"> Cancel </Button>
    </div>
</template>
