<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/** Authentification de l'application mobile : jetons d'accès personnels Passport. */
class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
            'device_name' => ['nullable', 'string', 'max:100'],
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

        return response()->json($this->jeton($user, $data['device_name'] ?? null), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ], [], ['password' => 'mot de passe']);

        // La table historique peut contenir des doublons d'e-mail : on prend le compte le plus récent dont le mot de passe correspond.
        $user = User::where('email', $data['email'])
            ->orderByDesc('id')
            ->get()
            ->first(fn (User $u) => Hash::check($data['password'], $u->password));

        if (! $user) {
            throw ValidationException::withMessages(['email' => 'Adresse e-mail ou mot de passe incorrect.']);
        }

        if (! $user->estActif()) {
            throw ValidationException::withMessages(['email' => 'Ce compte est désactivé. Contactez Pratik pour le réactiver.']);
        }

        if (Hash::needsRehash($user->password)) {
            $user->forceFill(['password' => $data['password']])->save();
        }

        return response()->json($this->jeton($user, $data['device_name'] ?? null));
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            report($e); // serveur d'e-mail indisponible : même réponse, l'erreur est journalisée
        }

        return response()->json([
            'message' => 'Si un compte existe pour cette adresse, un lien de réinitialisation vient d\'être envoyé.',
        ]);
    }

    public function logout(Request $request): Response
    {
        $request->user()->token()?->revoke();

        return response()->noContent();
    }

    /** @return array<string, mixed> */
    private function jeton(User $user, ?string $appareil): array
    {
        $resultat = $user->createToken($appareil ?: 'Application mobile');

        return [
            'token' => $resultat->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => now()->addSeconds((int) $resultat->expiresIn)->toIso8601String(),
            'user' => new UserResource($user),
        ];
    }
}
