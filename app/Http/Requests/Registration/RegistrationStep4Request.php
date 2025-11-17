<?php

namespace App\Http\Requests\Registration;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationStep4Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Step 4: Prestasi (opsional)
        return [
            'prestasi'              => 'nullable|array',
            'prestasi.*.nama_event' => 'required_with:prestasi|string|max:255',
            'prestasi.*.tingkat_id' => 'nullable|integer|exists:mst_tingkat,id',
            'prestasi.*.tanggal'    => 'nullable|date',
            'prestasi.*.peringkat'  => 'nullable|string|max:255',
            'prestasi.*.keterangan' => 'nullable|string',
        ];
    }
}
