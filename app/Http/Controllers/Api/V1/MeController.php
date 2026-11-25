<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MeController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(Request $request): UserResource
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'telephone' => ['sometimes', 'nullable', 'string', 'max:50'],
        ], [], ['name' => 'nom', 'telephone' => 'téléphone']);

        if (array_key_exists('telephone', $data)) {
            $data['agent_cel'] = $data['telephone'];
            unset($data['telephone']);
        }

        $user->update($data);

        return new UserResource($user->fresh());
    }

    public function password(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password:api'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], ['current_password' => 'mot de passe actuel', 'password' => 'nouveau mot de passe']);

        $request->user()->update(['password' => $request->input('password')]);

        return response()->json(['message' => 'Mot de passe modifié.']);
    }
}
