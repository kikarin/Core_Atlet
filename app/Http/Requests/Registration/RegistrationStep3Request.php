<?php

namespace App\Http\Requests\Registration;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationStep3Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Step 3: Sertifikat (opsional)
        return [
            'sertifikat' => 'nullable|array',
            'sertifikat.*.nama_sertifikat' => 'required_with:sertifikat|string|max:255',
            'sertifikat.*.penyelenggara' => 'nullable|string|max:255',
            'sertifikat.*.tanggal_terbit' => 'nullable|date',
            'sertifikat.*.file' => 'nullable|mimes:jpg,png,jpeg,pdf,webp|max:4096',
            'files' => 'nullable|array',
            'files.*' => 'mimes:jpg,png,jpeg,pdf,webp|max:4096',
            'nama_sertifikat' => 'nullable|array',
            'nama_sertifikat.*' => 'nullable|string|max:255',
            'penyelenggara' => 'nullable|array',
            'penyelenggara.*' => 'nullable|string|max:255',
            'tanggal_terbit' => 'nullable|array',
            'tanggal_terbit.*' => 'nullable|date',
        ];
    }
}
