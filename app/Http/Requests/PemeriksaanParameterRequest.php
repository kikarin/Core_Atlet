<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PemeriksaanParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pemeriksaan_id' => 'sometimes|exists:pemeriksaan,id',
            'nama_parameter' => 'required|string|max:200',
            'satuan'         => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'pemeriksaan_id.required' => 'Pemeriksaan wajib dipilih.',
            'pemeriksaan_id.exists'   => 'Pemeriksaan tidak valid.',
            'nama_parameter.required' => 'Nama parameter wajib diisi.',
            'nama_parameter.max'      => 'Nama parameter maksimal 200 karakter.',
            'satuan.max'              => 'Satuan maksimal 100 karakter.',
        ];
    }
}
