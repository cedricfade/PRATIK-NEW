<?php

namespace App\Http\Requests\Immobilier;

use App\Models\Immobilier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Formulaire de dépôt / modification d'un bien (espace annonceur). */
class BienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // l'appartenance du bien est vérifiée dans le contrôleur
    }

    protected function prepareForValidation(): void
    {
        $chiffres = fn ($v) => $v === null ? null : (preg_replace('/\D+/', '', (string) $v) ?: null);

        $this->merge([
            'prix' => $chiffres($this->input('prix')),
            'prix_m2' => $chiffres($this->input('prix_m2')),
            'latitude' => $this->input('latitude') !== null ? str_replace(',', '.', trim((string) $this->input('latitude'))) : null,
            'longitude' => $this->input('longitude') !== null ? str_replace(',', '.', trim((string) $this->input('longitude'))) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'min:10', 'max:250'],
            'type_immobilier_id' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
            'categorie_immobilier_id' => ['required', 'integer', 'exists:categorie_immobilier,ID'],
            'commune_id' => ['required', 'integer', 'exists:communes,ID'],
            'prix' => ['nullable', 'digits_between:1,13'],
            'prix_m2' => ['nullable', 'digits_between:1,13'],
            'periode' => ['nullable', Rule::in(['MOIS', 'JOUR'])],
            'surface' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'pieces' => ['nullable', 'integer', 'min:0', 'max:99'],
            'chambre' => ['nullable', 'integer', 'min:0', 'max:99'],
            'salle_bain' => ['nullable', 'integer', 'min:0', 'max:99'],
            'salons' => ['nullable', 'integer', 'min:0', 'max:99'],
            'etat_bien' => ['nullable', Rule::in(['NEUF', 'OCCASION'])],
            'age' => ['nullable', 'integer', 'min:0', 'max:99'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'google_maps' => ['nullable', 'url', 'max:1000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'mots_cles' => ['nullable', 'string', 'max:1000'],
            'video' => ['nullable', 'url', 'max:500'],
            'videos_3d' => ['nullable', 'url', 'max:250'],
            'equipements' => ['nullable', 'array'],
            'equipements.*' => [Rule::in(array_keys(Immobilier::EQUIPEMENTS))],
            'illustration' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'photos' => ['nullable', 'array', 'max:15'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ];
    }

    public function attributes(): array
    {
        return [
            'titre' => 'titre de l\'annonce',
            'type_immobilier_id' => 'type d\'offre',
            'categorie_immobilier_id' => 'catégorie',
            'commune_id' => 'commune',
            'prix_m2' => 'prix au m²',
            'chambre' => 'chambres',
            'salle_bain' => 'salles de bain',
            'etat_bien' => 'état du bien',
            'google_maps' => 'lien Google Maps',
            'video' => 'lien de la vidéo',
            'videos_3d' => 'lien de la visite virtuelle',
            'illustration' => 'photo principale',
            'photos.*' => 'photo',
        ];
    }

    /** Colonnes de la table immobilier remplies par le formulaire. */
    public function colonnesImmobilier(): array
    {
        $v = $this->validated();
        $cochés = $v['equipements'] ?? [];

        $colonnes = [
            'TYPE_IMMOBILIER_ID' => (int) $v['type_immobilier_id'],
            'CATEGORIE_IMMOBILIER_ID' => (int) $v['categorie_immobilier_id'],
            'PRIX' => $v['prix'] ?? null,
            'PRIX_M2' => $v['prix_m2'] ?? null,
            'PERIODE' => (int) $v['type_immobilier_id'] === 1 ? ($v['periode'] ?? 'MOIS') : null,
            'SURFACE' => isset($v['surface']) ? (string) $v['surface'] : null,
            'PIECES' => $v['pieces'] ?? null,
            'CHAMBRE' => $v['chambre'] ?? null,
            'SALLE_BAIN' => $v['salle_bain'] ?? null,
            'SALONS' => $v['salons'] ?? null,
            'ETAT_BIEN' => $v['etat_bien'] ?? null,
            'AGE' => $v['age'] ?? null,
            'ADRESSE' => $v['adresse'] ?? null,
            'LATITUDE' => $v['latitude'] ?? null,
            'LONGITUDE' => $v['longitude'] ?? null,
            'GOOGLE_MAPS' => $v['google_maps'] ?? null,
            'DESCRIPTION' => $v['description'] ?? null,
            'MOTS_CLES' => $v['mots_cles'] ?? null,
            'VIDEO' => $v['video'] ?? null,
            'VIDEOS_3D' => $v['videos_3d'] ?? null,
        ];

        foreach (array_keys(Immobilier::EQUIPEMENTS) as $col) {
            $colonnes[$col] = in_array($col, $cochés, true) ? 'OUI' : null;
        }

        return $colonnes;
    }
}
