<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // === 1️⃣ Résumé principal du Dashboard ===
    public function summary()
    {
        $now = Carbon::now();
        $last7 = $now->copy()->subDays(7);
        $total_categories = Category::where('created_at', '>=', $last7)->count();
        $total_products   = Product::where('created_at', '>=', $last7)->count();
        $total_suppliers  = Supplier::where('created_at', '>=', $last7)->count();
        $quantity_in_hand = Product::sum('quantity');
        $to_be_received   = PurchaseOrder::where('status', '!=', 'Delivered')->sum('quantity');

        $sales_units   = Sale::where('sale_date', '>=', $last7)->sum('quantity');
        $sales_revenue = Sale::where('sale_date', '>=', $last7)->sum(DB::raw('selling_price * quantity'));
        $sales_cost    = Sale::where('sale_date', '>=', $last7)->sum(DB::raw('buying_price * quantity'));
        $sales_profit  = $sales_revenue - $sales_cost;

        $purchase_orders_count = PurchaseOrder::where('order_date', '>=', $last7)->count();
        $purchase_total_cost   = PurchaseOrder::where('order_date', '>=', $last7)->sum('order_value');
        $purchase_returned     = PurchaseOrder::where('status', 'Returned')->sum('order_value');
        $purchase_on_the_way_count = PurchaseOrder::where('status', 'Returned')->count();
        $purchase_on_the_way   = PurchaseOrder::where('status', 'Out for delivery')->sum('order_value');

        $delayed_orders = PurchaseOrder::where('expected_date', '<', $now)
            ->where('status', '!=', 'Delivered')->count();

        $low_stock_count  = Product::whereColumn('quantity', '<=', 'threshold')->count();
        $out_of_stock_count = Product::where('quantity', 0)->count();

        return response()->json([
            'total_categories' => $total_categories,
            'total_products'   => $total_products,
            'total_suppliers'  => $total_suppliers,
            'quantity_in_hand' => $quantity_in_hand,
            'to_be_received'   => $to_be_received,
            'sales_last7' => [
                'units'   => $sales_units,
                'revenue' => $sales_revenue,
                'profit'  => $sales_profit,
                'cost'    => $sales_cost
            ],
            'purchase_last7' => [
                'orders'     => $purchase_orders_count,
                'cost'       => $purchase_total_cost,
                'returned'  => $purchase_returned,
                'returned_cost' => $purchase_returned,
                'on_the_way'  => $purchase_on_the_way_count,
                'on_the_way_cost' => $purchase_on_the_way,
            ],
            'low_stock_count'  => $low_stock_count,
            'out_of_stock_count' => $out_of_stock_count,
            'delayed_orders' => $delayed_orders
        ]);
    }

    // === 2️⃣ Graphique ventes vs achats ===
    public function salesVsPurchases()
    {
        return response()->json($this->getSalesVsPurchases());
    }

    private function getSalesVsPurchases()
    {
        $currentYear = now()->year;

        // 🔹 Données agrégées en base
        $sales = Sale::select(
            DB::raw("DATE_FORMAT(sale_date, '%Y-%m') as month"),
            DB::raw("SUM(total_value) as sales_total")
        )
            ->whereYear('sale_date', $currentYear)
            ->groupBy('month')
            ->pluck('sales_total', 'month');

        $purchases = PurchaseOrder::select(
            DB::raw("DATE_FORMAT(order_date, '%Y-%m') as month"),
            DB::raw("SUM(order_value) as purchase_total")
        )
            ->whereYear('order_date', $currentYear)
            ->groupBy('month')
            ->pluck('purchase_total', 'month');

        // 🔹 Générer tous les mois de janvier à décembre
        $months = collect(range(1, 12))->map(function ($m) use ($currentYear, $sales, $purchases) {
            $monthKey = sprintf('%d-%02d', $currentYear, $m);

            return [
                'month' => $monthKey,
                'sales' => (float) ($sales[$monthKey] ?? 0),
                'purchases' => (float) ($purchases[$monthKey] ?? 0),
            ];
        });

        return $months->values();
    }

    // === 3️⃣ Graphique commandes passées vs livrées ===
    public function orderSummary()
    {
        $currentYear = now()->year;

        $ordered = PurchaseOrder::select(
            DB::raw("DATE_FORMAT(order_date, '%Y-%m') as month"),
            DB::raw("COUNT(*) as ordered_count")
        )
            ->whereYear('order_date', $currentYear)
            ->groupBy('month')
            ->pluck('ordered_count', 'month');

        $delivered = PurchaseOrder::select(
            DB::raw("DATE_FORMAT(expected_date, '%Y-%m') as month"),
            DB::raw("COUNT(*) as delivered_count")
        )
            ->where('status', 'Delivered')
            ->whereYear('expected_date', $currentYear)
            ->groupBy('month')
            ->pluck('delivered_count', 'month');

        // Générer les 12 mois de l’année
        $months = collect(range(1, 12))->map(function ($m) use ($currentYear, $ordered, $delivered) {
            $monthKey = sprintf('%d-%02d', $currentYear, $m);

            return [
                'month' => $monthKey,
                'ordered' => (int) ($ordered[$monthKey] ?? 0),
                'delivered' => (int) ($delivered[$monthKey] ?? 0),
            ];
        });

        return response()->json($months->values());
    }

    // === 4️⃣ Produits les plus vendus sur les 7 derniers jours ===
    public function topProducts()
    {
        $top_products = Sale::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->where('sale_date', '>=', Carbon::now()->subDays(7)) // 🔥 filtrer sur 7 derniers jours
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->with('product:id,name,quantity,selling_price')
            ->get()
            ->map(function ($item) {
                return [
                    'product'   => $item->product->name,
                    'sold'      => (int) $item->total_sold,
                    'remaining' => (int) $item->product->quantity,
                    'price'     => (float) $item->product->selling_price,
                ];
            });

        return response()->json($top_products);
    }

    // === 5️⃣ Produits à faible stock ===
    public function lowStock()
    {
        $low_stock = Product::whereColumn('quantity', '<=', 'threshold')
            ->take(10)
            ->get(['id', 'name', 'quantity', 'threshold']);
        return response()->json($low_stock);
    }
}
