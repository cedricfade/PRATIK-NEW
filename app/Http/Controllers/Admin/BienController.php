<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\VideCachesPublics;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BienRequest;
use App\Models\CategorieImmobilier;
use App\Models\Commune;
use App\Models\Immobilier;
use App\Models\PhotoImmobilier;
use App\Models\TypeImmobilier;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BienController extends Controller
{
    use VideCachesPublics;

    public function index(Request $request): View
    {
        $statut = $request->query('statut', 'attente');
        $recherche = trim((string) $request->query('q'));

        $base = Immobilier::query()
            ->when($request->filled('type'), fn ($q) => $q->where('immobilier.TYPE_IMMOBILIER_ID', $request->integer('type')))
            ->when($recherche !== '', function ($q) use ($recherche) {
                ctype_digit($recherche)
                    ? $q->whereKey((int) $recherche)
                    : $q->whereHas('entreprise', fn ($e) => $e->where('TITRE', 'like', "%{$recherche}%"));
            });

        $compteurs = [
            'attente' => (clone $base)->where(fn ($q) => $q->where('VALIDATION', '!=', 'OUI')->orWhereNull('VALIDATION'))->count(),
            'valides' => (clone $base)->where('VALIDATION', 'OUI')->count(),
        ];
        $compteurs['tous'] = $compteurs['attente'] + $compteurs['valides'];

        $biens = $base
            ->when($statut === 'attente', fn ($q) => $q->where(fn ($w) => $w->where('VALIDATION', '!=', 'OUI')->orWhereNull('VALIDATION')))
            ->when($statut === 'valides', fn ($q) => $q->where('VALIDATION', 'OUI'))
            ->with([
                'entreprise:ID,TITRE,LIEN,COMMUNE_ID',
                'entreprise.commune:ID,DESIGNATION',
                'categorie:ID,DESIGNATION',
                'type:ID,DESIGNATION',
                'auteur:id,name',
            ])
            ->orderByDesc('immobilier.ID')
            ->paginate(25, ['immobilier.ID', 'ENTREPRISE_ID', 'ILLUSTATION', 'PRIX', 'CATEGORIE_IMMOBILIER_ID', 'TYPE_IMMOBILIER_ID', 'VALIDATION', 'FLASH', 'LUXE', 'UTILISATEUR', 'DATE_LOG'])
            ->withQueryString();

        return view('admin.immobilier.biens', [
            'biens' => $biens,
            'statut' => $statut,
            'compteurs' => $compteurs,
            'recherche' => $recherche,
            'types' => TypeImmobilier::orderBy('ID')->get(['ID', 'DESIGNATION']),
        ]);
    }

    public function edit(Immobilier $bien): View
    {
        $bien->load(['entreprise:ID,TITRE,LIEN,COMMUNE_ID', 'photos', 'auteur:id,name,email']);

        return view('admin.immobilier.bien-form', [
            'bien' => $bien,
            'communes' => Commune::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
            'categories' => CategorieImmobilier::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
            'types' => TypeImmobilier::orderBy('ID')->get(['ID', 'DESIGNATION']),
        ]);
    }

    public function update(BienRequest $request, Immobilier $bien): RedirectResponse
    {
        DB::transaction(function () use ($request, $bien) {
            $bien->entreprise?->update(['TITRE' => $request->input('titre'), 'COMMUNE_ID' => $request->input('commune_id')]);

            $donnees = $request->immobilier();
            if ($request->hasFile('illustration')) {
                $donnees['ILLUSTATION'] = Media::store($request->file('illustration'), 'immobilier', 'ILL');
            }
            $bien->fill($donnees)->save(); // l'ancienne illustration est supprimée par le modèle

            foreach ((array) $request->file('photos', []) as $photo) {
                PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => Media::store($photo, 'immobilier', 'Immo')]);
            }
        });

        $this->viderCacheAccueil();

        return back()->with('success', 'Bien enregistré.');
    }

    public function destroy(Immobilier $bien): RedirectResponse
    {
        $bien->supprimer(); // fiche + illustration + galerie
        $this->viderCacheAccueil();

        return redirect()->route('admin.biens.index', ['statut' => request('statut', 'tous')])->with('success', 'Bien supprimé avec toutes ses photos.');
    }

    public function validation(Immobilier $bien): RedirectResponse
    {
        $bien->update(['VALIDATION' => $bien->VALIDATION === 'OUI' ? 'NON' : 'OUI']);
        $this->viderCacheAccueil();

        return back()->with('success', $bien->VALIDATION === 'OUI' ? 'Bien validé : il est visible sur le site.' : 'Bien retiré du site (en attente).');
    }

    public function drapeau(Immobilier $bien, string $drapeau): RedirectResponse
    {
        $bien->update([$drapeau => strtoupper((string) $bien->{$drapeau}) === 'OUI' ? 'NON' : 'OUI']);
        $this->viderCacheAccueil();

        return back()->with('success', ($drapeau === 'FLASH' ? 'Mise en avant' : 'Label prestige').(strtoupper($bien->{$drapeau}) === 'OUI' ? ' activé(e).' : ' retiré(e).'));
    }

    public function destroyPhoto(Immobilier $bien, PhotoImmobilier $photo): RedirectResponse
    {
        abort_unless((int) $photo->IMMOBILIER_ID === (int) $bien->ID, 404);
        $photo->delete();

        return back()->with('success', 'Photo supprimée du serveur.');
    }
}
