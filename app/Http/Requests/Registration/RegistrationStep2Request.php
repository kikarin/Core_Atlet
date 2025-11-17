<?php

namespace App\Http\Requests\Registration;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationStep2Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get peserta_type dari registration
        $registration = \App\Models\PesertaRegistration::where('user_id', auth()->id())
            ->where('status', '!=', 'approved')
            ->first();

        $pesertaType = $registration?->peserta_type ?? $this->input('peserta_type');

        $rules = [];

        // Common rules untuk semua peserta
        $commonRules = [
            'nama'                => 'required|string|max:200',
            'jenis_kelamin'       => 'required|in:L,P',
            'tempat_lahir'        => 'nullable|string|max:100',
            'tanggal_lahir'       => 'nullable|date',
            'tanggal_bergabung'   => 'nullable|date',
            'alamat'              => 'nullable|string',
            'kecamatan_id'        => 'nullable|integer|exists:mst_kecamatan,id',
            'kelurahan_id'        => 'nullable|integer|exists:mst_desa,id',
            'no_hp'               => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:200',
            'kategori_pesertas'   => 'nullable|array',
            'kategori_pesertas.*' => 'exists:mst_kategori_peserta,id',
            'file'                => 'nullable|mimes:jpg,png,jpeg,webp|max:2048',
        ];

        // Rules khusus per jenis peserta
        switch ($pesertaType) {
            case 'atlet':
                $rules = array_merge($commonRules, [
                    'nik'           => 'nullable|string|size:16',
                    'nisn'          => 'nullable|string|max:30',
                    'agama'         => 'nullable|string|max:50',
                    'sekolah'       => 'nullable|string',
                    'kelas_sekolah' => 'nullable|string',
                    'ukuran_baju'   => 'nullable|string',
                    'ukuran_celana' => 'nullable|string',
                    'ukuran_sepatu' => 'nullable|string',
                ]);
                break;

            case 'pelatih':
                $rules = array_merge($commonRules, [
                    'nik'                      => 'nullable|string|size:16',
                    'pekerjaan_selain_melatih' => 'nullable|string|max:200',
                ]);
                break;

            case 'tenaga_pendukung':
                $rules = array_merge($commonRules, [
                    'nik' => 'nullable|string|size:16',
                ]);
                break;
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'kecamatan_id' => $this->kecamatan_id && $this->kecamatan_id !== '' ? (int) $this->kecamatan_id : null,
            'kelurahan_id' => $this->kelurahan_id && $this->kelurahan_id !== '' ? (int) $this->kelurahan_id : null,
        ]);
    }
}
