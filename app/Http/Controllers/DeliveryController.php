<?php

namespace App\Http\Controllers;

use App\Classes\ConvertToNumber;
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
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        auth()->user()->can('viewAny', Delivery::class);

        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? Delivery::count() : $request->per_page)
        : 10;

        $deliveries = Delivery::query()
            ->with(['store','supplier','purchase'])
            ->orderBy('id', 'DESC')
            ->filter(request(['search','suppliers','from_date','to_date']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($delivery) {
                return [
                    'id' => $delivery->id,
                    'tx_no' => $delivery->tx_no,
                    'quantity' => Number::format($delivery->quantity),
                    'amount' => Number::format($delivery->total,2),
                    'status' => $delivery->status,
                    'notes' => $delivery->notes,
                    'supplier' => $delivery->supplier?->name,
                    'purchase' => $delivery->purchase?->tx_no,
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
        $user = auth()->user();
        $user->can('create', Delivery::class);

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
                    'price' => $product->price?->sale_price,
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

            $order =  $purchase->items()->get()->map(function($item, $user){
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'size' => $item->product->size,
                    'unit' => $item->product->unit,
                    'image' => $item->product->image,
                    'stocks' => $item->product->stock?->in_store + $item->product->stock?->in_warehouse,
                    'price' => $item->price,
                    'qty' =>  Number::format($item->quantity),
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

        $user->can('create', Delivery::class);

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
            'purchase_id' => $request->purchase_id,
            'supplier_id' => $request->supplier_id,
            'user_id' => $user->id,
            'receiver' => $request->receiver,
            'created_at' => $request->transaction_date,
        ];

        DB::beginTransaction();

        try{
            $delivery = Delivery::create($purchaseAttributes);
            $delivery->update(['tx_no' => 'DEL' . str_pad($delivery->id, 5, '0', STR_PAD_LEFT)]);

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

            if($request->status == 'complete'){
                foreach($delivery->delivery_items as $item){
                    if (isset($item->product->stock)) {
                        // Calculate the new quantity
                        $newQuantity = $item->product->stock->in_store + $item->quantity;

                        // Update the stock quantity
                        $item->product->stock->update([
                            'in_warehouse' => $newQuantity
                        ]);
                    } else {
                        // Handle the error (e.g., log the issue, throw an exception, etc.)
                        // Log::error('Stock information not found for item: ' . $item->id);
                        throw new Exception('Stock quantity not found for item: ' . $item->id);
                    }
                }
            }

            DB::commit();

            return redirect()->route('delivery');

        }catch(Exception $e){
            DB::rollBack();
            Log::error('Error recording delivery: ' .$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'An error occurred while recording the sale. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Delivery $delivery)
    {
        auth()->user()->can('view', $delivery);

        $items = $delivery->delivery_items()->get()->map(function($item){

            $product = $item->product()->withTrashed()->first();
            return [
                'id' => $item->product_id,
                'name' => $product->name,
                'size' => $product->size,
                'image' => $product->image ?? asset('product.png'),
                'stocks' => Number::format($product->stock?->in_store + $product->stock?->in_warehouse),
                'price' => Number::format($item->price, 2),
                'qty' =>  Number::format($item->quantity).' '.$product->unit,
                'total' =>  Number::format($item->price * $item->quantity, 2),
            ];
        });

        $delivery = Delivery::with(['store', 'supplier', 'purchase'])->find($delivery->id);

        $delivery->quantity = Number::format($delivery->quantity);
        $delivery->discount = Number::format($delivery->discount, 2);
        $delivery->amount = Number::format($delivery->amount, 2);
        $delivery->total = Number::format($delivery->total, 2);
        $delivery->date = $delivery->created_at->format('F d, Y');

        return inertia('Delivery/Show', [
            'title' => "View Delivery",
            'delivery' =>  $delivery,
            'delivery_items' =>  $items,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Delivery $delivery, Request $request)
    {
        auth()->user()->can('update', $delivery);

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

        $orders = Purchase::query()
            ->with('store')
            ->select('id','tx_no')
            ->where('status','pending')
            ->orderBy('tx_no', 'ASC')
            ->get();

        $items =  $delivery->delivery_items()->get()->map(function($item){
            $product = $item->product()->withTrashed()->first();

            return [
                'id' => $item->product_id,
                'name' => $product->name,
                'size' => $product->size,
                'unit' => $product->unit,
                'image' => $product->image,
                'stocks' => $product->stock?->in_store + $product->stock?->in_warehouse,
                'price' =>  $item->price,
                'qty' =>  $item->quantity,
            ];
        });

        $order = '';
        $purchase = '';

        if($request->order_id){
            $purchase = Purchase::query()
                ->with(['store','supplier','items'])
                ->find($request->order_id);

            $order =  $purchase->items()->get()->map(function($item){
                $product = $item->product()->withTrashed()->first();

                return [
                    'id' => $item->product_id,
                    'name' => $product->name,
                    'size' => $product->size,
                    'unit' => $product->unit,
                    'image' => $product->image,
                    'stocks' => $product->stock?->in_store + $product->stock?->in_warehouse,
                    'price' =>  $item->price,
                    'qty' =>  $item->quantity,
                ];
            });
        }

        $delivery_details = Delivery::with(['store','purchase'])->find($delivery->id);

        return inertia('Delivery/Edit', [
            'title' => "Edit Delivery",
            'products' =>  $products,
            'orders' =>  $orders,
            'order' =>  $order ?? '',
            'purchase' =>  $purchase ?? '',
            'purchase_items' =>  $items,
            'delivery' => $delivery_details,
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
        $user = auth()->user();
        $user->can('update', $delivery);

        $request->validated();
        $convertStringToNumber = new ConvertToNumber();

        $discount = $convertStringToNumber->convertToNumber($request->discount);
        $total = $convertStringToNumber->convertToNumber($request->total);

        $purchaseAttributes = [
            'quantity' => $request->quantity,
            'discount' => $discount,
            'amount' => $total - $discount,
            'total' => $total,
            'status' => $request->status,
            'notes' => $request->notes,
            'purchase_id' => $request->purchase_id,
            'supplier_id' => $request->supplier_id,
            'user_id' => $user->id,
            'receiver' => $request->receiver,
            'created_at' => $request->transaction_date,
        ];


        DB::beginTransaction();

        try{
            $delivery->update($purchaseAttributes);

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

            $delivery->delivery_items()->forceDelete();
            $delivery->delivery_items()->createMany($products);

            // if status is completed, add products quantity to the inventory
            if($request->status == 'completed' || $request->status == 'partial' || $request->status == 'full'){
                foreach($delivery->delivery_items as $item){
                    if (isset($item->product->stock)) {
                        // Calculate the new quantity
                        $newQuantity = $item->product->stock->in_warehouse + $item->quantity;

                        // Update the stock quantity
                        $item->product->stock->update([
                            'in_warehouse' => $newQuantity
                        ]);
                    } else {
                        // Handle the error (e.g., log the issue, throw an exception, etc.)
                        // Log::error('Stock information not found for item: ' . $item->id);
                        throw new Exception('Stock quantity not found for item: ' . $item->id);
                    }
                }
            }

            DB::commit();

            return redirect()->route('delivery');

        }catch(Exception $e){
            DB::rollBack();
            Log::error('Error recording delivery: ' .$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'An error occurred while recording the sale. Please try again.']);
        }
    }

    public function downloadPDF(Delivery $delivery)
    {
        auth()->user()->can('view', $delivery);

        $items = $delivery->delivery_items()->get()->map(function($item){
            $product = $item->product()->withTrashed()->first();

            return [
                 'id' => $item->product_id,
                'name' => $product->name,
                'size' => $product->size,
                'image' => $product->image,
                'stocks' => Number::format($product->stock?->in_store + $product->stock?->in_warehouse, precision:2),
                'price' => $item->delivery->store->currency.' '.Number::format($item->price, precision:2),
                'qty' =>  Number::format($item->quantity).' '.$product->unit,
                'total' =>  $item->delivery->store->currency.' '.Number::format($item->price * $item->quantity, precision:2),
            ];
        });

        $delivery = Delivery::with(['store', 'supplier', 'purchase'])->find($delivery->id);

        $delivery->quantity = Number::format($delivery->quantity);
        $delivery->discount = $delivery->store->currency.' '.Number::format($delivery->discount, precision:2);
        $delivery->amount = $delivery->store->currency.' '.Number::format($delivery->amount, precision:2);
        $delivery->total = $delivery->store->currency.' '.Number::format($delivery->total, precision:2);

        $pdf = Pdf::loadView('delivery.downloadPdf', [
            'title' => "Delivery Details",
            'delivery' =>  $delivery,
            'delivery_items' =>  $items,
            'suppliers' => Supplier::select('id', 'name')->orderBy('name','ASC')->get(),
        ]);

        $filename = $delivery->tx_no.'-'.date('Y-m-d').'.pdf';
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream($filename);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Delivery $delivery)
    {
        auth()->user()->can('delete', $delivery);

        $delivery->delete();
        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        auth()->user()->can('bulk_delete', Delivery::class);

        Delivery::whereIn('id',$request->delivery_id)->delete();
        return redirect()->back();
    }
}
