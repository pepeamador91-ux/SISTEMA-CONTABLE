@extends('layouts.app')

@section('content')
<div class="relative flex h-screen overflow-hidden" 
     x-data="{ 
        sidebarOpen: true, 
        tipoDescarga: 'emitidas', 
        metodoAuth: 'ciec',
        empresaSeleccionada: null,
        search: '',
        openDropdown: false,
        empresas: {{ $empresas->toJson() }},
        fielCerNombre: '',
        fielKeyNombre: '',
        fielPasswordActual: '',
        ciecActual: '',
        cargando: false, 
        
        get filteredEmpresas() {
            return this.empresas.filter(e => 
                e.razon_social.toLowerCase().includes(this.search.toLowerCase()) || 
                e.rfc.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        seleccionarEmpresa(emp) {
            this.empresaSeleccionada = emp;
            this.ciecActual = emp.ciec || '';
            this.fielPasswordActual = emp.fiel_password || '';
            this.fielCerNombre = emp.fiel_cer_path || '';
            this.fielKeyNombre = emp.fiel_key_path || '';
            this.search = emp.rfc + ' - ' + emp.razon_social;
            this.openDropdown = false;
        },

        iniciarDescarga() {
            if(!this.empresaSeleccionada) {
                alert('Por favor, selecciona una empresa primero.');
                return;
            }

            // Validación básica de fechas antes de enviar
            const fechaInicio = document.querySelector('[name=fecha_inicio]')?.value;
            if (this.tipoDescarga === 'emitidas' && !fechaInicio) {
                alert('Por favor, selecciona al menos una fecha de inicio.');
                return;
            }
            
            this.cargando = true;
            
            // FormData captura automáticamente los archivos de los inputs type='file'
            const formData = new FormData(this.$refs.formDescarga);
            
            fetch('{{ route('descargas.procesar') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : null;
                
                if (!response.ok) {
                    throw new Error(data?.error || `Error ${response.status}: El servidor no respondió correctamente.`);
                }
                return data;
            })
            .then(data => {
                this.cargando = false;
                if(data.success) {
                    alert('Éxito: ' + (data.message || 'La información se ha procesado con éxito.'));
                } else {
                    alert('Atención: ' + data.error);
                }
            })
            .catch(error => {
                this.cargando = false;
                alert('Atención: ' + error.message);
                console.error(error);
            });
        }
     }">
    
    <!-- ESTILOS CSS -->
    <style>
        [x-cloak] { display: none !important; }
        @keyframes progress-loop {
            0% { left: -45%; }
            100% { left: 100%; }
        }
        .animate-progress-loop {
            animation: progress-loop 2s infinite linear;
        }
    </style>

    <!-- OVERLAY DE CARGA -->
    <div x-show="cargando" x-cloak
         class="absolute inset-0 z-[100] flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white p-8 rounded-3xl shadow-2xl max-w-sm w-full text-center border border-slate-100">
            <div class="mb-4 inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full animate-bounce">
                <span class="material-symbols-outlined text-3xl">cloud_download</span>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Procesando Solicitud</h3>
            <p class="text-sm text-slate-500 mb-6">Conectando con el SAT e importando datos. Por favor, no cierres esta ventana.</p>
            
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden relative">
                <div class="bg-blue-600 h-full rounded-full absolute top-0 left-0 animate-progress-loop" style="width: 45%"></div>
            </div>
            <p class="mt-4 text-[10px] font-black uppercase text-slate-400 tracking-widest">Sistema Contable - Windows Native</p>
        </div>
    </div>

    <!-- SIDEBAR -->
    <x-sidebar x-show="sidebarOpen" :class="sidebarOpen ? 'w-64' : 'w-20'" class="transition-all duration-300" />

    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- NAVBAR -->
        <x-navbar :usuario="$usuario" @toggle-sidebar="sidebarOpen = !sidebarOpen" />

        <!-- CONTENIDO PRINCIPAL -->
        <main class="flex-1 p-8 overflow-y-auto bg-slate-50">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center gap-3 mb-8">
                    <span class="material-symbols-outlined text-3xl text-blue-600">cloud_download</span>
                    <h2 class="text-2xl font-bold text-slate-800">Descarga Masiva SAT</h2>
                </div>

                <form x-ref="formDescarga" @submit.prevent="iniciarDescarga()" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <input type="hidden" name="empresa_id" :value="empresaSeleccionada ? empresaSeleccionada.id : ''">
                    <input type="hidden" name="metodo_auth" :value="metodoAuth">
                    
                    <!-- SECCIÓN 1: IDENTIFICACIÓN -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <h3 class="text-sm font-black uppercase text-slate-400 mb-4 tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">domain</span> 1. Identificación
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="relative">
                                <label class="block text-xs font-bold text-slate-700 mb-2">Buscar Empresa (RFC o Nombre)</label>
                                <div class="relative">
                                    <input type="text" 
                                           x-model="search" 
                                           @click="openDropdown = true"
                                           @click.away="openDropdown = false"
                                           placeholder="Escriba para buscar..."
                                           autocomplete="off"
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-10 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400">search</span>
                                </div>

                                <div x-show="openDropdown" x-cloak
                                     class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto"
                                     x-transition>
                                    <template x-for="emp in filteredEmpresas" :key="emp.id">
                                        <div @click="seleccionarEmpresa(emp)" 
                                             class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors">
                                            <div class="text-[10px] font-black text-blue-600" x-text="emp.rfc"></div>
                                            <div class="text-xs text-slate-600 truncate" x-text="emp.razon_social"></div>
                                        </div>
                                    </template>
                                    <div x-show="filteredEmpresas.length === 0" class="px-4 py-3 text-xs text-slate-400 italic">
                                        No se encontraron resultados
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2">Método de Autenticación</label>
                                <div class="flex p-1 bg-slate-100 rounded-xl">
                                    <button type="button" @click="metodoAuth = 'ciec'" :class="metodoAuth === 'ciec' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'" class="flex-1 py-1.5 rounded-lg text-xs font-bold transition-all">CIEC</button>
                                    <button type="button" @click="metodoAuth = 'fiel'" :class="metodoAuth === 'fiel' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'" class="flex-1 py-1.5 rounded-lg text-xs font-bold transition-all">e.Firma</button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-blue-50/50 rounded-xl border border-blue-100">
                            <div style="display: none;">
                                <input type="text" name="prevent_autofill_user">
                                <input type="password" name="prevent_autofill_pass">
                            </div>

                            <!-- Bloque CIEC -->
                            <div x-show="metodoAuth === 'ciec'">
                                <label class="block text-[10px] font-black uppercase text-blue-700 mb-1">Contraseña CIEC</label>
                                <input type="password" 
                                       name="ciec_pass_field" 
                                       x-model="ciecActual" 
                                       autocomplete="new-password"
                                       placeholder="Contraseña guardada o nueva"
                                       class="w-full bg-white border border-blue-200 rounded-lg px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                            </div>

                            <!-- Bloque e.Firma -->
                            <div x-show="metodoAuth === 'fiel'" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-blue-700 mb-1">Certificado (.cer)</label>
                                    <input type="file" name="fiel_cer" class="text-[10px] w-full file:bg-blue-600 file:text-white file:border-0 file:px-2 file:py-1 file:rounded-md cursor-pointer">
                                    <template x-if="fielCerNombre">
                                        <p class="text-[9px] text-green-600 mt-1 font-bold flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[12px]">check_circle</span> 
                                            Registrado: <span x-text="fielCerNombre"></span>
                                        </p>
                                    </template>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-blue-700 mb-1">Llave (.key)</label>
                                    <input type="file" name="fiel_key" class="text-[10px] w-full file:bg-blue-600 file:text-white file:border-0 file:px-2 file:py-1 file:rounded-md cursor-pointer">
                                    <template x-if="fielKeyNombre">
                                        <p class="text-[9px] text-green-600 mt-1 font-bold flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[12px]">check_circle</span> 
                                            Registrado: <span x-text="fielKeyNombre"></span>
                                        </p>
                                    </template>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-blue-700 mb-1">Password e.Firma</label>
                                    <input type="password" name="fiel_password"
                                           x-model="fielPasswordActual"
                                           autocomplete="new-password" placeholder="Clave privada"
                                           class="w-full bg-white border border-blue-200 rounded-lg px-4 py-2 text-sm outline-none">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: PARÁMETROS -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <h3 class="text-sm font-black uppercase text-slate-400 mb-4 tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">calendar_month</span> 2. Rango de Consulta
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-2">Tipo de Comprobante</label>
                                <select name="tipo_comprobante" x-model="tipoDescarga" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none">
                                    <option value="emitidas">Facturas Emitidas</option>
                                    <option value="recibidas">Facturas Recibidas</option>
                                </select>
                            </div>

                            <template x-if="tipoDescarga === 'emitidas'">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-2">Desde</label>
                                        <input type="date" name="fecha_inicio" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-2">Hasta</label>
                                        <input type="date" name="fecha_fin" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none">
                                    </div>
                                </div>
                            </template>

                            <template x-if="tipoDescarga === 'recibidas'">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-2">Año</label>
                                        <select name="anio" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none">
                                            @for($i = date('Y'); $i >= 2020; $i--)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-2">Mes</label>
                                        <select name="mes" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none">
                                            <option value="0">0 - Todo el año</option>
                                            @foreach(['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $index => $mes)
                                                <option value="{{ $index + 1 }}">{{ $index + 1 }} - {{ $mes }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <button type="submit" 
                            :disabled="cargando"
                            :class="cargando ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                            class="w-full bg-blue-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" x-show="!cargando">download</span>
                        <svg x-show="cargando" x-cloak class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="cargando ? 'Procesando Descarga...' : 'Iniciar Descarga Masiva'"></span>
                    </button>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection