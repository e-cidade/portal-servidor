<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        Log::info('Tentativa de login', [
            'login' => $this->login,
            'password_length' => strlen($this->password)
        ]);

        $userLogin = User::getUserByCpf($this->login);

        if (!$userLogin) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'login' => 'Usuário não encontrado ou não tem vínculo com RH . Verifique seu login e tente novamente.',
            ]);
        }

        $user = User::where('id_usuario', $userLogin->codigo)->first();
        $senhaHash = sha1(md5($this->password));


        if ($senhaHash !== $userLogin->senha || !$userLogin) {

            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'password' => 'Login ou Senha incorretos ou Login não tem vinculo com RH . Verifique e tente novamente. ',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
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

    /**
     * Obter o motivo específico da negação
     */
    private function getMotivoNegacao($user): string
    {
        // Usuário inativo
        if ($user->usuarioativo == 0) {
            return 'Usuário inativo. Entre em contato com o administrador do sistema.';
        }

        // Usuário sem permissão específica
        return 'Você não possui permissão para acessar este portal. Entre em contato com o administrador.';
    }
}
