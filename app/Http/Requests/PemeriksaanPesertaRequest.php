<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PemeriksaanPesertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'pemeriksaan_id'            => 'sometimes|exists:pemeriksaan,id',
            'ref_status_pemeriksaan_id' => 'required|exists:ref_status_pemeriksaan,id',
            'catatan_umum'              => 'nullable|string',
        ];

        if ($this->isMethod('post')) {
            $rules['atlet_ids'] = 'nullable|array';
            $rules['atlet_ids.*'] = 'exists:atlets,id';
            $rules['pelatih_ids'] = 'nullable|array';
            $rules['pelatih_ids.*'] = 'exists:pelatihs,id';
            $rules['tenaga_pendukung_ids'] = 'nullable|array';
            $rules['tenaga_pendukung_ids.*'] = 'exists:tenaga_pendukungs,id';
        } else {
            $rules['peserta_type'] = ['required', Rule::in(['App\\Models\\Atlet', 'App\\Models\\Pelatih', 'App\\Models\\TenagaPendukung'])];
            $rules['peserta_id'] = 'required|integer';
        }

        return $rules;
    }
} 