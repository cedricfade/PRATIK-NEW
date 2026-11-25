@extends('layouts.site')

@section('title', 'Inscrire mon entreprise dans l\'annuaire')
@section('description', 'Inscrivez votre entreprise dans l\'annuaire Pratik : fiche gratuite ou mise en avant avec logo, photos et statistiques de consultation.')

@section('content')
    <x-page-header title="Inscrire mon entreprise" eyebrow="Professionnels" :breadcrumbs="['Accueil' => route('home'), 'Inscrire mon entreprise' => null]">
        Laissez-nous vos coordonnées : un conseiller vous rappelle pour créer ou mettre à jour votre fiche.
    </x-page-header>

    <div class="container-page grid gap-10 py-10 lg:grid-cols-[1fr_380px]">
        <form method="POST" action="{{ route('souscription.store') }}" class="card relative grid gap-5 p-6 sm:grid-cols-2 sm:p-8">
            @csrf
            <x-site.anti-spam />
            <div class="sm:col-span-2">
                <label class="label" for="entreprise">Nom de l'entreprise</label>
                <input id="entreprise" name="entreprise" value="{{ old('entreprise') }}" required class="input">
                @error('entreprise')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="activite">Activité</label>
                <input id="activite" name="activite" value="{{ old('activite') }}" required placeholder="ex. cabinet comptable, quincaillerie, clinique…" class="input">
                @error('activite')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="commune">Commune</label>
                <select id="commune" name="commune" class="input">
                    <option value="">—</option>
                    @foreach ($communes as $c)
                        <option value="{{ $c->DESIGNATION }}" @selected(old('commune') === $c->DESIGNATION)>{{ $c->DESIGNATION }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="nom">Votre nom</label>
                <input id="nom" name="nom" value="{{ old('nom') }}" required class="input">
                @error('nom')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="telephone">Téléphone</label>
                <input id="telephone" name="telephone" value="{{ old('telephone') }}" required class="input" placeholder="07 00 00 00 00">
                @error('telephone')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input">
                @error('email')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <fieldset class="sm:col-span-2">
                <legend class="label">Formule envisagée</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer gap-3 rounded-lg border border-line p-4 has-[:checked]:border-ink has-[:checked]:bg-paper">
                        <input type="radio" name="formule" value="gratuite" @checked(old('formule', 'gratuite') === 'gratuite') class="mt-1 text-ink focus:ring-amber">
                        <span><span class="block font-medium">Fiche gratuite</span><span class="text-sm text-muted">Nom, téléphone, adresse et rubrique.</span></span>
                    </label>
                    <label class="flex cursor-pointer gap-3 rounded-lg border border-line p-4 has-[:checked]:border-ink has-[:checked]:bg-paper">
                        <input type="radio" name="formule" value="pro" @checked(old('formule') === 'pro') class="mt-1 text-ink focus:ring-amber">
                        <span><span class="block font-medium">Fiche Pro</span><span class="text-sm text-muted">Logo, photos, mise en tête de rubrique, statistiques.</span></span>
                    </label>
                </div>
            </fieldset>
            <div class="sm:col-span-2">
                <label class="label" for="message">Précisions <span class="font-normal text-muted">(facultatif)</span></label>
                <textarea id="message" name="message" rows="4" class="input">{{ old('message') }}</textarea>
            </div>
            <div class="sm:col-span-2"><button class="btn-ink py-3">Envoyer ma demande</button></div>
        </form>

        <aside class="space-y-6">
            <div class="rounded-xl bg-ink p-6 text-white/80">
                <p class="font-serif text-xl text-white">Ce que comprend la fiche Pro</p>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach (['Votre logo dans les résultats et en tête de votre rubrique', 'Galerie photos, description détaillée et plan d\'accès', 'Boutons « appeler », « WhatsApp » et « écrire » mis en avant', 'Statistiques : vues de la fiche, appels et e-mails reçus', 'Présence sur l\'application mobile Pratik'] as $point)
                        <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 shrink-0 text-amber" /> {{ $point }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="card p-6 text-sm">
                <p class="font-semibold">Vous préférez nous appeler ?</p>
                <a href="{{ tel_link(config('pratik.contact.phone_raw')) }}" class="mt-2 inline-flex items-center gap-2 text-lg font-semibold"><x-icon name="phone" class="size-5" /> {{ config('pratik.contact.phone') }}</a>
                <p class="mt-1 text-muted">Du lundi au vendredi, de 8 h à 17 h.</p>
            </div>
        </aside>
    </div>
@endsection
