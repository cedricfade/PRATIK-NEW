{{-- Bouton "autour de moi" : récupère la position GPS puis envoie le formulaire. --}}
<form id="autour-de-moi" method="POST" action="{{ route('pharmacies.autour') }}" x-data="geoForm()" class="scroll-mt-28 rounded-xl border border-garde/25 bg-garde-wash/70 p-5 sm:p-6">
    @csrf
    <input type="hidden" name="latitude" x-ref="lat">
    <input type="hidden" name="longitude" x-ref="lng">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="font-semibold text-garde">Pharmacie de garde la plus proche</p>
            <p class="mt-1 text-sm text-ink/70">Votre position sert uniquement à ce calcul : elle n'est ni enregistrée, ni partagée.</p>
        </div>
        <button type="button" @click="locate()" :disabled="loading" class="btn-garde shrink-0 py-3">
            <x-icon name="locate" class="size-4" />
            <span x-text="loading ? 'Localisation…' : 'Me localiser'">Me localiser</span>
        </button>
    </div>
    <p x-show="error" x-text="error" x-cloak class="mt-3 text-sm text-danger"></p>
    @error('latitude')<p class="mt-3 text-sm text-danger">{{ $message }}</p>@enderror
</form>
