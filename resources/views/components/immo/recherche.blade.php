@props(['categories', 'communes', 'transaction' => 'vente', 'filtres' => [], 'compact' => false])
{{-- Barre de recherche immobilier : l'onglet choisi décide de la page cible (achats / locations). --}}
<form x-data="{ t: '{{ $transaction === 'location' ? 'location' : 'vente' }}' }"
      :action="t === 'location' ? '{{ route('immo.locations') }}' : '{{ route('immo.achats') }}'"
      action="{{ $transaction === 'location' ? route('immo.locations') : route('immo.achats') }}"
      method="GET" role="search"
      {{ $attributes->merge(['class' => 'rounded-xl bg-white p-3 shadow-[var(--shadow-lift)] ring-1 ring-line']) }}>
    <div class="mb-3 inline-flex rounded-lg bg-sand p-1 text-sm font-semibold">
        <button type="button" @click="t = 'vente'" :class="t === 'vente' ? 'bg-white text-ink shadow-sm' : 'text-ink/60'" class="rounded-md px-4 py-1.5">Acheter</button>
        <button type="button" @click="t = 'location'" :class="t === 'location' ? 'bg-white text-ink shadow-sm' : 'text-ink/60'" class="rounded-md px-4 py-1.5">Louer</button>
    </div>
    <div class="grid gap-2 {{ $compact ? 'sm:grid-cols-2 lg:grid-cols-4' : 'sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto]' }}">
        <label class="block">
            <span class="sr-only">Type de bien</span>
            <select name="categorie" class="input">
                <option value="">Type de bien</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->ID }}" @selected(($filtres['categorie'] ?? null) == $c->ID)>{{ $c->DESIGNATION }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="sr-only">Commune</span>
            <select name="commune" class="input">
                <option value="">Commune</option>
                @foreach ($communes as $c)
                    <option value="{{ $c->ID }}" @selected(($filtres['commune'] ?? null) == $c->ID)>{{ $c->DESIGNATION }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="sr-only">Budget maximum</span>
            <input name="prix_max" inputmode="numeric" value="{{ isset($filtres['prix_max']) ? number_format($filtres['prix_max'], 0, ',', ' ') : '' }}" placeholder="Budget max" class="input">
        </label>
        <button class="btn-roof py-2.5"><x-icon name="search" class="size-4" /> Rechercher</button>
    </div>
</form>
