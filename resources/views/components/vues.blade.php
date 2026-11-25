@props(['nombre'])
@php $n = (int) $nombre; @endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }} title="Nombre de consultations">
    <x-icon name="eye" class="size-4" /> {{ number_format($n, 0, ',', "\u{202F}") }} {{ $n > 1 ? 'vues' : 'vue' }}
</span>
