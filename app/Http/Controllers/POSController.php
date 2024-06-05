<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Store;
use Illuminate\Http\Request;

class POSController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $products =  Product::query()
            ->with(['price','stock','category'])
            ->orderBy('name', 'ASC')
            ->where('visible','published')
            ->filter(request(['search']))
            ->paginate(24)
            ->withQueryString()
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'size' => $product->size,
                    'unit' => $product->unit,
                    'image' => $product->image,
                    'stocks' => $product->stock?->in_store + $product->stock?->in_warehouse,
                    'price' =>  $product->price->discount_price ?? 0,
                ];

            });

        $store = Store::find($user->store_id);

        $customers = Customer::select('id','name')->orderBy('name','ASC')->get();
        $units = ProductUnit::select('id','name')->orderBy('name', 'ASC')->get();
        $categories = ProductCategory::select('id','name')->orderBy('name', 'ASC')->get();
        $stores = Store::select('id','name')->orderBy('name', 'ASC')->get();

        return inertia('Pos/Index', [
            'title' => "Point of Sale",
            'store' => $store,
            'stores' => $stores,
            'customers' => $customers,
            'products' => $products,
            'units' => $units,
            'categories' => $categories,
            'filter' => $request->only(['search','store','page']),
        ]);
    }
}
