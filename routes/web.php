<?php

use App\Http\Controllers\Site\AnnuaireController;
use App\Http\Controllers\Site\FormulaireController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\NewsController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PapierController;
use App\Http\Controllers\Site\PharmacieController;
use App\Http\Controllers\Site\SitemapController;
use App\Http\Controllers\Site\StatController;
use App\Http\Controllers\Site\VolsController;
use Illuminate\Support\Facades\Route;

/*
| Site public principal (pratik-ci.com).
| Les URLs reprennent celles de l'ancien site pour conserver le référencement Google.
| Les anciennes variantes sont redirigées (301) dans routes/legacy.php.
*/

Route::get('/', HomeController::class)->name('home');

// --- Annuaire ---------------------------------------------------------------
Route::get('/annuaire', [AnnuaireController::class, 'index'])->name('annuaire.index');
Route::get('/annuaire-recherche', [AnnuaireController::class, 'recherche'])->name('recherche');
// Les liens historiques contiennent parfois "/", "&" ou des accents : on accepte tout.
Route::get('/details/{lien}', [AnnuaireController::class, 'fiche'])->where('lien', '.+')->name('annuaire.fiche');
Route::post('/contact-entreprise/{entreprise}', [AnnuaireController::class, 'contacter'])
    ->whereNumber('entreprise')
    ->middleware('throttle:formulaires')
    ->name('annuaire.contact');
// Les catégories ont des liens "slug" : la contrainte laisse passer /annuaire/siteannuaire.xml vers legacy.php.
Route::get('/annuaire/{cat}', [AnnuaireController::class, 'categorie'])->where('cat', '[A-Za-z0-9_-]+')->name('annuaire.categorie');
Route::get('/annuaire/{cat}/{sous}', [AnnuaireController::class, 'sousCategorie'])
    ->where(['cat' => '[A-Za-z0-9_-]+', 'sous' => '[^/]+'])
    ->name('annuaire.sous-categorie');

// --- Pharmacies de garde -----------------------------------------------------
Route::get('/pharmacies-de-garde', [PharmacieController::class, 'index'])->name('pharmacies.index');
Route::match(['get', 'post'], '/pharmacies-de-garde/autour-de-moi', [PharmacieController::class, 'autourDeMoi'])->name('pharmacies.autour');
Route::post('/pharmacie-proximite', [PharmacieController::class, 'autourDeMoi']);
Route::get('/pharmacies-de-garde/{lien}', [PharmacieController::class, 'commune'])->name('pharmacies.commune');

// --- News ---------------------------------------------------------------------
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/archives-news', [NewsController::class, 'index'])->name('news.archives');
Route::get('/news-web', [NewsController::class, 'web'])->name('news.web');
Route::get('/news-web/{lien}', [NewsController::class, 'article'])->where('lien', '.+')->name('news.article');
Route::get('/news-videos', [NewsController::class, 'videos'])->name('news.videos');
Route::get('/news/{lien}', [NewsController::class, 'une'])->where('lien', '(?!sitenews\.xml$).+')->name('news.une');

// --- Services pratiques -------------------------------------------------------
Route::get('/papier-administratif/{lien?}', PapierController::class)->name('papiers.index');
Route::get('/horaires-des-vols', VolsController::class)->name('vols.index');

// --- Formulaires ---------------------------------------------------------------
Route::get('/se-faire-connaitre', [FormulaireController::class, 'souscription'])->name('souscription');
Route::post('/se-faire-connaitre', [FormulaireController::class, 'enregistrerSouscription'])->middleware('throttle:formulaires')->name('souscription.store');
Route::get('/contact', [FormulaireController::class, 'contact'])->name('contact');
Route::post('/contact', [FormulaireController::class, 'enregistrerContact'])->middleware('throttle:formulaires')->name('contact.store');

// --- Pages légales ---------------------------------------------------------------
Route::get('/conditions-utilisation', [PageController::class, 'cgu'])->name('cgu');
Route::get('/regles-de-confidentialite', [PageController::class, 'confidentialite'])->name('confidentialite');

// --- Statistiques des annonceurs (clics "appeler" / "écrire") ---------------------
// Utilisées aussi par le module Immobilier : ne pas renommer.
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/stats/appel/{entreprise}', [StatController::class, 'appel'])->whereNumber('entreprise')->name('stats.appel');
    Route::post('/stats/email/{entreprise}', [StatController::class, 'email'])->whereNumber('entreprise')->name('stats.email');
});

// --- SEO ------------------------------------------------------------------------
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemaps/pages.xml', [SitemapController::class, 'pages']);
Route::get('/sitemaps/annuaire-rubriques.xml', [SitemapController::class, 'rubriques']);
Route::get('/sitemaps/annuaire-{tranche}.xml', [SitemapController::class, 'annuaire'])->whereNumber('tranche');
Route::get('/sitemaps/immobilier.xml', [SitemapController::class, 'immobilier']);
Route::get('/sitemaps/news.xml', [SitemapController::class, 'news']);
Route::get('/robots.txt', [SitemapController::class, 'robots']);
