<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PasswordSetupController extends Controller
{
    public function create(Request $request)
    {
        $email = session('password_setup_email');

        if (! $email) {
            return redirect()->route('login')
                ->with('error', 'No hay ningún usuario pendiente de crear contraseña.');
        }

        return view('auth.supervisor-create-password', compact('email'));
    }

    public function store(Request $request)
    {
        $email = session('password_setup_email');

        if (! $email) {
            return redirect()->route('login')
                ->with('error', 'Sesión expirada. Intenta de nuevo desde el inicio de sesión.');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required'   => 'La contraseña es obligatoria.',
            'password.min'        => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'  => 'Las contraseñas no coinciden.',
        ]);

        $user = User::where('email', $email)->firstOrFail();

        // Solo permitir si no tenía contraseña antes
        if (! is_null($user->password)) {
            return redirect()->route('login')
                ->with('error', 'Este usuario ya tiene contraseña configurada.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Limpiar de sesión
        $request->session()->forget('password_setup_email');

        // Loguearlo y mandarlo a su dashboard
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('supervisor.dashboard')
            ->with('success', 'Contraseña creada correctamente. Bienvenido.');
    }
}
