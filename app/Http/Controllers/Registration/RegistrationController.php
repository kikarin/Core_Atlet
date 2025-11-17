<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Http\Helpers\RecaptchaHelper;
use App\Models\User;
use App\Repositories\RegistrationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    protected $repository;

    public function __construct(RegistrationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show the registration page (Step 0 - Email/Password)
     */
    public function create(): Response
    {
        $recaptchaSiteKey = config('services.recaptcha.site_key');
        
        // Debug: Log if key is missing
        if (empty($recaptchaSiteKey)) {
            \Log::warning('reCAPTCHA Site Key is not configured. Please check your .env file.');
        }
        
        return Inertia::render('registration/Register', [
            'recaptchaSiteKey' => $recaptchaSiteKey ?: null,
        ]);
    }

    /**
     * Handle initial registration (Step 0)
     * Create user dengan status pending, redirect ke steps
     */
    public function store(Request $request)
    {
        $rules = [
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // Add reCAPTCHA validation if configured
        if (config('services.recaptcha.secret_key')) {
            $rules['recaptcha_token'] = 'required|string';
        }

        $request->validate($rules);

        // Verify reCAPTCHA if configured
        if (config('services.recaptcha.secret_key')) {
            $recaptchaToken = $request->input('recaptcha_token');
            if (!$recaptchaToken || !RecaptchaHelper::verify($recaptchaToken, $request->ip())) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['recaptcha_token' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.']);
            }
        }

        try {
            $user = $this->repository->createRegistrationUser([
                'email' => $request->email,
                'password' => $request->password,
                'name' => $request->email, // Temporary, akan diupdate di step 2
            ]);

            // Login user untuk session (tapi is_active = 0, jadi tidak bisa akses dashboard)
            auth()->login($user);

            Log::info('RegistrationController: User registered, redirecting to steps', [
                'user_id' => $user->id,
            ]);

            return redirect()->route('registration.steps', ['step' => 1]);
        } catch (\Exception $e) {
            Log::error('RegistrationController: Error creating registration user', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['email' => 'Terjadi kesalahan saat membuat akun. Silakan coba lagi.']);
        }
    }

    /**
     * Show success page setelah submit registration
     */
    public function success(): Response
    {
        return Inertia::render('registration/Success');
    }
}
