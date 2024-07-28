<?php

namespace App\Http\Controllers;

use App\Classes\ConvertToNumber;
use App\Http\Requests\PurchaseFormRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseItems;
use App\Models\Store;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        auth()->user()->can('viewAny', Purchase::class);

        $orders = Purchase::query()
            ->with(['store','supplier'])
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store','suppliers','from_date','to_date']))
            ->paginate($request->per_page ? ($request->per_page == 'All' ? Purchase::count(): $request->per_page) : 10)
            ->withQueryString()
            ->through(function ($order) {
                return [
                    'id' => $order->id,
                    'order_no' => $order->tx_no,
                    'quantity' => Number::format($order->quantity),
                    'discount' => Number::format($order->discount, 2),
                    'amount' => Number::format($order->total, 2),
                    'status' => $order->status,
                    'supplier' => $order->supplier?->name,
                    'store' => $order->store->name,
                    'created_at' => $order->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('Purchase/Index', [
            'title' => "Purchase Orders",
            'orders' => $orders,
            'suppliers' => Supplier::select('id', 'name')->orderBy('name','ASC')->get(),
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'filter' => $request->only(['search','store','per_page', 'suppliers']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $user->can('create', Purchase::class);

        $search = request(['search']);
        $products =  Product::query()
            ->with(['store', 'price', 'stock','category'])
            ->when( !$search, function($q){
                return $q->whereHas('stock', function($q){
                    $q->whereColumn('in_store','<','min_quantity')
                        ->whereColumn('in_warehouse','<','min_quantity');
                });
            })
            ->filter( $search)
            ->paginate(10)
            ->withQueryString()
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'size' => $product->size,
                    'unit' => $product->unit,
                    'image' => $product->image,
                    'category' => $product->category?->name,
                    'stocks' => $product->stock?->in_store + $product->stock?->in_warehouse,
                    'price' =>  $product->price?->sale_price,
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
        auth()->user()->can('create', Purchase::class);

        $request->validated();
        $convertStringToNumber = new ConvertToNumber();

        $discount = $convertStringToNumber->convertToNumber($request->discount);
        $total = $convertStringToNumber->convertToNumber($request->total);

        $purchaseAttributes = [
            'quantity' => $request->quantity,
            'discount' => $discount,
            'amount' => $total - $discount,
            'total' => $total,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
            'supplier_id' => $request->supplier_id,
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->store_id ?? 1,
            'created_at' => $request->transaction_date,
        ];

        DB::beginTransaction();
        try{

            $purchase = Purchase::create($purchaseAttributes);
            $purchase->update(['tx_no' => 'ORD' . str_pad($purchase->id, 5, '0', STR_PAD_LEFT)]);

            $products = [];

            foreach($request->items as $product){
                $products[] = [
                    'quantity' =>  $product['qty'],
                    'price' =>  $product['price'],
                    'purchase_id' =>  $purchase->id,
                    'product_id' =>  $product['id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];

            }
            PurchaseItems::insert($products);

            DB::commit();

            return redirect()->back();

        }catch(\Exception $e){
            DB::rollBack();
            Log::error('Error recording purchase order: ' .$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'An error occurred while recording the sale. Please try again.']);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        auth()->user()->can('view', $purchase);

        $items = $purchase->items()->get()->map(function($item){
            return [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'size' => $item->product->size,
                'image' => $item->product?->image ?? asset('product.png'),
                'stocks' => Number::format($item->product->stock?->in_store + $item->product->stock?->in_warehouse),
                'price' => Number::currency($item->price, in: $item->purchase->store->currency),
                'qty' =>  Number::format($item->quantity).' '.$item->product->unit,
                'total' =>  Number::currency($item->price * $item->quantity, in: $item->purchase->store->currency),
            ];
        });

        $purchase = Purchase::with('store', 'supplier')->find($purchase->id);

        $purchase->quantity = Number::format($purchase->quantity);
        $purchase->discount = Number::currency($purchase->discount, in: $purchase->store->currency);
        $purchase->amount = Number::currency($purchase->amount, in: $purchase->store->currency);
        $purchase->total = Number::currency($purchase->total, in: $purchase->store->currency);
        $purchase->date = $purchase->created_at->format('F d, Y');

        return inertia('Purchase/Show', [
            'title' => "View Purchase",
            'purchase' =>  $purchase,
            'purchase_items' =>  $items,
        ]);
    }

    public function downloadPDF(Purchase $purchase)
    {
        auth()->user()->can('view', $purchase);

        $items = $purchase->items()->get()->map(function($item){
            return [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'size' => $item->product->size,
                'unit' => $item->product->unit,
                'image' => $item->product->image,
                'stocks' => $item->product->stock?->in_store + $item->product->stock?->in_warehouse,
                'price' =>  $item->price,
                'qty' =>  Number::format($item->quantity).' '.$item->product->unit,
                'total' =>  $item->purchase->store->currency.' '.Number::format($item->price * $item->quantity, precision:2),
            ];
        });

        $pdf = Pdf::loadView('purchase.downloadPdf', [
            'title' => "Download Purchase Order",
            'purchase' =>  $purchase->with('store','supplier')->first(),
            'purchase_items' =>  $items,
            'suppliers' => Supplier::select('id', 'name')->orderBy('name','ASC')->get(),
        ]);

        $filename = $purchase->tx_no.date('-Y-m-d').'.pdf';
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream($filename);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        auth()->user()->can('update', $purchase);

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
                    'category' => $product->category?->name,
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

        $purchase = Purchase::with('store', 'supplier')->find($purchase->id);

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
        auth()->user()->can('update', $purchase);

        $request->validated();

        $convertStringToNumber = new ConvertToNumber();

        $discount = $convertStringToNumber->convertToNumber($request->discount);
        $total = $convertStringToNumber->convertToNumber($request->total);

        $purchaseAttributes = [
            'quantity' => $request->quantity,
            'discount' => $discount,
            'amount' => $total - $discount,
            'total' => $total,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
            'supplier_id' => $request->supplier_id,
            'created_at' => $request->transaction_date,
        ];

        DB::beginTransaction();
        try{

            $purchase->update($purchaseAttributes);

            foreach($request->items as $product){
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

            DB::commit();

            return redirect()->back();

        }catch(\Exception $e){
            DB::rollBack();
            Log::error('Error recording purchase order: ' .$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'An error occurred while recording the sale. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        auth()->user()->can('delete', $purchase);

        $purchase->delete();
        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        auth()->user()->can('create', Purchase::class);

        Purchase::whereIn('id',$request->orders_id)->delete();
        return redirect()->back();
    }
}
