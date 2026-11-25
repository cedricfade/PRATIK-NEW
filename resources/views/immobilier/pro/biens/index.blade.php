@extends('layouts.immo')

@section('title', 'Mes annonces')
@section('noindex', true)

@section('content')
    <x-immo.pro-nav />

    <div class="container-page py-8">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            @foreach ([null => 'Toutes', 'publies' => 'En ligne', 'attente' => 'En attente'] as $s => $label)
                <a href="{{ route('immo.pro.biens.index', array_filter(['statut' => $s])) }}" @class(['rounded-full border px-3.5 py-1.5 font-medium', 'border-ink bg-ink text-white' => $statut === ($s ?: null), 'border-line bg-white text-ink/75 hover:border-ink/30' => $statut !== ($s ?: null)])>{{ $label }}</a>
            @endforeach
            <span class="ml-auto text-muted">{{ $biens->total() }} {{ \Illuminate\Support\Str::plural('annonce', $biens->total()) }}</span>
        </div>

        @if ($biens->isEmpty())
            <x-empty icon="home" title="Aucune annonce ici" class="mt-6">
                <a href="{{ route('immo.pro.biens.create') }}" class="btn-roof mt-3">Déposer une annonce</a>
            </x-empty>
        @else
            <div class="card mt-5 overflow-x-auto">
                <table class="table-admin min-w-[760px]">
                    <thead>
                        <tr><th>Annonce</th><th>Prix</th><th>Statut</th><th class="text-right">Vues</th><th class="text-right">Photos</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($biens as $bien)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $bien->image_url }}" alt="" loading="lazy" class="size-12 shrink-0 rounded bg-sand object-cover">
                                        <div class="min-w-0">
                                            <p class="line-clamp-1 font-medium">{{ $bien->entreprise?->TITRE }}</p>
                                            <p class="text-xs text-muted">{{ $bien->est_location ? 'Location' : 'Vente' }} · {{ $bien->categorie?->DESIGNATION }} · {{ $bien->entreprise?->commune?->DESIGNATION }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">{{ $bien->prix_affiche }}</td>
                                <td><x-immo.statut :validation="$bien->VALIDATION" /></td>
                                <td class="text-right">{{ number_format((int) $bien->entreprise?->vues_affichees, 0, ',', ' ') }}</td>
                                <td class="text-right">{{ $bien->photos_count + 1 }}</td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        @if ($bien->entreprise?->LIEN)
                                            <a href="{{ route('immo.bien', $bien->entreprise->LIEN) }}" target="_blank" class="btn-ghost btn-sm" title="Voir l'annonce"><x-icon name="eye" class="size-4" /></a>
                                        @endif
                                        <a href="{{ route('immo.pro.biens.edit', $bien->ID) }}" class="btn-ghost btn-sm" title="Modifier"><x-icon name="pencil" class="size-4" /></a>
                                        <form method="POST" action="{{ route('immo.pro.biens.destroy', $bien->ID) }}" onsubmit="return confirm('Supprimer définitivement cette annonce et toutes ses photos ?')">
                                            @csrf @method('DELETE')
                                            <button class="btn-ghost btn-sm text-danger" title="Supprimer"><x-icon name="trash" class="size-4" /></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $biens->links() }}
        @endif
    </div>
@endsection
