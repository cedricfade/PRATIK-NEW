# API Pratik v1 — guide pour l'application Flutter

Cette API donne à l'application mobile les mêmes données que le site : annuaire, pharmacies de garde, presse, papiers administratifs, immobilier. Elle donne aussi accès à l'espace annonceur des professionnels de l'immobilier.

- **URL de base** : `https://pratik-ci.com/api/v1` (en local : `http://localhost/api/v1`)
- **Format** : JSON (UTF-8). Envoyez toujours l'en-tête `Accept: application/json`.
- **Dates** : ISO 8601 (`2026-11-25`, `2026-11-25T09:42:00+00:00`).
- **Images** : toutes les URLs (`logo_url`, `image_url`, `photos[].url`…) sont absolues et utilisables directement dans `Image.network`. Un champ image vaut `null` quand il n'y a pas d'image.
- **Langue** : les messages d'erreur sont en français et peuvent être affichés tels quels.
- **Test rapide** : `GET /ping` renvoie `{"ok": true, "version": "v1"}`.

---

## 1. Authentification

L'API utilise des **jetons Bearer** (Laravel Passport). Le jeton est renvoyé à l'inscription et à la connexion.

- Durée de vie : **6 mois**. Passé ce délai, l'API répond `401` et l'utilisateur doit se reconnecter.
- Stockez le jeton dans un stockage sécurisé (`flutter_secure_storage`), jamais en clair.
- Envoyez-le sur chaque requête protégée : `Authorization: Bearer <jeton>`.
- La déconnexion (`POST /auth/logout`) révoque le jeton côté serveur. Supprimez-le aussi localement.

Les comptes existants du site (administrateurs, annonceurs, visiteurs) se connectent avec leurs identifiants habituels, sans rien recréer.

### Rôles

`user.role.code` vaut :

| code | qui | accès |
|---|---|---|
| `visiteur` | compte créé depuis le site ou l'application | données publiques + profil |
| `pro` | agence, promoteur, aménageur foncier | + espace annonceur (`/pro/...`) |
| `admin` | équipe Pratik | + espace annonceur (tous les biens) |

### Exemple Dart (package `http`)

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

const baseUrl = 'https://pratik-ci.com/api/v1';

Future<String> connexion(String email, String motDePasse) async {
  final reponse = await http.post(
    Uri.parse('$baseUrl/auth/login'),
    headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
    body: jsonEncode({'email': email, 'password': motDePasse, 'device_name': 'Android Pixel 8'}),
  );

  final corps = jsonDecode(reponse.body);
  if (reponse.statusCode == 200) {
    return corps['token'] as String; // à enregistrer dans flutter_secure_storage
  }
  if (reponse.statusCode == 422) {
    throw Exception(corps['errors']['email'][0]); // ex. "Adresse e-mail ou mot de passe incorrect."
  }
  throw Exception(corps['message'] ?? 'Erreur ${reponse.statusCode}');
}

Future<Map<String, dynamic>> monProfil(String jeton) async {
  final reponse = await http.get(
    Uri.parse('$baseUrl/me'),
    headers: {'Accept': 'application/json', 'Authorization': 'Bearer $jeton'},
  );
  if (reponse.statusCode == 401) {
    throw Exception('Session expirée, reconnectez-vous.');
  }
  return jsonDecode(reponse.body)['data'] as Map<String, dynamic>;
}
```

### Endpoints d'authentification

Limite : 10 requêtes par minute par e-mail et par adresse IP.

| Méthode | Chemin | Corps | Réponse |
|---|---|---|---|
| POST | `/auth/register` | `name`, `email`, `password`, `password_confirmation`, `telephone`?, `device_name`? | `201` jeton + utilisateur |
| POST | `/auth/login` | `email`, `password`, `device_name`? | `200` jeton + utilisateur |
| POST | `/auth/forgot-password` | `email` | `200` (même réponse que le compte existe ou non) |
| POST | `/auth/logout` 🔒 | — | `204` |
| GET | `/me` 🔒 | — | `{data: utilisateur}` |
| PUT | `/me` 🔒 | `name`?, `email`?, `telephone`? | `{data: utilisateur}` |
| PUT | `/me/password` 🔒 | `current_password`, `password`, `password_confirmation` | `200` |

🔒 = jeton obligatoire.

Réponse de connexion ou d'inscription :

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
  "token_type": "Bearer",
  "expires_at": "2027-05-25T09:42:00+00:00",
  "user": {
    "id": 210,
    "name": "Awa Koné",
    "email": "awa@example.ci",
    "telephone": "0707070707",
    "role": { "code": "visiteur", "libelle": "Visiteur" },
    "entreprise_id": null,
    "cree_le": "2026-11-25T09:42:00+00:00"
  }
}
```

