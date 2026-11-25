{{-- Barre de recherche de l'annuaire. Variables : $communes, $terme (opt.), $commune (opt.) --}}
<form action="{{ route('recherche') }}" method="GET" role="search" class="flex flex-col gap-2 rounded-xl border border-line bg-white p-2 shadow-[var(--shadow-card)] sm:flex-row">
    <label class="sr-only" for="q-annuaire">Que cherchez-vous ?</label>
    <div class="flex flex-1 items-center gap-2 px-3">
        <x-icon name="search" class="size-5 text-muted" />
        <input id="q-annuaire" name="q" type="search" value="{{ $terme ?? '' }}" required minlength="2" placeholder="Nom, sigle, métier… ex. « notaire », « SODECI »" class="w-full border-0 bg-transparent py-3 text-[15px] focus:ring-0">
    </div>
    <label class="sr-only" for="commune-annuaire">Commune</label>
    <select id="commune-annuaire" name="commune" class="rounded-lg border-line bg-paper py-3 pr-9 text-sm sm:w-52">
        <option value="">Toute la Côte d'Ivoire</option>
        @foreach ($communes as $c)
            <option value="{{ $c->LIEN }}" @selected(($commune->LIEN ?? null) === $c->LIEN)>{{ $c->DESIGNATION }}</option>
        @endforeach
    </select>
    <button class="btn-ink px-6 py-3">Rechercher</button>
</form>
