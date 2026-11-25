<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EntrepriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('entreprise')?->ID;

        return [
            'titre' => ['required', 'string', 'max:300'],
            'sigle' => ['nullable', 'string', 'max:100'],
            'activite' => ['nullable', 'string', 'max:1000'],
            'commune_id' => ['nullable', 'integer', 'exists:communes,ID'],
            'type_id' => ['required', 'integer', 'exists:type_entreprise,ID'],
            'priorite' => ['nullable', 'integer', 'min:0', 'max:99'],
            'activation' => ['nullable', 'boolean'],
            'date_souscription' => ['nullable', 'date'],
            'lien' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9][a-z0-9-]*$/', Rule::unique('entreprises', 'LIEN')->ignore($id, 'ID')],

            'client' => ['required', Rule::in(['OUI', 'NON'])],
            'vip' => ['nullable', 'boolean'],
            'top10' => ['nullable', 'boolean'],
            'tel_1' => ['nullable', 'string', 'max:100'],
            'tel_2' => ['nullable', 'string', 'max:100'],
            'cel_1' => ['nullable', 'string', 'max:100'],
            'cel_2' => ['nullable', 'string', 'max:100'],
            'fax' => ['nullable', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'string', 'max:100'],
            'email_1' => ['nullable', 'email', 'max:100'],
            'email_2' => ['nullable', 'email', 'max:100'],
            'site' => ['nullable', 'string', 'max:100'],
            'bp' => ['nullable', 'string', 'max:100'],
            'service_client' => ['nullable', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:2000'],
            'localisation' => ['nullable', 'string', 'max:2000'],
            'geolocalisation' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'resume' => ['nullable', 'string', 'max:1500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'mot_cle' => ['nullable', 'string', 'max:5000'],
            'forme_juridique' => ['nullable', 'string', 'max:100'],
            'nature_id' => ['nullable', 'integer', 'exists:natures,ID'],
            'siege_id' => ['nullable', 'integer', 'exists:entreprises,ID'],
            'facebook' => ['nullable', 'string', 'max:200'],
            'instagram' => ['nullable', 'string', 'max:200'],
            'linkedin' => ['nullable', 'string', 'max:200'],
            'tweeter' => ['nullable', 'string', 'max:200'],
            'video' => ['nullable', 'string', 'max:2000'],
            'video_3d' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'supprimer_logo' => ['nullable', 'boolean'],
            'ajouter_pharmacie' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'titre' => 'raison sociale', 'commune_id' => 'commune', 'type_id' => 'type', 'email_1' => 'e-mail principal',
            'email_2' => 'e-mail secondaire', 'siege_id' => 'siège', 'lien' => 'lien (URL)',
        ];
    }

    public function messages(): array
    {
        return ['lien.regex' => 'Le lien ne peut contenir que des minuscules, chiffres et tirets.'];
    }

    /** Colonnes de la table "entreprises". */
    public function entreprise(): array
    {
        return [
            'TITRE' => $this->input('titre'),
            'SIGLE' => $this->input('sigle'),
            'ACTIVITE' => $this->input('activite'),
            'COMMUNE_ID' => $this->input('commune_id'),
            'TYPE_ID' => $this->integer('type_id'),
            'PRIORITE' => $this->input('priorite', 0) ?? 0,
            'ACTIVATION' => $this->boolean('activation') ? 1 : 0,
            'DATE_SOUSCRIPTION' => $this->input('date_souscription'),
        ];
    }

    /** Colonnes de la table "annuaires". */
    public function annuaire(): array
    {
        return [
            'CLIENT' => $this->input('client'),
            'VIP' => $this->boolean('vip') ? 1 : 0,
            'TOP10' => $this->boolean('top10') ? 1 : 0,
            'PRIORITE' => $this->input('priorite', 0) ?? 0,
            'TEL_1' => $this->input('tel_1'),
            'TEL_2' => $this->input('tel_2'),
            'CEL_1' => $this->input('cel_1'),
            'CEL_2' => $this->input('cel_2'),
            'FAX' => $this->input('fax'),
            'WHATSAPP' => $this->input('whatsapp'),
            'EMAIL_1' => $this->input('email_1'),
            'EMAIL_2' => $this->input('email_2'),
            'SITE' => $this->input('site'),
            'BP' => $this->input('bp'),
            'SERVICE_CLIENT' => $this->input('service_client'),
            'ADRESSE' => $this->input('adresse'),
            'LOCALISATION' => $this->input('localisation'),
            'GEOLOCALISATION' => $this->input('geolocalisation'),
            'LATITUDE' => $this->input('latitude'),
            'LONGITUDE' => $this->input('longitude'),
            'RESUME' => $this->input('resume'),
            'DESCRIPTION' => $this->input('description'),
            'MOT_CLE' => $this->input('mot_cle'),
            'ACTIVITES' => $this->input('activite'),
            'FORME_JURIDIQUE' => $this->input('forme_juridique'),
            'NATURE_ID' => $this->input('nature_id'),
            'SIEGE_ID' => $this->input('siege_id'),
            'FACEBOOK' => $this->input('facebook'),
            'INSTAGRAM' => $this->input('instagram'),
            'LINKEDIN' => $this->input('linkedin'),
            'TWEETER' => $this->input('tweeter'),
            'VIDEO' => $this->input('video'),
            'VIDEO_3D' => $this->input('video_3d'),
        ];
    }
}
