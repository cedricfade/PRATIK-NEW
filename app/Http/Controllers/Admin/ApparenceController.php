<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\VideCachesPublics;
use App\Http\Controllers\Controller;
use App\Models\ParametreSite;
use App\Support\Hero;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin › Apparence : heros (image de fond + textes) des pages d'accueil et chiffres clés. */
class ApparenceController extends Controller
{
    use VideCachesPublics;

    public function index(Request $request): View
    {
        $page = array_key_exists($request->query('page'), Hero::PAGES) ? $request->query('page') : 'accueil';

        $valeurs = [];
        foreach (Hero::CHAMPS as $champ) {
            $valeurs[$champ] = ParametreSite::valeur("hero.$page.$champ", Hero::DEFAUTS[$page][$champ] ?? null);
        }

        return view('admin.apparence.index', [
            'page' => $page,
            'valeurs' => $valeurs,
            'defauts' => Hero::DEFAUTS[$page],
            'imageUrl' => Hero::url($valeurs['image'], Hero::DEFAUTS[$page]['image']),
            'imageMobileUrl' => $valeurs['image_mobile'] ? Hero::url($valeurs['image_mobile']) : null,
            'imageParDefaut' => Hero::estImageParDefaut(ParametreSite::valeur("hero.$page.image")),
            'chiffres' => Hero::chiffres(),
        ]);
    }

    public function update(Request $request, string $page): RedirectResponse
    {
        abort_unless(array_key_exists($page, Hero::PAGES), 404);

        $data = $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'image_mobile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_defaut' => ['nullable', 'boolean'],
            'supprimer_image_mobile' => ['nullable', 'boolean'],
            'affichage' => ['required', Rule::in(['photo', 'banniere'])],
            'voile' => ['required', 'integer', 'between:0,90'],
            'cadrage' => ['required', Rule::in(['top', 'center', 'bottom'])],
            'surtitre' => ['nullable', 'string', 'max:150'],
            'titre' => ['nullable', 'string', 'max:150'],
            'titre_accent' => ['nullable', 'string', 'max:100'],
            'texte' => ['nullable', 'string', 'max:400'],
            'chiffres_afficher' => ['nullable', 'boolean'],
            'chiffres_visiteurs' => ['nullable', 'string', 'max:30'],
            'chiffres_visiteurs_libelle' => ['nullable', 'string', 'max:60'],
        ], [], [
            'image' => 'image de fond',
            'image_mobile' => 'image mobile',
            'titre_accent' => 'mot en couleur',
        ]);

        $cle = fn (string $champ) => "hero.$page.$champ";
        $ancienneImage = ParametreSite::valeur($cle('image'));
        $ancienneMobile = ParametreSite::valeur($cle('image_mobile'));

        $valeurs = [
            $cle('affichage') => $data['affichage'],
            $cle('voile') => (string) $data['voile'],
            $cle('cadrage') => $data['cadrage'],
            $cle('surtitre') => $data['surtitre'] ?? '',
            $cle('titre') => $data['titre'] ?? '',
            $cle('titre_accent') => $data['titre_accent'] ?? '',
            $cle('texte') => $data['texte'] ?? '',
        ];

        // Image principale : nouvelle image, retour à l'image par défaut, ou inchangée.
        if ($request->hasFile('image')) {
            $valeurs[$cle('image')] = Media::store($request->file('image'), 'heros', 'HERO', 2400);
        } elseif ($request->boolean('image_defaut')) {
            $valeurs[$cle('image')] = null;
        }

        if ($request->hasFile('image_mobile')) {
            $valeurs[$cle('image_mobile')] = Media::store($request->file('image_mobile'), 'heros', 'HEROM', 1200);
        } elseif ($request->boolean('supprimer_image_mobile')) {
            $valeurs[$cle('image_mobile')] = null;
        }

        if ($page === 'accueil') {
            $valeurs['chiffres.afficher'] = $request->boolean('chiffres_afficher') ? '1' : '0';
            $valeurs['chiffres.visiteurs'] = $data['chiffres_visiteurs'] ?? '';
            $valeurs['chiffres.visiteurs_libelle'] = $data['chiffres_visiteurs_libelle'] ?? '';
        }

        ParametreSite::enregistrer($valeurs);

        // Les fichiers remplacés ou retirés sont supprimés du disque.
        foreach ([[$ancienneImage, 'image'], [$ancienneMobile, 'image_mobile']] as [$ancien, $champ]) {
            if ($ancien && array_key_exists($cle($champ), $valeurs) && $valeurs[$cle($champ)] !== $ancien && ! Hero::estImageParDefaut($ancien)) {
                Media::delete('heros', $ancien);
            }
        }

        $this->viderCacheAccueil();

        return redirect()->route('admin.apparence.index', ['page' => $page])
            ->with('success', 'L\'en-tête de « '.Hero::PAGES[$page].' » a été mis à jour.');
    }
}
