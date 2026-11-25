<?php

use App\Http\Controllers\Immobilier\AnnonceurController;
use App\Http\Controllers\Immobilier\BienController;
use App\Http\Controllers\Immobilier\ContactController;
use App\Http\Controllers\Immobilier\HomeController;
use App\Http\Controllers\Immobilier\LegacyController;
use App\Http\Controllers\Immobilier\PageController;
use App\Http\Controllers\Immobilier\Pro\BienController as ProBienController;
use App\Http\Controllers\Immobilier\Pro\DashboardController;
use App\Http\Controllers\Immobilier\Pro\MessageController;
use App\Http\Controllers\Immobilier\Pro\ProfilController;
use App\Http\Controllers\Immobilier\Pro\StatistiqueController;
use Illuminate\Support\Facades\Route;

/*
| Sous-site Pratik Immobilier (ouvert dans un nouvel onglet depuis le site principal).
| Les URLs publiques reprennent celles de l'ancien site ; les anciennes variantes
| sont redirigées en 301 en bas de fichier.
*/

Route::prefix('immobilier')->name('immo.')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    Route::get('/achats', [BienController::class, 'index'])->defaults('transaction', 'vente')->name('achats');
    Route::get('/locations', [BienController::class, 'index'])->defaults('transaction', 'location')->name('locations');
    Route::get('/immobilier-de-luxe', [BienController::class, 'luxe'])->name('luxe');

    Route::get('/proprietes-{lien}', [BienController::class, 'show'])->where('lien', '[^/]+')->name('bien');
    Route::post('/proprietes-{lien}/contact', [ContactController::class, 'annonceur'])
        ->where('lien', '[^/]+')
        ->middleware('throttle:formulaires')
        ->name('bien.contact');

    Route::get('/agences', [AnnonceurController::class, 'index'])->defaults('categorie', 'agences')->name('agences');
    Route::get('/promoteurs', [AnnonceurController::class, 'index'])->defaults('categorie', 'promoteurs')->name('promoteurs');
    Route::get('/amenageurs-fonciers', [AnnonceurController::class, 'index'])->defaults('categorie', 'amenageurs')->name('amenageurs');
    Route::get('/annonceur/{lien}', [AnnonceurController::class, 'show'])->where('lien', '[^/]+')->name('annonceur');

    Route::get('/contact', [ContactController::class, 'create'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:formulaires')->name('contact.store');
    Route::get('/financement', [PageController::class, 'financement'])->name('financement');

    // Espace annonceur
    Route::prefix('espace-pro')->name('pro.')->middleware(['auth', 'pro'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/biens', [ProBienController::class, 'index'])->name('biens.index');
        Route::get('/biens/nouveau', [ProBienController::class, 'create'])->name('biens.create');
        Route::post('/biens', [ProBienController::class, 'store'])->name('biens.store');
        Route::get('/biens/{id}/modifier', [ProBienController::class, 'edit'])->whereNumber('id')->name('biens.edit');
        Route::put('/biens/{id}', [ProBienController::class, 'update'])->whereNumber('id')->name('biens.update');
        Route::delete('/biens/{id}', [ProBienController::class, 'destroy'])->whereNumber('id')->name('biens.destroy');
        Route::delete('/biens/{id}/photos/{photo}', [ProBienController::class, 'destroyPhoto'])->whereNumber(['id', 'photo'])->name('biens.photos.destroy');

        Route::get('/profil', [ProfilController::class, 'edit'])->name('profil');
        Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{id}', [MessageController::class, 'show'])->whereNumber('id')->name('messages.show');
        Route::post('/messages/{id}/reponse', [MessageController::class, 'repondre'])->whereNumber('id')->name('messages.repondre');

        Route::get('/statistiques', StatistiqueController::class)->name('statistiques');
    });
});

/*
|--------------------------------------------------------------------------
| Anciennes URLs (301)
|--------------------------------------------------------------------------
*/

// Recherches de l'ancien site
Route::get('/immobilier/biens-en-vente', [LegacyController::class, 'achats']);
Route::get('/immobilier/recherche-biens-ventes', [LegacyController::class, 'achats']);
Route::get('/immobilier/proprietes', [LegacyController::class, 'achats']);
Route::get('/immobilier/rechercheProprietes', [LegacyController::class, 'achats']);
Route::get('/immobilier/all_ventes', [LegacyController::class, 'achats']);
Route::get('/immobilier/rechercheProprietesAgence', [LegacyController::class, 'achats']);
Route::get('/immobilier/biens-en-location', [LegacyController::class, 'locations']);
Route::get('/immobilier/recherche-biens-locations', [LegacyController::class, 'locations']);
Route::get('/immobilier/all_lovations', [LegacyController::class, 'locations']);
Route::get('/immobilier/rechercheTerrains', [LegacyController::class, 'terrains']);
Route::get('/immobilier/rechercheProprietesPromoteur', [LegacyController::class, 'promoteurs']);
Route::get('/immobilier/rechercheProprietesPromoteurLux', [LegacyController::class, 'luxe']);
Route::get('/Pratik-Immobilier', [LegacyController::class, 'achats']);
Route::get('/search/immobilier/ia', [LegacyController::class, 'achats']);

// Annonceurs
Route::get('/immobilier/agence-{id}', [AnnonceurController::class, 'legacyEntreprise'])->whereNumber('id');
Route::get('/immobilier-agence/{id}', [AnnonceurController::class, 'legacyUtilisateur'])->whereNumber('id');
Route::get('/immobilier-promoteur/{id}', [AnnonceurController::class, 'legacyUtilisateur'])->whereNumber('id');
Route::get('/immobilier/promoteurs-{lien}', [AnnonceurController::class, 'legacyLien'])->where('lien', '[^/]+');
Route::redirect('/immobilier-promoteurs-biens', '/immobilier/promoteurs', 301);
Route::redirect('/immobilier/amenageurs_foncier', '/immobilier/amenageurs-fonciers', 301);
Route::redirect('/immobilier/Amenagements-fonciers', '/immobilier/amenageurs-fonciers', 301);

// Luxe
Route::get('/immobilier/immobilier-de-luxe/{lien}', [LegacyController::class, 'pageLuxe'])->where('lien', '[^/]+');

// Pages diverses
Route::redirect('/immobiliers', '/immobilier', 301);
Route::redirect('/immobiliers/financement', '/immobilier/financement', 301);
Route::redirect('/immobiliers/agence', '/immobilier/agences', 301);
Route::get('/immobiliers/{any}', fn () => redirect()->route('immo.home', [], 301))->where('any', '.*');

// Espace annonceur de l'ancien site
Route::redirect('/immobilier/Mes_Proprietes', '/immobilier/espace-pro/biens', 301);
Route::redirect('/immobilier/formulaire_proprietes', '/immobilier/espace-pro/biens/nouveau', 301);
Route::redirect('/immobilier/statistiques', '/immobilier/espace-pro/statistiques', 301);
Route::redirect('/immobilier/profile', '/immobilier/espace-pro/profil', 301);
Route::redirect('/inbox', '/immobilier/espace-pro/messages', 301);
Route::get('/inbox/reponse/{id}', fn (int $id) => redirect()->route('immo.pro.messages.show', $id, 301))->whereNumber('id');
