<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            // Générer un token pour l'utilisateur
            $token = $user->createToken('ClientToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'token' => $token,
                'user' => $user
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Connexion via API (connexion d'un user)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Authentification
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('ClientToken')->plainTextToken;

            return response()->json([
                'message' => 'Connexion réussie.',
                'token' => $token,
                'user' => $user
            ], 200);
        }

        return response()->json([
            'message' => 'Identifiants incorrects.',
        ], 401);
    }

    public function logout(Request $request)
    {
        // Révoquer le token de l'utilisateur authentifié
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ], 200);
    }
}
