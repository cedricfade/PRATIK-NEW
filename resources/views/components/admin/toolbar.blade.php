{{-- Barre de filtres / actions au-dessus d'une liste --}}
<div {{ $attributes->merge(['class' => 'mb-4 flex flex-wrap items-end gap-3']) }}>
    {{ $slot }}
</div>
