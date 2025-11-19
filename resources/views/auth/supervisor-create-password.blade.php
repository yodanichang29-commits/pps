<x-guest-layout>

    <div class="min-h-screen flex flex-col justify-center items-center bg-unah-gradient bg-[length:400%_400%] animate-gradient-move p-6">

        <div class="w-[400px] px-6 py-10 bg-white shadow-xl rounded-2xl mt-16 animate-fade-in">

            {{-- Logos institucionales --}}
            <div class="flex justify-center items-center gap-6 mb-6">
                <img src="{{ asset('img/UNAH-version-horizontal.png') }}" alt="UNAH Logo" class="h-16">
                <img src="{{ asset('img/Informatica-Administrativa.png') }}" alt="IA Logo" class="h-16">
            </div>

            <h2 class="text-center text-2xl font-bold text-unahblue">
                Crear contraseña
            </h2>

            <p class="text-center text-sm text-gray-600 mt-1 mb-6 px-2">
                Esta es la primera vez que ingresas al sistema.  
                Debes crear una contraseña para poder continuar.
            </p>

            {{-- Errores --}}
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded">
                    <ul class="text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('supervisor.password.store') }}" class="space-y-4">
                @csrf

                {{-- Nueva contraseña --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Nueva contraseña
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-unahblue focus:border-unahblue"
                    >
                </div>

                {{-- Confirmación --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Confirmar contraseña
                    </label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-unahblue focus:border-unahblue"
                    >
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-unahblue text-white font-semibold rounded-lg hover:bg-unahgold transition">
                    Guardar contraseña y continuar
                </button>
            </form>
        </div>
    </div>

</x-guest-layout>
