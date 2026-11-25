<?php

use App\Http\Controllers\Auth\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->middleware('throttle:auth');
    Route::get('/immobilier/connexion', [LoginController::class, 'create'])->name('immo.login');
    Route::post('/immobilier/connexion', [LoginController::class, 'store'])->middleware('throttle:auth');

    Route::get('/inscription', [RegisterController::class, 'create'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'store'])->middleware('throttle:auth');

    Route::get('/mot-de-passe-oublie', [PasswordController::class, 'request'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordController::class, 'email'])->middleware('throttle:auth')->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [PasswordController::class, 'reset'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [PasswordController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/mon-compte', [AccountController::class, 'show'])->name('compte');
    Route::put('/mon-compte', [AccountController::class, 'update'])->name('compte.update');
    Route::put('/mon-compte/mot-de-passe', [AccountController::class, 'password'])->name('compte.password');
});

// Anciennes URLs de connexion
Route::redirect('/Pratik-admin', '/connexion', 301);
Route::redirect('/login', '/connexion', 301);
Route::redirect('/immobilier/Login', '/immobilier/connexion', 301);
Route::get('/logout', fn () => redirect()->route('home'));
