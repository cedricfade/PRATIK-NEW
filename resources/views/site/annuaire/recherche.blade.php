@extends('layouts.site')

@section('title', $terme ? 'Recherche « '.$terme.' »' : 'Rechercher une entreprise')
@section('noindex', true)

@section('content')
    <section class="border-b border-line bg-sand/60">
        <div class="container-page py-8">
            <h1 class="text-3xl font-semibold">
                @if ($terme)
                    Résultats pour « {{ $terme }} »@if ($commune) <span class="text-ink/60">à {{ $commune->DESIGNATION }}</span>@endif
                @else
                    Rechercher une entreprise
                @endif
            </h1>
            <div class="mt-5 max-w-3xl">@include('site.annuaire._recherche')</div>
        </div>
    </section>

    <div class="container-page py-8">
        @if ($rubriques->isNotEmpty())
            <div class="mb-6 flex flex-wrap items-center gap-2 text-sm">
                <span class="text-muted">Rubriques correspondantes :</span>
                @foreach ($rubriques as $r)
                    <a href="{{ route('annuaire.sous-categorie', [$r->categorie->LIEN, $r->LIEN]) }}" class="rounded-full border border-line bg-white px-3 py-1 hover:border-ink/30">{{ $r->DESIGNATION }}</a>
                @endforeach
            </div>
        @endif

        @if ($entreprises === null)
            <x-empty title="Tapez au moins deux caractères">Par exemple un nom d'entreprise, un sigle ou un métier.</x-empty>
        @elseif ($entreprises->isEmpty())
            <x-empty title="Aucune entreprise trouvée">
                Vérifiez l'orthographe, essayez un mot plus général
                @if ($commune) ou <a href="{{ route('recherche', ['q' => $terme]) }}" class="link text-ink">cherchez dans toute la Côte d'Ivoire</a>@endif.
            </x-empty>
        @else
            <p class="mb-4 text-sm text-muted">{{ number_format($entreprises->total(), 0, ',', ' ') }} {{ $entreprises->total() > 1 ? 'résultats' : 'résultat' }}</p>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($entreprises as $entreprise)
                    <x-entreprise-card :entreprise="$entreprise" />
                @endforeach
            </div>
            {{ $entreprises->links() }}
        @endif
    </div>
@endsection
