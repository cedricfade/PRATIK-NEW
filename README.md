# Pratik — pratik-ci.com (nouvelle version)

Annuaire des entreprises de Côte d'Ivoire, pharmacies de garde, revue de presse, papiers
administratifs, horaires des vols et sous-site **Pratik Immobilier**, avec un backoffice et une
API pour l'application mobile Flutter.

- Laravel 13 · PHP 8.4+ · MySQL 8 / MariaDB 11
- Blade + Tailwind CSS 4 + Alpine.js, cartes Leaflet / OpenStreetMap
- Authentification web par session, API par jetons **Laravel Passport**

## Organisation

| Partie | Routes | Code |
|---|---|---|
| Site public | `routes/web.php` | `app/Http/Controllers/Site`, `resources/views/site` |
| Pratik Immobilier + espace annonceur | `routes/immobilier.php` | `app/Http/Controllers/Immobilier`, `resources/views/immobilier` |
| Backoffice (`/admin`) | `routes/admin.php` | `app/Http/Controllers/Admin`, `resources/views/admin` |
| API mobile (`/api/v1`) | `routes/api.php` | `app/Http/Controllers/Api`, doc : `docs/API.md` |
| Connexion / comptes | `routes/auth.php` | `app/Http/Controllers/Auth` |
| Anciennes URLs (301) | `routes/legacy.php` | — |

La base de données est **celle de l'ancien site**, sans changement de structure : les modèles
(`app/Models`) pointent sur les tables historiques et leurs colonnes en majuscules
(`entreprises.TITRE`, `immobilier.ILLUSTATION`…). Les identifiants métier (types, rôles,
sous-catégorie des pharmacies…) sont regroupés dans `config/pratik.php`.

Les images clients gardent leurs dossiers historiques (`images/Logos`,
`images/ImagesImmobiliers`…). `App\Support\Media` construit les URLs et, si un fichier manque
sur ce serveur, le sert depuis l'ancien site (`PRATIK_MEDIA_FALLBACK_URL`). Supprimer une
fiche supprime ses fichiers (événements des modèles) ; `php artisan pratik:images-orphelines`
nettoie les images qui ne sont plus utilisées.

## Lancer en local (Sail / Docker)

```bash
./vendor/bin/sail up -d
# importer la base de production (une fois) :
./vendor/bin/sail mysql laravel < "rs2187348_pratik (1).sql"
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan passport:keys
./vendor/bin/sail artisan passport:client --personal --name="Pratik Mobile" --provider=users
npm install && npm run build      # ou npm run dev
```

Site : http://localhost · phpMyAdmin : http://localhost:8080

## Tests

```bash
./vendor/bin/sail artisan test
```

Les tests utilisent la base `testing`, créée à partir de `database/schema/mysql-schema.sql`
(structure de la base historique, sans données).

## Mise en production

Voir **[docs/DEPLOIEMENT.md](docs/DEPLOIEMENT.md)**.
