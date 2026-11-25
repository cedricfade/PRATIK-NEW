<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Comptes de la table historique "users".
 * role_id : 1 super admin, 2 admin, 5 pro immobilier, 7 visiteur (voir config/pratik.php).
 */
#[Fillable([
    'name', 'email', 'password', 'role_id', 'entreprise_id', 'CATEGORIE', 'active',
    'agent_tel', 'agent_cel', 'agent_facebook', 'agent_twitter', 'agent_instagram', 'agent_youtube',
    'user_created',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements OAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role_id' => 'integer',
            'entreprise_id' => 'integer',
            'active' => 'integer',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id', 'ID');
    }

    public function biens(): HasMany
    {
        return $this->hasMany(Immobilier::class, 'UTILISATEUR', 'id');
    }

    /** @var list<int>|null */
    private ?array $entreprisesSuiviesCache = null;

    /**
     * Fiches "entreprises" auxquelles sont rattachés les messages et statistiques de l'annonceur :
     * sa société (si elle existe) + la fiche de chacun de ses biens. Les visiteurs écrivent
     * et consultent l'une ou l'autre selon que l'annonceur a une société rattachée ou non.
     *
     * @return list<int>
     */
    public function entreprisesSuivies(): array
    {
        return $this->entreprisesSuiviesCache ??= collect([$this->entreprise_id])
            ->merge(Immobilier::where('UTILISATEUR', $this->id)->pluck('ENTREPRISE_ID'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all() ?: [-1];
    }

    public function estAdmin(): bool
    {
        return in_array($this->role_id, [config('pratik.roles.super_admin'), config('pratik.roles.admin')], true);
    }

    public function estSuperAdmin(): bool
    {
        return $this->role_id === config('pratik.roles.super_admin');
    }

    /** Professionnel de l'immobilier (agence, promoteur…) avec un espace annonceur. */
    public function estPro(): bool
    {
        return in_array($this->role_id, [config('pratik.roles.immobilier'), config('pratik.roles.restauration')], true);
    }

    public function estActif(): bool
    {
        return $this->active === null || $this->active === 1;
    }

    public function getRoleLibelleAttribute(): string
    {
        return match ($this->role_id) {
            1 => 'Super admin',
            2 => 'Administrateur',
            4 => 'Restauration',
            5 => 'Professionnel immobilier',
            7 => 'Visiteur',
            default => 'Utilisateur',
        };
    }
}
