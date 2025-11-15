<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->orderBy('id', 'desc')->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Liste des produits récupérée avec succès',
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ], 200);
    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'buying_price' => 'required|numeric|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'threshold' => 'required|integer|min:0',
                'expiry_date' => 'nullable|date',
                'supplier_id' => 'nullable|exists:suppliers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $product = Product::create($request->all());


            return response()->json([
                'success' => true,
                'message' => 'Produit créé avec succès',
                'data' => $product
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produit récupéré avec succès',
            'data' => $product
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'category_id' => 'sometimes|required|exists:categories,id',
                'buying_price' => 'sometimes|required|numeric|min:0',
                'selling_price' => 'sometimes|nullable|numeric|min:0',
                'quantity' => 'sometimes|required|integer|min:0',
                'threshold' => 'sometimes|required|integer|min:0',
                'expiry_date' => 'sometimes|nullable|date',
                'supplier_id' => 'sometimes|nullable|exists:suppliers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ], 404);
            }

            $product->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Produit mis à jour avec succès',
                'data' => $product
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit supprimé avec succès'
        ], 200);
    }
}
