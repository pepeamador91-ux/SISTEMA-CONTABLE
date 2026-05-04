<!-- resources/views/components/sidebar.blade.php -->
<aside 
    :class="sidebarOpen ? 'w-64' : 'w-0'" 
    class="bg-slate-900 text-white transition-all duration-300 flex flex-col overflow-hidden shadow-xl z-20 h-screen shrink-0">
    
    <!-- El resto de tu código igual... -->
    <div class="p-6 border-b border-slate-800 flex items-center gap-3 min-w-[256px]">
        <span class="material-symbols-outlined text-blue-500">account_balance</span>
        <span class="font-bold tracking-tighter text-xl text-white">MENU</span>
    </div>

    <nav class="flex-1 mt-4 px-4 space-y-2 min-w-[256px]">
        <a href="{{ route('dashboard') }}" 
           class="flex items-center gap-3 py-2 px-3 rounded hover:bg-slate-800 transition text-white {{ request()->routeIs('dashboard') ? 'bg-slate-800' : '' }}">
            <span class="material-symbols-outlined text-slate-400">dashboard</span>
            <span class="text-sm font-medium">Inicio</span>
        </a>
        <a href="{{route('descargas.index')}}"
           class="flex items-center gap-3 py-2 px-3 rounded hover:bg-slate-800 transition text-white {{ request()->routeIs('descargas.index') ? 'bg-slate-800' : '' }}">
            <span class="material-symbols-outlined text-slate-400">download</span>
            <span class="text-sm font-medium">Descargas</span>
        </a>  
        <div x-data="{ configOpen: false }">
            <button @click="configOpen = !configOpen" class="w-full flex items-center justify-between py-2 px-3 rounded hover:bg-slate-800 transition text-white">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-slate-400">settings</span>
                    <span class="text-sm font-medium">Configuración</span>
                </div>
                <span class="material-symbols-outlined text-xs transition-transform" :class="configOpen ? 'rotate-180' : ''">expand_more</span>
            </button>
            <div x-show="configOpen" x-transition class="ml-9 mt-2 space-y-1 border-l border-slate-700">
                <a href="{{ route('usuarios.index') }}" class="block py-2 px-4 text- xs font-medium text-slate-400 hover:text-white hover:bg-slate-800 rounded transition">Usuarios</a>
                <a href="{{ route('empresas.index') }}" class="block py-2 px-4 text- xs font-medium text-slate-400 hover:text-white hover:bg-slate-800 rounded transition">Empresas</a>
            </div>
        </div>
    </nav>
</aside>