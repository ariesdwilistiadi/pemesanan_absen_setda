<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\LoginChallenge;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:filter', 'max:255'],
            'password' => ['required', 'string', 'max:4096'],
            'remember' => ['nullable', 'boolean'],
            'website' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (filled($value)) {
                        LoginChallenge::rotate($this->session());
                        $fail('Verifikasi keamanan gagal.');
                    }
                },
            ],
            'challenge_answer' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! LoginChallenge::isMature($this->session())) {
                        LoginChallenge::rotate($this->session());
                        $fail('Verifikasi keamanan gagal. Coba lagi secara perlahan.');

                        return;
                    }

                    if ((int) $value !== (int) LoginChallenge::answer($this->session())) {
                        LoginChallenge::rotate($this->session());
                        $fail('Jawaban verifikasi manusia tidak sesuai.');
                    }
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
            'remember' => $this->boolean('remember'),
        ]);
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), $this->identityDecaySeconds());
            RateLimiter::hit($this->ipThrottleKey(), $this->ipDecaySeconds());
            LoginChallenge::rotate($this->session());

            Log::warning('AUTHENTICATION FAILURE', [
                'email_hash' => hash('sha256', (string) $this->email),
                'ip_address' => $this->ip(),
                'throttle_key' => $this->throttleKey(),
                'ip_throttle_key' => $this->ipThrottleKey(),
                'user_agent' => $this->userAgent(),
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->ipThrottleKey());

        /** @var User $user */
        $user = $this->user();
        LoginChallenge::clear($this->session());

        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $tooManyIdentityAttempts = RateLimiter::tooManyAttempts(
            $this->throttleKey(),
            (int) env('LOGIN_MAX_ATTEMPTS_PER_IDENTITY', 5)
        );

        $tooManyIpAttempts = RateLimiter::tooManyAttempts(
            $this->ipThrottleKey(),
            (int) env('LOGIN_MAX_ATTEMPTS_PER_IP', 20)
        );

        if (! $tooManyIdentityAttempts && ! $tooManyIpAttempts) {
            return;
        }

        event(new Lockout($this));

        $seconds = max(
            RateLimiter::availableIn($this->throttleKey()),
            RateLimiter::availableIn($this->ipThrottleKey())
        );

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
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
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    public function ipThrottleKey(): string
    {
        return 'login-ip:'.$this->ip();
    }

    private function identityDecaySeconds(): int
    {
        return (int) env('LOGIN_LOCKOUT_SECONDS', 300);
    }

    private function ipDecaySeconds(): int
    {
        return (int) env('LOGIN_IP_LOCKOUT_SECONDS', 600);
    }
}