Un compte désactivé par l'équipe Pratik reçoit `422` avec le message « Ce compte est désactivé. Contactez Pratik pour le réactiver. »

---

## 2. Listes paginées

Les listes paginées suivent le format standard :

```json
{
  "data": [ ... ],
  "links": { "first": "...?page=1", "last": "...?page=12", "prev": null, "next": "...?page=2" },
  "meta": { "current_page": 1, "last_page": 12, "per_page": 20, "total": 231 }
}
```

- `?page=2` pour la page suivante (ou suivez `links.next` tel quel, il conserve les filtres).
- `?per_page=` de 1 à **50** (20 par défaut).

Le paramètre `commune` accepte le **lien** (`cocody`, `port-bouet`) ou l'**ID** d'une commune. Une commune inconnue renvoie `404`.

---

## 3. Données publiques (sans jeton)

Limite : 120 requêtes par minute par appareil.

### Référentiels

| Méthode | Chemin | Paramètres | Description |
|---|---|---|---|
| GET | `/communes` | — | Toutes les communes ; `abidjan: true` pour les communes du district |

### Annuaire

| Méthode | Chemin | Paramètres | Description |
|---|---|---|---|
| GET | `/annuaire/categories` | — | Catégories avec `nombre_entreprises` |
| GET | `/annuaire/categories/{lien}/sous-categories` | — | Sous-catégories d'une catégorie (`sante`, `commerces`…) |
| GET | `/annuaire/sous-categories/{id}/entreprises` | `commune`, `page`, `per_page` | Entreprises paginées, **clients Pratik en premier** |
| GET | `/annuaire/recherche` | `q` (2 car. min.), `commune`, `page` | Recherche par nom, sigle ou activité |
| GET | `/annuaire/entreprises/{lien}` | — | Fiche complète |

Élément de liste (`EntrepriseResource`) :

```json
{
  "id": 11924,
  "titre": "BIOCARE COTE D'IVOIRE",
  "sigle": "BIOCARE CI",
  "lien": "biocare-cote-divoire",
  "activite": "Santé, Pharmacies",
  "commune": { "id": 9, "designation": "Cocody", "lien": "cocody", "abidjan": true },
  "logo_url": "https://pratik-ci.com/images/Logos/606632023-11-201700498721.jpeg",
  "est_client": true,
  "telephones": ["2722523186", "+2250707907879"],
  "adresse": "Riviera 2",
  "latitude": 5.3714,
  "longitude": -3.9712,
  "url_web": "https://pratik-ci.com/details/biocare-cote-divoire"
}
```

La fiche complète ajoute : `emails`, `whatsapp`, `fax`, `site`, `service_client`, `reseaux` (`facebook`, `twitter`, `instagram`, `linkedin`), `boite_postale`, `localisation`, `resume`, `description`, `activites`, `forme_juridique`, `video`, `galerie` (`[{id, url}]`), `departements` (`[{libelle, telephone, email}]`), `thematiques` (`[{id, designation, lien, categorie}]`) et `vues`.

### Pharmacies de garde

| Méthode | Chemin | Paramètres | Description |
|---|---|---|---|
| GET | `/pharmacies/garde` | — | Période en cours + communes ayant des pharmacies de garde |
| GET | `/pharmacies/garde?commune=cocody` | `commune` | Pharmacies de garde de la commune (liste complète, non paginée) |
| GET | `/pharmacies/garde/proximite` | `lat`, `lng`, `rayon` (km, 5 par défaut, 0,5 à 50) | Pharmacies de garde autour d'un point, **triées par distance** (50 au maximum) |

