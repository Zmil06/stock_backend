<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Sale;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('product')->with('supplier')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Liste des ventes récupérée avec succès',
            'data' => $sales->items(),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'sale_date' => 'required|date',
            'selling_price' => 'required|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
            'total_value' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sale = Sale::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Vente créée avec succès',
            'data' => $sale
        ], 201);    
    }

    public function show($id)
    {
        $sale = Sale::with('product')->find($id);
        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'Vente non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vente récupérée avec succès',
            'data' => $sale
        ], 200);
    }


    public function update(Request $request, $id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'Vente non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'sometimes|required|exists:products,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'sale_date' => 'sometimes|required|date',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'buying_price' => 'sometimes|required|numeric|min:0',
            'total_value' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sale->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Vente mise à jour avec succès',
            'data' => $sale
        ], 200);
    }

    public function destroy($id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'Vente non trouvée'
            ], 404);
        }

        $sale->delete();
        return response()->json([
            'success' => true,
            'message' => 'Vente supprimée avec succès'
        ], 200);
    }
}
