<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaborKategoriTenagaPendukungRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'cabor_id'                  => 'required|exists:cabor,id',
            'cabor_kategori_id'         => 'required|exists:cabor_kategori,id',
            'tenaga_pendukung_ids'      => 'required|array|min:1',
            'tenaga_pendukung_ids.*'    => 'required|exists:tenaga_pendukungs,id',
            'jenis_tenaga_pendukung_id' => 'required|exists:mst_jenis_tenaga_pendukung,id',
            'is_active'                 => 'required|boolean',
        ];

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            $rules['id'] = 'required';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'cabor_id.required'                  => 'Cabor harus dipilih.',
            'cabor_id.exists'                    => 'Cabor tidak valid.',
            'cabor_kategori_id.required'         => 'Kategori harus dipilih.',
            'cabor_kategori_id.exists'           => 'Kategori tidak valid.',
            'tenaga_pendukung_ids.required'      => 'Tenaga Pendukung harus dipilih minimal 1.',
            'tenaga_pendukung_ids.array'         => 'Tenaga Pendukung harus berupa array.',
            'tenaga_pendukung_ids.min'           => 'Tenaga Pendukung harus dipilih minimal 1.',
            'tenaga_pendukung_ids.*.required'    => 'Tenaga Pendukung tidak boleh kosong.',
            'tenaga_pendukung_ids.*.exists'      => 'Tenaga Pendukung yang dipilih tidak valid.',
            'jenis_tenaga_pendukung_id.required' => 'Jenis tenaga pendukung harus dipilih.',
            'jenis_tenaga_pendukung_id.exists'   => 'Jenis tenaga pendukung yang dipilih tidak valid.',
            'is_active.required'                 => 'Status aktif harus dipilih.',
            'is_active.boolean'                  => 'Status aktif tidak valid.',
        ];
    }
}
