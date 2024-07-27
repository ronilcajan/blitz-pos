<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFormRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
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
                'image' => $product?->image ?? asset('product.png'),
                'store' => $product->store->name,
                'price' =>  Number::format($product->price?->sale_price,2),
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
