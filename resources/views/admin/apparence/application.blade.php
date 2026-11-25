@extends('layouts.admin')
@section('title', 'Application mobile')

@section('content')
@include('admin.apparence._onglets', ['actif' => 'application'])

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.apparence.application.update') }}"
      x-data="{ image: @js($imageUrl), choisir(e) { const f = e.target.files[0]; if (f) this.image = URL.createObjectURL(f) } }"
      class="grid gap-6 lg:grid-cols-[320px_1fr]">
    @csrf @method('PUT')

    {{-- Aperçu téléphone --}}
    <div>
        <div class="mx-auto w-[260px] rounded-[2.2rem] border-[10px] border-ink bg-ink shadow-[var(--shadow-lift)]">
            <div class="relative aspect-[9/19.5] overflow-hidden rounded-[1.6rem] bg-sand">
                <template x-if="image"><img :src="image" alt="" class="size-full object-cover"></template>
                <div x-show="!image" class="grid size-full place-items-center p-6 text-center text-sm text-muted">Aucune image</div>
                <span class="absolute top-3 right-3 rounded-full bg-ink/70 px-3 py-1 text-[11px] font-semibold text-white">Passer · {{ $r['duree'] ?: 5 }}</span>
            </div>
        </div>
        <p class="mt-3 text-center text-sm">
            @if ($enLigne)
                <span class="tag-garde"><x-icon name="check-circle" class="size-3" /> Affichée en ce moment dans l'app</span>
            @else
                <span class="tag">Non affichée actuellement</span>
            @endif
        </p>
    </div>

    <div class="card space-y-5 p-5">
        <div>
            <h2 class="font-sans text-base font-semibold">Publicité à l'ouverture de l'application</h2>
            <p class="mt-1 text-sm text-muted">Image plein écran montrée quelques secondes au lancement de l'app (Android et iPhone), avec un bouton « Passer ». Un appui sur l'image ouvre le lien, s'il y en a un.</p>
        </div>

        <label class="flex items-center gap-2 text-sm font-medium">
            <input type="hidden" name="actif" value="0">
            <input type="checkbox" name="actif" value="1" @checked(old('actif', $r['actif']) === '1') class="rounded border-line">
            Afficher la publicité
        </label>

        <div>
            <label class="label">Image</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" @change="choisir" class="block w-full text-sm">
            <p class="mt-1 text-xs text-muted">Format portrait, idéalement 1080 × 1920 px. Gardez le texte important au centre : les bords peuvent être rognés selon le téléphone.</p>
            @if ($imageUrl)
                <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="supprimer_image" value="1" class="rounded border-line"> Retirer l'image (désactive la publicité)</label>
            @endif
            @error('image')<p class="input-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="label">Lien ouvert au toucher <span class="font-normal text-muted">(facultatif)</span></label>
            <input type="url" name="lien" value="{{ old('lien', $r['lien']) }}" class="input" placeholder="https://pratik-ci.com/details/…">
            @error('lien')<p class="input-error">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="label">Durée (secondes)</label>
                <input type="number" name="duree" min="2" max="15" value="{{ old('duree', $r['duree'] ?: 5) }}" class="input" required>
            </div>
            <div>
                <label class="label">Du <span class="font-normal text-muted">(facultatif)</span></label>
                <input type="date" name="debut" value="{{ old('debut', $r['debut']) }}" class="input">
            </div>
            <div>
                <label class="label">Au <span class="font-normal text-muted">(facultatif)</span></label>
                <input type="date" name="fin" value="{{ old('fin', $r['fin']) }}" class="input">
                @error('fin')<p class="input-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <button class="btn-ink">Enregistrer</button>
    </div>
</form>

