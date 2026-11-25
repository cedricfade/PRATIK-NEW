<?php

namespace App\Support;

use App\Models\ParametreSite;

/**
 * Hero (bandeau d'en-tête avec image de fond) des pages d'accueil,
 * entièrement réglable depuis le backoffice : Admin › Apparence.
 *
 * Deux modes d'affichage :
 *  - "photo"    : l'image sert de fond, assombrie par un voile, avec le titre du site par-dessus ;
 *  - "banniere" : l'image est une bannière déjà composée (texte intégré, ex. Noël, CAN…),
 *                 affichée telle quelle, sans titre par-dessus.
 *
 * Dans les textes, {entreprises}, {biens}, {ventes}, {locations} et {pharmacies}
 * sont remplacés par les chiffres réels.
 */
class Hero
{
    public const PAGES = ['accueil' => 'Accueil du site', 'immobilier' => 'Accueil Pratik Immobilier'];

    public const CHAMPS = ['image', 'image_mobile', 'affichage', 'voile', 'cadrage', 'surtitre', 'titre', 'titre_accent', 'texte'];

    public const DEFAUTS = [
        'accueil' => [
            'image' => 'brand/hero-accueil.jpg',
            'affichage' => 'photo',
            'voile' => '60',
            'cadrage' => 'center',
            'surtitre' => "Côte d'Ivoire · {entreprises} entreprises référencées",
            'titre' => 'Le bon contact, la bonne adresse,',
            'titre_accent' => 'sans chercher partout.',
            'texte' => '',
        ],
        'immobilier' => [
            'image' => 'brand/hero-immobilier.jpg',
            'affichage' => 'photo',
            'voile' => '60',
            'cadrage' => 'bottom',
            'surtitre' => "Pratik Immobilier · Côte d'Ivoire",
            'titre' => 'Trouvez où vivre, investir',
            'titre_accent' => 'ou vous installer.',
            'texte' => '{ventes} biens à vendre et {locations} à louer, publiés directement par des agences, des promoteurs et des aménageurs. Vous les contactez sans intermédiaire.',
        ],
    ];

    /** Chiffres affichés sous le hero de l'accueil. */
    public const CHIFFRES_DEFAUTS = [
        'afficher' => '1',
        'visiteurs' => '+10 000',
        'visiteurs_libelle' => 'visiteurs par jour',
    ];

    /**
     * @param  array<string, int|string>  $chiffres  valeurs pour les variables {…}
     * @return array<string, mixed>
     */
    public static function pour(string $page, array $chiffres = []): array
    {
        $defauts = self::DEFAUTS[$page];
        $v = fn (string $champ) => ParametreSite::valeur("hero.$page.$champ", $defauts[$champ] ?? null);

        $remplacements = collect($chiffres)->mapWithKeys(fn ($n, $cle) => [
            '{'.$cle.'}' => is_numeric($n) ? number_format((int) $n, 0, ',', "\u{202F}") : (string) $n,
        ])->all();
        $texte = fn (?string $t) => trim(strtr((string) $t, $remplacements));

        return [
            'image_url' => self::url($v('image'), $defauts['image']),
            'image_mobile_url' => ($m = ParametreSite::valeur("hero.$page.image_mobile")) ? self::url($m) : null,
            'affichage' => $v('affichage') === 'banniere' ? 'banniere' : 'photo',
            'voile' => max(0, min(90, (int) $v('voile'))) / 100,
            'cadrage' => in_array($v('cadrage'), ['top', 'center', 'bottom'], true) ? $v('cadrage') : 'center',
            'surtitre' => $texte($v('surtitre')),
            'titre' => $texte($v('titre')),
            'titre_accent' => $texte($v('titre_accent')),
            'texte' => $texte($v('texte')),
        ];
    }

    /** @return array{afficher: bool, visiteurs: string, visiteurs_libelle: string} */
    public static function chiffres(): array
    {
        $v = fn (string $c) => ParametreSite::valeur("chiffres.$c", self::CHIFFRES_DEFAUTS[$c]);

        return [
            'afficher' => $v('afficher') === '1',
            'visiteurs' => (string) $v('visiteurs'),
            'visiteurs_libelle' => (string) $v('visiteurs_libelle'),
        ];
    }

    /** Image envoyée depuis l'admin (dossier images/Heros) ou image par défaut livrée avec le site. */
    public static function url(?string $valeur, ?string $defaut = null): ?string
    {
        $valeur = $valeur ?: $defaut;

        if (! $valeur) {
            return null;
        }

        return str_starts_with($valeur, 'brand/') ? asset($valeur) : Media::url('heros', $valeur);
    }

    public static function estImageParDefaut(?string $valeur): bool
    {
        return ! $valeur || str_starts_with($valeur, 'brand/');
    }
}
