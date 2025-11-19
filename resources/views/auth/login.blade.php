<x-guest-layout>
<div class="min-h-screen flex flex-col justify-center items-center bg-unah-gradient bg-[length:400%_400%] animate-gradient-move p-6">
    <div class="w-[400px] px-6 py-12 bg-white shadow-xl rounded-2xl mt-16">


             <!-- Logos -->
        <div class="flex justify-center items-center gap-6 mb-6">
            <img src="{{ asset('img/UNAH-version-horizontal.png') }}" alt="UNAH Logo" class="h-16">
            <img src="{{ asset('img/Informatica-Administrativa.png') }}" alt="IA Logo" class="h-16">
        </div>

            <h2 class="text-center text-2xl font-bold text-unahblue">
                Inicio de sesión
            </h2>
            <p class="text-center text-sm text-gray-600 mt-1 mb-6">
                Accede con tu correo institucional
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
            

{{-- ⚠️ Mostrar botón solo cuando el usuario NO tiene contraseña --}}
@if ($errors->has('email') && session('password_setup_email'))
    <div class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
        <p class="text-yellow-800 text-sm mb-3 font-semibold">
            Este supervisor aún no tiene contraseña configurada.
        </p>

        <a href="{{ route('supervisor.password.create') }}"
           class="block w-full text-center px-4 py-2 bg-unahblue text-white rounded-lg font-semibold hover:bg-unahgold transition">
            Crear contraseña
        </a>
    </div>
@endif




            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-label for="email" value="Correo institucional" />
                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" required autofocus />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-label for="password" value="Contraseña" />
                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-unahblue shadow-sm focus:ring focus:ring-unahgold" name="remember">
                        <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                    </label>
                </div>

                <div class="flex items-center justify-between mt-6">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-unahblue" href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif

                    <button type="submit" class="ml-3 px-4 py-2 bg-unahblue text-white rounded hover:bg-unahgold transition">
    {{ __('INICIAR SESIÓN') }}
</button>

                </div>
                <div class="mt-4 text-center">
    <p class="text-sm text-gray-600">
        ¿No tienes una cuenta?
        <a href="{{ route('register') }}" class="text-unahblue hover:underline font-semibold">
            Regístrate aquí
        </a>
    </p>
</div>

            </form>
        </div>
    </div>
</x-guest-layout>
