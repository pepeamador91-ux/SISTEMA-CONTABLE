@props(['icon', 'label' => null])

<div class="mb-5">
    @if($label)
        <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wider mb-2">{{ $label }}</label>
    @endif
    <div class="relative group">
        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 group-focus-within:text-blue-600 transition-colors">
            {{ $icon }}
        </span>
        <input {{ $attributes->merge(['class' => 'w-full pl-10 pr-4 py-2.5 border-b-2 border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-600 focus:outline-none transition-all text-slate-800']) }}>
    </div>
</div>