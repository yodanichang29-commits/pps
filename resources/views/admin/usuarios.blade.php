@extends('layouts.admin')

@section('content')
    <h1 class="text-xl font-bold mb-4">Gestión de Usuarios</h1>

    @foreach ($usuarios as $usuario)
        <div class="mb-4 p-4 border rounded">
            <p><strong>Nombre:</strong> {{ $usuario->name }}</p>
            <p><strong>Correo:</strong> {{ $usuario->email }}</p>
            <p><strong>Rol actual:</strong> {{ $usuario->getRoleNames()->first() ?? 'Sin rol' }}</p>

            <form action="{{ route('admin.usuarios.updateRol', $usuario->id) }}" method="POST" class="mt-2">
                @csrf
                @method('PUT')
                <select name="rol" class="border p-1">
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->name }}" {{ $usuario->hasRole($rol->name) ? 'selected' : '' }}>
                            {{ $rol->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Actualizar Rol</button>
            </form>
        </div>
    @endforeach
@endsection
