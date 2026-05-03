@extends('layouts.app')

@section('content')
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">
    <!-- Menú Lateral -->
    <x-sidebar />

    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Barra Superior -->
        <x-navbar :usuario="$usuario" />

        <!-- Contenido del Perfil Nivelado -->
        <main class="flex-1 p-6 overflow-y-auto bg-slate-50">
            <div class="max-w-3xl mx-auto">
                
                <!-- Encabezado Nivelado -->
                <div class="flex items-center gap-3 mb-6">
                    <span class="material-symbols-outlined text-2xl text-slate-600">manage_accounts</span>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">Mi Perfil</h2>
                </div>

                <!-- Alertas Compactas -->
                @if(session('success'))
                    <div class="mb-5 p-3 bg-green-600 text-white rounded-xl shadow-sm flex items-center gap-2 text-xs font-bold">
                        <span class="material-symbols-outlined text-sm">check_circle</span> 
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <!-- Bloque: Información General -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Información de la Cuenta</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 mb-1.5 uppercase tracking-tight">Nombre de Usuario</label>
                                <input type="text" name="name" value="{{ $usuario }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-blue-500/20 outline-none transition text-slate-700">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 mb-1.5 uppercase tracking-tight">Correo Electrónico</label>
                                <input type="email" name="email" value="{{ $email ?? '' }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-blue-500/20 outline-none transition text-slate-700">
                            </div>
                        </div>
                    </div>

                    <!-- Bloque: Seguridad -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm" x-data="{ newPw: '' }">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                            <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Seguridad y Acceso</h3>
                        </div>
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 mb-1.5 uppercase tracking-tight">Contraseña Actual</label>
                                <div class="relative">
                                    <input type="password" name="current_password" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-red-400/20" placeholder="••••••••">
                                </div>
                                <p class="text-[9px] text-slate-400 mt-1.5 font-medium italic">* Es obligatorio confirmar tu clave actual para guardar cambios.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-slate-50">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 mb-1.5 uppercase tracking-tight">Nueva Contraseña</label>
                                    <input type="password" name="password" x-model="newPw" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="Dejar en blanco para no cambiar">
                                    
                                    <!-- Medidor de Seguridad Compacto -->
                                    <div class="mt-2 flex gap-1">
                                        <template x-for="i in 3">
                                            <div class="h-1 flex-1 rounded-full bg-slate-100 transition-colors" 
                                                 :class="newPw.length >= (i*4) ? 'bg-green-500' : (newPw.length > 0 ? 'bg-red-400' : 'bg-slate-100')"></div>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 mb-1.5 uppercase tracking-tight">Confirmar Nueva Clave</label>
                                    <input type="password" name="password_confirmation" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="Repetir clave">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de Acción Proporcional -->
                    <div class="flex justify-end">
                        <button type="submit" class="bg-slate-900 hover:bg-blue-600 text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection