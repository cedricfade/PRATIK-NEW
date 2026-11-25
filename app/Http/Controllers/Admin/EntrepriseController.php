<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\VideCachesPublics;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EntrepriseRequest;
use App\Models\Annuaire;
use App\Models\CategorieAnnuaire;
use App\Models\Commune;
use App\Models\Departement;
use App\Models\Entreprise;
use App\Models\Nature;
use App\Models\PhotoAnnuaire;
use App\Models\SousCategorie;
use App\Models\ThematiqueAnnuaire;
use App\Models\TypeEntreprise;
use App\Models\User;
use App\Support\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EntrepriseController extends Controller
{
    use VideCachesPublics;

    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q'));
        $type = $request->query('type', (string) config('pratik.types.annuaire'));

        $entreprises = Entreprise::query()
            ->when($type !== 'tous', fn ($q) => $q->where('entreprises.TYPE_ID', (int) $type))
            ->when($request->filled('commune'), fn ($q) => $q->where('entreprises.COMMUNE_ID', $request->integer('commune')))
            ->when($request->query('client') === 'OUI', fn ($q) => $q->whereHas('annuaire', fn ($a) => $a->where('CLIENT', 'OUI')))
            ->when($request->boolean('vip'), fn ($q) => $q->whereHas('annuaire', fn ($a) => $a->where('VIP', 1)))
            ->when($recherche !== '', function ($q) use ($recherche) {
                ctype_digit($recherche) ? $q->whereKey((int) $recherche) : $q->recherche($recherche);
            })
            ->with([
                'annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1,CLIENT,VIP,TEL_1,CEL_1',
                'commune:ID,DESIGNATION',
                'type:ID,DESIGNATION',
            ])
            ->orderByDesc('entreprises.ID')
            ->paginate(25, ['entreprises.ID', 'TITRE', 'SIGLE', 'LIEN', 'TYPE_ID', 'COMMUNE_ID', 'COMPTEUR', 'VUES_BONUS', 'ACTIVATION', 'DATE_LOG'])
            ->withQueryString();

        return view('admin.entreprises.index', [
            'entreprises' => $entreprises,
            'recherche' => $recherche,
            'type' => $type,
            'types' => TypeEntreprise::orderBy('ID')->get(['ID', 'DESIGNATION']),
            'communes' => Commune::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
        ]);
    }

    public function create(Request $request): View
    {
        $entreprise = new Entreprise(['TYPE_ID' => config('pratik.types.annuaire'), 'ACTIVATION' => 1, 'PRIORITE' => 0]);
        $annuaire = new Annuaire(['CLIENT' => 'NON', 'VIP' => 0, 'TOP10' => 0]);

        return view('admin.entreprises.form', $this->donneesFormulaire($entreprise, $annuaire) + [
            'pourPharmacie' => $request->boolean('pharmacie'),
        ]);
    }

    public function store(EntrepriseRequest $request): RedirectResponse
    {
        $entreprise = DB::transaction(function () use ($request) {
            $entreprise = Entreprise::create($request->entreprise() + [
                'TYPE' => 1,
                'COMPTEUR' => 0,
                'DATE_LOG' => now()->toDateString(),
                'UTILISATEUR' => $request->user()->id,
                'LIEN' => 'tmp-'.uniqid(),
            ]);

            $entreprise->update(['LIEN' => $request->filled('lien') ? $request->input('lien') : Entreprise::lienUnique($entreprise->TITRE, $entreprise->ID)]);

            $donnees = $request->annuaire() + ['ENTREPRISE_ID' => $entreprise->ID];
            if ($request->hasFile('logo')) {
                $donnees['LOGO'] = Media::store($request->file('logo'), 'logos', '', 800);
            }
            Annuaire::create($donnees);

            if ($request->boolean('ajouter_pharmacie')) {
                $sousCategorie = SousCategorie::find(config('pratik.pharmacie_sous_categorie'));
                ThematiqueAnnuaire::create([
                    'ENTREPRISE_ID' => $entreprise->ID,
                    'CATEGORIE_ID' => $sousCategorie?->CATEGORIE_ID,
                    'SOUS_CATEGORIE_ID' => config('pratik.pharmacie_sous_categorie'),
                ]);
            }

            return $entreprise;
        });

        $this->viderCacheAccueil();

        return redirect()->route('admin.entreprises.edit', [$entreprise->ID, 'onglet' => 'thematiques'])
            ->with('success', 'Entreprise créée. Rattachez-la maintenant à ses rubriques de l\'annuaire.');
    }

    public function edit(Entreprise $entreprise): View
    {
        $annuaire = $entreprise->annuaire()->first() ?? new Annuaire(['CLIENT' => 'NON', 'VIP' => 0, 'TOP10' => 0]);

        return view('admin.entreprises.form', $this->donneesFormulaire($entreprise, $annuaire) + ['pourPharmacie' => false]);
    }

    public function update(EntrepriseRequest $request, Entreprise $entreprise): RedirectResponse
    {
        DB::transaction(function () use ($request, $entreprise) {
            $entreprise->update($request->entreprise() + [
                'LIEN' => $request->filled('lien') ? $request->input('lien') : ($entreprise->LIEN ?: Entreprise::lienUnique($request->input('titre'), $entreprise->ID)),
            ]);

            $annuaire = $entreprise->annuaire()->first() ?? new Annuaire(['ENTREPRISE_ID' => $entreprise->ID]);
            $donnees = $request->annuaire();

            if ($request->hasFile('logo')) {
                $donnees['LOGO'] = Media::store($request->file('logo'), 'logos', '', 800);
            } elseif ($request->boolean('supprimer_logo')) {
                $donnees['LOGO'] = null;
                $donnees['LOGO1'] = null;
            }

            // update() via le modèle : l'ancien logo est supprimé du disque s'il change.
            $annuaire->fill($donnees)->save();
        });

        $this->viderCacheAccueil();

        return back()->with('success', 'Fiche enregistrée.');
    }

    public function destroy(Entreprise $entreprise): RedirectResponse
    {
        $titre = $entreprise->TITRE;

        DB::transaction(function () use ($entreprise) {
            User::where('entreprise_id', $entreprise->ID)->update(['entreprise_id' => null]);
            $entreprise->delete(); // supprime fiche annuaire, logo, galerie, thématiques, gardes…
        });

        $this->viderCacheAccueil();
        $this->viderCacheGardes();

        return redirect()->route('admin.entreprises.index')->with('success', "« {$titre} » a été supprimée, ainsi que ses images.");
    }

    /** Recherche rapide (annonceurs, siège…). */
    public function lookup(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q'));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $resultats = Entreprise::fichesAnnuaire()
            ->when(ctype_digit($q), fn ($r) => $r->whereKey((int) $q), fn ($r) => $r->recherche($q))
            ->with('commune:ID,DESIGNATION')
            ->limit(15)
            ->get(['ID', 'TITRE', 'SIGLE', 'COMMUNE_ID'])
            ->map(fn ($e) => ['id' => $e->ID, 'titre' => $e->TITRE, 'sigle' => $e->SIGLE, 'commune' => $e->commune?->DESIGNATION]);

        return response()->json($resultats);
    }

    public function storePhotos(Request $request, Entreprise $entreprise): RedirectResponse
    {
        $request->validate(['photos' => ['required', 'array', 'max:20'], 'photos.*' => ['image', 'max:6144']]);

        $annuaire = $entreprise->annuaire()->firstOrCreate(['ENTREPRISE_ID' => $entreprise->ID], ['CLIENT' => 'NON']);

        foreach ($request->file('photos') as $photo) {
            PhotoAnnuaire::create(['ANNUAIRE_ID' => $annuaire->ID, 'IMAGE' => Media::store($photo, 'galerie', 'GAL')]);
        }

        return redirect()->route('admin.entreprises.edit', [$entreprise->ID, 'onglet' => 'galerie'])->with('success', count($request->file('photos')).' photo(s) ajoutée(s).');
    }

    public function destroyPhoto(Entreprise $entreprise, PhotoAnnuaire $photo): RedirectResponse
    {
        abort_unless((int) $photo->ANNUAIRE_ID === (int) $entreprise->annuaire()->value('ID'), 404);
        $photo->delete(); // le fichier est supprimé par le modèle

        return redirect()->route('admin.entreprises.edit', [$entreprise->ID, 'onglet' => 'galerie'])->with('success', 'Photo supprimée.');
    }

    public function storeThematique(Request $request, Entreprise $entreprise): RedirectResponse
    {
        $data = $request->validate(['sous_categorie_id' => ['required', 'integer', 'exists:sous_categorie,ID']]);
        $sous = SousCategorie::findOrFail($data['sous_categorie_id']);

        ThematiqueAnnuaire::firstOrCreate([
            'ENTREPRISE_ID' => $entreprise->ID,
            'SOUS_CATEGORIE_ID' => $sous->ID,
        ], ['CATEGORIE_ID' => $sous->CATEGORIE_ID]);

        return redirect()->route('admin.entreprises.edit', [$entreprise->ID, 'onglet' => 'thematiques'])->with('success', "Rubrique « {$sous->DESIGNATION} » ajoutée.");
    }

    public function destroyThematique(Entreprise $entreprise, ThematiqueAnnuaire $thematique): RedirectResponse
    {
        abort_unless((int) $thematique->ENTREPRISE_ID === (int) $entreprise->ID, 404);
        $thematique->delete();

        return redirect()->route('admin.entreprises.edit', [$entreprise->ID, 'onglet' => 'thematiques'])->with('success', 'Rubrique retirée.');
    }

    public function storeDepartement(Request $request, Entreprise $entreprise): RedirectResponse
    {
        $data = $request->validate([
            'libelle' => ['required', 'string', 'max:250'],
            'tel' => ['nullable', 'string', 'max:50'],
            'cel' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
        ]);

        $annuaire = $entreprise->annuaire()->firstOrCreate(['ENTREPRISE_ID' => $entreprise->ID], ['CLIENT' => 'NON']);
        Departement::create(['ANNUAIRE_ID' => $annuaire->ID, 'LIBELLE' => $data['libelle'], 'TEL' => $data['tel'], 'CEL' => $data['cel'], 'EMAIL' => $data['email']]);

        return redirect()->route('admin.entreprises.edit', [$entreprise->ID, 'onglet' => 'departements'])->with('success', 'Service ajouté.');
    }

    public function destroyDepartement(Entreprise $entreprise, Departement $departement): RedirectResponse
    {
        abort_unless((int) $departement->ANNUAIRE_ID === (int) $entreprise->annuaire()->value('ID'), 404);
        $departement->delete();

        return redirect()->route('admin.entreprises.edit', [$entreprise->ID, 'onglet' => 'departements'])->with('success', 'Service supprimé.');
    }

    private function donneesFormulaire(Entreprise $entreprise, Annuaire $annuaire): array
    {
        $existe = $entreprise->exists;

        return [
            'entreprise' => $entreprise,
            'annuaire' => $annuaire,
            'types' => TypeEntreprise::orderBy('ID')->get(['ID', 'DESIGNATION']),
            'communes' => Commune::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
            'natures' => Nature::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
            'categories' => CategorieAnnuaire::orderBy('DESIGNATION')->with(['sousCategories' => fn ($q) => $q->orderBy('DESIGNATION')->select(['ID', 'CATEGORIE_ID', 'DESIGNATION'])])->get(['ID', 'DESIGNATION']),
            'siege' => $annuaire->SIEGE_ID ? Entreprise::find($annuaire->SIEGE_ID, ['ID', 'TITRE']) : null,
            'thematiques' => $existe
                ? ThematiqueAnnuaire::where('ENTREPRISE_ID', $entreprise->ID)->with(['categorie:ID,DESIGNATION', 'sousCategorie:ID,DESIGNATION'])->get()
                : collect(),
            'photos' => $annuaire->exists ? $annuaire->photos()->get() : collect(),
            'departements' => $annuaire->exists ? $annuaire->departements()->get() : collect(),
        ];
    }
}
