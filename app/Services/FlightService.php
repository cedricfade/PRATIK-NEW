<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Départs / arrivées d'un aéroport via AeroDataBox (api.market).
 * Repris de l'ancien site : deux appels par jour (matin / après-midi), mis en cache 5 minutes.
 */
class FlightService
{
    private const SEGMENTS = [['T00:00', 'T11:59'], ['T12:00', 'T23:59']];

    /** @return array{departures: array, arrivals: array, erreur: bool} */
    public function vols(string $aeroport, string $date): array
    {
        $cle = "vols:{$aeroport}:{$date}";

        if ($enCache = Cache::get($cle)) {
            return $enCache;
        }

        $resultat = ['departures' => [], 'arrivals' => [], 'erreur' => false];
        $config = config('pratik.aerodb');

        if (blank($config['key'] ?? null)) {
            return ['erreur' => true] + $resultat;
        }

        $reussis = 0;

        foreach (self::SEGMENTS as [$debut, $fin]) {
            $url = rtrim($config['url'], '/')."/flights/airports/iata/{$aeroport}/{$date}{$debut}/{$date}{$fin}";

            try {
                $reponse = Http::timeout(10)
                    ->retry(2, 2000, fn ($e) => $e instanceof RequestException && $e->response->status() === 429, throw: false)
                    ->withHeaders(['Accept' => 'application/json', 'x-magicapi-key' => $config['key']])
                    ->get($url, [
                        'withLeg' => 'true',
                        'withCancelled' => 'true',
                        'withCodeshared' => 'true',
                        'withCargo' => 'false',
                        'withPrivate' => 'false',
                    ]);
            } catch (\Throwable $e) {
                Log::warning('AeroDataBox injoignable : '.$e->getMessage());

                continue;
            }

            if (! $reponse->successful() || ! is_array($reponse->json())) {
                Log::warning('AeroDataBox HTTP '.$reponse->status()." pour {$aeroport} {$date}{$debut}");

                continue;
            }

            $resultat['departures'] = array_merge($resultat['departures'], $reponse->json('departures') ?? []);
            $resultat['arrivals'] = array_merge($resultat['arrivals'], $reponse->json('arrivals') ?? []);
            $reussis++;
        }

        $tri = fn (string $cote) => fn ($a, $b) => strcmp(
            $a[$cote]['scheduledTime']['utc'] ?? '9999',
            $b[$cote]['scheduledTime']['utc'] ?? '9999',
        );
        usort($resultat['departures'], $tri('departure'));
        usort($resultat['arrivals'], $tri('arrival'));

        $resultat['erreur'] = $reussis === 0;

        // Complet : 5 min. Partiel ou en échec : courte durée, pour ne pas saturer l'API (limite de débit).
        Cache::put($cle, $resultat, match (true) {
            $reussis === count(self::SEGMENTS) => now()->addMinutes(5),
            $reussis > 0 => now()->addMinutes(2),
            default => now()->addMinute(),
        });

        return $resultat;
    }

    /** Ramène un vol brut de l'API à ce que la page affiche. */
    public static function normaliser(array $vol, string $sens): array
    {
        $ici = $vol[$sens === 'depart' ? 'departure' : 'arrival'] ?? [];
        $labas = $vol[$sens === 'depart' ? 'arrival' : 'departure'] ?? [];

        $prevu = $ici['scheduledTime']['local'] ?? $ici['scheduledTime']['utc'] ?? null;
        $revise = $ici['revisedTime']['local'] ?? $ici['revisedTime']['utc'] ?? null;
        $heurePrevue = $prevu ? substr((string) $prevu, 11, 5) : null;
        $heureRevisee = $revise ? substr((string) $revise, 11, 5) : null;

        $statut = strtolower((string) ($vol['status'] ?? ''));
        $cle = match (true) {
            str_contains($statut, 'cancel') => 'annule',
            str_contains($statut, 'divert') => 'deroute',
            str_contains($statut, 'delay') => 'retarde',
            str_contains($statut, 'boarding'), str_contains($statut, 'gateclosed'), str_contains($statut, 'gate closed') => 'embarquement',
            str_contains($statut, 'arrived') => 'arrive',
            str_contains($statut, 'departed') => 'decolle',
            str_contains($statut, 'inflight'), str_contains($statut, 'in flight'), str_contains($statut, 'enroute') => 'en-vol',
            default => 'prevu',
        };

        return [
            'numero' => $vol['number'] ?? '—',
            'compagnie' => $vol['airline']['name'] ?? '—',
            'compagnie_iata' => $vol['airline']['iata'] ?? null,
            'heure' => $heurePrevue ?? '—',
            'heure_revisee' => $heureRevisee && $heureRevisee !== $heurePrevue ? $heureRevisee : null,
            'en_retard' => $heureRevisee && $heurePrevue && $heureRevisee > $heurePrevue,
            'ville' => $labas['airport']['municipalityName'] ?? $labas['airport']['shortName'] ?? $labas['airport']['name'] ?? '—',
            'aeroport' => $labas['airport']['name'] ?? null,
            'iata' => $labas['airport']['iata'] ?? null,
            'terminal' => $ici['terminal'] ?? null,
            'porte' => $sens === 'depart' ? ($ici['gate'] ?? null) : ($ici['baggageBelt'] ?? null),
            'statut' => $cle,
        ];
    }
}
