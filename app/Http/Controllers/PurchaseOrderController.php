<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
      /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseOrder = PurchaseOrder::with(['product', 'supplier'])->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Liste des commandes d\'achat récupérée avec succès',
            'data' => $purchaseOrder->items(),
            'meta' => [
                'current_page' => $purchaseOrder->currentPage(),
                'last_page' => $purchaseOrder->lastPage(),
                'per_page' => $purchaseOrder->perPage(),
                'total' => $purchaseOrder->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
          'product_id' => 'required|exists:products,id',
          'supplier_id' => 'required|exists:suppliers,id',
          'quantity' => 'required|integer|min:1',
          'order_value' => 'required|numeric|min:0',
          'order_date' => 'required|date',
          'expected_date' => 'nullable|date',
          'status' => 'required|string|max:50',
          'received' => 'required|boolean',
          'received_date' => 'nullable|date',
      ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $purchaseOrder = PurchaseOrder::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Commande d\'achat créée avec succès',
            'data' => $purchaseOrder
        ], 201);    
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['product', 'supplier'])->find($id);
        if (!$purchaseOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Commande d\'achat non trouvée'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Commande d\'achat récupérée avec succès',
            'data' => $purchaseOrder
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        if (!$purchaseOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Commande d\'achat non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'sometimes|required|exists:products,id',
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'order_value' => 'sometimes|required|numeric|min:0',
            'order_date' => 'sometimes|required|date',
            'expected_date' => 'nullable|date',
            'status' => 'sometimes|required|string|max:50',
            'received' => 'sometimes|required|boolean',
            'received_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $purchaseOrder->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Commande d\'achat mise à jour avec succès',
            'data' => $purchaseOrder
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        if (!$purchaseOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Commande d\'achat non trouvée'
            ], 404);
        }

        $purchaseOrder->delete();
        return response()->json([
            'success' => true,
            'message' => 'Commande d\'achat supprimée avec succès'
        ], 200);
    }
}
