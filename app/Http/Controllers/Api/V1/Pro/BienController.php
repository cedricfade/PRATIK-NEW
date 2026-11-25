<?php

namespace App\Http\Controllers\Api\V1\Pro;

use App\Http\Controllers\Api\V1\Concerns;
use App\Http\Controllers\Api\V1\ImmobilierController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BienRequest;
use App\Http\Resources\Api\BienDetailResource;
use App\Http\Resources\Api\BienResource;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\PhotoImmobilier;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/** Annonces du professionnel connecté. Il ne voit et ne modifie que les siennes. */
class BienController extends Controller
{
    use Concerns;

    private const RELATIONS_DETAIL = [
        'photos:ID,IMMOBILIER_ID,IMAGE',
        'auteur:id,name,email,entreprise_id,agent_tel,agent_cel',
        'auteur.entreprise:ID,TITRE,LIEN',
        'auteur.entreprise.annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1,TEL_1,TEL_2,CEL_1,CEL_2,EMAIL_1,EMAIL_2,WHATSAPP',
    ];

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate(['statut' => ['nullable', 'in:publie,en_attente']]);

        $biens = Immobilier::where('UTILISATEUR', $request->user()->id)
            ->when($request->query('statut') === 'publie', fn ($q) => $q->where('VALIDATION', 'OUI'))
            ->when($request->query('statut') === 'en_attente', fn ($q) => $q->where(fn ($w) => $w->where('VALIDATION', '!=', 'OUI')->orWhereNull('VALIDATION')))
            ->with(ImmobilierController::RELATIONS_LISTE)
            ->orderByDesc('ID')
            ->paginate($this->parPage($request))
            ->withQueryString();

        return BienResource::collection($biens);
    }

    public function show(Request $request, int $bien): BienDetailResource
    {
        return new BienDetailResource($this->bien($request, $bien)->load([...ImmobilierController::RELATIONS_LISTE, ...self::RELATIONS_DETAIL]));
    }

    public function store(BienRequest $request): BienDetailResource
    {
        $user = $request->user();

        $bien = DB::transaction(function () use ($request, $user) {
            $fiche = Entreprise::create([
                'COMMUNE_ID' => $request->integer('commune_id'),
                'TYPE_ID' => config('pratik.types.immobilier'),
                'TITRE' => $request->string('titre')->trim()->toString(),
                'LIEN' => 'bien-'.uniqid(),
                'COMPTEUR' => 0,
                'DATE_LOG' => now()->toDateString(),
                'UTILISATEUR' => $user->id,
                'ACTIVATION' => 1,
            ]);
            $fiche->update(['LIEN' => Entreprise::lienUnique($fiche->TITRE, $fiche->ID)]);

            $bien = Immobilier::create($request->colonnesImmobilier() + [
                'ENTREPRISE_ID' => $fiche->ID,
                'VALIDATION' => 'NON', // validation par un administrateur avant publication
                'DATE_LOG' => now()->toDateString(),
                'UTILISATEUR' => $user->id,
                'ILLUSTATION' => $request->hasFile('illustration')
                    ? Media::store($request->file('illustration'), 'immobilier', 'ILL')
                    : Immobilier::IMAGE_DEFAUT,
            ]);

            $this->ajouterPhotos($request, $bien);

            return $bien;
        });

        return (new BienDetailResource($bien->load([...ImmobilierController::RELATIONS_LISTE, ...self::RELATIONS_DETAIL])))
            ->additional(['message' => 'Annonce enregistrée. Elle sera visible après validation par l\'équipe Pratik.']);
    }

    public function update(BienRequest $request, int $bien): BienDetailResource
    {
        $bien = $this->bien($request, $bien);

        DB::transaction(function () use ($request, $bien) {
            $colonnes = $request->colonnesImmobilier();

            if ($request->hasFile('illustration')) {
                // L'ancien fichier est supprimé par l'événement "updated" du modèle.
                $colonnes['ILLUSTATION'] = Media::store($request->file('illustration'), 'immobilier', 'ILL');
            }

            if ($colonnes) {
                $bien->update($colonnes);
            }

            $fiche = $bien->entreprise;
            if ($fiche) {
                if ($request->filled('titre') && $request->input('titre') !== $fiche->TITRE) {
                    $fiche->TITRE = $request->string('titre')->trim()->toString();
                    $fiche->LIEN = Entreprise::lienUnique($fiche->TITRE, $fiche->ID);
                }
                if ($request->filled('commune_id')) {
                    $fiche->COMMUNE_ID = $request->integer('commune_id');
                }
                $fiche->save();
            }

            $this->ajouterPhotos($request, $bien);
        });

        return new BienDetailResource($bien->fresh()->load([...ImmobilierController::RELATIONS_LISTE, ...self::RELATIONS_DETAIL]));
    }

    public function destroy(Request $request, int $bien): Response
    {
        // Supprime la fiche, la photo principale et toute la galerie (fichiers compris).
        $this->bien($request, $bien)->supprimer();

        return response()->noContent();
    }

    public function destroyPhoto(Request $request, int $bien, int $photo): Response
    {
        $bien = $this->bien($request, $bien);

        PhotoImmobilier::where('IMMOBILIER_ID', $bien->ID)->findOrFail($photo)->delete();

        return response()->noContent();
    }

    /** Charge le bien et vérifie qu'il appartient à l'utilisateur (les admins ont accès à tout). */
    private function bien(Request $request, int $id): Immobilier
    {
        $bien = Immobilier::with('entreprise')->findOrFail($id);
        $user = $request->user();

        abort_unless($user->estAdmin() || (int) $bien->UTILISATEUR === (int) $user->id, 403, 'Cette annonce ne vous appartient pas.');

        return $bien;
    }

    private function ajouterPhotos(Request $request, Immobilier $bien): void
    {
        foreach ($request->file('photos', []) as $fichier) {
            PhotoImmobilier::create([
                'IMMOBILIER_ID' => $bien->ID,
                'IMAGE' => Media::store($fichier, 'immobilier', 'Immo'),
            ]);
        }
    }
}
