@extends('layouts.app')

@section('content')
<div class="flex h-screen overflow-hidden" 
     x-data="{ 
        sidebarOpen: true, 
        modalOpen: {{ $errors->any() ? 'true' : 'false' }}, 
        editMode: {{ (old('_method') === 'PUT') ? 'true' : 'false' }},
        search: '',
        formData: { 
            id: '{{ old('id', '') }}',
            razon_social: '{{ old('razon_social', '') }}', 
            rfc: '{{ old('rfc', '') }}', 
            ciec: '{{ old('ciec', '') }}', 
            fiel_password: '{{ old('fiel_password', '') }}',
            fiel_cer_name: '',
            fiel_key_name: ''
        }
     }">
    
    <x-sidebar />

    <div class="flex-1 flex flex-col overflow-hidden">
        <x-navbar :usuario="$usuario" />

        <main class="flex-1 p-6 overflow-y-auto bg-slate-50">
            <div class="max-w-full mx-auto">
                
                <!-- Encabezado -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-2xl text-slate-600">domain</span>
                        <h2 class="text-xl font-bold text-slate-800 tracking-tight">Gestión de Empresas</h2>
                    </div>
                    
                    <button @click="editMode = false; formData = { id: '', razon_social: '', rfc: '', ciec: '', fiel_password: '', fiel_cer_name: '', fiel_key_name: '' }; modalOpen = true" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm transition-all active:scale-95">
                        <span class="material-symbols-outlined text-sm">add_business</span> Nueva Empresa
                    </button>
                </div>

                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-700 text-xs font-bold rounded-xl flex items-center gap-2 shadow-sm animate-fade-in">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-end bg-white rounded-t-2xl">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2 text-slate-400 text-sm">search</span>
                            <input type="text" x-model="search" placeholder="Buscar RFC o Razón Social..." class="pl-9 pr-4 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-blue-500/20 w-64">
                        </div>
                    </div>

                    <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-320px)]">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 sticky top-0 z-10 border-b border-slate-200">
                                <tr>
                                    <th class="p-3 text-[10px] font-black uppercase text-slate-500 tracking-widest w-16">ID</th>
                                    <th class="p-3 text-[10px] font-black uppercase text-slate-500 tracking-widest">RFC</th>
                                    <th class="p-3 text-[10px] font-black uppercase text-slate-500 tracking-widest">Razón Social</th>
                                    <th class="p-3 text-[10px] font-black uppercase text-slate-500 tracking-widest text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($empresas as $e)
                                <tr x-show="search === '' || `{{ strtolower($e->rfc) }} {{ strtolower($e->razon_social) }}`.includes(search.toLowerCase())" 
                                    class="hover:bg-blue-50/30 transition-colors">
                                    <td class="p-3 text-xs font-bold text-slate-400">#{{ $e->id }}</td>
                                    <td class="p-3 text-xs font-bold text-slate-700 uppercase">{{ $e->rfc }}</td>
                                    <td class="p-3 text-xs text-slate-600 font-medium">{{ $e->razon_social }}</td>
                                    <td class="p-3">
                                        <div class="flex justify-center gap-1">
                                            <button @click="
                                                editMode = true; 
                                                formData = { 
                                                    id: '{{ $e->id }}', 
                                                    razon_social: '{{ $e->razon_social }}', 
                                                    rfc: '{{ $e->rfc }}',
                                                    ciec: '{{ $e->ciec }}', 
                                                    fiel_password: '{{ $e->fiel_password }}',
                                                    fiel_cer_name: '{{ $e->fiel_cer_path }}',
                                                    fiel_key_name: '{{ $e->fiel_key_path }}'
                                                };
                                                modalOpen = true; 
                                            " 
                                                class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                                <span class="material-symbols-outlined text-lg">edit</span>
                                            </button>
                                            
                                            <form action="{{ route('empresas.destroy', $e->id) }}" method="POST" class="inline-block">
                                                @csrf @method('DELETE')
                                                <button type="submit" onclick="return confirm('¿Eliminar {{ $e->razon_social }}?')"
                                                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                            <x-paginador :coleccion="$empresas" />
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL EMPRESA ACTUALIZADO -->
    <div x-show="modalOpen" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak x-transition>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl border border-slate-200">
                
                <div class="bg-slate-50 p-4 border-b border-slate-100 flex justify-between items-center rounded-t-2xl">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">business_center</span>
                        <h3 class="font-bold text-slate-800" x-text="editMode ? 'Editar Empresa' : 'Nueva Empresa'"></h3>
                    </div>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form :action="editMode ? `/empresas/${formData.id}` : '{{ route('empresas.store') }}'" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      class="p-6">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    <input type="hidden" name="id" x-model="formData.id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Razón Social</label>
                            <input type="text" name="razon_social" x-model="formData.razon_social" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">RFC</label>
                            <input type="text" name="rfc" x-model="formData.rfc" required maxlength="13" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 uppercase outline-none focus:ring-2 focus:ring-blue-500/20">
                            @error('rfc')
                                <p class="text-red-500 text-[10px] font-bold mt-1 italic">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Contraseña CIEC (Opcional)</label>
                            <input type="password" name="ciec" x-model="formData.ciec" placeholder="••••••••" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>
                    </div>

                    <!-- SECCIÓN FIEL CON INDICADORES DE ARCHIVOS -->
                    <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 mb-6">
                        <h4 class="text-[10px] font-black text-blue-600 uppercase mb-3 tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">verified_user</span> Archivos FIEL (.cer / .key)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 mb-1">Archivo Certificado (.cer)</label>
                                <input type="file" name="fiel_cer" accept=".cer" class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                                
                                <template x-if="formData.fiel_cer_name">
                                    <p class="text-[9px] text-blue-600 mt-1 font-bold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px]">description</span> 
                                        Actual: <span x-text="formData.fiel_cer_name"></span>
                                    </p>
                                </template>
                            </div>
                            
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 mb-1">Archivo Llave (.key)</label>
                                <input type="file" name="fiel_key" accept=".key" class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                                
                                <template x-if="formData.fiel_key_name">
                                    <p class="text-[9px] text-blue-600 mt-1 font-bold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px]">key</span> 
                                        Actual: <span x-text="formData.fiel_key_name"></span>
                                    </p>
                                </template>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-[9px] font-bold text-slate-500 mb-1">Contraseña de la FIEL</label>
                                <input type="password" name="fiel_password" x-model="formData.fiel_password" placeholder="••••••••" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="flex-1 px-4 py-2 border border-slate-200 text-slate-500 rounded-xl text-xs font-black uppercase hover:bg-slate-50 transition">Cancelar</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-black uppercase hover:bg-blue-700 shadow-lg shadow-blue-200 transition">
                            <span x-text="editMode ? 'Actualizar Empresa' : 'Guardar Empresa'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection