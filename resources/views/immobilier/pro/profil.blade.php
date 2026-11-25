@extends('layouts.immo')

@section('title', 'Mon profil annonceur')
@section('noindex', true)

@section('content')
    <x-immo.pro-nav />

    <form method="POST" action="{{ route('immo.pro.profil.update') }}" enctype="multipart/form-data" class="container-page grid gap-6 py-8 lg:grid-cols-[1fr_320px]">
        @csrf @method('PUT')

        <div class="space-y-6">
            @if ($errors->any())
                <div class="rounded-lg border border-danger/30 bg-red-50 px-4 py-3 text-sm text-danger">
                    <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @if ($entreprise)
                <section class="card space-y-4 p-6">
                    <h2 class="font-sans text-base font-semibold">Ma société</h2>
                    <p class="-mt-2 text-sm text-muted">Ces informations apparaissent sur vos annonces et sur votre page annonceur.</p>
                    <div class="grid gap-4 sm:grid-cols-[1fr_180px]">
                        <div><label class="label" for="titre">Raison sociale</label><input id="titre" name="titre" value="{{ old('titre', $entreprise->TITRE) }}" required class="input"></div>
                        <div><label class="label" for="sigle">Sigle</label><input id="sigle" name="sigle" value="{{ old('sigle', $entreprise->SIGLE) }}" class="input"></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="commune_id">Commune</label>
                            <select id="commune_id" name="commune_id" required class="input">
                                @foreach ($communes as $c)<option value="{{ $c->ID }}" @selected(old('commune_id', $entreprise->COMMUNE_ID) == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach
                            </select>
                        </div>
                        <div><label class="label" for="adresse">Adresse</label><input id="adresse" name="adresse" value="{{ old('adresse', $annuaire?->ADRESSE) }}" class="input"></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (['tel_1' => ['TEL_1', 'Téléphone fixe'], 'tel_2' => ['TEL_2', 'Fixe (2)'], 'cel_1' => ['CEL_1', 'Mobile'], 'cel_2' => ['CEL_2', 'Mobile (2)'], 'whatsapp' => ['WHATSAPP', 'WhatsApp'], 'site' => ['SITE', 'Site web'], 'email_1' => ['EMAIL_1', 'E-mail de contact'], 'email_2' => ['EMAIL_2', 'E-mail (2)']] as $champ => [$col, $label])
                            <div><label class="label" for="{{ $champ }}">{{ $label }}</label><input id="{{ $champ }}" name="{{ $champ }}" value="{{ old($champ, $annuaire?->{$col}) }}" class="input" @if (str_starts_with($champ, 'email')) type="email" @endif></div>
                        @endforeach
                    </div>
                    <div>
                        <label class="label" for="description">Présentation</label>
                        <textarea id="description" name="description" rows="5" class="input">{{ old('description', trim(strip_tags((string) $annuaire?->DESCRIPTION))) }}</textarea>
                    </div>
                </section>
            @endif

            <section class="card space-y-4 p-6">
                <h2 class="font-sans text-base font-semibold">Mon compte</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2"><label class="label" for="name">Nom du compte</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required class="input"></div>
                    <div><label class="label" for="agent_tel">Téléphone de l'agent</label><input id="agent_tel" name="agent_tel" value="{{ old('agent_tel', $user->agent_tel) }}" class="input"></div>
                    <div><label class="label" for="agent_cel">Mobile de l'agent</label><input id="agent_cel" name="agent_cel" value="{{ old('agent_cel', $user->agent_cel) }}" class="input"></div>
                    @foreach (['agent_facebook' => 'Page Facebook', 'agent_instagram' => 'Instagram', 'agent_youtube' => 'Chaîne YouTube', 'agent_twitter' => 'X (Twitter)'] as $champ => $label)
                        <div><label class="label" for="{{ $champ }}">{{ $label }}</label><input id="{{ $champ }}" name="{{ $champ }}" type="url" value="{{ old($champ, $user->{$champ}) }}" class="input" placeholder="https://"></div>
                    @endforeach
                </div>
                <p class="text-sm text-muted">Pour changer d'adresse e-mail de connexion ou de mot de passe : <a href="{{ route('compte') }}" class="link text-ink">paramètres du compte</a>.</p>
            </section>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
            @if ($entreprise)
                <div class="card p-5" x-data="photosPreview()">
                    <p class="text-sm font-semibold">Logo</p>
                    <div class="mt-3 grid h-32 place-items-center overflow-hidden rounded-lg border border-line bg-white p-2">
                        <template x-if="apercus.length"><img :src="apercus[0].url" alt="" class="max-h-full object-contain"></template>
                        <template x-if="! apercus.length">
                            @if ($logo)
                                <img src="{{ $logo }}" alt="Logo actuel" class="max-h-full object-contain">
                            @else
                                <span class="text-sm text-muted">Aucun logo</span>
                            @endif
                        </template>
                    </div>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" @change="choisir($event)" class="mt-3 block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-sand file:px-3 file:py-2 file:text-sm file:font-semibold">
                    <p class="mt-1 text-xs text-muted">Format carré de préférence, 4 Mo maximum. L'ancien logo est remplacé.</p>
                </div>
            @endif
            <button class="btn-roof w-full py-3">Enregistrer</button>
            @if ($entreprise?->LIEN)
                <a href="{{ route('immo.annonceur', $entreprise->LIEN) }}" target="_blank" class="btn-line w-full">Voir ma page publique</a>
            @endif
        </aside>
    </form>
@endsection
