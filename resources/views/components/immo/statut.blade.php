@props(['validation'])
@if (strtoupper((string) $validation) === 'OUI')
    <span {{ $attributes->merge(['class' => 'tag-garde']) }}><x-icon name="check" class="size-3" /> En ligne</span>
@else
    <span {{ $attributes->merge(['class' => 'tag-amber']) }}><x-icon name="clock" class="size-3" /> En attente de validation</span>
@endif
