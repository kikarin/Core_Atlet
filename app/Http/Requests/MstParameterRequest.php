<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MstParameterRequest extends FormRequest
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
            'nama'   => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
        ];

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            $rules['nama']   = 'required|string|max:255|unique:mst_parameter,nama,'.$this->id;
            $rules['satuan'] = 'required|string|max:255|unique:mst_parameter,satuan,'.$this->id;
        } else {
            $rules['nama']   = 'required|string|max:255|unique:mst_parameter,nama';
            $rules['satuan'] = 'required|string|max:255|unique:mst_parameter,satuan';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nama.required'   => 'Nama parameter wajib diisi.',
            'nama.string'     => 'Nama parameter harus berupa teks.',
            'nama.max'        => 'Nama parameter tidak boleh lebih dari 255 karakter.',
            'nama.unique'     => 'Nama parameter sudah ada.',
            'satuan.required' => 'Satuan parameter wajib diisi.',
            'satuan.string'   => 'Satuan parameter harus berupa teks.',
            'satuan.max'      => 'Satuan parameter tidak boleh lebih dari 255 karakter.',
            'satuan.unique'   => 'Satuan parameter sudah ada.',
        ];
    }
}
