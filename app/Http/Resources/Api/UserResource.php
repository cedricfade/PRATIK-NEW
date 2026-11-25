<?php

namespace App\Http\Resources\Api;

use App\Models\User;
use Illuminate\Http\Request;

/** @mixin User */
class UserResource extends ApiResource
{
    public static function codeRole(User $user): string
    {
        return match (true) {
            $user->estAdmin() => 'admin',
            $user->estPro() => 'pro',
            default => 'visiteur',
        };
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'telephone' => self::texte($this->agent_cel) ?? self::texte($this->agent_tel),
            'role' => [
                'code' => self::codeRole($this->resource),
                'libelle' => $this->role_libelle,
            ],
            'entreprise_id' => $this->entreprise_id ?: null,
            'cree_le' => self::dateHeure($this->created_at),
        ];
    }
}
