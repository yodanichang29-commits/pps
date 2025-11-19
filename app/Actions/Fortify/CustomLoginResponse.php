<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->rol === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->rol === 'supervisor') {
            return redirect()->route('supervisor.dashboard');
        }

        if ($user->rol === 'estudiante') {
            return redirect()->route('estudiantes.dashboard');
        }

        return redirect()->intended(config('fortify.home'));
    }
}
