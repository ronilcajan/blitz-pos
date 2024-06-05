<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
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
            ->paginate(25)
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

        $customers = Customer::select('id','name')->orderBy('name','ASC')->get();
        $store = Store::find($user->store_id);

        return inertia('Pos/Index', [
            'title' => "Point of Sale",
            'store' => $store,
            'customers' => $customers,
            'products' => $products,
            'filter' => $request->only(['search','store','per_page']),
        ]);
    }
}
