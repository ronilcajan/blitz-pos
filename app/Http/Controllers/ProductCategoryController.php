<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ProductCategory::class);

        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? ProductCategory::count() : $request->per_page)
        : 10;

        $product_categories = ProductCategory::query()
            ->with(['store'])
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'store' => $category->store->name,
                    'created_at' => $category->created_at->tz(session('timezone'))->format('M d, Y h:i: A'),
                ];
        });

        return inertia('ProductCategory/Index', [
            'title' => 'Products Category',
            'product_categories' => $product_categories,
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
        Gate::authorize('create', ProductCategory::class);

        $data = $request->validate([
            'name' => 'required',
            'description' => ''
        ]);

        $data['store_id'] = auth()->user()->store_id ?? 1;

        ProductCategory::create($data);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $category = ProductCategory::find($request->id);
        Gate::authorize('create', $category);

        $data = $request->validate([
            'name' => 'required',
            'description' => ''
        ]);

        $category->update($data);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function bulkDelete(Request $request)
    {
        Gate::authorize('bulk_delete', ProductCategory::class);

        ProductCategory::whereIn('id',$request->categories_id)->delete();
        return redirect()->back();
    }

    public function destroy(ProductCategory $product_category)
    {
        Gate::authorize('delete', $product_category);
        ProductCategory::find($product_category->id)->delete();
        return redirect()->back();
    }
}
