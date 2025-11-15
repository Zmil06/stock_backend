<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::with('product')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Liste des fournisseurs récupérée avec succès',
            'data' => $suppliers->items(),
            'meta' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'takes_back_returns' => 'required|boolean',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $supplier = Supplier::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur créé avec succès',
            'data' => $supplier
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Fournisseur non trouvé'
            ], 404);

            return response()->json([
                'success' => true,
                'message' => 'Fournisseur récupéré avec succès',
                'data' => $supplier
            ], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Fournisseur non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'takes_back_returns' => 'sometimes|required|boolean',
            'address' => 'sometimes|nullable|string',
            'logo' => 'sometimes|nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $supplier->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur mis à jour avec succès',
            'data' => $supplier
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Fournisseur non trouvé'
            ], 404);
        }

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur supprimé avec succès'
        ], 200);
    }
}
