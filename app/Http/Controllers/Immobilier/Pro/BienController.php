<?php

namespace App\Http\Controllers\Immobilier\Pro;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Immobilier\HomeController;
use App\Http\Controllers\Immobilier\Support\Annonceurs;
use App\Http\Requests\Immobilier\BienRequest;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\PhotoImmobilier;
use App\Models\TypeImmobilier;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Les biens d'un annonceur : liste, dépôt, modification, suppression. */
class BienController extends Controller
{
    public function index(Request $request): View
    {
        $statut = in_array($request->query('statut'), ['publies', 'attente'], true) ? $request->query('statut') : null;

        $biens = Immobilier::query()
            ->where('UTILISATEUR', $request->user()->id)
            ->when($statut === 'publies', fn ($q) => $q->where('VALIDATION', 'OUI'))
            ->when($statut === 'attente', fn ($q) => $q->where(fn ($q) => $q->where('VALIDATION', '!=', 'OUI')->orWhereNull('VALIDATION')))
            ->with(['entreprise:ID,TITRE,LIEN,COMMUNE_ID,COMPTEUR,VUES_BONUS', 'entreprise.commune:ID,DESIGNATION', 'categorie:ID,DESIGNATION'])
            ->withCount('photos')
            ->orderByDesc('ID')
            ->paginate(15)
            ->withQueryString();

        return view('immobilier.pro.biens.index', compact('biens', 'statut'));
    }

    public function create(): View
    {
        return view('immobilier.pro.biens.form', $this->donneesFormulaire(new Immobilier(['TYPE_IMMOBILIER_ID' => 2])));
    }

    public function store(BienRequest $request): RedirectResponse
    {
        $user = $request->user();
        $fichiers = [];

        try {
            $bien = DB::transaction(function () use ($request, $user, &$fichiers) {
                $fiche = Entreprise::create([
                    'COMMUNE_ID' => (int) $request->validated('commune_id'),
                    'TYPE_ID' => config('pratik.types.immobilier'),
                    'TITRE' => $request->validated('titre'),
                    'LIEN' => 'bien-'.uniqid(),
                    'COMPTEUR' => 0,
                    'DATE_LOG' => now()->toDateString(),
                    'UTILISATEUR' => $user->id,
                    'ACTIVATION' => 1,
                ]);
                $fiche->update(['LIEN' => Entreprise::lienUnique($request->validated('titre'), $fiche->ID)]);

                $illustration = Immobilier::IMAGE_DEFAUT;
                if ($request->hasFile('illustration')) {
                    $illustration = $fichiers[] = Media::store($request->file('illustration'), 'immobilier', 'ILL');
                }

                $bien = Immobilier::create($request->colonnesImmobilier() + [
                    'ENTREPRISE_ID' => $fiche->ID,
                    'ILLUSTATION' => $illustration,
                    'VALIDATION' => 'NON',
                    'DATE_LOG' => now()->toDateString(),
                    'UTILISATEUR' => $user->id,
                ]);

                foreach ($request->file('photos', []) as $photo) {
                    $nom = $fichiers[] = Media::store($photo, 'immobilier', 'Immo');
                    PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => $nom]);
                }

                return $bien;
            });
        } catch (\Throwable $e) {
            foreach ($fichiers as $nom) {
                Media::delete('immobilier', $nom);
            }
            throw $e;
        }

        $this->viderCaches();

        return redirect()->route('immo.pro.biens.edit', $bien->ID)
            ->with('success', 'Votre annonce est enregistrée. Elle sera visible sur le site dès sa validation par l\'équipe Pratik.');
    }

    public function edit(Request $request, int $id): View
    {
        $bien = $this->bienDe($request, $id);
        $bien->load(['entreprise:ID,TITRE,LIEN,COMMUNE_ID', 'photos:ID,IMMOBILIER_ID,IMAGE']);

        return view('immobilier.pro.biens.form', $this->donneesFormulaire($bien));
    }

    public function update(BienRequest $request, int $id): RedirectResponse
    {
        $bien = $this->bienDe($request, $id);
        $nouveaux = [];

        try {
            DB::transaction(function () use ($request, $bien, &$nouveaux) {
                // Le lien (URL) ne change pas : il est déjà référencé par Google.
                Entreprise::whereKey($bien->ENTREPRISE_ID)->update([
                    'TITRE' => $request->validated('titre'),
                    'COMMUNE_ID' => (int) $request->validated('commune_id'),
                ]);

                $colonnes = $request->colonnesImmobilier();
                if ($request->hasFile('illustration')) {
                    // L'ancienne photo principale est supprimée par le modèle (événement "updated").
                    $colonnes['ILLUSTATION'] = $nouveaux[] = Media::store($request->file('illustration'), 'immobilier', 'ILL');
                }
                $bien->update($colonnes);

                foreach ($request->file('photos', []) as $photo) {
                    $nom = $nouveaux[] = Media::store($photo, 'immobilier', 'Immo');
                    PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => $nom]);
                }
            });
        } catch (\Throwable $e) {
            foreach ($nouveaux as $nom) {
                Media::delete('immobilier', $nom);
            }
            throw $e;
        }

        $this->viderCaches();

        return redirect()->route('immo.pro.biens.edit', $bien->ID)->with('success', 'Modifications enregistrées.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        // Supprime la fiche, la photo principale et toute la galerie (fichiers compris).
        $this->bienDe($request, $id)->supprimer();
        $this->viderCaches();

        return redirect()->route('immo.pro.biens.index')->with('success', 'L\'annonce et ses photos ont été supprimées.');
    }

    public function destroyPhoto(Request $request, int $id, int $photo): RedirectResponse
    {
        $bien = $this->bienDe($request, $id);

        PhotoImmobilier::where('IMMOBILIER_ID', $bien->ID)->whereKey($photo)->firstOrFail()->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    /** Charge un bien en vérifiant qu'il appartient à l'annonceur connecté (les admins passent). */
    private function bienDe(Request $request, int $id): Immobilier
    {
        $bien = Immobilier::whereKey($id)->firstOrFail();
        $user = $request->user();

        abort_unless((int) $bien->UTILISATEUR === (int) $user->id || $user->estAdmin(), 403, 'Ce bien ne fait pas partie de vos annonces.');

        return $bien;
    }

    private function donneesFormulaire(Immobilier $bien): array
    {
        return [
            'bien' => $bien,
            'types' => Cache::remember('immo:types', 86400, fn () => TypeImmobilier::orderBy('ID')->get(['ID', 'DESIGNATION'])),
            'categories' => HomeController::categories(),
            'communes' => HomeController::communes(),
        ];
    }

    private function viderCaches(): void
    {
        foreach (['immo:home:une', 'immo:home:ventes', 'immo:home:locations', 'immo:home:carte', 'immo:home:compteurs', 'home:biens'] as $cle) {
            Cache::forget($cle);
        }
        Annonceurs::oublier();
    }
}
