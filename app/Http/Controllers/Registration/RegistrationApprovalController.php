<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Models\PesertaRegistration;
use App\Repositories\RegistrationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationApprovalController extends Controller
{
    protected $repository;

    public function __construct(RegistrationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Index page untuk list pengajuan registrasi
     */
    public function index(Request $request): Response
    {
        $filters = [
            'status' => $request->get('status'),
            'peserta_type' => $request->get('peserta_type'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search'),
            'per_page' => $request->get('per_page', 15),
        ];

        $registrations = $this->repository->getRegistrationsForAdmin($filters);

        return Inertia::render('modules/registration-approval/Index', [
            'registrations' => $registrations,
            'filters' => $filters,
        ]);
    }

    /**
     * API endpoint untuk datatable
     */
    public function apiIndex(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'peserta_type' => $request->get('peserta_type'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search'),
            'per_page' => $request->get('per_page', 15),
        ];

        $registrations = $this->repository->getRegistrationsForAdmin($filters);

        $data = $registrations->map(function ($registration) {
            return [
                'id' => $registration->id,
                'user_id' => $registration->user_id,
                'user_name' => $registration->user->name ?? '-',
                'user_email' => $registration->user->email ?? '-',
                'peserta_type' => $registration->peserta_type,
                'peserta_type_label' => ucfirst(str_replace('_', ' ', $registration->peserta_type)),
                'status' => $registration->status,
                'status_label' => $this->getStatusLabel($registration->status),
                'step_current' => $registration->step_current,
                'created_at' => $registration->created_at->format('Y-m-d H:i:s'),
                'created_at_formatted' => $registration->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $registrations->total(),
                'current_page' => $registrations->currentPage(),
                'per_page' => $registrations->perPage(),
                'last_page' => $registrations->lastPage(),
                'search' => $filters['search'] ?? '',
                'sort' => '',
                'order' => 'asc',
            ],
        ]);
    }

    /**
     * Show detail pengajuan
     */
    public function show($id): Response
    {
        $registration = PesertaRegistration::with(['user'])->findOrFail($id);
        
        $data = $registration->data_json ?? [];
        
        // Load additional data untuk preview
        $additionalData = [];
        
        if (isset($data['step_2'])) {
            $step2 = $data['step_2'];
            
            // Load kecamatan, kelurahan jika ada
            if (isset($step2['kecamatan_id'])) {
                $additionalData['kecamatan'] = \App\Models\MstKecamatan::find($step2['kecamatan_id']);
            }
            if (isset($step2['kelurahan_id'])) {
                $additionalData['kelurahan'] = \App\Models\MstDesa::find($step2['kelurahan_id']);
            }
            
            // Load kategori peserta jika ada
            if (isset($step2['kategori_pesertas']) && is_array($step2['kategori_pesertas'])) {
                $additionalData['kategori_pesertas'] = \App\Models\MstKategoriPeserta::whereIn('id', $step2['kategori_pesertas'])->get();
            }
            
            // Load profile photo dari media library
            $profileMedia = $registration->getFirstMedia('profile_photo');
            if ($profileMedia) {
                $data['step_2']['file_url'] = $profileMedia->getUrl();
            }
        }
        
        // Load sertifikat files dari media library
        if (isset($data['step_3']['sertifikat']) && is_array($data['step_3']['sertifikat'])) {
            $sertifikatMedia = $registration->getMedia('sertifikat_files');
            foreach ($data['step_3']['sertifikat'] as $index => &$sertifikat) {
                if (isset($sertifikat['media_id'])) {
                    $media = $sertifikatMedia->where('id', $sertifikat['media_id'])->first();
                    if ($media) {
                        $sertifikat['file_url'] = $media->getUrl();
                    }
                }
            }
        }
        
        // Load dokumen files dari media library
        if (isset($data['step_5']['dokumen']) && is_array($data['step_5']['dokumen'])) {
            $dokumenMedia = $registration->getMedia('dokumen_files');
            foreach ($data['step_5']['dokumen'] as $index => &$dokumen) {
                if (isset($dokumen['media_id'])) {
                    $media = $dokumenMedia->where('id', $dokumen['media_id'])->first();
                    if ($media) {
                        $dokumen['file_url'] = $media->getUrl();
                    }
                }
            }
        }

        return Inertia::render('modules/registration-approval/Show', [
            'registration' => $registration,
            'registrationData' => $data,
            'additionalData' => $additionalData,
        ]);
    }

    /**
     * Approve registration
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'ids' => 'nullable|array', // Untuk bulk approve
            'ids.*' => 'exists:peserta_registrations,id',
        ]);

        try {
            $approvedBy = Auth::id();
            $ids = $request->input('ids', [$id]);

            $approved = [];
            $errors = [];

            foreach ($ids as $registrationId) {
                try {
                    $registration = PesertaRegistration::with('user')->findOrFail($registrationId);
                    
                    if (!in_array($registration->status, ['submitted', 'rejected'], true)) {
                        $errors[] = "Pengajuan #{$registrationId} tidak dapat disetujui (status: {$registration->status})";
                        continue;
                    }

                    if ($registration->status === 'rejected') {
                        $registration->update([
                            'status' => 'submitted',
                            'rejected_reason' => null,
                        ]);

                        $registration->user?->update([
                            'registration_status' => 'pending',
                            'registration_rejected_reason' => null,
                        ]);
                    }

                    $result = $this->repository->approveRegistration($registration->fresh(), $approvedBy);
                    $approved[] = $registrationId;

                    Log::info('RegistrationApprovalController: Registration approved', [
                        'registration_id' => $registrationId,
                        'approved_by' => $approvedBy,
                    ]);
                } catch (\Exception $e) {
                    Log::error('RegistrationApprovalController: Error approving registration', [
                        'registration_id' => $registrationId,
                        'error' => $e->getMessage(),
                    ]);
                    $errors[] = "Gagal menyetujui pengajuan #{$registrationId}: " . $e->getMessage();
                }
            }

            if (count($approved) > 0) {
                $message = count($approved) > 1 
                    ? count($approved) . ' pengajuan berhasil disetujui'
                    : 'Pengajuan berhasil disetujui';
                
                if (count($errors) > 0) {
                    $message .= '. ' . implode('. ', $errors);
                }

                // Return JSON response for API calls
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                    ]);
                }

                return redirect()->route('registration-approval.index')
                    ->with('success', $message);
            } else {
                $errorMessage = implode('. ', $errors);
                
                // Return JSON response for API calls
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                    ], 400);
                }

                return redirect()->back()
                    ->withErrors(['error' => $errorMessage]);
            }
        } catch (\Exception $e) {
            Log::error('RegistrationApprovalController: Error in approve', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyetujui pengajuan.']);
        }
    }

    /**
     * Reject registration
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejected_reason' => 'required|string|max:1000',
            'ids' => 'nullable|array', // Untuk bulk reject
            'ids.*' => 'exists:peserta_registrations,id',
        ]);

        try {
            $rejectedBy = Auth::id();
            $reason = $request->input('rejected_reason');
            $ids = $request->input('ids', [$id]);

            $rejected = [];
            $errors = [];

            foreach ($ids as $registrationId) {
                try {
                    $registration = PesertaRegistration::with('user')->findOrFail($registrationId);
                    
                    if ($registration->status !== 'submitted') {
                        $errors[] = "Pengajuan #{$registrationId} tidak dapat ditolak (status: {$registration->status})";
                        continue;
                    }

                    $this->repository->rejectRegistration($registration, $reason, $rejectedBy);
                    $rejected[] = $registrationId;

                    Log::info('RegistrationApprovalController: Registration rejected', [
                        'registration_id' => $registrationId,
                        'rejected_by' => $rejectedBy,
                        'reason' => $reason,
                    ]);
                } catch (\Exception $e) {
                    Log::error('RegistrationApprovalController: Error rejecting registration', [
                        'registration_id' => $registrationId,
                        'error' => $e->getMessage(),
                    ]);
                    $errors[] = "Gagal menolak pengajuan #{$registrationId}: " . $e->getMessage();
                }
            }

            if (count($rejected) > 0) {
                $message = count($rejected) > 1 
                    ? count($rejected) . ' pengajuan berhasil ditolak'
                    : 'Pengajuan berhasil ditolak';
                
                if (count($errors) > 0) {
                    $message .= '. ' . implode('. ', $errors);
                }

                // Return JSON response for API calls
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                    ]);
                }

                return redirect()->route('registration-approval.index')
                    ->with('success', $message);
            } else {
                $errorMessage = implode('. ', $errors);
                
                // Return JSON response for API calls
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                    ], 400);
                }

                return redirect()->back()
                    ->withErrors(['error' => $errorMessage]);
            }
        } catch (\Exception $e) {
            Log::error('RegistrationApprovalController: Error in reject', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menolak pengajuan.']);
        }
    }

    /**
     * Get status label
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'draft' => 'Draft',
            'submitted' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($status),
        };
    }
}
