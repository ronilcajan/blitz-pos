<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormRequest;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Store::class);

        $transformedStore = Store::query()
            ->orderBy('id', 'DESC')
            ->filter(request(['search']))
            ->paginate($request->per_page ? ($request->per_page == 'All' ? Store::count() : $request->per_page) : 10)
            ->withQueryString()
            ->through(function ($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'address' => $store->address,
                    'contact' => $store->contact,
                    'email' => $store->email,
                    'logo' => $store->logo,
                    'created_at' => $store->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('Store/Index', [
            'title' => "Store",
            'stores' => $transformedStore,
            'filters' => $request->only(['search']),
            'per_page' => $request->only(['per_page'])
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFormRequest $request)
    {
        // $this->authorize('create', Store::class);
       
        $validate = $request->validated();

        if($request->hasFile('logo')){
            $logo = $request->file('logo')->store('store','public');
            $validate['logo'] = asset('storage/'. $logo);
        }
        
        Store::create($validate);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Store $store)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Store $store)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store)
    {
        //
    }
}