Sans `commune` :

```json
{
  "data": {
    "periode": { "debut": "2026-11-21", "fin": "2026-11-27", "libelle": "du samedi 21 novembre au vendredi 27 novembre 2026" },
    "communes": [
      { "id": 3, "designation": "Abobo", "lien": "abobo", "abidjan": true, "nombre_pharmacies": 16 }
    ]
  }
}
```

Avec `commune` ou à proximité :

```json
{
  "data": [
    {
      "id": 34413,
      "titre": "PHARMACIE DU MARCHE",
      "lien": "pharmacie-du-marche",
      "commune": { "id": 4, "designation": "Adjamé", "lien": "adjame", "abidjan": true },
      "adresse": "Face au marché Gouro",
      "localisation": null,
      "telephones": ["2720371234"],
      "emails": [],
      "latitude": 5.3445,
      "longitude": -4.0221,
      "distance_km": 0.94
    }
  ],
  "meta": { "position": { "lat": 5.3364, "lng": -4.0267 }, "rayon_km": 3, "total": 14 }
}
```

`distance_km` n'est présent que sur `/proximite`. Avec `?commune=`, `meta` contient `periode`, `commune` et `total`.

### Presse

| Méthode | Chemin | Paramètres | Description |
|---|---|---|---|
| GET | `/news/titrologie` | `date` (`AAAA-MM-JJ`, facultatif) | Unes des journaux. Sans date : la dernière date publiée |
| GET | `/news/web` | `page`, `per_page` | Articles d'actualité (liens vers le site d'origine) |
| GET | `/news/videos` | `page`, `per_page` | Vidéos YouTube |

```json
{
  "data": {
    "date": "2026-11-25",
    "quotidiens": [
      {
        "id": 43096,
        "titre": "Fraternité Matin",
        "source": { "id": 1, "designation": "Fraternité Matin" },
        "type": "quotidien",
        "image_url": "https://pratik-ci.com/images/ImagesNews/586652026-11-251795586801.jpeg",
        "date_parution": "2026-11-25",
        "numero": "17512",
        "rang": 1,
        "url_web": "https://pratik-ci.com/news/fraternite-matin-2026-11-25"
      }
    ],
    "hebdomadaires": [],
    "mensuels": [],
    "total": 23
  }
}
```

Article web : `{id, titre, resume, image_url, lien_article, source: {id, designation, logo_url}, date_parution}`.
Vidéo : `{id, titre, youtube_id, embed_url, vignette_url, publie_le}`.

### Papiers administratifs

| Méthode | Chemin | Paramètres | Description |
|---|---|---|---|
| GET | `/papiers-administratifs` | `commune` (facultatif) | Démarches nationales + liens propres à la ville choisie |

```json
{
  "data": {
    "nationaux": [
      { "id": 3, "titre": "Passeport en Côte d'Ivoire", "description": "…", "lien": "https://monpasseport.ci/", "image_url": "https://…", "commune_id": null, "pieces": ["Extrait de naissance", "Certificat de nationalité"] }
    ],
    "commune": { "id": 9, "designation": "Cocody", "lien": "cocody" },
    "locaux": [],
    "communes_disponibles": [ { "id": 9, "designation": "Cocody", "lien": "cocody", "abidjan": true } ]
  }
}
```

### Immobilier

Seules les annonces **validées** par l'équipe Pratik sont listées.

| Méthode | Chemin | Paramètres | Description |
|---|---|---|---|
| GET | `/immobilier/categories` | — | Appartement, villa, terrain… avec `nombre_biens` |
| GET | `/immobilier/biens` | `transaction` (`vente`/`location`), `categorie` (ID), `commune`, `prix_min`, `prix_max` (FCFA), `chambres` (minimum), `luxe` (`1`), `tri` (`recent`, `prix_asc`, `prix_desc`), `page`, `per_page` | Annonces paginées |
| GET | `/immobilier/biens/{lien}` | — | Fiche complète, photos et annonceur |
| GET | `/immobilier/annonceurs` | `type` (`agence`, `promoteur`, `amenageur`) | Professionnels ayant au moins une annonce publiée |

