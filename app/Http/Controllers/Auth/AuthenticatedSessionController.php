<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Procesar login
     */
    public function store(Request $request)
    {
        // Validar que vengan email y password (no vacíos)
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Buscar usuario por correo
        $user = User::where('email', $credentials['email'])->first();

        // 🔥 CASO ESPECIAL: supervisor SIN contraseña (password = null)
        if ($user && $user->password === null && $user->rol === 'supervisor') {
            // Lo logueamos sin verificar contraseña esta primera vez
            Auth::login($user);

            $request->session()->regenerate();

            // Lo enviamos a crear su contraseña obligatoriamente
            return redirect()->route('supervisor.password.create');
        }

        // ✅ CASO NORMAL: usuario con contraseña definida
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // ❌ Credenciales incorrectas
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cerrar sesión
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Mostrar formulario para que el supervisor cree su contraseña
     */
    public function showCreatePasswordForm(Request $request)
    {
        $user = $request->user();

        // Si no hay usuario o ya tiene contraseña, lo mandamos al dashboard
        if (!$user || $user->password !== null || $user->rol !== 'supervisor') {
            return redirect('/dashboard');
        }

        return view('auth.supervisor-create-password');
    }

    /**
     * Guardar la contraseña creada por el supervisor
     */
    public function storeCreatePassword(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->password !== null || $user->rol !== 'supervisor') {
            return redirect('/dashboard');
        }

        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'password.required'  => 'La contraseña es obligatoria',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect('/dashboard')
            ->with('success', 'Contraseña creada correctamente. Bienvenido al sistema.');
    }
}
