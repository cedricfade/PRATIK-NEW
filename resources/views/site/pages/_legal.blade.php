{{-- Mise en page commune aux pages légales. Variables : $titre, $sections (titre => html) , $intro --}}
<x-page-header :title="$titre" eyebrow="Informations légales" :breadcrumbs="['Accueil' => route('home'), $titre => null]">
    En vigueur depuis le 1er octobre 2023 · dernière mise à jour le 11 octobre 2023
</x-page-header>
<div class="container-page grid gap-10 py-10 lg:grid-cols-[220px_1fr]">
    <nav class="hidden lg:block" aria-label="Sommaire">
        <ol class="sticky top-28 space-y-2 text-sm text-muted">
            @foreach (array_keys($sections) as $i => $t)
                <li><a href="#s{{ $i + 1 }}" class="hover:text-ink">{{ $t }}</a></li>
            @endforeach
        </ol>
    </nav>
    <article class="prose-pratik max-w-3xl">
        <p class="text-lg text-ink">{{ $intro }}</p>
        @foreach ($sections as $t => $html)
            <h2 id="s{{ $loop->iteration }}" class="mt-10 mb-3 scroll-mt-28 text-xl font-semibold">{{ $loop->iteration }}. {{ $t }}</h2>
            {!! $html !!}
        @endforeach
        <div class="mt-12 rounded-lg bg-sand/70 p-5 text-sm not-prose">
            <p class="font-semibold">SIEC — Pratik</p>
            <p class="mt-1">Cocody Mermoz, en face du CNRA, Abidjan · {{ config('pratik.contact.phone') }} · <a href="mailto:{{ config('pratik.contact.email') }}">{{ config('pratik.contact.email') }}</a></p>
        </div>
    </article>
</div>