Élément de liste (`BienResource`) :

```json
{
  "id": 2470,
  "titre": "Vente d'un appartement de 5 pièces à la Riviera Golf",
  "lien": "vente-dun-appartement-de-05-pieces-a-la-rivera-golf-99138",
  "transaction": "vente",
  "type_annonceur": "agence",
  "categorie": { "id": 1, "designation": "Appartement" },
  "commune": { "id": 9, "designation": "Cocody", "lien": "cocody", "abidjan": true },
  "prix": 140000000,
  "prix_affiche": "140 000 000 FCFA",
  "surface_m2": "220",
  "pieces": 5,
  "chambres": 4,
  "salles_de_bain": 4,
  "luxe": false,
  "image_url": "https://pratik-ci.com/images/ImagesImmobiliers/ILL215652026-11-101794303650.jpeg",
  "statut": "publie",
  "publie_le": "2026-11-10",
  "url_web": "https://pratik-ci.com/immobilier/proprietes-vente-dun-appartement-…"
}
```

- `prix` vaut `null` quand le prix est « sur demande ».
- `prix_affiche` est déjà formaté (` / mois` pour une location).

La fiche complète ajoute : `matricule`, `prix_m2`, `salons`, `etat`, `age`, `adresse`, `latitude`, `longitude`, `description`, `video`, `video_3d`, `equipements` (objet `{piscine: true, garage: false, …}`), `equipements_liste` (libellés prêts à afficher), `photos` (`[{id, url}]`), `vues` et `annonceur` (`{id, entreprise_id, nom, logo_url, telephones, emails, whatsapp, lien_annuaire}`).

Une annonce non validée renvoie `404`, sauf pour son auteur ou un administrateur connecté.

Annonceur : `{id, entreprise_id, nom, lien, logo_url, commune, telephones, emails, nombre_biens}`.

### Statistiques de clic

Quand l'utilisateur touche « Appeler » ou « Écrire » sur une fiche, prévenez l'API : ces chiffres alimentent les statistiques des annonceurs.

| Méthode | Chemin | Description |
|---|---|---|
| POST | `/stats/{entreprise_id}/appel` | Compte un appel → `204` |
| POST | `/stats/{entreprise_id}/email` | Compte un e-mail → `204` |

Pour un bien immobilier, utilisez `annonceur.entreprise_id`. Pour une fiche annuaire, utilisez `id`.

---

## 4. Espace annonceur 🔒 (rôle `pro` ou `admin`)

Un visiteur reçoit `403`. Un annonceur ne voit et ne modifie que **ses** annonces : sur l'annonce d'un autre, l'API répond `403`.

| Méthode | Chemin | Description |
|---|---|---|
| GET | `/pro/biens` | Mes annonces (`?statut=publie` ou `en_attente`), paginées |
| POST | `/pro/biens` | Créer une annonce (multipart) |
| GET | `/pro/biens/{id}` | Détail d'une de mes annonces |
| PUT | `/pro/biens/{id}` | Modifier (JSON, ou multipart via `POST` + `_method=PUT`) |
| DELETE | `/pro/biens/{id}` | Supprimer l'annonce, ses photos et ses fichiers → `204` |
| DELETE | `/pro/biens/{id}/photos/{photo_id}` | Retirer une photo de la galerie → `204` |
| GET | `/pro/statistiques` | Vues, appels, e-mails (`?annee=2026`) |
| GET | `/pro/messages` | Demandes reçues des internautes, paginées |

### Champs d'une annonce