{{-- Carrousel publicitaire de l'accueil de l'app --}}
<section class="card mt-8">
    <div class="border-b border-line px-5 py-4">
        <h2 class="font-sans text-base font-semibold">Bannières de l'accueil de l'application</h2>
        <p class="mt-1 text-sm text-muted">Elles défilent en haut de l'écran d'accueil, au-dessus des catégories. Format paysage 1920 × 720 px, le même que les bannières du site. Un appui ouvre le lien : <code class="rounded bg-sand px-1">https://…</code> pour une page web, ou <code class="rounded bg-sand px-1">pratik://app/entreprise/lien-de-la-fiche</code>, <code class="rounded bg-sand px-1">pratik://app/bien/lien-du-bien</code>, <code class="rounded bg-sand px-1">pratik://app/immobilier</code> pour ouvrir un écran de l'app.</p>
    </div>

    @if ($pubs->isNotEmpty())
        <div class="divide-y divide-line/70">
            @foreach ($pubs as $pub)
                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.apparence.pubs.update', $pub) }}" class="grid items-center gap-4 px-5 py-4 lg:grid-cols-[180px_1fr_auto]">
                    @csrf @method('PUT')
                    <img src="{{ $pub->image_url }}" alt="" class="aspect-[2/1] w-full rounded-lg border border-line object-cover">
                    <div class="grid gap-3 sm:grid-cols-6">
                        <div class="sm:col-span-3"><label class="label">Titre (interne)</label><input name="titre" value="{{ $pub->titre }}" class="input py-2"></div>
                        <div class="sm:col-span-3"><label class="label">Lien</label><input name="lien" value="{{ $pub->lien }}" class="input py-2" placeholder="https://… ou pratik://app/…"></div>
                        <div><label class="label">Ordre</label><input type="number" name="ordre" min="0" value="{{ $pub->ordre }}" class="input py-2"></div>
                        <div class="sm:col-span-2"><label class="label">Du</label><input type="date" name="debut" value="{{ $pub->debut?->toDateString() }}" class="input py-2"></div>
                        <div class="sm:col-span-2"><label class="label">Au</label><input type="date" name="fin" value="{{ $pub->fin?->toDateString() }}" class="input py-2"></div>
                        <label class="flex items-center gap-2 pt-6 text-sm"><input type="hidden" name="actif" value="0"><input type="checkbox" name="actif" value="1" @checked($pub->actif) class="rounded border-line"> Active</label>
                        <div class="sm:col-span-6"><label class="label">Remplacer l'image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="text-sm"></div>
                    </div>
                    <div class="flex items-center gap-2 lg:flex-col lg:items-stretch">
                        @if ($pub->en_ligne)<span class="tag-garde justify-center">En ligne</span>@else<span class="tag justify-center">Hors ligne</span>@endif
                        <button class="btn-ink btn-sm">Enregistrer</button>
                        <button form="supprimer-pub-{{ $pub->id }}" class="btn-ghost btn-sm text-danger" onclick="return confirm('Supprimer cette bannière ?')">Supprimer</button>
                    </div>
                </form>
                <form id="supprimer-pub-{{ $pub->id }}" method="POST" action="{{ route('admin.apparence.pubs.destroy', $pub) }}" class="hidden">@csrf @method('DELETE')</form>
            @endforeach
        </div>
    @else
        <p class="px-5 py-6 text-sm text-muted">Aucune bannière : l'application affiche l'image d'en-tête de l'accueil du site.</p>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.apparence.pubs.store') }}" class="grid gap-3 border-t border-line bg-paper px-5 py-4 sm:grid-cols-6">
        @csrf
        <p class="font-semibold sm:col-span-6">Ajouter une bannière</p>
        <div class="sm:col-span-2"><label class="label">Image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp" required class="block w-full text-sm">@error('image')<p class="input-error">{{ $message }}</p>@enderror</div>
        <div class="sm:col-span-2"><label class="label">Titre (interne)</label><input name="titre" value="{{ old('titre') }}" class="input py-2" placeholder="Promo Noël partenaire X"></div>
        <div class="sm:col-span-2"><label class="label">Lien</label><input name="lien" value="{{ old('lien') }}" class="input py-2" placeholder="https://… ou pratik://app/…">@error('lien')<p class="input-error">{{ $message }}</p>@enderror</div>
        <div><label class="label">Ordre</label><input type="number" name="ordre" min="0" value="{{ old('ordre', 0) }}" class="input py-2"></div>
        <div class="sm:col-span-2"><label class="label">Du</label><input type="date" name="debut" value="{{ old('debut') }}" class="input py-2"></div>
        <div class="sm:col-span-2"><label class="label">Au</label><input type="date" name="fin" value="{{ old('fin') }}" class="input py-2"></div>
        <label class="flex items-center gap-2 pt-6 text-sm"><input type="hidden" name="actif" value="0"><input type="checkbox" name="actif" value="1" checked class="rounded border-line"> Active</label>
        <div class="sm:col-span-6"><button class="btn-amber"><x-icon name="plus" class="size-4" /> Ajouter au carrousel</button></div>
    </form>
</section>
@endsection
