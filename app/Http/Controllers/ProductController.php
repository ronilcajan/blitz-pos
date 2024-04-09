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
            ->orderBy('name', 'ASC')
            ->filter(request(['search','store']))
            ->paginate($request->per_page ? ($request->per_page == 'All' ?  Product::count() : $request->per_page) : 10)
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
                    'retail_price' => $product->stocks->max('retail_price') ? "P ".$product->stocks->max('retail_price') : 0,
                    'min_quantity' => $product->stocks->max('min_quantity'),
                    'in_store' => $product->stocks->sum('in_store'),
                    'in_warehouse' =>  $product->stocks->sum('in_warehouse'),
                ];
        });
        return inertia('Product/Index', [
            'title' => 'Products',
            'products' => $products,
            'stores' => Store::select('id', 'name')->get(),
            'product_categories' => ProductCategory::select('id', 'name')
                ->get(),
            'filter' => $request->only(['search']),
            'per_page' => $request->only(['per_page'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Gate::authorize('create', Customer::class);

        return inertia('Product/Create', [
            'title' => "Add New Product",
            'stores' => Store::select('id', 'name')
                ->orderBy('id', 'DESC')->get(),
            'units' => ProductUnit::select('id','name')->orderBy('id', 'DESC')
            ->get(),
            'categories' => ProductCategory::select('id','name')->orderBy('id', 'DESC')
            ->get(),
            'suppliers' => Supplier::select('id','name')->orderBy('id', 'DESC')
            ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductFormRequest $request)
    {
        // Gate::authorize('create', Supplier::class);

        $validate = $request->validated();

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
            $validate['image'] = asset('storage/'. $image);
        }
         
        $product = Product::create($product_data);

        $product_supplier_data = [
            'unit_price' => $request->unit_price,
            'mark_up_price' => $request->mark_up_price,
            'retail_price' => $request->retail_price,
            'min_quantity' => $request->min_quantity,
            'manual_percentage' => $request->manual_percentage,
            'in_store' => $request->in_store,
            'in_warehouse' => $request->in_warehouse,
            'product_id' => $product->id,
            'supplier_id' => $request->supplier_id,
        ];

        ProductSupplier::create($product_supplier_data);

        return redirect()->back();
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
