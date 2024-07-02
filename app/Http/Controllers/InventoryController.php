<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFormRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSupplier;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Product::class);

        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? Product::count() : $request->per_page)
        : 10;

        $products = Product::query()
        ->with(['store', 'price', 'stock','category'])
        ->orderBy('name', 'ASC')
        ->filter(request(['search','store','category','type']))
        ->paginate($perPage)
        ->withQueryString()
        ->through(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'size' => $product->size,
                'unit' => $product->unit,
                'barcode' => $product->barcode,
                'sku' => $product->stock?->sku,
                'min_quantity' => $product->stock?->min_quantity,
                'in_store' => $product->stock?->in_store ? Number::format($product->stock->in_store, precision: 2) : null,
                'in_warehouse' => $product->stock?->in_warehouse ? Number::format($product->stock->in_warehouse, precision: 2) : null,
                'image' => $product->image,
                'store' => $product->store->name,
                'price' =>  $product->price?->discount_price ? Number::currency($product->price->discount_price, in: 'PHP') : null,
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
     * Show the form for editing the specified resource.
     */
    public function edit(ProductSupplier $inventory)
    {
        $product = Product::find($inventory->product_id);
        Gate::authorize('update', $product);

         $data = [
            'id' => $inventory->id,
            'name' => $inventory->product->name,
            'barcode' => $inventory->product->barcode,
            'sku' => $inventory->product->sku,
            'size' => $inventory->product->size,
            'dimension' => $inventory->product->dimension,
            'unit' => $inventory->product->unit,
            'product_type' => $inventory->product->product_type,
            'brand' => $inventory->product->brand,
            'manufacturer' => $inventory->product->manufacturer,
            'description' => $inventory->product->description,
            'product_category_id' => $inventory->product->product_category_id,
            'store_id' => $inventory->product->store_id,
            'image' => $inventory->product->image,

            'product_id' =>  $inventory->product_id,
            'unit_price' =>  $inventory->unit_price,
            'mark_up_price' =>  $inventory->mark_up_price,
            'retail_price' =>  $inventory->retail_price,
            'min_quantity' =>  $inventory->min_quantity,
            'manual_percentage' =>  $inventory->manual_percentage,
            'in_store' =>  $inventory->in_store,
            'in_warehouse' =>  $inventory->in_warehouse,
            'supplier_id' =>  $inventory->supplier_id,
        ];

        return inertia('Inventory/Edit', [
            'title' => "Edit Product",
            'product' => $data,
            'stores' => Store::select('id', 'name')->orderBy('id', 'DESC')->get(),
            'units' => ProductUnit::select('id','name')->orderBy('id', 'DESC')
            ->get(),
            'categories' => ProductCategory::select('id','name')->orderBy('id', 'DESC')
            ->get(),
            'suppliers' => Supplier::select('id','name')->orderBy('id', 'DESC')
            ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductFormRequest $request)
    {
        $product = Product::find($request->id);
        Gate::authorize('update', $product);

        $product_data = [
            'name' => $request->name,
            'barcode' => $request->barcode,
            'sku' => $request->sku,
            'size' => $request->size,
            'dimension' => $request->dimension,
            'unit' => $request->unit,
            'product_type' => $request->product_type,
            'brand' => $request->brand,
            'manufacturer' => $request->manufacturer,
            'description' => $request->description,
            'product_category_id' => $request->product_category_id,
            'store_id' => $request->store_id ?? auth()->id(),
        ];

        if($request->hasFile('image')){
            $image = $request->file('image')->store('products','public');
            $product_data['image'] = asset('storage/'. $image);
        }

        $product->update($product_data);

        $product_supplier = ProductSupplier::find($request->product_supplier_id);

        $product_supplier_data = [
            'unit_price' => $request->unit_price,
            'mark_up_price' => $request->mark_up_price,
            'retail_price' => $request->retail_price,
            'min_quantity' => $request->min_quantity,
            'manual_percentage' => $request->manual_percentage,
            'in_store' => $request->in_store,
            'in_warehouse' => $request->in_warehouse,
            'supplier_id' => $request->supplier_id,
        ];

        $product_supplier->update($product_supplier_data);

        return redirect()->back();
    }

    public function update_stocks(Product $product, Request $request)
    {
        Gate::authorize('update', $product);

        $new_stocks = [
            'in_warehouse' => $product->stock->in_warehouse - $request->qty,
            'in_store' => $product->stock->in_store + $request->qty
        ];

        $product->stock()->update($new_stocks);

        return redirect()->back();
    }

    public function bulk_update(Request $request)
    {
        DB::beginTransaction();

        try {
            foreach ($request->products_id as $product_id) {

                // Retrieve the product with its associated stock information
                $product = Product::with('stock')->find($product_id);

                // Check if there are stocks in the warehouse
                if ($product?->stock?->in_warehouse > 0) {

                    // Calculate the quantity to transfer
                    $transfer_stocks = min($product->stock->in_warehouse, $request->qty);

                    // Define the updated stock values
                    $new_stocks = [
                        'in_warehouse' => $product->stock->in_warehouse - $transfer_stocks,
                        'in_store' => $product->stock->in_store + $transfer_stocks
                    ];

                    // Update the stock values in the database
                    $product->stock()->update($new_stocks);
                }
            }

            DB::commit();

            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording multiple stocks transfer: ' .$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'An error occurred while recording the sale. Please try again.']);
        }
    }
}
