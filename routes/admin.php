<?php

use App\Http\Controllers\Admin\AnnonceurController;
use App\Http\Controllers\Admin\AppMobileController;
use App\Http\Controllers\Admin\ApparenceController;
use App\Http\Controllers\Admin\BienController;
use App\Http\Controllers\Admin\CategorieController;
use App\Http\Controllers\Admin\CommuneController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EntrepriseController;
use App\Http\Controllers\Admin\GardeController;
use App\Http\Controllers\Admin\ImmoReferentielController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\NewsWebController;
use App\Http\Controllers\Admin\PapierController;
use App\Http\Controllers\Admin\PeriodeGardeController;
use App\Http\Controllers\Admin\PharmacieController;
use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\Admin\SousCategorieController;
use App\Http\Controllers\Admin\TitrologieController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\VuesController;
use Illuminate\Support\Facades\Route;

/*
| Backoffice — préfixe /admin, noms "admin.*", middlewares web + auth + admin
| (voir bootstrap/app.php).
*/

Route::get('/', DashboardController::class)->name('dashboard');

// --- Pharmacies de garde ---
Route::get('/gardes', [GardeController::class, 'index'])->name('gardes.index');
Route::post('/gardes', [GardeController::class, 'store'])->name('gardes.store');
Route::delete('/gardes/{garde}', [GardeController::class, 'destroy'])->whereNumber('garde')->name('gardes.destroy');
Route::delete('/gardes/periode/{periode}', [GardeController::class, 'vider'])->whereNumber('periode')->name('gardes.vider');

Route::get('/periodes', [PeriodeGardeController::class, 'index'])->name('periodes.index');
Route::post('/periodes', [PeriodeGardeController::class, 'store'])->name('periodes.store');
Route::put('/periodes/{periode}', [PeriodeGardeController::class, 'update'])->whereNumber('periode')->name('periodes.update');
Route::delete('/periodes/{periode}', [PeriodeGardeController::class, 'destroy'])->whereNumber('periode')->name('periodes.destroy');

Route::get('/pharmacies', [PharmacieController::class, 'index'])->name('pharmacies.index');

// --- Annuaire ---
Route::get('/entreprises/recherche.json', [EntrepriseController::class, 'lookup'])->name('entreprises.lookup');
Route::resource('entreprises', EntrepriseController::class)->except('show')->whereNumber('entreprise');
Route::post('/entreprises/{entreprise}/photos', [EntrepriseController::class, 'storePhotos'])->whereNumber('entreprise')->name('entreprises.photos.store');
Route::delete('/entreprises/{entreprise}/photos/{photo}', [EntrepriseController::class, 'destroyPhoto'])->whereNumber(['entreprise', 'photo'])->name('entreprises.photos.destroy');
Route::post('/entreprises/{entreprise}/thematiques', [EntrepriseController::class, 'storeThematique'])->whereNumber('entreprise')->name('entreprises.thematiques.store');
Route::delete('/entreprises/{entreprise}/thematiques/{thematique}', [EntrepriseController::class, 'destroyThematique'])->whereNumber(['entreprise', 'thematique'])->name('entreprises.thematiques.destroy');
Route::post('/entreprises/{entreprise}/departements', [EntrepriseController::class, 'storeDepartement'])->whereNumber('entreprise')->name('entreprises.departements.store');
Route::delete('/entreprises/{entreprise}/departements/{departement}', [EntrepriseController::class, 'destroyDepartement'])->whereNumber(['entreprise', 'departement'])->name('entreprises.departements.destroy');

Route::resource('categories', CategorieController::class)->only(['index', 'store', 'update', 'destroy'])->parameters(['categories' => 'categorie'])->whereNumber('categorie');
Route::resource('sous-categories', SousCategorieController::class)->only(['index', 'store', 'edit', 'update', 'destroy'])->parameters(['sous-categories' => 'sousCategorie'])->whereNumber('sousCategorie');
Route::resource('communes', CommuneController::class)->only(['index', 'store', 'update', 'destroy'])->whereNumber('commune');

// --- Immobilier ---
Route::resource('biens', BienController::class)->only(['index', 'edit', 'update', 'destroy'])->whereNumber('bien');
Route::patch('/biens/{bien}/validation', [BienController::class, 'validation'])->whereNumber('bien')->name('biens.validation');
Route::patch('/biens/{bien}/drapeau/{drapeau}', [BienController::class, 'drapeau'])->whereNumber('bien')->whereIn('drapeau', ['FLASH', 'LUXE'])->name('biens.drapeau');
Route::delete('/biens/{bien}/photos/{photo}', [BienController::class, 'destroyPhoto'])->whereNumber(['bien', 'photo'])->name('biens.photos.destroy');

