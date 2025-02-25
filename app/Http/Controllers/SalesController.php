<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SalesController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $sales = Sale::with('inventory')
            ->when($search, function ($query, $search) {
                return $query->whereHas('inventory', function ($q) use ($search) {
                    $q->where('item_code', 'like', "%{$search}%");
                })
                ->orWhere('customer', 'like', "%{$search}%");
            })
            ->paginate(5)
            ->appends($request->query());

        $inventory = Inventory::select('id', 'item_code', 'item_description', 'item_qty', 'item_price')
            ->where('item_qty', '>', 0)
            ->get();

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'inventory' => $inventory,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:inventories,id',
            'item_qty' => 'required|integer|min:1',
            'customer' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Get inventory item
            $inventory = Inventory::findOrFail($request->item_id);

            // Validate stock availability
            if ($inventory->item_qty < $request->item_qty) {
                return back()->withErrors(['item_qty' => 'Insufficient stock available']);
            }

            // Calculate total amount
            $amount_sold = $inventory->item_price * $request->item_qty;

            // Create sale record
            Sale::create([
                'item_id' => $request->item_id,
                'item_qty' => $request->item_qty,
                'amount_sold' => $amount_sold,
                'customer' => $request->customer,
                'created_by' => Auth::id(),
            ]);

            // Update inventory quantity
            $inventory->update([
                'item_qty' => $inventory->item_qty - $request->item_qty,
                'total_amount' => $inventory->total_amount - $amount_sold,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred while processing the sale']);
        }
    }

    public function destroy(Sale $sale)
    {
        try {
            DB::beginTransaction();

            // Restore inventory quantity
            $inventory = Inventory::findOrFail($sale->item_id);
            $inventory->update([
                'item_qty' => $inventory->item_qty + $sale->item_qty,
                'updated_by' => Auth::id(),
            ]);

            // Delete sale
            $sale->update(['deleted_by' => Auth::id()]);
            $sale->delete();

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale cancelled successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred while cancelling the sale']);
        }
    }
}
