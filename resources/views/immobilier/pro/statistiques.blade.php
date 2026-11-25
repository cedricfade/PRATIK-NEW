@extends('layouts.immo')

@section('title', 'Mes statistiques')
@section('noindex', true)

@section('content')
    <x-immo.pro-nav />

    <div class="container-page py-8">
        {{-- Choix de la période --}}
        <form method="GET" class="card flex flex-wrap items-end gap-3 p-4">
            <div>
                <label for="mois" class="label">Mois</label>
                <select id="mois" name="mois" class="input w-44">
                    <option value="0">Toute l'année</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($moisChoisi === $m)>{{ ucfirst(\Carbon\Carbon::create(2000, $m, 1)->locale('fr')->isoFormat('MMMM')) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="annee" class="label">Année</label>
                <select id="annee" name="annee" class="input w-32">
                    @foreach ($annees as $a)
                        <option value="{{ $a }}" @selected($annee === $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-roof">Afficher</button>
            <p class="ml-auto text-sm text-muted">Période affichée : <strong class="text-ink first-letter:uppercase">{{ $libellePeriode }}</strong></p>
        </form>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            @foreach (['vues' => ['Consultations', 'bg-ink'], 'appels' => ['Clics « appeler »', 'bg-roof'], 'emails' => ['Clics « e-mail »', 'bg-garde']] as $cle => [$label, $couleur])
                <div class="card p-5">
                    <p class="flex items-center gap-2 text-sm text-muted"><span class="size-2.5 rounded-sm {{ $couleur }}"></span> {{ $label }}</p>
                    <p class="mt-2 font-serif text-3xl font-semibold">{{ number_format($totaux[$cle], 0, ',', ' ') }}</p>
                    <p class="text-xs text-muted first-letter:uppercase">{{ $libellePeriode }}</p>
                </div>
            @endforeach
        </div>

        <section class="card mt-6 p-6">
            <h2 class="font-sans text-base font-semibold">Mois par mois — {{ $annee }}</h2>
            <div class="mt-6 overflow-x-auto">
                <div class="flex h-56 min-w-[560px] items-end gap-3 border-b border-line">
                    @foreach ($mois as $m)
                        <a href="?annee={{ $annee }}&mois={{ $m['numero'] }}" @class(['flex h-full flex-1 flex-col justify-end rounded-t', 'bg-roof/10' => $moisChoisi === $m['numero']])
                           title="{{ ucfirst($m['complet']) }} : {{ $m['vues'] }} consultations, {{ $m['appels'] }} appels, {{ $m['emails'] }} e-mails">
                            <div class="flex h-full items-end justify-center gap-0.5 px-1">
                                <span class="w-1/3 rounded-t-sm bg-ink" style="height: {{ round($m['vues'] / $max * 100, 1) }}%"></span>
                                <span class="w-1/3 rounded-t-sm bg-roof" style="height: {{ round($m['appels'] / $max * 100, 1) }}%"></span>
                                <span class="w-1/3 rounded-t-sm bg-garde" style="height: {{ round($m['emails'] / $max * 100, 1) }}%"></span>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="flex min-w-[560px] gap-3 pt-2">
                    @foreach ($mois as $m)<p class="flex-1 text-center text-xs text-muted">{{ $m['libelle'] }}</p>@endforeach
                </div>
            </div>

            <table class="table-admin mt-8">
                <thead><tr><th>Mois</th><th class="text-right">Consultations</th><th class="text-right">Appels</th><th class="text-right">E-mails</th></tr></thead>
                <tbody>
                    @foreach (array_reverse($mois) as $m)
                        <tr @class(['bg-roof/10' => $moisChoisi === $m['numero']])>
                            <td>{{ ucfirst($m['complet']) }}</td>
                            <td class="text-right">{{ number_format($m['vues'], 0, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($m['appels'], 0, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($m['emails'], 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="card mt-6">
            <div class="border-b border-line px-5 py-4">
                <h2 class="font-sans text-base font-semibold">Vues par annonce</h2>
                <p class="text-sm text-muted">Nombre total de consultations de chaque annonce depuis sa publication.</p>
            </div>
            @if ($parBien->isEmpty())
                <p class="px-5 py-6 text-sm text-muted">Aucune annonce pour l'instant.</p>
            @else
                <table class="table-admin">
                    <thead><tr><th>Annonce</th><th>Publiée le</th><th>Statut</th><th class="text-right">Vues</th></tr></thead>
                    <tbody>
                        @foreach ($parBien as $b)
                            <tr>
                                <td><a href="{{ route('immo.bien', $b->LIEN) }}" class="line-clamp-1 hover:text-roof-deep">{{ $b->TITRE }}</a></td>
                                <td class="whitespace-nowrap text-muted">{{ $b->DATE_LOG && ! str_starts_with((string) $b->DATE_LOG, '0000') ? \Carbon\Carbon::parse($b->DATE_LOG)->format('d/m/Y') : '—' }}</td>
                                <td><x-immo.statut :validation="$b->VALIDATION" /></td>
                                <td class="text-right font-semibold">{{ number_format((int) $b->COMPTEUR + (int) $b->VUES_BONUS, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <p class="mt-4 text-xs text-muted">Les consultations comptent les visites de vos annonces et de votre page annonceur. Les clics sont comptés quand un visiteur appuie sur vos boutons « appeler », « WhatsApp » ou « e-mail ».</p>
    </div>
@endsection
