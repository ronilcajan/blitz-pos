<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFormRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductPrice;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Support\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Models\Activity;

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
            ->with(['store', 'price', 'stock','category','images'])
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
                    'usage_type' => $product->usage_type,
                    'brand' => $product->brand,
                    'description' => $product->description,
                    'image' => $product?->images[0]->image ?? asset('product.png'),
                    'visible' => $product->visible === 'published',
                    'store' => $product->store->name,
                    'category' => $product->category?->name,
                    'price' =>  $product->price?->sale_price ? Number::format($product->price?->sale_price,2) : null,
                ];
        });

        return inertia('Product/Index', [
            'title' => 'Products',
            'products' => $products,
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
            'title' => "Add a Product",
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
            'usage_type' => $request->usage_type,
            'brand' => $request->brand,
            'description' => $request->description,
            'visible' => $request->visible,
            'expiration_date' => $request->expiration_date,
            'product_category_id' => $request->product_category_id,
            'store_id' => $request->store_id ?? auth()->user()->store_id,
        ];

        DB::beginTransaction();

        try {
            $product = Product::create($productAttributes);

            if($request->hasFile('image')){
                $productImages = [];

                foreach($request->file('image') as $image){
                    $path = $image->store('products', 'public');

                    $productImages[] = [
                        'image' => asset('storage/' . $path),
                        'product_id' => $product->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                ProductImage::insert($productImages);
            }

            $productPriceAttributes = [
                'base_price' => $request->base_price,
                'markup_price' => $request->markup_price,
                'discount_rate' => $request->discount_rate,
                'discount_type' => $request->discount_type,
                'tax_rate' => $request->tax_rate,
                'tax_type' => $request->discount_type,
                'sale_price' => $request->sale_price,
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

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }

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
        $product = Product::with(['price','stock','images'])
            ->findOrFail($product->id);

        Gate::authorize('update', $product);

        return inertia('Product/Edit', [
            'title' => "Edit Product",
            'product' => $product,
            'units' => ProductUnit::select('id','name')
                ->orderBy('name', 'DESC')->get(),
            'categories' => ProductCategory::select('id','name')
                ->orderBy('name', 'DESC')
            ->get(),
        ]);
    }

    public function show(Product $product)
    {

        $product = Product::with(['price','stock','images','sales','category'])
            ->findOrFail($product->id);

        $sales = $product->sales()
            ->orderBy('created_at', 'DESC')
            ->with(['sale.customer'])
            ->paginate(10);

        
        $productPriceModel = new ProductPrice();
        $priceActivity = Activity::query()
            ->where('subject_type', get_class($productPriceModel))
            ->where('event', 'updated')
            ->where('subject_id', $product->price->id)
            ->orderBy('created_at', 'DESC')
            ->paginate(10); 

        return inertia('Product/Show', [
            'title' => "Product details",
            'product' => $product,
            'sales' => $sales,
            'price_activity' => $priceActivity,
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
            'usage_type' => $request->usage_type,
            'brand' => $request->brand,
            'description' => $request->description,
            'product_category_id' => $request->product_category_id,
            'visible' => $request->visible,
            'expiration_date' => $request->expiration_date,
            'store_id' => $request->store_id ?? auth()->user()->store_id,
        ];

        DB::beginTransaction();

        try {
            $product->update($productAttributes);


           // Check if both image and images_url are present in the request
            if ($request->hasFile('image') || $request->has('images_url')) {
                // Delete existing product images
                ProductImage::where('product_id', $product->id)->delete();

                $productImages = [];

                   // Handle images from URLs
                if ($request->has('images_url')) {
                    foreach ($request->input('images_url') as $imageUrl) {
                        $productImages[] = [
                            'image' => $imageUrl,
                            'product_id' => $product->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // Handle uploaded images
                if ($request->hasFile('image')) {
                    
                    foreach ($request->file('image') as $image) {
                        $path = $image->store('products', 'public');
                        $productImages[] = [
                            'image' => asset('storage/' . $path),
                            'product_id' => $product->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

             

                // Insert new product images
                ProductImage::insert($productImages);
            }

            $productPriceAttributes = [
                'base_price' => $request->base_price,
                'markup_price' => $request->markup_price,
                'discount_rate' => $request->discount_rate,
                'discount_type' => $request->discount_type,
                'tax_rate' => $request->tax_rate,
                'tax_type' => $request->discount_type,
                'sale_price' => $request->sale_price,
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

            DB::commit();
            return redirect()->back();
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            return redirect()->back()->with('error', $th->getMessage());
        }

        

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
