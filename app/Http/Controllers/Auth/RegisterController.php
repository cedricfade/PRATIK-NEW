<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Création d'un compte visiteur (role 7), comme sur l'ancien site. */
class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], ['name' => 'nom', 'telephone' => 'téléphone', 'password' => 'mot de passe']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'agent_cel' => $data['telephone'] ?? null,
            'role_id' => config('pratik.roles.visiteur'),
            'CATEGORIE' => 'Visiteur',
            'active' => 1,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('compte')->with('success', 'Bienvenue sur Pratik ! Votre compte est créé.');
    }
}
