<?php
namespace App\Http\Controllers;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChartController extends Controller
{
    public function getYearlySales()
    {
        try {
            Log::info("SalesChartController::getYearlySales() called");

            $currentYear = now()->year;
            $previousYear = $currentYear - 1;

            // Get the month names for labels
            $labels = [];
            for ($i = 1; $i <= 12; $i++) {
                $labels[] = date('M', mktime(0, 0, 0, $i, 1));
            }

            // Get current and previous year's sales data
            $currentYearData = $this->getMonthlySalesData($currentYear);
            $previousYearData = $this->getMonthlySalesData($previousYear);

            return response()->json([
                'series' => [
                    [
                        'name' => (string) $previousYear,
                        'data' => $previousYearData
                    ],
                    [
                        'name' => (string) $currentYear,
                        'data' => $currentYearData
                    ]
                ],
                'labels' => $labels
            ]);

        } catch (\Exception $e) {
            Log::error("Error in getYearlySales: " . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'series' => [],
                'labels' => []
            ], 500);
        }
    }

    private function getMonthlySalesData($year)
    {
        $connection = DB::getDefaultConnection();

        if ($connection === 'pgsql') {
            $monthlySales = Sale::select(
                    DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                    DB::raw('SUM(amount_sold) as total')
                )
                ->whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$year])
                ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
                ->orderBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
                ->get()
                ->pluck('total', 'month')
                ->toArray();
        } else {
            $monthlySales = Sale::select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(amount_sold) as total')
                )
                ->whereYear('created_at', $year)
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy(DB::raw('MONTH(created_at)'))
                ->get()
                ->pluck('total', 'month')
                ->toArray();
        }

        // Fill in zeros for months with no sales
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $monthlySales[$i] ?? 0;
        }

        return $data;
    }

    public function getItemSales(Request $request)
    {
        try {
            Log::info("ChartController::getItemSales() called");
            $year = $request->input('year', now()->year);

            $labels = [];
            for ($i = 1; $i <= 12; $i++) {
                $labels[] = date('M', mktime(0, 0, 0, $i, 1));
            }

            $connection = DB::getDefaultConnection();

            if ($connection === 'pgsql') {
                $itemSales = Sale::with('inventory')
                    ->select(
                        'item_id',
                        DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                        DB::raw('SUM(amount_sold) as total')
                    )
                    ->whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$year])
                    ->groupBy('item_id', DB::raw('EXTRACT(MONTH FROM created_at)'))
                    ->orderBy('item_id')
                    ->orderBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
                    ->get();
            } else {
                $itemSales = Sale::with('inventory')
                    ->select(
                        'item_id',
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('SUM(amount_sold) as total')
                    )
                    ->whereYear('created_at', $year)
                    ->groupBy('item_id', DB::raw('MONTH(created_at)'))
                    ->orderBy('item_id')
                    ->orderBy(DB::raw('MONTH(created_at)'))
                    ->get();
            }

            $itemsData = [];
            foreach ($itemSales as $sale) {
                $itemCode = $sale->inventory->item_code;
                if (!isset($itemsData[$itemCode])) {
                    $itemsData[$itemCode] = array_fill(0, 12, 0);
                }
                $itemsData[$itemCode][$sale->month - 1] = (float) $sale->total;
            }

            $series = [];
            foreach ($itemsData as $itemCode => $monthlySales) {
                $series[] = [
                    'name' => $itemCode,
                    'data' => $monthlySales
                ];
            }

            return response()->json([
                'series' => $series,
                'labels' => $labels,
                'year' => $year
            ]);
        } catch (\Exception $e) {
            Log::error("Error in getItemSales: " . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'series' => [],
                'labels' => []
            ], 500);
        }
    }
}
