<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import FormSertifikat from './FormSertifikat.vue';

const props = defineProps<{
  visible: boolean;
  sertifikat: any | null;
  atletId: number;
  onClose: () => void;
  onSaved: () => void;
}>();

const formKey = ref(0); // force remount form on sertifikat change
watch(() => props.sertifikat, () => { formKey.value++; });

function handleSaved() {
  props.onSaved();
  props.onClose();
}
</script>
<template>
  <Dialog v-model:open="props.visible" @update:open="(val) => { if (!val) props.onClose(); }">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Edit Sertifikat</DialogTitle>
      </DialogHeader>
      <FormSertifikat
        v-if="props.sertifikat"
        :key="formKey"
        :mode="'edit'"
        :atlet-id="props.atletId"
        :initial-data="props.sertifikat"
        @saved="handleSaved"
        @cancel="props.onClose"
      />
      <DialogFooter>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template> 