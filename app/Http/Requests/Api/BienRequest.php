<?php

namespace App\Http\Requests\Api;

use App\Models\Immobilier;
use Illuminate\Foundation\Http\FormRequest;

/** Création / modification d'un bien depuis l'application (multipart pour les photos). */
class BienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $creation = $this->isMethod('post') && ! $this->route('bien');
        $requis = $creation ? 'required' : 'sometimes';

        $regles = [
            'titre' => [$requis, 'string', 'min:5', 'max:300'],
            'type_id' => [$requis, 'integer', 'in:1,2,3,4'],
            'categorie_id' => [$requis, 'integer', 'exists:categorie_immobilier,ID'],
            'commune_id' => [$requis, 'integer', 'exists:communes,ID'],
            'prix' => ['sometimes', 'nullable', 'regex:/^[\d\s.,]+$/'],
            'prix_m2' => ['sometimes', 'nullable', 'regex:/^[\d\s.,]+$/'],
            'surface' => ['sometimes', 'nullable', 'string', 'max:10'],
            'pieces' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:99'],
            'chambres' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:99'],
            'salles_de_bain' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:99'],
            'salons' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:99'],
            'etat' => ['sometimes', 'nullable', 'string', 'max:50'],
            'age' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:99'],
            'adresse' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:20000'],
            'video' => ['sometimes', 'nullable', 'url', 'max:500'],
            'equipements' => ['sometimes', 'array'],
            'equipements.*' => ['string', 'in:'.implode(',', array_map('strtolower', array_keys(Immobilier::EQUIPEMENTS)))],
            'illustration' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'photos' => ['sometimes', 'array', 'max:15'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ];

        return $regles;
    }

    public function attributes(): array
    {
        return [
            'type_id' => 'type d\'annonce',
            'categorie_id' => 'catégorie',
            'commune_id' => 'commune',
            'salles_de_bain' => 'salles de bain',
            'photos.*' => 'photo',
        ];
    }

    /** Colonnes de la table "immobilier" correspondant aux champs envoyés. */
    public function colonnesImmobilier(): array
    {
        $carte = [
            'type_id' => 'TYPE_IMMOBILIER_ID',
            'categorie_id' => 'CATEGORIE_IMMOBILIER_ID',
            'surface' => 'SURFACE',
            'pieces' => 'PIECES',
            'chambres' => 'CHAMBRE',
            'salles_de_bain' => 'SALLE_BAIN',
            'salons' => 'SALONS',
            'etat' => 'ETAT_BIEN',
            'age' => 'AGE',
            'adresse' => 'ADRESSE',
            'latitude' => 'LATITUDE',
            'longitude' => 'LONGITUDE',
            'description' => 'DESCRIPTION',
            'video' => 'VIDEO',
        ];

        $valides = $this->validated();
        $colonnes = [];

        foreach ($carte as $champ => $colonne) {
            if (array_key_exists($champ, $valides)) {
                $colonnes[$colonne] = $valides[$champ];
            }
        }

        foreach (['prix' => 'PRIX', 'prix_m2' => 'PRIX_M2'] as $champ => $colonne) {
            if (array_key_exists($champ, $valides)) {
                $colonnes[$colonne] = preg_replace('/\D+/', '', (string) $valides[$champ]) ?: null;
            }
        }

        if (array_key_exists('equipements', $valides)) {
            $coches = array_map('strtoupper', $valides['equipements']);
            foreach (array_keys(Immobilier::EQUIPEMENTS) as $colonne) {
                $colonnes[$colonne] = in_array($colonne, $coches, true) ? 'OUI' : 'NON';
            }
        }

        return $colonnes;
    }
}
