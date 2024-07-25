<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerFormRequest;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Customer::class);

        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? Customer::count() : $request->per_page)
        : 10;

        $customers = Customer::query()
            ->with('store')
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'logo' => $customer->logo,
                    'store' => $customer->store->name,
                    'created_at' => $customer->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('Customers/Index', [
            'title' => "Customers",
            'customers' => $customers,
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'filter' => $request->only(['search','store','per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Customer::class);

        return inertia('Customers/Create', [
            'title' => "Add New Customer",
            'stores' => Store::select('id', 'name')->orderBy('id', 'DESC')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerFormRequest $request)
    {
        Gate::authorize('create', Customer::class);

        $validate = $request->validated();

        if($request->hasFile('logo')){
            $logo = $request->file('logo')->store('customers','public');
            $validate['logo'] = asset('storage/'. $logo);
        }

        Customer::create($validate);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        Gate::authorize('update', $customer);

        $sales = $customer->sales()->latest()->paginate(10);
        
        return inertia('Customers/Show', [
            'title' => "View Customer",
            'customer' => $customer,
            'sales' => $sales,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        Gate::authorize('update', $customer);

        $data = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'logo' => $customer->logo,
        ];

        return inertia('Customers/Edit', [
            'title' => "Edit Customers",
            'customer' => $data,
            'stores' => Store::select('id', 'name')->orderBy('id', 'DESC')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerFormRequest $request)
    {
        $customer = Customer::find($request->id);
        Gate::authorize('update', $customer);

        $validate = $request->validated();

        if($request->hasFile('logo')){
            $logo = $request->file('logo')->store('customers','public');
            $validate['logo'] = asset('storage/'. $logo);
        }

        $customer->update($validate);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function bulkDelete(Request $request)
    {
        Gate::authorize('bulk_delete', Customer::class);

        Customer::whereIn('id',$request->customers_id)->delete();
        return redirect()->back();
    }

    public function destroy(Customer $customer)
    {
        Gate::authorize('delete', $customer);

        $customer->delete();
        return redirect()->back();
    }
}
