@extends('layouts.immo')

@section('title', 'Contact et ouverture d\'un compte annonceur')
@section('description', 'Contactez Pratik Immobilier : question sur une annonce, ouverture d\'un compte annonceur pour agences, promoteurs et propriétaires.')

@section('content')
    <x-page-header tone="immo" title="Nous contacter" :breadcrumbs="['Immobilier' => route('immo.home'), 'Contact' => null]">
        Une question sur une annonce, un bien à confier, ou l'envie de publier vos biens sur Pratik : écrivez-nous, nous répondons en général dans la journée.
    </x-page-header>

    <div class="container-page grid gap-10 py-10 lg:grid-cols-[1fr_380px]">
        <form method="POST" action="{{ route('immo.contact.store') }}" class="card grid gap-4 p-6 sm:grid-cols-2 sm:p-8">
            @csrf
            <div class="hidden" aria-hidden="true"><label>Site web <input name="site_web" tabindex="-1" autocomplete="off"></label></div>
            <div class="sm:col-span-2">
                <label class="label" for="nom">Nom et prénom, ou société</label>
                <input id="nom" name="nom" value="{{ old('nom') }}" required class="input">
                @error('nom')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="telephone">Téléphone</label>
                <input id="telephone" name="telephone" value="{{ old('telephone') }}" required inputmode="tel" class="input">
                @error('telephone')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input">
                @error('email')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="objet">Objet</label>
                <select id="objet" name="objet" class="input" required>
                    @foreach (['Je souhaite publier mes biens (compte annonceur)', 'Question sur une annonce', 'Je cherche un bien', 'Signaler une annonce', 'Autre demande'] as $o)
                        <option @selected(old('objet') === $o)>{{ $o }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="message">Message</label>
                <textarea id="message" name="message" rows="6" required class="input">{{ old('message') }}</textarea>
                @error('message')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <button class="btn-roof px-6">Envoyer</button>
            </div>
        </form>

        <aside class="space-y-5">
            <div class="card p-6">
                <p class="font-semibold">Pratik Immobilier</p>
                <ul class="mt-4 space-y-3 text-sm">
                    <li><a href="{{ tel_link(config('pratik.contact.phone_raw')) }}" class="flex items-center gap-2 hover:text-roof-deep"><x-icon name="phone" class="size-4" /> {{ config('pratik.contact.phone') }}</a></li>
                    <li><a href="mailto:{{ config('pratik.contact.email') }}" class="flex items-center gap-2 hover:text-roof-deep"><x-icon name="mail" class="size-4" /> {{ config('pratik.contact.email') }}</a></li>
                    <li class="flex items-center gap-2"><x-icon name="map-pin" class="size-4" /> {{ config('pratik.contact.address') }}</li>
                </ul>
            </div>
            <div class="rounded-xl border border-roof/40 bg-roof/10 p-6 text-sm">
                <p class="font-semibold text-ink">Pour les professionnels</p>
                <p class="mt-2 text-ink/75">Agences, promoteurs, aménageurs : nous créons votre espace annonceur. Vous y publiez vos biens, suivez les consultations et répondez aux demandes.</p>
                @guest
                    <p class="mt-3">Déjà annonceur ? <a href="{{ route('immo.login') }}" class="link text-ink">Se connecter</a></p>
                @endguest
            </div>
        </aside>
    </div>
@endsection
