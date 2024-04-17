<?php

namespace App\Http\Controllers;

use App\Models\Product;
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

            $products = Product::query()
            ->with(['store', 'stock'])
            ->orderBy('name', 'ASC')
            ->where('barcode',request()->search)
            ->first();

        return inertia('Purchase/Create', [
            'title' => "New Purchase",
            'products' =>  $products ,
            'filter' =>  request()->only(['search']) ,
            'stores' => Store::select('id', 'name')->orderBy('id', 'DESC')->get(),
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
