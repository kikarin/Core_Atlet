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
            'cabor_id' => 'required|exists:cabor,id',
            'cabor_kategori_id' => 'required|exists:cabor_kategori,id',
            'tenaga_pendukung_id' => 'required|exists:tenaga_pendukungs,id',
            'jenis_tenaga_pendukung_id' => 'required|exists:mst_jenis_tenaga_pendukung,id',
            'is_active' => 'required|boolean',
        ];
        if ($this->isMethod('patch') || $this->isMethod('put')) {
            $rules['id'] = 'required|exists:cabor_kategori_tenaga_pendukung,id';
        }
        return $rules;
    }

    public function messages(): array
    {
        return [
            'cabor_id.required' => 'Cabor wajib dipilih.',
            'cabor_id.exists' => 'Cabor tidak valid.',
            'cabor_kategori_id.required' => 'Kategori wajib dipilih.',
            'cabor_kategori_id.exists' => 'Kategori tidak valid.',
            'tenaga_pendukung_id.required' => 'Tenaga Pendukung wajib dipilih.',
            'tenaga_pendukung_id.exists' => 'Tenaga Pendukung tidak valid.',
            'jenis_tenaga_pendukung_id.required' => 'Jenis Tenaga Pendukung wajib dipilih.',
            'jenis_tenaga_pendukung_id.exists' => 'Jenis Tenaga Pendukung tidak valid.',
            'is_active.required' => 'Status wajib diisi.',
            'is_active.boolean' => 'Status harus berupa aktif/nonaktif.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(array_map(function ($value) {
            return $value === '' ? null : $value;
        }, $this->all()));
        if (($this->isMethod('patch') || $this->isMethod('put')) && $this->route('id')) {
            $this->merge(['id' => $this->route('id')]);
        }
    }
} 