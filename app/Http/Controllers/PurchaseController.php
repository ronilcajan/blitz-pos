<?php

namespace App\Http\Controllers;

use App\Classes\TransactionCodeGenerator;
use App\Http\Requests\PurchaseFormRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseItems;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Barryvdh\DomPDF\Facade\Pdf;


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

        return inertia('Purchase/Index', [
            'title' => "Purchase Orders",
            'orders' => $orders,
            'suppliers' => Supplier::select('id', 'name')->orderBy('name','ASC')->get(),
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

        return inertia('Purchase/Create', [
            'title' => "New Purchase",
            'products' =>  $products ,
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
    public function store(PurchaseFormRequest $request)
    {
        $request->validated();
        $generator = new TransactionCodeGenerator();
        $code = $generator->generate();

        $discount = $this->convertToNumber($request->details['discount']);
        $total = $this->convertToNumber($request->details['total']);

        $purchaseAttributes = [
            'tx_no' => "PO" .$code,
            'quantity' => $request->details['quantity'],
            'discount' => $discount,
            'amount' => $total - $discount,
            'total' => $total,
            'status' => $request->details['status'] ?? 'pending',
            'notes' => $request->details['notes'],
            'supplier_id' => $request->details['supplier_id'],
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->store_id ?? 1,
            'created_at' => $request->details['transaction_date'],
        ];

        $purchase_created = Purchase::create($purchaseAttributes);

        $products = [];


        foreach($request->products as $product){
            $products[] = [
                'quantity' =>  $product['qty'],
                'price' =>  $product['price'],
                'purchase_id' =>  $purchase_created->id,
                'product_id' =>  $product['id'],
                'created_at' => now(),
                'updated_at' => now()
            ];

        }
        PurchaseItems::insert($products);

        return redirect()->back();

    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        $items = $purchase->items()->get()->map(function($item){
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

        $purchase = Purchase::with('store', 'supplier')->find($purchase->id);

        return inertia('Purchase/Show', [
            'title' => "View Purchase",
            'purchase' =>  $purchase,
            'purchase_items' =>  $items,
        ]);
    }

    public function downloadPDF(Purchase $purchase)
    {
        $items = $purchase->items()->get()->map(function($item){
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

        $pdf = Pdf::loadView('purchase.pdf', [
            'title' => "Download Purchase Order",
            'purchase' =>  $purchase->with('store','supplier')->first(),
            'purchase_items' =>  $items,
            'suppliers' => Supplier::select('id', 'name')->orderBy('name','ASC')->get(),
        ]);

        $filename = 'purchase-'.$purchase->tx_no.'.pdf';
        return $pdf->download($filename);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
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

        $items = $purchase->items()->get()->map(function($item){
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

        return inertia('Purchase/Edit', [
            'title' => "Edit Purchase",
            'products' =>  $products,
            'purchase' =>  $purchase,
            'purchase_items' =>  $items,
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
    public function update(PurchaseFormRequest $request, Purchase $purchase)
    {
        $request->validated();

        $discount = $this->convertToNumber($request->details['discount']);
        $total = $this->convertToNumber($request->details['total']);

        $purchaseAttributes = [
            'quantity' => $request->details['quantity'],
            'discount' => $discount,
            'amount' => $total - $discount,
            'total' => $total,
            'status' => $request->details['status'] ?? 'pending',
            'notes' => $request->details['notes'],
            'supplier_id' => $request->details['supplier_id'],
            'created_at' => $request->details['transaction_date'],
        ];

        $purchase->update($purchaseAttributes);

        $products = [];

        foreach($request->products as $product){
            $products[] = [
                'quantity' =>  $product['qty'],
                'price' =>  $product['price'],
                'product_id' =>  $product['id'],
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $purchase->items()->forceDelete();
        $purchase->items()->createMany($products);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        // Gate::authorize('bulk_delete', Supplier::class);

        Purchase::whereIn('id',$request->orders_id)->delete();
        return redirect()->back();
    }

    public function convertToNumber($string)
    {
        return floatval(str_replace(',','',$string));
    }
}
