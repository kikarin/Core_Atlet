import { useToast } from '@/components/ui/toast/useToast';
import { router } from '@inertiajs/vue3';

type SaveOptions = {
    url: string;
    redirectUrl?: string;
    mode: 'create' | 'edit';
    id?: number | string;
    successMessage?: string;
    errorMessage?: string;
    onSuccess?: () => void;
};

export function useHandleFormSave() {
    const { toast } = useToast();

    /**
     * Mengembalikan error field ke pemanggil, hanya error umum yang pakai toast
     */
    const handleError = (errors: Record<string, any>, fallbackMessage: string, setFormErrors?: (errors: Record<string, string>) => void) => {
        if (!errors || Object.keys(errors).length === 0) {
            toast({ title: fallbackMessage, variant: 'destructive' });
            return;
        }
        // Jika ada setFormErrors, lempar error ke form
        if (setFormErrors) {
            setFormErrors(errors);
        } else {
            // Fallback: tetap pakai toast jika tidak ada handler
            Object.entries(errors).forEach(([field, message]) => {
                toast({
                    title: `${field}: ${message}`,
                    variant: 'destructive',
                });
            });
        }
    };

    const save = (data: Record<string, any>, options: SaveOptions & { setFormErrors?: (errors: Record<string, string>) => void }) => {
        const {
            url,
            redirectUrl = url,
            mode,
            id,
            successMessage = 'Data berhasil disimpan',
            errorMessage = 'Gagal menyimpan data',
            onSuccess,
            setFormErrors,
        } = options;

        console.log('Form data being sent:', data);
        console.log('Has file:', !!data.file);
        if (data.file) {
            console.log('File details:', {
                name: data.file.name,
                size: data.file.size,
                type: data.file.type
            });
        }

        // Check if we have file uploads and need to use FormData
        const hasFiles = Object.values(data).some(value => value instanceof File);
        let requestData = data;

        if (hasFiles) {
            const formData = new FormData();
            Object.entries(data).forEach(([key, value]) => {
                if (value instanceof File) {
                    formData.append(key, value);
                } else if (Array.isArray(value)) {
                    value.forEach(item => formData.append(`${key}[]`, item));
                } else {
                    if (value === null || value === undefined) {
                        formData.append(key, '');
                    } else {
                        formData.append(key, value);
                    }
                }
            });
            requestData = formData;
            console.log('Using FormData for file upload');
        }

        // Tambahkan log untuk debug field yang dikirim
        console.log('RequestData (final):', requestData);
        if (requestData instanceof FormData) {
            for (let pair of requestData.entries()) {
                console.log(pair[0]+ ': ' + pair[1]);
            }
        }

        // For edit operations, we'll use POST with _method=PUT to handle file uploads properly
        if (mode === 'edit' && id) {
            if (requestData instanceof FormData) {
                requestData.append('_method', 'PUT');
            } else {
                requestData = { ...requestData, _method: 'PUT' };
            }
            
            return router.post(`${url}/${id}`, requestData, {
                ...(requestData instanceof FormData ? { forceFormData: true } : {}),
                      onSuccess: () => {
                          toast({ title: successMessage, variant: 'success' });
                          if (onSuccess) {
                              onSuccess();
                          } else {
                              router.visit(redirectUrl);
                          }
                      },
                      onError: (errors) => handleError(errors, errorMessage, setFormErrors),
            });
        }
        
        // For create operations
        return router.post(url, requestData, {
            ...(requestData instanceof FormData ? { forceFormData: true } : {}),
                      onSuccess: () => {
                          toast({ title: successMessage, variant: 'success' });
                          if (onSuccess) {
                              onSuccess();
                          } else {
                              router.visit(redirectUrl);
                          }
                      },
                      onError: (errors) => handleError(errors, errorMessage, setFormErrors),
                  });
    };

    return { save };
}
