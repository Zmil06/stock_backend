<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::all()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Liste des magasins récupérée avec succès',
            'data' => $stores->items(),
            'meta' => [
                'current_page' => $stores->currentPage(),
                'last_page' => $stores->lastPage(),
                'per_page' => $stores->perPage(),
                'total' => $stores->total(),
            ]
        ], 200);
    }

    public function show($id)
    {
        $store = Store::find($id);
        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Commande d\'achat non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande d\'achat récupérée avec succès',
            'data' => $store
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'nullable|string',
            'manager_name' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $store = Store::create($validator);
        return response()->json(['success' => true, 'store' => $store], 201);
    }

    public function update(Request $request, Store $store)
    {
        $store = Store::find($store->id);
        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Magasin non trouvé'
            ], 404);
        }

        $validator =  Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|nullable|string',
            'manager_name' => 'sometimes|nullable|string',
            'phone' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $store->update($request->all());
        return response()->json(['success' => true, 'store' => $store]);
    }

    public function destroy($id)
    {
        $store = Store::find($id);
        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Magasin non trouvé'
            ], 404);
        }

        $store->delete();

        return response()->json([
            'success' => true,
            'message' => 'Magasin supprimé avec succès'
        ], 200);
    }
}
