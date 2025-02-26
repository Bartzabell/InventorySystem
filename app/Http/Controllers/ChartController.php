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

            // Get current year's sales data
            $currentYearData = $this->getMonthlySalesData($currentYear);

            // Get previous year's sales data
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

    /**
     * Get monthly sales data for a specific year
     *
     * @param int $year
     * @return array
     */
    private function getMonthlySalesData($year)
    {
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

        // Fill in zeros for months with no sales
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $monthlySales[$i] ?? 0;
        }

        return $data;
    }

    /**
     * Get monthly item sales by item_code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItemSales(Request $request)
    {
        try {
            Log::info("ChartController::getItemSales() called");
            $year = $request->input('year', now()->year);
            // Get the month names for labels
            $labels = [];
            for ($i = 1; $i <= 12; $i++) {
                $labels[] = date('M', mktime(0, 0, 0, $i, 1));
            }
            // Get items sold by month, grouped by item_id with inventory relationship
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

            // Group by item_code from inventory relationship
            $itemsData = [];
            foreach ($itemSales as $sale) {
                // Get item_code from the inventory relationship where inventory.id = sale.item_id
                $itemCode = $sale->inventory->item_code;

                if (!isset($itemsData[$itemCode])) {
                    $itemsData[$itemCode] = array_fill(0, 12, 0);
                }
                // Arrays are 0-indexed, but months are 1-indexed
                $itemsData[$itemCode][$sale->month - 1] = (float) $sale->total;
            }

            // Format for ApexCharts
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

    // Keep the sample data method for fallback if needed
    private function getSampleData()
    {
        $labels = [];
        for ($i = 1; $i <= 12; $i++) {
            $labels[] = date('M', mktime(0, 0, 0, $i, 1));
        }
        $currentYear = now()->year;
        return response()->json([
            'series' => [
                [
                    'name' => (string) ($currentYear - 1),
                    'data' => [5000, 7000, 6000, 8000, 9000, 8500, 10000, 9500, 8800, 10500, 11000, 12000]
                ],
                [
                    'name' => (string) $currentYear,
                    'data' => [6000, 8000, 7500, 9000, 10000, 9500, 11000, 10500, 9800, 11500, 12000, 0]
                ]
            ],
            'labels' => $labels,
            'note' => 'Sample data generated by controller'
        ]);
    }
}
