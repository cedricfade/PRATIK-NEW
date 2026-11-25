@props(['label', 'value', 'icon' => 'chart', 'href' => null, 'tone' => 'ink', 'hint' => null])
@php
    $tones = ['ink' => 'bg-sand text-ink', 'amber' => 'bg-amber-wash text-amber-deep', 'garde' => 'bg-garde-wash text-garde', 'roof' => 'bg-roof/20 text-roof-deep', 'danger' => 'bg-red-50 text-danger'];
@endphp
<{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'card flex items-center gap-4 p-4'.($href ? ' card-hover' : '')]) }}>
    <span class="grid size-11 shrink-0 place-items-center rounded-lg {{ $tones[$tone] ?? $tones['ink'] }}"><x-icon :name="$icon" class="size-5" /></span>
    <span class="min-w-0">
        <span class="block text-2xl font-semibold tabular-nums leading-tight">{{ is_numeric($value) ? number_format($value, 0, ',', ' ') : $value }}</span>
        <span class="block truncate text-sm text-muted">{{ $label }}</span>
        @if ($hint)<span class="block truncate text-xs text-ink/50">{{ $hint }}</span>@endif
    </span>
</{{ $href ? 'a' : 'div' }}>
