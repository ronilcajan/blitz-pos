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
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Product::class);

        $products = ProductSupplier::query()
        ->select('product_supplier.id as product_supplier_id', 'products.*', 'product_supplier.*') // Select product_supplier ID
        ->leftJoin('products', 'products.id', '=', 'product_supplier.product_id')
        ->with(['product.store', 'product.category','supplier']) // Load productSuppliers relationship
        ->filter(request(['search', 'store', 'category', 'supplier']))
        ->orderBy('products.name', 'ASC')
            ->paginate($request->per_page ? ($request->per_page == 'All' ?  ProductSupplier::count() : $request->per_page) : 10)
            ->withQueryString()
            ->through(function ($supplier_product) {

                return [
                    'id' => $supplier_product->product_supplier_id,
                    'name' => $supplier_product->product->name,
                    'barcode' => $supplier_product->product->barcode,
                    'sku' => $supplier_product->product->sku,
                    'size' => $supplier_product->product->size,
                    'dimension' => $supplier_product->product->dimension,
                    'unit' => $supplier_product->product->unit,
                    'product_type' => $supplier_product->product->product_type,
                    'brand' => $supplier_product->product->brand,
                    'manufacturer' => $supplier_product->product->manufacturer,
                    'description' => $supplier_product->product->description,
                    'image' => $supplier_product->product->image,
                    'store' => $supplier_product->product->store->name,
                    'category' => $supplier_product->product->category->name,
                    'supplier' => $supplier_product->supplier->name,
                    'unit_price' => $supplier_product->unit_price,
                    'mark_up_price' => $supplier_product->mark_up_price,
                    'retail_price' => $supplier_product->retail_price,
                    'min_quantity' => $supplier_product->min_quantity,
                    'in_store' => $supplier_product->in_store,
                    'in_warehouse' => $supplier_product->in_warehouse,
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
     * Display the specified resource.
     */
    public function show(ProductSupplier $inventory)
    {
       
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
}
