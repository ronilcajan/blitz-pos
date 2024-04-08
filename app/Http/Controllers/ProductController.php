<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Gate::authorize('viewAny', ProductCategory::class);
        $products = Product::query()
            ->with(['store', 'stocks','category'])
            ->orderBy('id', 'ASC')
            ->filter(request(['search','store']))
            ->paginate($request->per_page ? ($request->per_page == 'All' ? Product::count() : $request->per_page) : 10)
            ->withQueryString()
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'sku' => $product->sku,
                    'size' => $product->size,
                    'dimension' => $product->dimension,
                    'unit' => $product->unit,
                    'product_type' => $product->product_type,
                    'brand' => $product->brand,
                    'manufacturer' => $product->manufacturer,
                    'description' => $product->description,
                    'image' => $product->image,
                    'store' => $product->store->name,
                    'category' => $product->category->name,
                    'unit_price' => $product->stocks->max('unit_price'),
                    'mark_up_price' => $product->stocks->max('mark_up_price'),
                    'sell_price' => $product->stocks->max('sell_price'),
                    'min_quantity' => $product->stocks->max('min_quantity'),
                    'in_store' => $product->stocks->sum('in_store'),
                    'in_warehouse' =>  $product->stocks->sum('in_warehouse'),
                ];
        });
        return inertia('Product/Index', [
            'title' => 'Products',
            'products' => $products,
            'stores' => Store::select('id', 'name')->get(),
            'product_categories' => ProductCategory::select('id', 'name')->get(),
            'filter' => $request->only(['search']),
            'per_page' => $request->only(['per_page'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function bulkDelete(Request $request)
    {
        // Gate::authorize('bulk_delete', ProductCategory::class);

        Product::whereIn('id',$request->products_id)->delete();
        return redirect()->back();
    }

    public function destroy(Product $product)
    {
        // Gate::authorize('delete', $product_category);
        Product::find($product->id)->delete();
        return redirect()->back();
    }
}
