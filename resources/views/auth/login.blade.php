@extends('layouts.app')

@section('content')
<div class="min-h-screen flex border-t-4 border-blue-700">
    <!-- Lado Izquierdo: Diseño e Identidad -->
    <div class="hidden lg:flex lg:w-1/2 bg-slate-900 items-center justify-center p-12 relative overflow-hidden">
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-blue-500 text-5xl">account_balance</span>
                <h1 class="text-4xl font-bold text-white tracking-tight">SISTEMA <span class="text-blue-500">IMPUESTOS</span></h1>
            </div>
            <p class="text-slate-400 text-lg max-w-md">Gestión contable y tributaria profesional para empresas y despachos.</p>
        </div>
        <!-- Un toque decorativo sutil -->
        <div class="absolute bottom-0 right-0 opacity-10">
            <span class="material-symbols-outlined" style="font-size: 400px;">analytics</span>
        </div>
    </div>

    <!-- Lado Derecho: Formulario -->
    <div class="w-full lg:w-1/2 flex items-center justify-center bg-white">
        <div class="max-w-md w-full p-8">
            <div class="mb-10 lg:hidden text-center">
                <h2 class="text-3xl font-bold text-slate-800">SISTEMA IMPUESTOS</h2>
            </div>

            <div class="mb-8">
                <h3 class="text-2xl font-bold text-slate-800">Bienvenido</h3>
                <p class="text-slate-500">Inicie sesión para acceder al panel administrativo.</p>
            </div>

            <form action="#" method="POST">
                @csrf
                <x-input-icon 
                    label="Usuario o Correo" 
                    type="email" 
                    name="email" 
                    icon="person" 
                    placeholder="ejemplo@dominio.com" />

                <x-input-icon 
                    label="Clave de Acceso" 
                    type="password" 
                    name="password" 
                    icon="key" 
                    placeholder="••••••••" />

                <div class="flex items-center justify-between mb-8">
                    <label class="flex items-center text-sm text-slate-600 cursor-pointer">
                        <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 mr-2">
                        Recordar sesión
                    </label>
                    <a href="#" class="text-sm font-medium text-blue-600 hover:underline">¿Olvidó su clave?</a>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded hover:bg-blue-700 transition-all shadow-lg shadow-slate-200 uppercase tracking-widest text-sm">
                    Entrar al Sistema
                </button>
            </form>

            <footer class="mt-12 text-center text-slate-400 text-xs uppercase tracking-tighter">
                &copy; {{ date('Y') }} Sistema de Impuestos - Control Interno
            </footer>
        </div>
    </div>
</div>
@endsection