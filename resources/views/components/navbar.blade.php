<header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-30" x-data="{ open: false }">
    
    <!-- Gatillo Sidebar -->
    <button @click="sidebarOpen = !sidebarOpen" class="hover:text-blue-600 transition">
        <span class="material-symbols-outlined text-3xl">menu_open</span>
    </button>

    <!-- Usuario -->
    <div class="relative">
        <button @click="open = !open" @click.away="open = false" 
            class="flex items-center gap-3 p-1 pr-4 border rounded-full hover:bg-slate-50 transition shadow-sm">
            
            <span class="material-symbols-outlined text-4xl text-blue-600">account_circle</span>
            
            <div class="text-right leading-none">
                <p class="text-xs font-bold text-slate-800">{{ $usuario->name ?? 'Admin' }}</p>
                <small class="text-[10px] text-blue-500 font-black uppercase tracking-tighter">Online</small>
            </div>
        </button>

        <!-- Menu Desplegable -->
        <div x-show="open" x-cloak x-transition 
            class="absolute right-0 mt-2 w-48 bg-white border rounded-2xl shadow-xl py-1 overflow-hidden z-50">
            
            <!-- Perfil -->
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-blue-50">
                <span class="material-symbols-outlined text-lg">person</span> Perfil
            </a>
            
            <hr class="border-slate-100">
            
            <!-- BOTÓN DE SALIR CORREGIDO -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm font-bold text-red-500 hover:bg-red-50 transition text-left">
                    <span class="material-symbols-outlined text-lg">logout</span> Salir
                </button>
            </form>
        </div>
    </div>
</header>