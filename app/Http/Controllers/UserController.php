<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // Login d'un utilisateur
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Email ou mot de passe incorrect.'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    // Enregistrement d'un nouvel utilisateur
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nom' => 'required|string|max:255',
                'prenoms' => 'required|string|max:255',
                'contact' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation échouée.',
                'details' => $e->errors(),
            ], 422);
        }

        $validatedData['password'] = Hash::make($validatedData['password']);
        $user = User::create($validatedData);
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur enregistré avec succès.',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'Déconnexion réussie']);
    }

    // Liste tous les utilisateurs
    public function index()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'Aucun utilisateur trouvé.'], 404);
        }

        return response()->json([
            'message' => 'Utilisateurs récupérés avec succès.',
            'users' => $users,
        ], 200);
    }

    // Affiche un utilisateur spécifique
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
        }

        return response()->json([
            'message' => 'Utilisateur récupéré avec succès.',
            'user' => $user,
        ], 200);
    }

    // Met à jour un utilisateur

    public function update(Request $request, $id)
    {


        $produit = User::where('id', $id)->first();
        if ($produit) {
            $produit->fill($request->all())->save();
            return response()->json([
                'data' => $produit,
                'message' => 'Modification effectuée avec succès',
                'status' => true
            ], 200);
        }

        return response()->json([
            'message' => 'user inexistant',
            'status' => false
        ], 404);
    }
    // Supprime un utilisateur

    public function destroy($id)
    {
        $categorie = User::where('id', $id)->first();

        $categorie->delete();

        return response()->json(['message' => "Utilisateur supprimé",'status' => true],200);
    }

    // Méthode de recherche d'utilisateurs par nom, prénom ou email

    public function search(Request $request)
    {
        // Récupérer la requête de recherche
        $query = $request->input('q');

        // Vérifier si le champ de recherche est vide
        if (!$query) {
            return response()->json(['error' => 'Le champ de recherche est vide.'], 400);
        }

        $users = User::where(function ($queryBuilder) use ($query) {
            $queryBuilder->where('nom', 'LIKE', "%$query%")
                         ->orWhere('prenoms', 'LIKE', "%$query%")
                         ->orWhere('email', 'LIKE', "%$query%")
                         ->orWhere('contact', 'LIKE', "%$query%");
        })->get();

        // Vérifier si des utilisateurs correspondant à la recherche ont été trouvés
        if ($users->isEmpty()) {
            return response()->json(['message' => 'Aucun utilisateur correspondant trouvé.'], 404);
        }

        return response()->json([
            'message' => 'Utilisateurs correspondants trouvés.',
            'utilisateurs' => $users,
        ], 200);
    }


}
