import { ref } from 'vue';
import { useToast } from '@/components/ui/toast/useToast';
import { router } from '@inertiajs/vue3';
import { useSertifikatEdit } from './sertifikat/useSertifikatEdit';

export function useShowAtlet(item: any) {
  const { toast } = useToast();
  // Sertifikat Edit Modal
  const {
    showEditModal,
    sertifikatToEdit,
    openEditSertifikat,
    closeEditModal,
    onSavedSertifikat,
  } = useSertifikatEdit();

  // Delete Single
  const showDeleteModal = ref(false);
  const sertifikatToDelete = ref<any | null>(null);
  function handleDeleteSertifikat(sertifikat: any) {
    sertifikatToDelete.value = sertifikat;
    showDeleteModal.value = true;
  }
  function confirmDeleteSertifikat() {
    if (!sertifikatToDelete.value) return;
    router.delete(`/atlet/${item.id}/sertifikat/${sertifikatToDelete.value.id}`, {
      onSuccess: () => {
        toast({ title: 'Sertifikat berhasil dihapus', variant: 'success' });
        router.reload();
      },
      onError: () => {
        toast({ title: 'Gagal menghapus sertifikat', variant: 'destructive' });
      },
    });
    showDeleteModal.value = false;
    sertifikatToDelete.value = null;
  }

  // Delete Selected
  const showDeleteSelectedModal = ref(false);
  const idsToDelete = ref<number[]>([]);
  function handleDeleteSelectedSertifikat(ids: number[]) {
    idsToDelete.value = ids;
    showDeleteSelectedModal.value = true;
  }
  function confirmDeleteSelectedSertifikat() {
    if (!idsToDelete.value.length) return;
    Promise.all(idsToDelete.value.map(id => router.delete(`/atlet/${item.id}/sertifikat/${id}`)))
      .then(() => {
        toast({ title: 'Sertifikat terpilih berhasil dihapus', variant: 'success' });
        router.reload();
      })
      .catch(() => {
        toast({ title: 'Gagal menghapus beberapa sertifikat', variant: 'destructive' });
      });
    showDeleteSelectedModal.value = false;
    idsToDelete.value = [];
  }
  const selectedSertifikat = ref<number[]>([]);
  function handleUpdateSelectedSertifikat(val: number[]) {
    selectedSertifikat.value = val;
  }

  // Show Creator Modal
  const showCreatorModal = ref(false);
  const sertifikatCreator = ref<any | null>(null);
  function handleShowCreator(sertifikat: any) {
    sertifikatCreator.value = sertifikat;
    showCreatorModal.value = true;
  }

  // Sertifikat Saved Handler
  function handleSertifikatSaved() {
    onSavedSertifikat();
    router.reload();
  }

  return {
    showEditModal,
    sertifikatToEdit,
    openEditSertifikat,
    closeEditModal,
    onSavedSertifikat,
    showDeleteModal,
    sertifikatToDelete,
    showDeleteSelectedModal,
    idsToDelete,
    selectedSertifikat,
    showCreatorModal,
    sertifikatCreator,
    handleEditSertifikat: openEditSertifikat,
    handleDeleteSertifikat,
    confirmDeleteSertifikat,
    handleDeleteSelectedSertifikat,
    handleUpdateSelectedSertifikat,
    confirmDeleteSelectedSertifikat,
    handleShowCreator,
    handleSertifikatSaved,
  };
} 