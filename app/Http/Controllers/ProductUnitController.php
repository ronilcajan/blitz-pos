<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Gate::authorize('viewAny', Expenses::class);

        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? ProductUnit::count() : $request->per_page)
        : 10;

        $units = ProductUnit::query()
            ->with(['store'])
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'store' => $unit->store->name,
                    'created_at' => $unit->created_at->tz(session('timezone'))->format('M d, Y h:i: A'),
                ];
            });

        return inertia('ProductUnit/Index', [
            'title' => 'Product Units',
            'units' => $units,
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'filters' => $request->only(['search']),
            'per_page' => $request->only(['per_page'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Gate::authorize('create', ExpensesCategory::class);

        $data = $request->validate([
            'name' => 'required'
        ]);

        $data['store_id'] = auth()->user()->store_id ?? 1;

        ProductUnit::create($data);

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $category = ProductUnit::find($request->id);
        // Gate::authorize('create', $category);

        $data = $request->validate([
            'name' => 'required'
        ]);

        $category->update($data);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function bulkDelete(Request $request)
    {
        // Gate::authorize('bulk_delete', ExpensesCategory::class);

        ProductUnit::whereIn('id',$request->units_id)->delete();
        return redirect()->back();
    }

    public function destroy(ProductUnit $product_unit)
    {
        // Gate::authorize('delete', $product_unit);
        ProductUnit::find($product_unit->id)->delete();
        return redirect()->back();
    }
}
