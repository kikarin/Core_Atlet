import { ref } from 'vue';

export function useSertifikatEdit() {
  const showEditModal = ref(false);
  const sertifikatToEdit = ref<any | null>(null);

  function openEditSertifikat(sertifikat: any) {
    sertifikatToEdit.value = sertifikat;
    showEditModal.value = true;
  }

  function closeEditModal() {
    showEditModal.value = false;
    sertifikatToEdit.value = null;
  }

  function onSavedSertifikat() {
    closeEditModal();
    // Parent bisa listen event 'sertifikat-edited' jika ingin reload
  }

  return {
    showEditModal,
    sertifikatToEdit,
    openEditSertifikat,
    closeEditModal,
    onSavedSertifikat,
  };
} 