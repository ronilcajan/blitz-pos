<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFormRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Support\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
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
                    'visible' => $product->visible === 'published',
                    'store' => $product->store->name,
                    'category' => $product->category->name,
                    'price' =>  $product->price?->discount_price ? Number::currency($product->price->discount_price, in: 'PHP') : null,
                ];
        });

        return inertia('Product/Index', [
            'title' => 'Products',
            'products' => $products,
            'stores' => Store::select('id', 'name')
                ->orderBy('name','DESC')->get(),
            'product_categories' => ProductCategory::select('id', 'name')
                ->orderBy('name','DESC')
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
        Gate::authorize('create', Product::class);

        return inertia('Product/Create', [
            'title' => "Add New Product",
            // 'barcode' =>  $data,
            'stores' => Store::select('id', 'name')
                ->orderBy('name', 'ASC')->get(),
            'units' => ProductUnit::select('id','name')
                ->orderBy('name', 'ASC')->get(),
            'categories' => ProductCategory::select('id','name')
                ->orderBy('name', 'ASC')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductFormRequest $request)
    {
        Gate::authorize('create', Product::class);

        $request->validated();

        $productAttributes = [
            'name' => $request->name,
            'barcode' => $request->barcode,
            'size' => $request->size,
            'color' => $request->color,
            'dimension' => $request->dimension,
            'unit' => $request->unit,
            'product_type' => $request->product_type,
            'brand' => $request->brand,
            'manufacturer' => $request->manufacturer,
            'description' => $request->description,
            'product_category_id' => $request->product_category_id,
            'store_id' => $request->store_id ?? auth()->user()->store_id,
        ];

        if($request->hasFile('image')){
            $image = $request->file('image')->store('products','public');
            $productAttributes['image'] = asset('storage/'. $image);
        }
        $product = Product::create($productAttributes);

        $productPriceAttributes = [
            'base_price' => $request->base_price,
            'markup_price' => $request->markup_price,
            'sale_price' => $request->sale_price,
            'discount' => $request->discount,
            'manual_percentage' => $request->manual_percentage ?? 'manual',
            'discount_price' => $request->discount_price,
            'product_id' => $product->id
        ];

        $productStocksAttributes = [
            'sku' => $request->sku,
            'min_quantity' => $request->min_quantity,
            'in_store' => $request->in_store,
            'in_warehouse' => $request->in_warehouse,
            'product_id' => $product->id
        ];

        // Update or create price attributes
        $product->price()->updateOrCreate([], $productPriceAttributes);
        // Update or create stock attributes
        $product->stock()->updateOrCreate([], $productStocksAttributes);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function barcode_api(String $barcode)
    {
        try {
            $response = Http::get('https://api.upcitemdb.com/prod/trial/lookup', [
                'upc' => $barcode
            ]);

            return inertia('Product/Create', [
                'title' => "Add New Product",
                'stores' => Store::select('id', 'name')
                    ->orderBy('id', 'DESC')->get(),
                'barcode' => $response,
                'units' => ProductUnit::select('id','name')->orderBy('id', 'DESC')
                ->get(),
                'categories' => ProductCategory::select('id','name')->orderBy('id', 'DESC')
                ->get(),
                'suppliers' => Supplier::select('id','name')->orderBy('id', 'DESC')
                ->get(),
            ]);

        } catch (\Exception $e) {
            // Handle exceptions, log errors, etc.
            return response()->json(['error' => 'An error occurred while fetching data.'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product = Product::with(['price','stock'])
            ->findOrFail($product->id);

        Gate::authorize('update', $product);

        return inertia('Product/Edit', [
            'title' => "Edit Product",
            'product' => $product,
            'stores' => Store::select('id', 'name')
                ->orderBy('name', 'DESC')->get(),
            'units' => ProductUnit::select('id','name')
                ->orderBy('name', 'DESC')->get(),
            'categories' => ProductCategory::select('id','name')
                ->orderBy('name', 'DESC')
            ->get(),
        ]);
    }

       /**
     * Update the specified resource in storage.
     */
    public function update(ProductFormRequest $request)
    {
        $product = Product::findOrFail($request->id);

        Gate::authorize('update', $product);

        $request->validated();

        $productAttributes = [
            'name' => $request->name,
            'barcode' => $request->barcode,
            'size' => $request->size,
            'color' => $request->color,
            'dimension' => $request->dimension,
            'unit' => $request->unit,
            'product_type' => $request->product_type,
            'brand' => $request->brand,
            'visible' => $request->visible,
            'manufacturer' => $request->manufacturer,
            'description' => $request->description,
            'product_category_id' => $request->product_category_id,
            'store_id' => $request->store_id ?? auth()->user()->store_id,
        ];

        if($request->hasFile('image')){
            $image = $request->file('image')->store('products','public');
            $productAttributes['image'] = asset('storage/'. $image);
        }
        $product->update($productAttributes);

        $productPriceAttributes = [
            'base_price' => $request->base_price,
            'markup_price' => $request->markup_price,
            'sale_price' => $request->sale_price,
            'discount' => $request->discount,
            'manual_percentage' => $request->manual_percentage,
            'discount_price' => $request->discount_price,
            'product_id' => $product->id
        ];

        $productStocksAttributes = [
            'sku' => $request->sku,
            'min_quantity' => $request->min_quantity,
            'in_store' => $request->in_store,
            'in_warehouse' => $request->in_warehouse,
            'product_id' => $product->id
        ];

       // Update or create price attributes
        $product->price()->updateOrCreate([], $productPriceAttributes);
        // Update or create stock attributes
        $product->stock()->updateOrCreate([], $productStocksAttributes);


        return redirect()->back();
    }

    public function change_status(Product $product, Request $request)
    {
        Gate::authorize('update', $product);
        $product->update(['visible' => $request->status ? 'published' : 'hide']);
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function bulkDelete(Request $request)
    {
        Gate::authorize('bulk_delete', Product::class);

        Product::whereIn('id',$request->products_id)->delete();
        return redirect()->back();
    }

    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);
        Product::find($product->id)->delete();
        return redirect()->back();
    }
}
