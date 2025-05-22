<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    //this index is for TABLE
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        //FOR TABLE PAGINATION AND SEARCH
        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                return $query->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(5)
            ->appends($request->query());

        return Inertia::render('Customer/Index', [
            'customers' => $customers,
            'filters' => $request->only('search')
        ]);
    }

    //this STORE IS FOR CREATE
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone_number' => 'required|string|min:10',
        ]);

        Customer::create([
            'customer_name' => $request->customer_name,
            'contact_person' => $request->contact_person,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'tin_no' =>$request->tin_no,
            'address' =>$request->address,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('customer.index');
    }

    //this UPDATE IS FOR EDIT
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone_number' => 'required|string|min:10',
        ]);

        $customer->update([
            'customer_name' => $request->customer_name,
            'contact_person' => $request->contact_person,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'tin_no' =>$request->tin_no,
            'address' =>$request->address,
            'updated_by' => Auth::id(),
        ]);
        return redirect()->route('customer.index');
    }

    // FOR DELETE
    public function destroy(Customer $inventory)
    {
        $inventory->delete();
        return redirect()->route('customer.index');
    }
}
