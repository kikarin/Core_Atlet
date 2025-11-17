<?php

namespace App\Http\Requests\Registration;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationStep5Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Step 5: Dokumen (opsional)
        return [
            'dokumen'                    => 'nullable|array',
            'dokumen.*.jenis_dokumen_id' => 'nullable|integer|exists:mst_jenis_dokumen,id',
            'dokumen.*.nomor'            => 'nullable|string|max:255',
            'dokumen.*.file'             => 'nullable|mimes:jpg,png,jpeg,pdf,webp|max:4096',
            'files'                      => 'nullable|array',
            'files.*'                    => 'mimes:jpg,png,jpeg,pdf,webp|max:4096',
            'jenis_dokumen_id'           => 'nullable|array',
            'jenis_dokumen_id.*'         => 'nullable|integer|exists:mst_jenis_dokumen,id',
            'nomor'                      => 'nullable|array',
            'nomor.*'                    => 'nullable|string|max:255',
        ];
    }
}
