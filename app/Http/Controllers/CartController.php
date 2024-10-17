<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    // Récupérer tous les paniers de l'utilisateur connecté
    public function index()
{
    $userId = Auth::id(); // Récupérer l'ID de l'utilisateur connecté

    // Vérifier si l'utilisateur est authentifié
    if (!$userId) {
        return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
    }

    // Récupérer tous les paniers de l'utilisateur avec les détails des produits
    $carts = Cart::where('user_id', $userId)->with('produit')->get();

    // Vérifier si l'utilisateur a des paniers
    if ($carts->isEmpty()) {
        return response()->json(['message' => 'Aucun panier trouvé pour cet utilisateur.'], 404);
    }

    // Regrouper les paniers par produit_id et additionner les quantités
    $groupedCarts = $carts->groupBy('produit_id')->map(function ($group) {
        $uri = "http://localhost:8000/api/produits/".$group->first()->produit->id;
        return [
            'id' => $group->first()->produit->id, // ID du produit
            'nom_produit' => $group->first()->produit->libelle, // Nom du produit
            'URI' => $uri, // URI
            'quantite' => $group->sum('quantite'), // Somme des quantités
        ];
    });

    return response()->json([
        'message' => 'Paniers récupérés avec succès.',
        'paniers' => $groupedCarts->values()->all(), // Retourner les valeurs sous forme de tableau
    ], 200);
}


    // Ajouter un produit au panier
    public function store(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
        }

        try {
            $validatedData = $request->validate([
                'produit_id' => 'required|exists:produits,id',
                'quantite' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Erreur de validation.',
                'details' => $e->errors(),
            ], 422);
        }

        $cart = Cart::create([
            'user_id' => $userId,
            'produit_id' => $validatedData['produit_id'],
            'quantite' => $validatedData['quantite'],
        ]);

        return response()->json([
            'message' => 'Produit ajouté au panier avec succès.',
            'panier' => $cart,
        ], 201);
    }

    // Récupérer un panier spécifique
    public function show($id)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
        }

        $cart = Cart::where('user_id', $userId)->with('produit')->find($id);

        if (!$cart) {
            return response()->json(['error' => 'Panier non trouvé ou ne vous appartient pas.'], 404);
        }
        $uri = "http://localhost:8000/api/produits/".$cart->produit->id;
        return response()->json([
            'message' => 'Panier récupéré avec succès.',
            'panier' => [
                'id' => $cart->id,
                'nom_produit' => $cart->produit->libelle, // Remplacer 'libelle' par le champ correct de Produit
                'quantite' => $cart->quantite,
                'URI' => $uri,
            ],
        ], 200);
    }

    // Mettre à jour un panier existant
    public function update(Request $request, $id)
    {
        $userId = Auth::id(); // ID de l'utilisateur connecté

        // Vérifier si l'utilisateur est authentifié
        if (!$userId) {
            return response()->json(['error' => 'Utilisateur non authentifié.'], 401);
        }

        // Récupérer le panier et vérifier s'il appartient à l'utilisateur connecté
        $cart = Cart::where('id', $id)->where('user_id', $userId)->first();

        // Si le panier n'est pas trouvé ou n'appartient pas à l'utilisateur
        if (!$cart) {
            return response()->json(['error' => 'Panier non trouvé ou ne vous appartient pas.'], 404);
        }

        // Si des données à mettre à jour sont envoyées
        if ($request->isMethod('patch') || $request->isMethod('put')) {
            $cart->fill($request->all())->save(); // Remplir les champs avec les données envoyées et sauvegarder

            return response()->json([
                'data' => $cart,
                'message' => 'Modification effectuée avec succès',
                'status' => true
            ], 200);
        }

        // Si aucune donnée n'a été envoyée pour la mise à jour
        return response()->json([
            'message' => 'Aucune donnée à mettre à jour',
            'status' => false
        ], 400);
    }


    // Supprimer un panier

    public function destroy($id)
    {
        $categorie = Cart::where('id', $id)->first();

        $categorie->delete();

        return response()->json(['message' => "Produit supprimé au panier",'status' => true],200);
    }
}
