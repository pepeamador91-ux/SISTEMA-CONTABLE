@extends('layouts.app')

@section('content')
<!-- Se mantienen todas las variables originales en el x-data -->
<div class="flex h-screen overflow-hidden" 
     x-data="{ 
        sidebarOpen: true, 
        modalOpen: false, 
        editMode: false,
        search: '',
        actionUrl: '{{ route('usuarios.store') }}',
        formData: { name: '', email: '', role: 'usuario', label: 'Usuario Estándar' }
     }">
    
    <x-sidebar />

    <div class="flex-1 flex flex-col overflow-hidden">
        <x-navbar :usuario="$usuario" />

        <main class="flex-1 p-6 overflow-y-auto bg-slate-50">
            <div class="max-w-full mx-auto">
                
                <!-- Encabezado -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-2xl text-slate-600">group</span>
                        <h2 class="text-xl font-bold text-slate-800 tracking-tight">Gestión de Usuarios</h2>
                    </div>
                    <!-- Botón Nuevo: Resetea el formulario y pone editMode en false -->
                    <button @click="editMode = false; actionUrl = '{{ route('usuarios.store') }}'; formData = { name: '', email: '', role: 'usuario', label: 'Usuario Estándar' }; modalOpen = true" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm transition-all active:scale-95">
                        <span class="material-symbols-outlined text-sm">person_add</span> Nuevo Usuario
                    </button>
                </div>

                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-700 text-xs font-bold rounded-xl flex items-center gap-2 shadow-sm animate-fade-in">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif
                
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-700 text-xs font-bold rounded-xl shadow-sm animate-fade-in">
                        <ul class="list-disc list-inside ml-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-end bg-white rounded-t-2xl">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2 text-slate-400 text-sm">search</span>
                            <!-- Se anexa x-model para que la búsqueda funcione -->
                            <input type="text" x-model="search" placeholder="Buscar usuario..." class="pl-9 pr-4 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-blue-500/20 w-64">
                        </div>
                    </div>

                    <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-320px)]">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 sticky top-0 z-10 border-b border-slate-200">
                                <tr>
                                    <th class="p-3 text-[10px] font-black uppercase text-slate-500 tracking-widest w-16">ID</th>
                                    <th class="p-3 text-[10px] font-black uppercase text-slate-500 tracking-widest">Nombre del Usuario</th>
                                    <th class="p-3 text-[10px] font-black uppercase text-slate-500 tracking-widest">Correo Electrónico</th>
                                    <th class="p-3 text-[10px] font-black uppercase text-slate-500 tracking-widest">Rol asignado</th>
                                    <th class="p-3 text-[10px] font-black uppercase text-slate-500 tracking-widest text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($usuariosListado as $u)
                                <!-- Se anexa el x-show para filtrar dinámicamente -->
                                <tr x-show="search === '' || `{{ strtolower($u->name) }} {{ strtolower($u->email) }}`.includes(search.toLowerCase())" 
                                    class="hover:bg-blue-50/30 transition-colors">
                                    <td class="p-3 text-xs font-bold text-slate-400">#{{ $u->id }}</td>
                                    <td class="p-3 text-xs font-bold text-slate-700 uppercase">{{ $u->name }}</td>
                                    <td class="p-3 text-xs text-slate-600">{{ $u->email }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[9px] font-black uppercase border border-slate-200">
                                            {{ $u->role_name }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex justify-center gap-1">
                                            <!-- Botón Editar -->
                                            <button @click="
                                                editMode = true; 
                                                modalOpen = true; 
                                                actionUrl = '/usuarios/{{ $u->id }}'; 
                                                formData = { 
                                                    name: '{{ $u->name }}', 
                                                    email: '{{ $u->email }}', 
                                                    role: '{{ $u->role }}', 
                                                    label: '{{ \App\Models\User::$roles[$u->role] ?? 'Usuario Estándar' }}' 
                                                }" 
                                                class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                                <span class="material-symbols-outlined text-lg">edit</span>
                                            </button>
                                            
                                            <form action="{{ route('usuarios.destroy', $u->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        onclick="return confirm('¿Estás seguro de que deseas eliminar a {{ $u->name }}? El registro se conservará en el historial pero ya no aparecerá en esta lista.')"
                                                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" 
                                                        title="Eliminar">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <x-paginador :coleccion="$usuariosListado" />
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL ÚNICO -->
    <div x-show="modalOpen" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak x-transition>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-200">
                
                <div class="bg-slate-50 p-4 border-b border-slate-100 flex justify-between items-center rounded-t-2xl">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600" x-text="editMode ? 'edit_note' : 'person_add'"></span>
                        <h3 class="font-bold text-slate-800" x-text="editMode ? 'Editar Usuario' : 'Nuevo Usuario'"></h3>
                    </div>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Formulario Dinámico -->
                <form :action="actionUrl" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="editMode">
                        @method('PUT')
                    </template>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Nombre del Usuario</label>
                        <input type="text" name="name" x-model="formData.name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all uppercase font-bold text-slate-700">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Correo Electrónico</label>
                        <input type="email" name="email" x-model="formData.email" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-600">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">
                           <span x-text="editMode ? 'Nueva Contraseña (Opcional)' : 'Contraseña / Clave'"></span>
                        </label>
                        <input type="password" name="password" :required="!editMode" autocomplete="new-password" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>

                    <!-- Selector de Roles Personalizado -->
                    <div x-data="{ searchRole: '', open: false }">
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 tracking-widest">Rol del Sistema</label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm flex justify-between items-center text-slate-700 font-bold">
                                <span x-text="formData.label"></span>
                                <span class="material-symbols-outlined text-slate-400" :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition class="absolute left-0 right-0 z-[150] mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl p-2">
                                <input type="text" x-model="searchRole" placeholder="Buscar rol..." class="w-full px-3 py-1.5 mb-2 bg-slate-50 border border-slate-100 rounded-lg text-xs outline-none">
                                <div class="overflow-y-auto max-h-[180px]">
                                    @foreach(\App\Models\User::$roles as $key => $roleName)
                                    <button type="button" 
                                            x-show="'{{ strtolower($roleName) }}'.includes(searchRole.toLowerCase())"
                                            @click="formData.role = '{{ $key }}'; formData.label = '{{ $roleName }}'; open = false"
                                            class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-blue-50 font-bold text-slate-700 flex justify-between">
                                        <span>{{ $roleName }}</span>
                                        <span x-show="formData.role == '{{ $key }}'" class="material-symbols-outlined text-sm text-blue-600">check</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" name="role" :value="formData.role">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false" class="flex-1 px-4 py-2 border border-slate-200 text-slate-500 rounded-xl text-xs font-black uppercase hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-black uppercase hover:bg-blue-700 shadow-lg shadow-blue-200 transition">
                            <span x-text="editMode ? 'Actualizar' : 'Guardar'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection