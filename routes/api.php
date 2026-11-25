<?php

use App\Http\Controllers\Api\V1\AccueilController;
use App\Http\Controllers\Api\V1\AnnuaireController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ImmobilierController;
use App\Http\Controllers\Api\V1\LancementController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\PapierController;
use App\Http\Controllers\Api\V1\PharmacieController;
use App\Http\Controllers\Api\V1\Pro\BienController;
use App\Http\Controllers\Api\V1\Pro\MessageController;
use App\Http\Controllers\Api\V1\Pro\StatistiqueController;
use App\Http\Controllers\Api\V1\ReferenceController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Api\V1\VolsController;
use Illuminate\Support\Facades\Route;

/*
| API mobile (application Flutter) — préfixe /api/v1, authentification Passport
| (jetons d'accès personnels, en-tête "Authorization: Bearer <jeton>").
| Documentation : docs/API.md
*/

Route::prefix('v1')->name('api.')->group(function () {
    Route::get('/ping', fn () => ['ok' => true, 'version' => 'v1'])->name('ping');

    // --- Authentification ---
    Route::prefix('auth')->name('auth.')->middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    });

    Route::middleware(['auth:api', 'throttle:api'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [MeController::class, 'show'])->name('me');
        Route::put('/me', [MeController::class, 'update'])->name('me.update');
        Route::put('/me/password', [MeController::class, 'password'])->name('me.password');
    });

    // --- Données publiques ---
    Route::get('/accueil', AccueilController::class)->middleware('throttle:api')->name('accueil');
    Route::get('/lancement', LancementController::class)->middleware('throttle:api')->name('lancement');
    Route::get('/vols', VolsController::class)->middleware('throttle:api')->name('vols');
    Route::post('/immobilier/biens/{lien}/contact', [ContactController::class, 'bien'])->middleware('throttle:formulaires')->name('contact.bien');
    Route::post('/annuaire/entreprises/{lien}/contact', [ContactController::class, 'entreprise'])->middleware('throttle:formulaires')->name('contact.entreprise');
    Route::middleware('throttle:api')->group(function () {
        Route::get('/communes', [ReferenceController::class, 'communes'])->name('communes');

        Route::prefix('annuaire')->name('annuaire.')->group(function () {
            Route::get('/categories', [AnnuaireController::class, 'categories'])->name('categories');
            Route::get('/categories/{lien}/sous-categories', [AnnuaireController::class, 'sousCategories'])->name('sous-categories');
            Route::get('/sous-categories/{id}/entreprises', [AnnuaireController::class, 'entreprisesParSousCategorie'])->whereNumber('id')->name('entreprises');
            Route::get('/recherche', [AnnuaireController::class, 'recherche'])->name('recherche');
            Route::get('/entreprises/{lien}', [AnnuaireController::class, 'show'])->name('entreprise');
        });

        Route::get('/pharmacies/garde', [PharmacieController::class, 'garde'])->name('pharmacies.garde');
        Route::get('/pharmacies/garde/proximite', [PharmacieController::class, 'proximite'])->name('pharmacies.proximite');

        Route::get('/news/titrologie', [NewsController::class, 'titrologie'])->name('news.titrologie');
        Route::get('/news/web', [NewsController::class, 'web'])->name('news.web');
        Route::get('/news/videos', [NewsController::class, 'videos'])->name('news.videos');

        Route::get('/papiers-administratifs', [PapierController::class, 'index'])->name('papiers');

        Route::prefix('immobilier')->name('immobilier.')->group(function () {
            Route::get('/categories', [ImmobilierController::class, 'categories'])->name('categories');
            Route::get('/biens', [ImmobilierController::class, 'index'])->name('biens');
            Route::get('/biens/{lien}', [ImmobilierController::class, 'show'])->name('bien');
            Route::get('/annonceurs', [ImmobilierController::class, 'annonceurs'])->name('annonceurs');
        });

        Route::post('/stats/{entreprise}/{type}', [StatsController::class, 'store'])
            ->whereNumber('entreprise')
            ->whereIn('type', ['appel', 'email'])
            ->name('stats');
    });

    // --- Espace annonceur (comptes pro immobilier) ---
    Route::prefix('pro')->name('pro.')->middleware(['auth:api', 'pro', 'throttle:api'])->group(function () {
        Route::get('/biens', [BienController::class, 'index'])->name('biens.index');
        Route::post('/biens', [BienController::class, 'store'])->name('biens.store');
        Route::get('/biens/{bien}', [BienController::class, 'show'])->whereNumber('bien')->name('biens.show');
        Route::put('/biens/{bien}', [BienController::class, 'update'])->whereNumber('bien')->name('biens.update');
        Route::delete('/biens/{bien}', [BienController::class, 'destroy'])->whereNumber('bien')->name('biens.destroy');
        Route::delete('/biens/{bien}/photos/{photo}', [BienController::class, 'destroyPhoto'])->whereNumber(['bien', 'photo'])->name('biens.photos.destroy');
        Route::get('/statistiques', StatistiqueController::class)->name('statistiques');
        Route::get('/messages', MessageController::class)->name('messages');
        Route::get('/messages/{id}', [MessageController::class, 'show'])->whereNumber('id')->name('messages.show');
        Route::post('/messages/{id}/repondre', [MessageController::class, 'repondre'])->whereNumber('id')->name('messages.repondre');
    });
});
