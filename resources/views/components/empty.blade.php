@props(['icon' => 'search', 'title' => 'Aucun résultat'])
<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-line bg-white px-6 py-14 text-center']) }}>
    <x-icon :name="$icon" class="mx-auto size-8 text-ink/30" />
    <p class="mt-3 font-semibold">{{ $title }}</p>
    @if ($slot->isNotEmpty())<div class="mx-auto mt-1 max-w-md text-sm text-muted">{{ $slot }}</div>@endif
</div>
