<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| Redirections 301 des anciennes URLs vers les nouvelles,
| pour ne perdre ni visiteurs ni référencement Google.
| Chargé après web.php, admin.php et immobilier.php (voir bootstrap/app.php).
*/

// Pharmacies
Route::redirect('/pharmacieGarde', '/pharmacies-de-garde', 301);

// Annuaire
Route::get('/annuaires/{lien}', fn (string $lien) => redirect()->route('annuaire.fiche', $lien, 301))->where('lien', '.+');
Route::get('/annuaire-recherches-{q}', fn (string $q) => redirect()->route('recherche', ['q' => $q], 301))->where('q', '.+');
Route::get('/recherche', fn (Request $request) => redirect()->route('recherche', array_filter([
    'q' => $request->query('q', $request->query('search_box')),
]), 301));
Route::get('/seachAnnuaire', fn (Request $request) => redirect()->route('recherche', array_filter([
    'q' => $request->query('q', $request->query('search_box')),
]), 301));
Route::redirect('/annuaire-categories', '/annuaire', 301);

// Inscription des entreprises
foreach (['/new/souscription', '/new/se-faire-connaitre', '/soumission', '/formulaire-entreprise'] as $ancienne) {
    Route::redirect($ancienne, '/se-faire-connaitre', 301);
}

// Services
Route::redirect('/Horaires-Avions', '/horaires-des-vols', 301);
Route::redirect('/papierAdministratif', '/papier-administratif', 301);

// Bons plans : module retiré (aucune donnée), on renvoie vers l'accueil.
Route::redirect('/lesbonsplans', '/', 301);
Route::redirect('/bonsplans', '/', 301);
Route::redirect('/Pratik-LesBons-Plans', '/', 301);
Route::get('/bonsplans/{any}', fn () => redirect('/', 301))->where('any', '.*');
Route::get('/bonsplans-{any}', fn () => redirect('/', 301))->where('any', '.*');

// Anciens sitemaps
foreach (['/immobilier/siteimmobilier.xml', '/news/sitenews.xml', '/annuaire/siteannuaire.xml', '/sitemaps', '/site'] as $ancien) {
    Route::redirect($ancien, '/sitemap.xml', 301);
}

// Divers
Route::redirect('/home', '/', 301);

// Ancien backoffice
Route::get('/Pratik-Admin-{any}', fn () => redirect('/admin', 301))->where('any', '.*');
Route::get('/Pratik-admin-{any}', fn () => redirect('/admin', 301))->where('any', '.*');
Route::get('/Pratik-admin/{any}', fn () => redirect('/admin', 301))->where('any', '.*');
