<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Support\Captcha;

class LoginRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    /**
     * Bersihkan NIP (spasi, titik, dll.) sebelum divalidasi supaya cocok
     * dengan format tersimpan di database: digit polos.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nip' => User::bersihkanNip($this->input('nip')),
        ]);
    }

    public function rules(): array
    {
        $aturan = [
            'nip' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];

        if (Captcha::aktif()) {
            $aturan['captcha'] = ['required', 'string'];
        }

        return $aturan;
    }

    public function messages(): array
    {
        return [
            'nip.required' => 'NIP wajib diisi.',
            'captcha.required' => 'Isi angka yang tampil pada gambar.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Captcha dicek SEBELUM NIP/password disentuh, jadi bot yang gagal captcha
        // tidak bisa memakai login sebagai alat tebak password. Salah captcha juga
        // dihitung ke batas percobaan, dan kode selalu hangus setelah dicek.
        if (Captcha::aktif() && ! Captcha::cocok($this->input('captcha'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'captcha' => 'Angka captcha salah atau sudah kedaluwarsa. Silakan coba dengan gambar yang baru.',
            ]);
        }

        if (! Auth::attempt($this->only('nip', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'nip' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'nip' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('nip')).'|'.$this->ip());
    }
}