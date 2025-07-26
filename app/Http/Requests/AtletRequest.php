<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AtletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'nik'               => 'required|string|size:16|unique:atlets,nik,' . $this->id,
            'nama'              => 'required|string|max:200',
            'jenis_kelamin'     => 'required|in:L,P',
            'tempat_lahir'      => 'nullable|string|max:100',
            'tanggal_lahir'     => 'nullable|date',
            'tanggal_bergabung' => 'nullable|date',
            'alamat'            => 'nullable|string',
            'kecamatan_id'      => 'nullable|integer',
            'kelurahan_id'      => 'nullable|integer',
            'no_hp'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:200',
            'is_active'         => 'required|boolean',
            'is_delete_foto'    => 'nullable|boolean',

            // Rules for AtletOrangTua
            'atlet_orang_tua_id'      => 'nullable|integer',
            'nama_ibu_kandung'        => 'nullable|string|max:255',
            'tempat_lahir_ibu'        => 'nullable|string|max:255',
            'tanggal_lahir_ibu'       => 'nullable|date',
            'alamat_ibu'              => 'nullable|string',
            'no_hp_ibu'               => 'nullable|string|max:20',
            'pekerjaan_ibu'           => 'nullable|string|max:255',
            'nama_ayah_kandung'       => 'nullable|string|max:255',
            'tempat_lahir_ayah'       => 'nullable|string|max:255',
            'tanggal_lahir_ayah'      => 'nullable|date',
            'alamat_ayah'             => 'nullable|string',
            'no_hp_ayah'              => 'nullable|string|max:20',
            'pekerjaan_ayah'          => 'nullable|string|max:255',
            'nama_wali'               => 'nullable|string|max:255',
            'tempat_lahir_wali'       => 'nullable|string|max:255',
            'tanggal_lahir_wali'      => 'nullable|date',
            'alamat_wali'             => 'nullable|string',
            'no_hp_wali'              => 'nullable|string|max:20',
            'pekerjaan_wali'          => 'nullable|string|max:255',
        ];

        // Only validate file if it's present in the request
        if ($this->hasFile('file')) {
            $rules['file'] = 'mimes:jpg,png,jpeg,webp|max:2048';
        } else {
            $rules['file'] = 'nullable';
        }

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            $rules['id'] = 'required';
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Convert empty strings to null for optional fields
        $this->merge([
            'kecamatan_id' => $this->kecamatan_id && $this->kecamatan_id !== '' ? (int) $this->kecamatan_id : null,
            'kelurahan_id' => $this->kelurahan_id && $this->kelurahan_id !== '' ? (int) $this->kelurahan_id : null,
            'is_active'    => $this->is_active === '1' || $this->is_active === 1 || $this->is_active === true ? 1 : 0,

            // Prepare AtletOrangTua fields - convert empty strings to null
            'nama_ibu_kandung'  => $this->nama_ibu_kandung ?: null,
            'tempat_lahir_ibu'  => $this->tempat_lahir_ibu ?: null,
            'tanggal_lahir_ibu' => $this->tanggal_lahir_ibu ?: null,
            'alamat_ibu'        => $this->alamat_ibu ?: null,
            'no_hp_ibu'         => $this->no_hp_ibu ?: null,
            'pekerjaan_ibu'     => $this->pekerjaan_ibu ?: null,

            'nama_ayah_kandung'  => $this->nama_ayah_kandung ?: null,
            'tempat_lahir_ayah'  => $this->tempat_lahir_ayah ?: null,
            'tanggal_lahir_ayah' => $this->tanggal_lahir_ayah ?: null,
            'alamat_ayah'        => $this->alamat_ayah ?: null,
            'no_hp_ayah'         => $this->no_hp_ayah ?: null,
            'pekerjaan_ayah'     => $this->pekerjaan_ayah ?: null,

            'nama_wali'          => $this->nama_wali ?: null,
            'tempat_lahir_wali'  => $this->tempat_lahir_wali ?: null,
            'tanggal_lahir_wali' => $this->tanggal_lahir_wali ?: null,
            'alamat_wali'        => $this->alamat_wali ?: null,
            'no_hp_wali'         => $this->no_hp_wali ?: null,
            'pekerjaan_wali'     => $this->pekerjaan_wali ?: null,
        ]);
    }
}
