<?php

namespace App\Http\Requests\Admin;

use App\Models\Immobilier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:300'],
            'commune_id' => ['nullable', 'integer', 'exists:communes,ID'],
            'categorie_id' => ['nullable', 'integer', 'exists:categorie_immobilier,ID'],
            'type_id' => ['required', 'integer', 'exists:type_immobilier,ID'],
            'prix' => ['nullable', 'string', 'max:25'],
            'prix_m2' => ['nullable', 'string', 'max:25'],
            'surface' => ['nullable', 'string', 'max:10'],
            'pieces' => ['nullable', 'integer', 'min:0', 'max:99'],
            'chambre' => ['nullable', 'integer', 'min:0', 'max:99'],
            'salle_bain' => ['nullable', 'integer', 'min:0', 'max:99'],
            'salons' => ['nullable', 'integer', 'min:0', 'max:99'],
            'etat_bien' => ['nullable', 'string', 'max:50'],
            'adresse' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'google_maps' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:20000'],
            'mots_cles' => ['nullable', 'string', 'max:1000'],
            'metas' => ['nullable', 'string', 'max:5000'],
            'video' => ['nullable', 'string', 'max:2000'],
            'videos_3d' => ['nullable', 'string', 'max:250'],
            'v_nom' => ['nullable', 'string', 'max:250'],
            'v_tel' => ['nullable', 'string', 'max:50'],
            'v_cel' => ['nullable', 'string', 'max:50'],
            'v_email' => ['nullable', 'email', 'max:100'],
            'v_whatsapp' => ['nullable', 'string', 'max:250'],
            'validation' => ['required', Rule::in(['OUI', 'NON'])],
            'flash' => ['nullable', 'boolean'],
            'luxe' => ['nullable', 'boolean'],
            'equipements' => ['nullable', 'array'],
            'equipements.*' => [Rule::in(array_keys(Immobilier::EQUIPEMENTS))],
            'illustration' => ['nullable', 'image', 'max:8192'],
            'photos' => ['nullable', 'array', 'max:20'],
            'photos.*' => ['image', 'max:8192'],
        ];
    }

    /** Colonnes de la table "immobilier" (hors fichiers). */
    public function immobilier(): array
    {
        $prix = fn ($v) => $v === null ? null : preg_replace('/\D+/', '', (string) $v);

        $donnees = [
            'CATEGORIE_IMMOBILIER_ID' => $this->input('categorie_id'),
            'TYPE_IMMOBILIER_ID' => $this->integer('type_id'),
            'PRIX' => $prix($this->input('prix')),
            'PRIX_M2' => $prix($this->input('prix_m2')),
            'SURFACE' => $this->input('surface'),
            'PIECES' => $this->input('pieces'),
            'CHAMBRE' => $this->input('chambre'),
            'SALLE_BAIN' => $this->input('salle_bain'),
            'SALONS' => $this->input('salons'),
            'ETAT_BIEN' => $this->input('etat_bien'),
            'ADRESSE' => $this->input('adresse'),
            'LATITUDE' => $this->input('latitude'),
            'LONGITUDE' => $this->input('longitude'),
            'GOOGLE_MAPS' => $this->input('google_maps'),
            'DESCRIPTION' => $this->input('description'),
            'MOTS_CLES' => $this->input('mots_cles'),
            'METAS' => $this->input('metas'),
            'VIDEO' => $this->input('video'),
            'VIDEOS_3D' => $this->input('videos_3d'),
            'V_NOM' => $this->input('v_nom'),
            'V_TEL' => $this->input('v_tel'),
            'V_CEL' => $this->input('v_cel'),
            'V_EMAIL' => $this->input('v_email'),
            'V_WHATSAPP' => $this->input('v_whatsapp'),
            'VALIDATION' => $this->input('validation'),
            'FLASH' => $this->boolean('flash') ? 'OUI' : 'NON',
            'LUXE' => $this->boolean('luxe') ? 'OUI' : 'NON',
        ];

        $coches = (array) $this->input('equipements', []);
        foreach (array_keys(Immobilier::EQUIPEMENTS) as $col) {
            $donnees[$col] = in_array($col, $coches, true) ? 'OUI' : 'NON';
        }

        return $donnees;
    }
}
