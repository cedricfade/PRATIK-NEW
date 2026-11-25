<?php

namespace App\Http\Resources\Api;

use App\Models\ContactEntreprise;
use Illuminate\Http\Request;

/** @mixin ContactEntreprise */
class MessageResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => trim($this->prenom.' '.$this->nom),
            'telephone' => self::texte($this->contact),
            'email' => self::texte($this->email),
            'objet' => $this->objet,
            'message' => $this->contactmessage,
            'reponse' => self::texte($this->reponse),
            'lu' => $this->est_lu,
            'repondu' => (int) $this->status === 2 || filled($this->reponse),
            'recu_le' => self::dateHeure($this->created_at),
        ];
    }
}
