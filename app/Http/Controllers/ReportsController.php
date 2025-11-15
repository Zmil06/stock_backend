<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Helpers de période
     * - period=month|year|all|custom
     * - custom: start=YYYY-MM-DD&end=YYYY-MM-DD
     */
    private function resolvePeriod(Request $request): array
    {
        $period = $request->query('period', 'month'); // month|year|all|custom
        $today  = Carbon::today();

        if ($period === 'all') {
            return [null, null, 'all'];
        }

        if ($period === 'custom') {
            $start = $request->query('start');
            $end   = $request->query('end');
            $start = $start ? Carbon::parse($start)->startOfDay() : null;
            $end   = $end   ? Carbon::parse($end)->endOfDay()     : null;
            return [$start, $end, 'custom'];
        }

        if ($period === 'year') {
            $start = $today->copy()->startOfYear();
            $end   = $today->copy()->endOfYear();
            return [$start, $end, 'year'];
        }

        // default month
        $start = $today->copy()->startOfMonth();
        $end   = $today->copy()->endOfMonth();
        return [$start, $end, 'month'];
    }

    /** Période précédente (pour MoM/YoY et % d’augmentation) */
    private function previousRange(Carbon $start = null, Carbon $end = null, string $mode = 'month'): array
    {
        if ($start === null || $end === null) {
            return [null, null];
        }
        if ($mode === 'year') {
            return [$start->copy()->subYear(), $end->copy()->subYear()];
        }
        if ($mode === 'month') {
            $diffDays = $start->diffInDays($end) + 1;
            $pEnd   = $start->copy()->subDay();
            $pStart = $pEnd->copy()->subDays($diffDays - 1)->startOfDay();
            return [$pStart, $pEnd];
        }
        // custom → même durée décalée juste avant
        $diffDays = $start->diffInDays($end) + 1;
        $pEnd   = $start->copy()->subDay();
        $pStart = $pEnd->copy()->subDays($diffDays - 1)->startOfDay();
        return [$pStart, $pEnd];
    }

    /** Somme revenue / cost sur période (Sales) */
    private function salesAggregates(?Carbon $start, ?Carbon $end): array
    {
        $q = Sale::query();
        if ($start && $end) $q->whereBetween('sale_date', [$start, $end]);
        $revenue = (clone $q)->sum(DB::raw('selling_price * quantity'));
        $cost    = (clone $q)->sum(DB::raw('buying_price * quantity'));
        return [$revenue, $cost, $revenue - $cost];
    }

    /** Somme order_value des achats reçus (Delivered) */
    private function purchasesValue(?Carbon $start, ?Carbon $end): float
    {
        $q = PurchaseOrder::query()->where('status', 'Delivered');
        if ($start && $end) $q->whereBetween('order_date', [$start, $end]);
        return (float) $q->sum('order_value');
    }

    /**
     * GET /api/reports/overview
     * - Total Profit, Revenue, Sales(cost), Net purchase value, Net sales value
     * - MoM Profit %, YoY Profit %
     * Params: ?period=month|year|all|custom&start=...&end=...
     */
    public function overview(Request $request)
    {
        [$start, $end, $mode] = $this->resolvePeriod($request);

        // Courant (période choisie)
        [$revenue, $cogs, $profit] = $this->salesAggregates($start, $end);
        $netPurchase = $this->purchasesValue($start, $end);
        $netSales    = $revenue;

        // MoM / YoY (variation)
        [$pStart, $pEnd] = $this->previousRange($start, $end, $mode);
        [$prevRevenue, $prevCogs, $prevProfit] = $this->salesAggregates($pStart, $pEnd);

        $mom = ($prevProfit != 0) ? round((($profit - $prevProfit) / $prevProfit) * 100, 2) : null;

        // YoY : même période l’an dernier
        $yoStart = $start ? $start->copy()->subYear() : null;
        $yoEnd   = $end   ? $end->copy()->subYear()   : null;
        [$yoRevenue, $yoCogs, $yoProfit] = $this->salesAggregates($yoStart, $yoEnd);
        $yoy = ($yoProfit != 0) ? round((($profit - $yoProfit) / $yoProfit) * 100, 2) : null;

        return response()->json([
            'period' => [
                'mode'  => $mode,
                'start' => $start?->toDateString(),
                'end'   => $end?->toDateString(),
            ],
            'overview' => [
                'total_profit'       => (float) $profit,
                'revenue'            => (float) $revenue,
                'sales_cost'         => (float) $cogs,            // “Sales” de la maquette = coût des ventes
                'net_purchase_value' => (float) $netPurchase,     // achats (fournisseurs)
                'net_sales_value'    => (float) $netSales,        // chiffre d’affaires
                'mom_profit_pct'     => $mom,                     // variation du profit vs période précédente
                'yoy_profit_pct'     => $yoy,                     // variation du profit vs année précédente
            ],
        ]);
    }

    /**
     * GET /api/reports/best-categories?limit=3&period=month|year|...&start=&end=
     * Top catégories par CA + % d’évolution vs période précédente
     */
    public function bestCategories(Request $request)
    {
        $limit = (int) $request->query('limit', 3);
        [$start, $end, $mode] = $this->resolvePeriod($request);
        [$pStart, $pEnd] = $this->previousRange($start, $end, $mode);

        // CA par catégorie (période courante)
        $current = DB::table('sales')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(sales.selling_price * sales.quantity) as turnover')
            )
            ->when($start && $end, fn($q) => $q->whereBetween('sale_date', [$start, $end]))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('turnover')
            ->limit($limit)
            ->get();

        // CA période précédente (pour %)
        $prev = DB::table('sales')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                DB::raw('SUM(sales.selling_price * sales.quantity) as turnover')
            )
            ->when($pStart && $pEnd, fn($q) => $q->whereBetween('sale_date', [$pStart, $pEnd]))
            ->groupBy('categories.id')
            ->get()
            ->keyBy('id');

        $data = $current->map(function ($row) use ($prev) {
            $prevTurnover = (float) ($prev[$row->id]->turnover ?? 0);
            $inc = ($prevTurnover != 0)
                ? round((($row->turnover - $prevTurnover) / $prevTurnover) * 100, 2)
                : null;
            return [
                'category' => $row->name,
                'turnover' => (float) $row->turnover,
                'increase_pct' => $inc,
            ];
        });

        return response()->json([
            'period' => [
                'start' => $start?->toDateString(),
                'end'   => $end?->toDateString(),
            ],
            'items' => $data,
        ]);
    }

    /**
     * GET /api/reports/profit-vs-revenue?year=2025
     * Série mensuelle (12 mois) : revenue, cost, profit
     */
    public function profitVsRevenue(Request $request)
    {
        $year = (int) $request->query('year', Carbon::today()->year);

        $monthly = DB::table('sales')
            ->select(
                DB::raw('MONTH(sale_date) as m'),
                DB::raw('SUM(selling_price * quantity) as revenue'),
                DB::raw('SUM(buying_price * quantity)  as cost')
            )
            ->whereYear('sale_date', $year)
            ->groupBy('m')
            ->orderBy('m')
            ->get();

        // Index par mois pour sortie 1..12
        $map = [];
        foreach ($monthly as $r) {
            $map[(int) $r->m] = ['revenue' => (float) $r->revenue, 'cost' => (float) $r->cost];
        }

        $out = [];
        for ($m = 1; $m <= 12; $m++) {
            $rev = $map[$m]['revenue'] ?? 0.0;
            $cost = $map[$m]['cost'] ?? 0.0;
            $out[] = [
                'month'   => Carbon::createFromDate($year, $m, 1)->format('M'),
                'revenue' => $rev,
                'profit'  => $rev - $cost,
            ];
        }

        return response()->json([
            'year' => $year,
            'series' => $out,
        ]);
    }

    /**
     * GET /api/reports/best-products?limit=5&period=...&start=&end=
     * Top produits (CA), avec catégorie & stock restant, + % d’évolution
     */
    public function bestProducts(Request $request)
    {
        $limit = (int) $request->query('limit', 5);
        [$start, $end, $mode] = $this->resolvePeriod($request);
        [$pStart, $pEnd] = $this->previousRange($start, $end, $mode);

        // CA par produit (courant)
        $current = DB::table('sales')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(sales.quantity) as sold_qty'),
                DB::raw('SUM(sales.selling_price * sales.quantity) as turnover')
            )
            ->when($start && $end, fn($q) => $q->whereBetween('sale_date', [$start, $end]))
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderByDesc('turnover')
            ->limit($limit)
            ->get();

        // CA par produit (période précédente)
        $prev = DB::table('sales')
            ->select(
                'product_id',
                DB::raw('SUM(selling_price * quantity) as turnover')
            )
            ->when($pStart && $pEnd, fn($q) => $q->whereBetween('sale_date', [$pStart, $pEnd]))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Récup stock restant
        $productIds = $current->pluck('product_id')->all();
        $stocks = Product::whereIn('id', $productIds)
            ->pluck('quantity', 'id');

        $data = $current->map(function ($row) use ($prev, $stocks) {
            $prevTurnover = (float) ($prev[$row->product_id]->turnover ?? 0);
            $inc = ($prevTurnover != 0)
                ? round((($row->turnover - $prevTurnover) / $prevTurnover) * 100, 2)
                : null;
            return [
                'product_id'        => $row->product_id,
                'product'           => $row->product_name,
                'category'          => $row->category_name,
                'remaining_quantity' => (int) ($stocks[$row->product_id] ?? 0),
                'sold_quantity'     => (int) $row->sold_qty,
                'turnover'          => (float) $row->turnover,
                'increase_pct'      => $inc,
            ];
        });

        return response()->json([
            'period' => [
                'start' => $start?->toDateString(),
                'end'   => $end?->toDateString(),
            ],
            'items' => $data,
        ]);
    }
}
