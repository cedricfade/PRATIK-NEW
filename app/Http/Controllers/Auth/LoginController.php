<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.login', ['espace' => $request->routeIs('immo.*') ? 'immo' : 'site']);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->orderByDesc('id')->first();

        if ($user && ! $user->estActif()) {
            throw ValidationException::withMessages(['email' => 'Ce compte est désactivé. Contactez Pratik pour le réactiver.']);
        }

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Adresse e-mail ou mot de passe incorrect.']);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->destination($request->user(), $request));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $depuisImmo = str_contains((string) url()->previous(), '/immobilier');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($depuisImmo ? 'immo.home' : 'home');
    }

    private function destination(User $user, Request $request): string
    {
        if ($user->estAdmin()) {
            return route('admin.dashboard');
        }

        if ($user->estPro() || $request->routeIs('immo.*')) {
            return $user->estPro() ? route('immo.pro.dashboard') : route('immo.home');
        }

        return route('home');
    }
}
