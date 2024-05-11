<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Number;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth()->user()->can('viewAny', Purchase::class);
        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? Delivery::count() : $request->per_page)
        : 10;

        $deliveries = Delivery::query()
            ->with(['store','supplier','store'])
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($order) {
                return [
                    'id' => $order->id,
                    'order_no' => $order->tx_no,
                    'quantity' => Number::format($order->quantity),
                    'discount' => Number::format($order->discount, maxPrecision: 2),
                    'amount' => Number::currency($order->amount - $order->discount, in: 'PHP'),
                    'status' => $order->status,
                    'supplier' => $order->supplier->name,
                    'store' => $order->store->name,
                    'created_at' => $order->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('Delivery/Index', [
            'title' => "Deliveries",
            'products' => $deliveries,
            'suppliers' => Supplier::select('id', 'name')->orderBy('name','ASC')->get(),
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'filter' => $request->only(['search','store','per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // auth()->user()->can('create', Purchase::class);

        $products =  Product::query()
            ->with(['store', 'price', 'stock','category'])
            ->filter(request(['search']))
            ->paginate(5)
            ->withQueryString()
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'size' => $product->size,
                    'unit' => $product->unit,
                    'image' => $product->image,
                    'category' => $product->category->name,
                    'stocks' => $product->stock?->in_store + $product->stock?->in_warehouse,
                    'price' =>  $product->price?->discount_price,
                ];
        });

        $orders = Purchase::select('id','tx_no')
            ->where('status','pending')
            ->orderBy('tx_no', 'ASC')
            ->get();

        $order = '';
        $purchase = '';
        if($request->order_id){
            $purchase = Purchase::query()
                ->with(['store','supplier','items'])
                ->find($request->order_id);

            $order =  $purchase->items()->get()->map(function($item){
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'size' => $item->product->size,
                    'unit' => $item->product->unit,
                    'image' => $item->product->image,
                    'stocks' => $item->product->stock?->in_store + $item->product->stock?->in_warehouse,
                    'price' =>  $item->price,
                    'qty' =>  $item->quantity,
                ];
            });
        }

        return inertia('Delivery/Create', [
            'title' => "New Delivery",
            'products' =>  $products,
            'orders' =>  $orders,
            'order' =>  $order ?? '',
            'purchase' =>  $purchase ?? '',
            'stores' => Store::select('id', 'name')->get(),
            'suppliers' => Supplier::select('id', 'name')->orderBy('name','ASC')->get(),
            'units' => ProductUnit::select('id','name')
            ->orderBy('name', 'ASC')->get(),
            'categories' => ProductCategory::select('id','name')
            ->orderBy('name', 'ASC')->get(),
            'filter' =>  request()->only(['search','barcode']) ,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeliveryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Delivery $delivery)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Delivery $delivery)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeliveryRequest $request, Delivery $delivery)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Delivery $delivery)
    {
        //
    }
}
