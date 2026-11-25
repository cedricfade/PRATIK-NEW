<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q'));

        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role_id', $request->integer('role')))
            ->when($recherche !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$recherche}%")->orWhere('email', 'like', "%{$recherche}%")))
            ->with('entreprise:ID,TITRE')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('id')->get(['id', 'name']),
            'recherche' => $recherche,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->estSuperAdmin(), 403, 'Seul un super administrateur peut créer un administrateur.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(10)],
            'role_id' => ['required', Rule::in([config('pratik.roles.super_admin'), config('pratik.roles.admin')])],
        ]);

        User::create($data + ['active' => 1, 'user_created' => (string) $request->user()->id]);

        return back()->with('success', 'Administrateur créé.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'active' => ['nullable', 'boolean'],
        ]);

        $rolesAdmin = [config('pratik.roles.super_admin'), config('pratik.roles.admin')];
        $toucheAdmin = in_array((int) $data['role_id'], $rolesAdmin, true) || $user->estAdmin();

        if ($toucheAdmin && ! $request->user()->estSuperAdmin()) {
            return back()->with('error', 'Seul un super administrateur peut attribuer ou retirer un rôle administrateur.');
        }

        if ($user->is($request->user()) && (! in_array((int) $data['role_id'], $rolesAdmin, true) || ! $request->boolean('active'))) {
            return back()->with('error', 'Vous ne pouvez pas retirer vos propres droits ni désactiver votre compte.');
        }

        $user->update(['role_id' => (int) $data['role_id'], 'active' => $request->boolean('active') ? 1 : 0]);

        return back()->with('success', 'Compte de '.$user->name.' mis à jour.');
    }

    public function password(Request $request, User $user): RedirectResponse
    {
        if ($user->estAdmin() && ! $request->user()->estSuperAdmin() && ! $user->is($request->user())) {
            return back()->with('error', 'Seul un super administrateur peut changer le mot de passe d\'un autre administrateur.');
        }

        $request->validate(['password' => ['required', 'string', Password::min(8)]]);
        $user->update(['password' => $request->input('password')]);

        return back()->with('success', 'Mot de passe de '.$user->name.' modifié.');
    }
}