Route::get('/annonceurs', [AnnonceurController::class, 'index'])->name('annonceurs.index');
Route::post('/annonceurs', [AnnonceurController::class, 'store'])->name('annonceurs.store');
Route::patch('/annonceurs/{user}/activation', [AnnonceurController::class, 'activation'])->whereNumber('user')->name('annonceurs.activation');
Route::put('/annonceurs/{user}/mot-de-passe', [AnnonceurController::class, 'password'])->whereNumber('user')->name('annonceurs.password');

Route::get('/immo-referentiels', [ImmoReferentielController::class, 'index'])->name('immo-referentiels.index');
Route::post('/immo-referentiels/{table}', [ImmoReferentielController::class, 'store'])->whereIn('table', ['categories', 'types'])->name('immo-referentiels.store');
Route::put('/immo-referentiels/{table}/{id}', [ImmoReferentielController::class, 'update'])->whereIn('table', ['categories', 'types'])->whereNumber('id')->name('immo-referentiels.update');
Route::delete('/immo-referentiels/{table}/{id}', [ImmoReferentielController::class, 'destroy'])->whereIn('table', ['categories', 'types'])->whereNumber('id')->name('immo-referentiels.destroy');

// --- News ---
Route::get('/titrologie', [TitrologieController::class, 'index'])->name('titrologie.index');
Route::post('/titrologie', [TitrologieController::class, 'store'])->name('titrologie.store');
Route::delete('/titrologie/{news}', [TitrologieController::class, 'destroy'])->whereNumber('news')->name('titrologie.destroy');

Route::get('/news-web', [NewsWebController::class, 'index'])->name('news-web.index');
Route::post('/news-web', [NewsWebController::class, 'store'])->name('news-web.store');
Route::delete('/news-web/{news}', [NewsWebController::class, 'destroy'])->whereNumber('news')->name('news-web.destroy');

Route::resource('videos', VideoController::class)->only(['index', 'store', 'update', 'destroy'])->whereNumber('video');

Route::get('/sources', [SourceController::class, 'index'])->name('sources.index');
Route::post('/sources/{type}', [SourceController::class, 'store'])->whereIn('type', ['presse', 'web'])->name('sources.store');
Route::put('/sources/{type}/{id}', [SourceController::class, 'update'])->whereIn('type', ['presse', 'web'])->whereNumber('id')->name('sources.update');
Route::delete('/sources/{type}/{id}', [SourceController::class, 'destroy'])->whereIn('type', ['presse', 'web'])->whereNumber('id')->name('sources.destroy');

// --- Contenus ---
// --- Vues affichées (augmentation en masse, VIP, immobilier, au cas par cas) ---
Route::get('/vues', [VuesController::class, 'index'])->name('vues.index');
Route::post('/vues/ajouter', [VuesController::class, 'ajouter'])->name('vues.ajouter');
Route::post('/vues/remise-a-zero', [VuesController::class, 'remettreAZero'])->name('vues.zero');

// --- Apparence : heros des pages d'accueil, chiffres ---
Route::get('/apparence/application', [AppMobileController::class, 'edit'])->name('apparence.application');
Route::put('/apparence/application', [AppMobileController::class, 'update'])->name('apparence.application.update');
Route::post('/apparence/application/pubs', [AppMobileController::class, 'storePub'])->name('apparence.pubs.store');
Route::put('/apparence/application/pubs/{pub}', [AppMobileController::class, 'updatePub'])->whereNumber('pub')->name('apparence.pubs.update');
Route::delete('/apparence/application/pubs/{pub}', [AppMobileController::class, 'destroyPub'])->whereNumber('pub')->name('apparence.pubs.destroy');
Route::get('/apparence', [ApparenceController::class, 'index'])->name('apparence.index');
Route::put('/apparence/{page}', [ApparenceController::class, 'update'])->whereIn('page', ['accueil', 'immobilier'])->name('apparence.update');

Route::resource('papiers', PapierController::class)->only(['index', 'store', 'edit', 'update', 'destroy'])->whereNumber('papier');

// --- Relation clients ---
Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
Route::delete('/messages/{type}/{id}', [MessageController::class, 'destroy'])->whereIn('type', ['contacts', 'entreprises'])->whereNumber('id')->name('messages.destroy');
Route::delete('/messages/{type}', [MessageController::class, 'destroyMany'])->whereIn('type', ['contacts', 'entreprises'])->name('messages.destroy-many');

Route::get('/utilisateurs', [UserController::class, 'index'])->name('users.index');
Route::post('/utilisateurs', [UserController::class, 'store'])->name('users.store');
Route::put('/utilisateurs/{user}', [UserController::class, 'update'])->whereNumber('user')->name('users.update');
Route::put('/utilisateurs/{user}/mot-de-passe', [UserController::class, 'password'])->whereNumber('user')->name('users.password');
