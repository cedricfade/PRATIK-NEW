@props(['src' => null, 'name' => '', 'size' => 'size-14'])
@php
    $mots = preg_split('/\s+/', trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', (string) $name)));
    $initiales = mb_strtoupper(mb_substr($mots[0] ?? '', 0, 1).mb_substr($mots[1] ?? '', 0, 1)) ?: 'P';
    $teintes = ['bg-amber-wash text-amber-deep', 'bg-garde-wash text-garde', 'bg-sand text-ink', 'bg-sky-50 text-sky-800', 'bg-rose-50 text-rose-800'];
    $teinte = $teintes[crc32((string) $name) % count($teintes)];
@endphp
<div {{ $attributes->merge(['class' => "$size shrink-0 overflow-hidden rounded-lg border border-line bg-white"]) }}
     @if ($src) x-data="{ ko: false }" @endif>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $name }}" loading="lazy" class="size-full object-contain p-1" x-show="!ko" x-on:error="ko = true">
        <span x-show="ko" x-cloak class="grid size-full place-items-center font-serif text-lg font-semibold {{ $teinte }}">{{ $initiales }}</span>
    @else
        <span class="grid size-full place-items-center font-serif text-lg font-semibold {{ $teinte }}">{{ $initiales }}</span>
    @endif
</div>
