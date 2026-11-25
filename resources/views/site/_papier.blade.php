<article class="card flex flex-col p-5">
    <div class="flex gap-4">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="" loading="lazy" class="size-14 shrink-0 rounded-lg border border-line bg-white object-contain p-1">
        @else
            <span class="grid size-14 shrink-0 place-items-center rounded-lg bg-sand"><x-icon name="file-text" class="size-6 text-ink/60" /></span>
        @endif
        <div class="min-w-0">
            <h3 class="font-sans text-base font-semibold leading-snug">{{ $item->TITRE }}</h3>
            @if ($item->DESCRIPTION)<p class="mt-1 text-sm text-ink/70">{{ $item->DESCRIPTION }}</p>@endif
        </div>
    </div>
    @if ($item->pieces->isNotEmpty())
        <div class="mt-4 rounded-lg bg-paper p-4">
            <p class="text-xs font-semibold tracking-wide text-muted uppercase">Pièces à fournir</p>
            <ul class="mt-2 space-y-1.5 text-sm">
                @foreach ($item->pieces as $piece)
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 shrink-0 text-garde" /> {{ $piece->LIBELLE }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if ($item->LIEN)
        <a href="{{ $item->LIEN }}" target="_blank" rel="noopener" class="btn-line mt-4 self-start">Site officiel <x-icon name="external" class="size-4" /></a>
    @endif
</article>
