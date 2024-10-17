<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    // Méthode pour récupérer tous les produits
    public function index()
    {
        $produits = Produit::all();

        if ($produits->isEmpty()) {
            return response()->json(['message' => 'Aucun produit disponible.'], 404);
        }

        return response()->json([
            'message' => 'Produits récupérés avec succès.',
            'produits' => $produits,
        ], 200);
    }

    // Méthode pour créer un nouveau produit
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'libelle' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'required|url',
                'prix' => 'required|numeric',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Erreur de validation des données.',
                'details' => $e->errors(),
            ], 422);
        }

        $product = Produit::create($validatedData);

        return response()->json([
            'message' => 'Produit créé avec succès.',
            'produit' => $product,
        ], 201);
    }

    // Méthode pour afficher un produit spécifique
    public function show($id)
    {
        $product = Produit::find($id);

        if (!$product) {
            return response()->json(['error' => 'Produit non trouvé.'], 404);
        }

        return response()->json([
            'message' => 'Produit récupéré avec succès.',
            'produit' => $product,
        ], 200);
    }

    // Méthode pour mettre à jour un produit
    public function update(Request $request, $id)
    {


        $produit = Produit::where('id', $id)->first();
        if ($produit) {
            $produit->fill($request->all())->save();
            return response()->json([
                'data' => $produit,
                'message' => 'Modification effectuée avec succès',
                'status' => true
            ], 200);
        }

        return response()->json([
            'message' => 'Produit inexistant',
            'status' => false
        ], 404);
    }




    // Méthode pour supprimer un produit
    public function destroy($id)
    {
        $categorie = Produit::where('id', $id)->first();

        $categorie->delete();

        return response()->json(['message' => "Produit supprimé",'status' => true],200);
    }

    // Méthode de recherche des produits par libellé
    public function search(Request $request)
{
    // Récupérer la requête de recherche
    $query = $request->input('q');

    // Vérifier si le champ de recherche est vide
    if (!$query) {
        return response()->json(['error' => 'Le champ de recherche est vide.'], 400);
    }

    // Journaliser la requête pour débogage
    \Log::info("Recherche de produits avec la requête: $query");

    // Rechercher uniquement dans le champ libelle
    $produits = Produit::where('libelle', 'LIKE', "%$query%")
        ->orWhere('description', 'LIKE', "%$query%")
        ->orWhere('prix', 'LIKE', "%$query%")
        ->orWhere('statut', 'LIKE', "%$query%") // Assurez-vous que statut est convertible en chaîne
        ->get();

    // Journaliser le résultat de la recherche
    \Log::info("Produits trouvés: ", $produits->toArray());

    if ($produits->isEmpty()) {
        return response()->json(['message' => 'Aucun produit correspondant trouvé.'], 404);
    }

    return response()->json([
        'message' => 'Produits correspondants trouvés.',
        'produits' => $produits,
    ], 200);
}


}
