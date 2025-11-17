<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\RegistrationStep1Request;
use App\Http\Requests\Registration\RegistrationStep2Request;
use App\Http\Requests\Registration\RegistrationStep3Request;
use App\Http\Requests\Registration\RegistrationStep4Request;
use App\Http\Requests\Registration\RegistrationStep5Request;
use App\Models\PesertaRegistration;
use App\Repositories\RegistrationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationStepController extends Controller
{
    protected $repository;

    public function __construct(RegistrationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show registration steps page
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        if (!$user || $user->registration_status !== 'pending') {
            return redirect()->route('register');
        }

        $step = (int) $request->get('step', 1);
        $step = max(1, min(6, $step)); // Validasi step 1-6 (6 = preview)

        // Get atau create registration
        $registration = PesertaRegistration::where('user_id', $user->id)
            ->where('status', '!=', 'approved')
            ->first();

        if (!$registration && $step > 1) {
            // Jika belum ada registration dan bukan step 1, redirect ke step 1
            return redirect()->route('registration.steps', ['step' => 1]);
        }

        $data = [
            'step' => $step,
            'registration' => $registration,
            'registrationData' => $registration?->data_json ?? [],
        ];

        // Load data tambahan berdasarkan step
        if ($step === 2 && $registration) {
            // Load master data untuk form (kecamatan, kelurahan, kategori peserta, dll)
            $data['kecamatanOptions'] = \App\Models\MstKecamatan::select('id', 'nama')->orderBy('nama')->get();
            $data['kategoriPesertaOptions'] = \App\Models\MstKategoriPeserta::select('id', 'nama')->orderBy('nama')->get();
            
            if ($registration->peserta_type === 'atlet') {
                // Load parameter umum master untuk atlet
                $data['parameterUmumMaster'] = \App\Models\MstParameter::where('kategori', 'umum')->get();
            }
        }

        return Inertia::render('registration/Steps/Index', $data);
    }

    /**
     * Handle Step 1: Pilih Jenis Peserta
     */
    public function storeStep1(RegistrationStep1Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('register');
        }

        try {
            $pesertaType = $request->validated()['peserta_type'];
            
            $registration = $this->repository->getOrCreateRegistration($user, $pesertaType);
            $this->repository->saveStepData($registration, 1, ['peserta_type' => $pesertaType]);

            Log::info('RegistrationStepController: Step 1 completed', [
                'user_id' => $user->id,
                'peserta_type' => $pesertaType,
            ]);

            return redirect()->route('registration.steps', ['step' => 2])
                ->with('success', 'Jenis peserta berhasil dipilih');
        } catch (\Exception $e) {
            Log::error('RegistrationStepController: Error in step 1', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['peserta_type' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    /**
     * Handle Step 2: Data Diri
     */
    public function storeStep2(RegistrationStep2Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('register');
        }

        try {
            $registration = PesertaRegistration::where('user_id', $user->id)
                ->where('status', '!=', 'approved')
                ->firstOrFail();

            $data = $request->validated();
            
            // Handle file upload jika ada
            if ($request->hasFile('file')) {
                $data['file'] = $request->file('file');
            }

            $this->repository->saveStepData($registration, 2, $data);

            Log::info('RegistrationStepController: Step 2 completed', [
                'user_id' => $user->id,
                'registration_id' => $registration->id,
            ]);

            return redirect()->route('registration.steps', ['step' => 3])
                ->with('success', 'Data diri berhasil disimpan');
        } catch (\Exception $e) {
            Log::error('RegistrationStepController: Error in step 2', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    /**
     * Handle Step 3: Sertifikat (Opsional)
     */
    public function storeStep3(RegistrationStep3Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('register');
        }

        try {
            $registration = PesertaRegistration::where('user_id', $user->id)
                ->where('status', '!=', 'approved')
                ->firstOrFail();

            $data = $request->validated();
            
            // Handle multiple file uploads untuk sertifikat
            // Data dari frontend bisa berupa: sertifikat[0][nama_sertifikat], sertifikat[0][file], dll
            $sertifikatFiles = [];
            if (isset($data['sertifikat']) && is_array($data['sertifikat'])) {
                foreach ($data['sertifikat'] as $index => $sertifikat) {
                    // Check if file is uploaded via form (Laravel converts array notation to nested array)
                    $file = null;
                    if (isset($sertifikat['file']) && $sertifikat['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $file = $sertifikat['file'];
                    } elseif ($request->hasFile("sertifikat.{$index}.file")) {
                        $file = $request->file("sertifikat.{$index}.file");
                    }
                    
                    if ($file) {
                        $sertifikatFiles[] = [
                            'file' => $file,
                            'nama_sertifikat' => $sertifikat['nama_sertifikat'] ?? null,
                            'penyelenggara' => $sertifikat['penyelenggara'] ?? null,
                            'tanggal_terbit' => $sertifikat['tanggal_terbit'] ?? null,
                        ];
                    } elseif (isset($sertifikat['nama_sertifikat']) && !empty($sertifikat['nama_sertifikat'])) {
                        // Include sertifikat without file (for existing data)
                        $sertifikatFiles[] = [
                            'nama_sertifikat' => $sertifikat['nama_sertifikat'] ?? null,
                            'penyelenggara' => $sertifikat['penyelenggara'] ?? null,
                            'tanggal_terbit' => $sertifikat['tanggal_terbit'] ?? null,
                        ];
                    }
                }
            }
            
            if (!empty($sertifikatFiles)) {
                $data['sertifikat_files'] = $sertifikatFiles;
            }

            $this->repository->saveStepData($registration, 3, $data);

            return redirect()->route('registration.steps', ['step' => 4])
                ->with('success', 'Data sertifikat berhasil disimpan');
        } catch (\Exception $e) {
            Log::error('RegistrationStepController: Error in step 3', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    /**
     * Handle Step 4: Prestasi (Opsional)
     */
    public function storeStep4(RegistrationStep4Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('register');
        }

        try {
            $registration = PesertaRegistration::where('user_id', $user->id)
                ->where('status', '!=', 'approved')
                ->firstOrFail();

            $data = $request->validated();
            $this->repository->saveStepData($registration, 4, $data);

            return redirect()->route('registration.steps', ['step' => 5])
                ->with('success', 'Data prestasi berhasil disimpan');
        } catch (\Exception $e) {
            Log::error('RegistrationStepController: Error in step 4', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    /**
     * Handle Step 5: Dokumen (Opsional)
     */
    public function storeStep5(RegistrationStep5Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('register');
        }

        try {
            $registration = PesertaRegistration::where('user_id', $user->id)
                ->where('status', '!=', 'approved')
                ->firstOrFail();

            $data = $request->validated();
            
            // Handle multiple file uploads untuk dokumen
            // Data dari frontend bisa berupa: dokumen[0][jenis_dokumen_id], dokumen[0][file], dll
            $dokumenFiles = [];
            if (isset($data['dokumen']) && is_array($data['dokumen'])) {
                foreach ($data['dokumen'] as $index => $dokumen) {
                    // Check if file is uploaded via form (Laravel converts array notation to nested array)
                    $file = null;
                    if (isset($dokumen['file']) && $dokumen['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $file = $dokumen['file'];
                    } elseif ($request->hasFile("dokumen.{$index}.file")) {
                        $file = $request->file("dokumen.{$index}.file");
                    }
                    
                    if ($file) {
                        $dokumenFiles[] = [
                            'file' => $file,
                            'jenis_dokumen_id' => $dokumen['jenis_dokumen_id'] ?? null,
                            'nomor' => $dokumen['nomor'] ?? null,
                        ];
                    } elseif (isset($dokumen['jenis_dokumen_id']) || isset($dokumen['nomor'])) {
                        // Include dokumen without file (for existing data)
                        $dokumenFiles[] = [
                            'jenis_dokumen_id' => $dokumen['jenis_dokumen_id'] ?? null,
                            'nomor' => $dokumen['nomor'] ?? null,
                        ];
                    }
                }
            }
            
            if (!empty($dokumenFiles)) {
                $data['dokumen_files'] = $dokumenFiles;
            }

            $this->repository->saveStepData($registration, 5, $data);

            return redirect()->route('registration.steps', ['step' => 6])
                ->with('success', 'Data dokumen berhasil disimpan');
        } catch (\Exception $e) {
            Log::error('RegistrationStepController: Error in step 5', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    /**
     * Submit final registration (dari preview)
     */
    public function submit(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('register');
        }

        try {
            $registration = PesertaRegistration::where('user_id', $user->id)
                ->where('status', '!=', 'approved')
                ->firstOrFail();

            // Validate semua step wajib sudah diisi
            $data = $registration->data_json ?? [];
            if (!isset($data['step_1']) || !isset($data['step_2'])) {
                return redirect()->back()
                    ->withErrors(['error' => 'Data diri wajib diisi. Silakan lengkapi step 1 dan 2.']);
            }

            $this->repository->submitRegistration($registration);

            // Logout user karena belum bisa login sampai approved
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::info('RegistrationStepController: Registration submitted', [
                'user_id' => $user->id,
                'registration_id' => $registration->id,
            ]);

            return redirect()->route('registration.success')
                ->with('success', 'Pengajuan registrasi berhasil dikirim. Menunggu persetujuan admin.');
        } catch (\Exception $e) {
            Log::error('RegistrationStepController: Error submitting registration', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat mengirim pengajuan. Silakan coba lagi.']);
        }
    }

    /**
     * Save draft (auto-save dari frontend)
     */
    public function saveDraft(Request $request, int $step)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $registration = PesertaRegistration::where('user_id', $user->id)
                ->where('status', '!=', 'approved')
                ->first();

            if (!$registration) {
                return response()->json(['success' => false, 'message' => 'Registration not found'], 404);
            }

            $data = $request->except(['_token', 'step']);
            $this->repository->saveStepData($registration, $step, $data);

            return response()->json([
                'success' => true,
                'message' => 'Draft berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            Log::error('RegistrationStepController: Error saving draft', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan draft',
            ], 500);
        }
    }
}
