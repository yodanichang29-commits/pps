<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Actions\Fortify\CustomLoginResponse;

// 🔥 NUEVOS use
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Redirección según el rol
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);

        // ⚙️ LÓGICA DE LOGIN PERSONALIZADO
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (! $user) {
                // Usuario no existe → Fortify muestra "estas credenciales no coinciden..."
                return null;
            }

            // 🧩 Caso especial: usuario SIN contraseña (supervisor recién creado)
            if (is_null($user->password)) {

                // Guardamos el correo en sesión para el flujo de "crear contraseña"
                session([
                    'password_setup_email' => $user->email,
                ]);

                // Lanzamos error en el campo email con mensaje claro
                throw ValidationException::withMessages([
                    'email' => 'Este usuario aún no tiene contraseña configurada. ' .
                               'Haz clic en "Crear contraseña" para definirla por primera vez.',
                ]);
            }

            // ✅ Flujo normal: comparar contraseña
            if (Hash::check($request->password, $user->password)) {
                return $user;
            }

            // Contraseña incorrecta → Fortify devuelve error estándar
            return null;
        });

        // Limites de intentos de login
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
