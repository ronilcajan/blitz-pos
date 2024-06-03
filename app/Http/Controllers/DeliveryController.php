<?php

namespace App\Http\Controllers;

use App\Classes\ConvertToNumber;
use App\Classes\TransactionCodeGenerator;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Delivery;
use App\Models\DeliveryItems;
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
            ->with(['store','supplier','purchase'])
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store','supplier']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($delivery) {
                return [
                    'id' => $delivery->id,
                    'tx_no' => $delivery->tx_no,
                    'quantity' => Number::format($delivery->quantity),
                    'discount' => Number::format($delivery->discount, maxPrecision: 2),
                    'amount' => Number::currency($delivery->amount - $delivery->discount, in: 'PHP'),
                    'status' => $delivery->status,
                    'notes' => $delivery->notes,
                    'supplier' => $delivery->supplier?->name,
                    'purchase' => $delivery->purchase?->tx_no,
                    'store' => $delivery->store->name,
                    'created_at' => $delivery->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('Delivery/Index', [
            'title' => "Deliveries",
            'deliveries' => $deliveries,
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
        $user = auth()->user();
        //auth()->user()->can('create', Purchase::class);

        $request->validated();
        $generator = new TransactionCodeGenerator();
        $convertStringToNumber = new ConvertToNumber();

        $discount = $convertStringToNumber->convertToNumber($request->discount);
        $total = $convertStringToNumber->convertToNumber($request->total);

        $purchaseAttributes = [
            'tx_no' => "DE" .$generator->generate(),
            'quantity' => $request->quantity,
            'discount' => $discount,
            'amount' => $total - $discount,
            'total' => $total,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
            'purchase_id' => $request->purchase_id,
            'supplier_id' => $request->supplier_id,
            'user_id' => $user->id,
            'store_id' => $user->store_id ?? 1,
            'created_at' => $request->transaction_date,
        ];

        $delivery = Delivery::create($purchaseAttributes);

        $products = [];

        foreach($request->items as $product){
            $products[] = [
                'quantity' =>  $product['qty'],
                'price' =>  $product['price'],
                'delivery_id' =>  $delivery->id,
                'product_id' =>  $product['id'],
                'created_at' => now(),
                'updated_at' => now()
            ];

        }
        DeliveryItems::insert($products);

        return redirect()->back();
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
    public function edit(Delivery $delivery, Request $request)
    {
        // auth()->user()->can('update', $purchase);

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


        $items = '';
        if($delivery->purchase->id){
            $items =  $delivery->delivery_items()->get()->map(function($item){
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

        return inertia('Delivery/Edit', [
            'title' => "Edit Delivery",
            'products' =>  $products,
            'orders' =>  $orders,
            'order' =>  $order ?? '',
            'purchase' =>  $purchase ?? '',
            'purchase_items' =>  $items,
            'delivery' => $delivery,
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