| Champ | Création | Type | Notes |
|---|---|---|---|
| `titre` | obligatoire | texte, 5 à 300 caractères | sert aussi à construire le lien de l'annonce |
| `type_id` | obligatoire | 1, 2, 3 ou 4 | 1 agence location, 2 agence vente, 3 promoteur, 4 aménageur foncier |
| `categorie_id` | obligatoire | ID | voir `/immobilier/categories` |
| `commune_id` | obligatoire | ID | voir `/communes` |
| `prix`, `prix_m2` | | texte | les espaces et points sont acceptés (`"120 000 000"`) |
| `surface` | | texte | en m² |
| `pieces`, `chambres`, `salles_de_bain`, `salons`, `age` | | entier | |
| `etat`, `adresse`, `description` | | texte | |
| `latitude`, `longitude` | | nombre | |
| `video` | | URL | |
| `equipements[]` | | liste | `piscine`, `jardin`, `parking`, `garage`, `meublee`, `ascenseur`, `groupe_electrogene`, `video_surveillance`, `guerite`, `interphone`, `chauffe_eau`, `buanderie`, `baie_vitree`, `split`, `salle_gym`, `stockage`, `eau`, `electricite` |
| `illustration` | | image jpg/png/webp, 8 Mo max | photo principale ; la remplacer supprime l'ancienne |
| `photos[]` | | images, 15 max | ajoutées à la galerie |

En modification, n'envoyez que les champs à changer. Si `equipements` est envoyé, il remplace toute la liste.

Une nouvelle annonce est créée avec `statut: "en_attente"`. Elle devient visible après validation par l'équipe Pratik, et la réponse contient un `message` à afficher à l'utilisateur.

Exemple Dart pour créer une annonce avec photos :

```dart
final requete = http.MultipartRequest('POST', Uri.parse('$baseUrl/pro/biens'))
  ..headers.addAll({'Accept': 'application/json', 'Authorization': 'Bearer $jeton'})
  ..fields.addAll({
    'titre': 'Villa duplex 5 pièces à Angré',
    'type_id': '2',
    'categorie_id': '5',
    'commune_id': '9',
    'prix': '120000000',
    'chambres': '4',
    'equipements[0]': 'piscine',
    'equipements[1]': 'garage',
  })
  ..files.add(await http.MultipartFile.fromPath('illustration', facade.path))
  ..files.add(await http.MultipartFile.fromPath('photos[]', salon.path))
  ..files.add(await http.MultipartFile.fromPath('photos[]', cuisine.path));

final reponse = await http.Response.fromStream(await requete.send()); // 201 attendu
```

Statistiques :

```json
{
  "data": {
    "annee": 2026,
    "mois_en_cours": { "vues": 152, "appels": 12, "emails": 4 },
    "total_annee": { "vues": 1840, "appels": 97, "emails": 31 },
    "par_mois": [ { "mois": 1, "vues": 120, "appels": 8, "emails": 2 }, "… 12 mois" ],
    "biens": { "publies": 14, "en_attente": 2 }
  }
}
```

Message reçu : `{id, nom, telephone, email, objet, message, reponse, lu, recu_le}`. La réponse de `/pro/messages` contient aussi `meta.non_lus`.

---

## 5. Erreurs

Toutes les erreurs sont en JSON avec un champ `message`.

| Code | Signification | Que faire |
|---|---|---|
| `401` | Jeton absent, invalide, expiré ou révoqué | Renvoyer vers l'écran de connexion |
| `403` | Connecté mais pas autorisé (visiteur sur `/pro`, annonce d'un autre) | Afficher `message` |
| `404` | Ressource introuvable (fiche, annonce non validée, commune inconnue) | Afficher un état vide |
| `422` | Données invalides | Afficher `errors.<champ>[0]` sous le champ concerné |
| `429` | Trop de requêtes | Réessayer après le délai indiqué dans l'en-tête `Retry-After` |
| `500` | Erreur serveur | Message générique + nouvel essai |

Exemple de `422` :

```json
{
  "message": "Le champ latitude doit être un nombre. (et 1 autre erreur)",
  "errors": {
    "lat": ["Le champ latitude doit être un nombre."],
    "lng": ["Le champ longitude est obligatoire."]
  }
}
```

Le `401` renvoie aujourd'hui le texte anglais du framework (`"Unauthenticated."`) : basez-vous sur le code HTTP, pas sur le texte.
