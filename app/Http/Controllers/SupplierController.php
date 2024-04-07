<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequestForm;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Supplier::class);

        $suppliers = Supplier::query()
            ->with('store')
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store']))
            ->paginate($request->per_page ? ($request->per_page == 'All' ? Supplier::count()->get() : $request->per_page) : 10)
            ->withQueryString()
            ->through(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'contact_person' => $supplier->contact_person,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                    'address' => $supplier->address,
                    'logo' => $supplier->logo,
                    'store' => $supplier->store->name,
                    'created_at' => $supplier->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('Suppliers/Index', [
            'title' => "Suppliers",
            'suppliers' => $suppliers,
            'stores' => Store::select('id', 'name')->get(),
            'filter' => $request->only(['search','store','per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $this->authorize('create', Supplier::class);

        return inertia('Suppliers/Create', [
            'title' => "Add New Supplier",
            'stores' => Store::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequestForm $request)
    {
        // Gate::authorize('create',  User::class);

        $validate = $request->validated();

        if($request->hasFile('logo')){
            $logo = $request->file('logo')->store('suppliers','public');
            $validate['logo'] = asset('storage/'. $logo);
        }
        
        Supplier::create($validate);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        // $this->authorize('update', $supplier);

        $data = [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'contact_person' => $supplier->contact_person,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'address' => $supplier->address,
            'store_id' => $supplier->store->id ?? '',
            'logo' => $supplier->logo,
        ];

        return inertia('Suppliers/Edit', [
            'title' => "Edit Supplier",
            'supplier' => $data,
            'stores' => Store::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequestForm $request)
    {
        $supplier = Supplier::find($request->id);

        // $this->authorize('update', $supplier);

        $validate = $request->validated();

        if($request->hasFile('logo')){
            $logo = $request->file('logo')->store('suppliers','public');
            $validate['logo'] = asset('storage/'. $logo);
        }
        
        $supplier->update($validate);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function bulkDelete(Request $request)
    {
        // Gate::authorize('bulk_delete', User::class);

        Supplier::whereIn('id',$request->suppliers_id)->delete();
        return redirect()->back();
    }

    public function destroy(Supplier $supplier)
    {
        // Gate::authorize('delete', $supplier);

        $supplier->delete();
        return redirect()->back();
    }
}
