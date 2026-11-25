@extends('layouts.site')

@section('title', 'Nous contacter')
@section('description', 'Contactez l\'équipe Pratik : question sur l\'annuaire, correction d\'une fiche, partenariat ou publicité.')

@section('content')
    <x-page-header title="Nous contacter" eyebrow="Pratik" :breadcrumbs="['Accueil' => route('home'), 'Contact' => null]">
        Une fiche à corriger, une question sur la publicité, une suggestion ? Écrivez-nous, nous lisons tous les messages.
    </x-page-header>

    <div class="container-page grid gap-10 py-10 lg:grid-cols-[1fr_340px]">
        <form method="POST" action="{{ route('contact.store') }}" class="card relative grid gap-5 p-6 sm:grid-cols-2 sm:p-8">
            @csrf
            <x-site.anti-spam />
            <div><label class="label" for="nom">Nom</label><input id="nom" name="nom" value="{{ old('nom') }}" required class="input">@error('nom')<p class="input-error">{{ $message }}</p>@enderror</div>
            <div><label class="label" for="telephone">Téléphone <span class="font-normal text-muted">(facultatif)</span></label><input id="telephone" name="telephone" value="{{ old('telephone') }}" class="input"></div>
            <div class="sm:col-span-2"><label class="label" for="email">E-mail</label><input id="email" type="email" name="email" value="{{ old('email') }}" required class="input">@error('email')<p class="input-error">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><label class="label" for="objet">Objet</label><input id="objet" name="objet" value="{{ old('objet') }}" required class="input">@error('objet')<p class="input-error">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><label class="label" for="message">Message</label><textarea id="message" name="message" rows="6" required class="input">{{ old('message') }}</textarea>@error('message')<p class="input-error">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><button class="btn-ink py-3">Envoyer</button></div>
        </form>
        <aside class="card h-fit divide-y divide-line text-sm">
            <div class="p-5">
                <p class="text-muted">Téléphone</p>
                <a href="{{ tel_link(config('pratik.contact.phone_raw')) }}" class="mt-1 block text-base font-semibold">{{ config('pratik.contact.phone') }}</a>
            </div>
            <div class="p-5">
                <p class="text-muted">E-mail</p>
                <a href="mailto:{{ config('pratik.contact.email') }}" class="mt-1 block font-semibold">{{ config('pratik.contact.email') }}</a>
            </div>
            <div class="p-5">
                <p class="text-muted">Adresse</p>
                <p class="mt-1 font-semibold">Cocody Mermoz, en face du CNRA<br>Abidjan, Côte d'Ivoire</p>
            </div>
        </aside>
    </div>
@endsection
