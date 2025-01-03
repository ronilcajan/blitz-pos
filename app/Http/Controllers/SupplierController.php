<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequestForm;
use App\Imports\ImportSupplier;
use App\Models\Delivery;
use App\Models\Store;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Supplier::class);

        $perPage = $request->per_page
        ? (
            $request->per_page == 'All'
            ? Supplier::count()
            : $request->per_page
        )
        : 10;

        $suppliers = Supplier::query()
            ->with('store')
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store']))
            ->paginate($perPage)
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
                    'store' => $supplier->store?->name,
                ];
        });

        return inertia('Suppliers/Index', [
            'title' => "Suppliers",
            'suppliers' => $suppliers,
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'filter' => $request->only(['search','store','per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Supplier::class);

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
        Gate::authorize('create', Supplier::class);

        $validate = $request->validated();

        if($request->hasFile('logo')){
            $logo = $request->file('logo')->store('suppliers','public');
            $validate['logo'] = asset('storage/'. $logo);
        }
        $validate['store_id'] = $request->store_id ?? auth()->user()->store_id;

        Supplier::create($validate);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
       $purchases = $supplier->purchases()->latest()->take(20)->get();
       $deliveries = $supplier->deliveries()->latest()->take(20)->get();

        return inertia('Suppliers/Show', [
            'title' => 'View supplier',
            'supplier' => $supplier,
            'purchases' => $purchases,
            'deliveries' => $deliveries
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        Gate::authorize('update',  $supplier);

        $data = [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'contact_person' => $supplier->contact_person,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'address' => $supplier->address,
            'store_id' => $supplier->store_id ?? auth()->id(),
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
        Gate::authorize('update', $supplier);

        $validate = $request->validated();

        if($request->hasFile('logo')){
            $logo = $request->file('logo')->store('suppliers','public');
            $validate['logo'] = asset('storage/'. $logo);
        }

        $supplier->update($validate);

        return redirect()->back();
    }


    public function import(Request $request)
    {
        try {
            $filename = $request->file('import_file')->store('temp');
            Excel::import(new ImportSupplier, $filename);

            Storage::delete($filename);

            return redirect()->back();
        }
         catch (Exception $e) {
            Log::error('Error recording supploer: ' .$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'An error occurred while importing the products. Please try again.'.$e->getMessage()]);
         }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function bulkDelete(Request $request)
    {
        Gate::authorize('bulk_delete', Supplier::class);

        Supplier::whereIn('id',$request->suppliers_id)->delete();
        return redirect()->back();
    }

    public function destroy(Supplier $supplier)
    {
        Gate::authorize('delete', $supplier);

        $supplier->delete();
        return redirect()->back();
    }
}
