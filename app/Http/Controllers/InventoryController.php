<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSupplier;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Gate::authorize('viewAny', Product::class);
        $products = Product::query()
        ->leftJoin('product_supplier', 'products.id', '=', 'product_supplier.product_id')
        ->with(['store', 'category', 'stocks.supplier']) // Load productSuppliers relationship
        ->filter(request(['search', 'store', 'category', 'supplier']))
        ->orderBy('products.name', 'ASC')
            ->paginate($request->per_page ? ($request->per_page == 'All' ?  ProductSupplier::count() : $request->per_page) : 10)
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
                    'category' => $product->category?->name,
                    'supplier' => $product->stock?->supplier->name,
                    'unit_price' => $product->unit_price,
                    'mark_up_price' => $product->mark_up_price,
                    'retail_price' => $product->retail_price,
                    'min_quantity' => $product->min_quantity,
                    'in_store' => $product->in_store,
                    'in_warehouse' => $product->in_warehouse,
                ];
        });

        return inertia('Inventory/Index', [
            'title' => 'Inventory',
            'products' => $products,
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'suppliers' => Supplier::select('id', 'name')->orderBy('name','ASC')->get(),
            'product_categories' => ProductCategory::select('id', 'name')->orderBy('name','ASC')
                ->get(),
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
