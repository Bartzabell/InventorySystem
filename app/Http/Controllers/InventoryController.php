<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        //FOR TABLE PAGINATION AND SEARCH
        $inventories = Inventory::query()
            ->when($search, function ($query, $search) {
                return $query->where('item_code', 'like', "%{$search}%")
                    ->orWhere('item_description', 'like', "%{$search}%")
                    ->orWhere('item_price', 'like', "%{$search}%")
                    ->orWhere('item_qty', 'like', "%{$search}%");
            })
            ->paginate(5)
            ->appends($request->query()); // Retain search query in pagination links

        return Inertia::render('Inventory/Index', [
            'inventories' => $inventories,
            'filters' => $request->only('search') // Retain search term in frontend
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_code' => 'required|string|max:255',
            'item_description' => 'required|string|max:255',
            'item_qty' => 'required|integer',
            'item_price' => 'required|numeric',
        ]);

        Inventory::create([
            'item_code' => $request->item_code,
            'item_description' => $request->item_description,
            'item_qty' => $request->item_qty,
            'item_price' => $request->item_price,
            'total_amount' =>($request->item_price * $request->item_qty),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('inventory.index');
    }

    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'item_code' => 'required|string|max:255',
            'item_description' => 'required|string|max:255',
            'item_qty' => 'required|integer',
            'item_price' => 'required|numeric',
        ]);

        $inventory->update([
            'item_code' => $request->item_code,
            'item_description' => $request->item_description,
            'item_qty' => $request->item_qty,
            'item_price' => $request->item_price,
            'total_amount' => ($request->item_price * $request->item_qty),
            'updated_by' => Auth::id(),
        ]);
        return redirect()->route('inventory.index');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index');
    }
}
