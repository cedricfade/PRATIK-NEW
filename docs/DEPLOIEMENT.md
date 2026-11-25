# Mise en production de la nouvelle version de pratik-ci.com

Cette version remplace le code de l'ancien site **sans toucher aux données** : elle lit et écrit
dans les mêmes tables (`entreprises`, `annuaires`, `immobilier`, `pharmacie_garde`, `news`…) et
réutilise les mêmes dossiers d'images. Les comptes existants (admins, annonceurs, visiteurs)
gardent leurs mots de passe.

## Ce que font les migrations sur la base de production

`php artisan migrate` est **purement additif** :

| Migration | Effet |
|---|---|
| `0001_01_01_000000_create_users_table` | Ne recrée pas `users`. Crée `password_reset_tokens` et `sessions` si absentes. |
| `0001_01_01_000001/2` (cache, jobs) | Crée les tables `cache`, `jobs`… si absentes. |
| `2026_11_25_000100_add_performance_indexes…` | Ajoute des index (lien, commune, dates de garde, catégories…). |
| `2026_11_25_000200_add_search_indexes…` | Ajoute l'index FULLTEXT de recherche et des index composites. |
| `2026_11_25_000300_add_garde_en_cours_index` | Index pour retrouver instantanément les gardes en cours. |
| `2026_11_25_000400_convert_legacy_myisam_tables…` | Passe en InnoDB les quelques petites tables restées en MyISAM (`users`, `contacts`, `roles`…). Les données ne changent pas ; seul le moteur de stockage change (plus de tables « crashed », verrouillage par ligne). |
| `2026_11_25_000500_create_parametres_site_table` | Nouvelle table des réglages (en-têtes des pages d'accueil, chiffres) gérés dans Admin › Apparence. |
| `2026_11_25_000600_add_vues_bonus_to_entreprises` | Ajoute la colonne `VUES_BONUS` (0 par défaut) et la table `historique_vues` : vues ajoutées depuis Admin › Vues, séparées du compteur réel `COMPTEUR`. |
| `2026_11_25_000700_create_pubs_app_table` | Nouvelle table des bannières publicitaires de l'application mobile (Admin › Apparence › Application mobile). |
| `…_create_oauth_*` (Passport) | Crée les tables de jetons pour l'application mobile. |

Aucune table, colonne ou ligne existante n'est supprimée ni renommée. Chaque migration vérifie ce
qui existe déjà et peut être relancée sans risque.

## Étapes

### 1. Sauvegarder (obligatoire)

- Exporter la base complète depuis phpMyAdmin (comme `rs2187348_pratik.sql`).
- Archiver l'ancien code **et** le dossier `images/` du serveur.

### 2. Envoyer le code

Structure recommandée sur l'hébergement :

```
/home/<compte>/pratik-new/     ← tout le projet Laravel (hors public)
/home/<compte>/public_html/    ← contenu du dossier public/ du projet
/home/<compte>/public_html/images/   ← dossier d'images EXISTANT, à conserver tel quel
```

Le dossier `images/` (Logos, ImagesImmobiliers, ImagesNews, Galerie, LogosNews,
PapierAdministratif, ImagesPubs…) reste en place : la nouvelle version utilise les mêmes
sous-dossiers et les mêmes noms de fichiers. **Ne pas copier l'ancien dossier `assets/`** : il
contient surtout des images de l'ancien template, inutiles ici.

Si `public_html` n'est pas le dossier `public/` du projet, modifier `public_html/index.php` pour
pointer vers `../pratik-new/vendor/autoload.php` et `../pratik-new/bootstrap/app.php`, puis
indiquer le chemin public à Laravel :

```php
$app = require_once __DIR__.'/../pratik-new/bootstrap/app.php';
$app->usePublicPath(__DIR__);
```

### 3. Fichier `.env` de production

Partir de `.env.example` et renseigner au minimum :

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pratik-ci.com
APP_KEY=            # php artisan key:generate --show

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=rs2187348_pratik
DB_USERNAME=…
DB_PASSWORD=…

# Images : chemin ABSOLU du dossier images existant. Obligatoire en production
# (avec config:cache, le chemin public calculé en ligne de commande serait faux).
PRATIK_MEDIA_PATH=/home/<compte>/public_html/images
PRATIK_MEDIA_URL=/images
# En production, vider cette ligne (les fichiers sont sur place) :
PRATIK_MEDIA_FALLBACK_URL=

MAIL_MAILER=smtp     # + MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD
MAIL_FROM_ADDRESS="infopratik2@gmail.com"

AERODB_API_KEY=…     # horaires des vols
GA_MEASUREMENT_ID=G-229RZNXBJB
ADSENSE_CLIENT=ca-pub-6852533725093240
```

`PRATIK_MEDIA_FALLBACK_URL` sert seulement en développement ou pendant une bascule vers un autre
serveur : si une image n'est pas trouvée sur place, elle est chargée depuis l'ancien site.

### 4. Installer

```bash
cd ~/pratik-new
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan passport:keys            # une seule fois
php artisan passport:client --personal --name="Pratik Mobile" --provider=users   # une seule fois
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Les assets (`public/build`) se compilent en local avec `npm run build`, puis s'envoient avec le
reste du dossier `public/`.

Droits d'écriture nécessaires : `storage/`, `bootstrap/cache/` et le dossier `images/`
(envois de logos et de photos).

### 5. Vérifier

- Pages : accueil, une fiche `/details/…`, `/pharmacies-de-garde`, `/news`, `/immobilier`
  (nouvel onglet), une fiche bien `/immobilier/proprietes-…`.
- Connexion admin sur `/connexion` → redirection vers `/admin`.
- Connexion annonceur sur `/immobilier/connexion` → espace annonceur.
- `https://pratik-ci.com/sitemap.xml`, puis le soumettre à nouveau dans Google Search Console.

## Après la mise en ligne : libérer de l'espace disque

Les images qui ne sont plus référencées en base (entreprises supprimées, biens retirés, anciennes
galeries…) peuvent être repérées puis retirées :

```bash
php artisan pratik:images-orphelines              # simulation : liste et taille, rien n'est touché
php artisan pratik:images-orphelines --deplacer   # déplace vers storage/app/corbeille-images/<date>
# après vérification du site, supprimer le dossier de corbeille
```

Désormais, la suppression d'une entreprise, d'un bien, d'une photo de galerie, d'une une de
journal ou d'un papier administratif efface automatiquement ses fichiers (le fichier est conservé
s'il est encore utilisé par une autre fiche). Le remplacement d'un logo ou d'une photo supprime
l'ancien fichier.

## Retour arrière

Les migrations n'ayant rien modifié d'existant, l'ancien code peut être remis en place tel quel
sur la même base. Les nouvelles tables (sessions, cache, oauth_*) et les index n'interfèrent pas
avec lui.
