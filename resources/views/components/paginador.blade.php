@props(['coleccion'])

<div class="p-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl flex items-center justify-between">
    <!-- Conteo de items -->
    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-tight">
        Mostrando <span class="text-blue-600">{{ $coleccion->count() }}</span> de <span class="text-slate-800">{{ $coleccion->total() }}</span> registros
    </div>

    <div class="flex items-center gap-6">
        <!-- Selector de Cantidad -->
        <div class="flex items-center gap-2">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ver:</label>
            <select onchange="window.location.href = this.value" class="bg-white border border-slate-200 rounded-lg text-[11px] font-bold text-slate-700 py-1 px-2 outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                @foreach([25, 50, 100] as $perPage)
                    <option value="{{ request()->fullUrlWithQuery(['perPage' => $perPage]) }}" {{ request('perPage') == $perPage ? 'selected' : '' }}>
                        {{ $perPage }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Flechas de Navegación -->
        <div class="flex items-center gap-1">
            @if ($coleccion->onFirstPage())
                <span class="p-1.5 text-slate-300 cursor-not-allowed">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </span>
            @else
                <a href="{{ $coleccion->previousPageUrl() }}" class="p-1.5 text-slate-600 hover:bg-blue-600 hover:text-white rounded-lg transition-all shadow-sm bg-white border border-slate-200">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </a>
            @endif

            <div class="px-3 py-1 bg-blue-600 text-white rounded-lg text-[11px] font-black shadow-lg shadow-blue-200">
                {{ $coleccion->currentPage() }}
            </div>

            @if ($coleccion->hasMorePages())
                <a href="{{ $coleccion->nextPageUrl() }}" class="p-1.5 text-slate-600 hover:bg-blue-600 hover:text-white rounded-lg transition-all shadow-sm bg-white border border-slate-200">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
            @else
                <span class="p-1.5 text-slate-300 cursor-not-allowed">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </span>
            @endif
        </div>
    </div>
</div>