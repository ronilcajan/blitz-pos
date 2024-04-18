<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSupplier;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Gate::authorize('viewAny', Customer::class);

        $orders = Purchase::query()
            ->with(['store','supplier','store'])
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store']))
            ->paginate($request->per_page ? ($request->per_page == 'All' ? Purchase::count()->get() : $request->per_page) : 10)
            ->withQueryString()
            ->through(function ($order) {
                return [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'quantity' => $order->quantity,
                    'discount' => $order->discount,
                    'amount' => $order->amount,
                    'status' => $order->status,
                    'supplier' => $order->supplier->name,
                    'user' => $order->user->name,
                    'store' => $order->store->name,
                    'created_at' => $order->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('Purchase/Index', [
            'title' => "Purchase Orders",
            'orders' => $orders,
            'supplier' => Supplier::select('id', 'name')->orderBy('name','ASC')->get(),
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'filter' => $request->only(['search','store','per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Gate::authorize('create', Expenses::class);

        $products = ProductSupplier::query()
            ->select('product_supplier.id as product_supplier_id', 'products.*', 'product_supplier.*') // Select product_supplier ID
            ->leftJoin('products', 'products.id', '=', 'product_supplier.product_id')
            ->with(['product']) // Load productSuppliers relationship
            ->filter(request(['search']))
            ->orderBy('product_supplier.in_store', 'DESC')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($supplier_product) {
                return [
                    'id' => $supplier_product->product_supplier_id,
                    'name' => $supplier_product->name,
                    'barcode' => $supplier_product->barcode,
                    'size' => $supplier_product->size,
                    'color' => $supplier_product->color,
                    'unit' => $supplier_product->unit,
                    'image' => $supplier_product->image,
                    'unit_price' => $supplier_product->unit_price,
                    'stocks' => $supplier_product->in_store + $supplier_product->in_warehouse,
                ];
            });

        $search_products = Product::query()
            ->with(['store', 'stock'])
            ->orderBy('name', 'ASC')
            ->where('barcode',request()->barcode)
            ->first();

        return inertia('Purchase/Create', [
            'title' => "New Purchase",
            'products' =>  $products ,
            'search_products' =>  $search_products,
            'filter' =>  request()->only(['search','barcode','per_page']) ,
        ]);
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
    public function show(Purchase $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $order)
    {
        //
    }
}
