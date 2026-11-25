<div class="mb-5 flex flex-wrap items-center gap-2">
    @foreach (\App\Support\Hero::PAGES as $cle => $libelle)
        <a href="{{ route('admin.apparence.index', ['page' => $cle]) }}" @class([
            'rounded-md px-3 py-2 text-sm font-medium',
            'bg-ink text-white' => $actif === $cle,
            'bg-white text-ink/70 ring-1 ring-line hover:text-ink' => $actif !== $cle,
        ])>{{ $libelle }}</a>
    @endforeach
    <a href="{{ route('admin.apparence.application') }}" @class([
        'inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium',
        'bg-ink text-white' => $actif === 'application',
        'bg-white text-ink/70 ring-1 ring-line hover:text-ink' => $actif !== 'application',
    ])><x-icon name="smartphone" class="size-4" /> Application mobile</a>
</div>
