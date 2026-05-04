@extends('layouts.app')

@section('content')
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">
    
    <!-- Aquí se anexa el menú automáticamente -->
    <x-sidebar />

    <div class="flex-1 flex flex-col overflow-hidden">

    <x-navbar :usuario="$usuario" />

        <!-- Contenido Variable -->
        <main class="flex-1 p-8 overflow-y-auto">
            <h2 class="text-2xl font-bold text-slate-800 uppercase">Bienvenido {{ $usuario->name }}</h2>
            <p>Al sistema para calcular los impuestos.</p>
        </main>
    </div>
</div>
@endsection