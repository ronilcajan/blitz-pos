<?php

namespace App\Http\Controllers;

use App\Classes\ConvertToNumber;
use App\Http\Requests\StoreInhouseTransactionRequest;
use App\Models\InhouseStockTransaction;
use App\Models\InHouseTransactionItems;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class InhouseStockTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request )
    {
        // Gate::authorize('viewAny', Supplier::class);

        $perPage = $request->per_page
        ? (
            $request->per_page == 'All'
            ? InhouseStockTransaction::count()
            : $request->per_page
        )
        : 10;

        $transactions = InhouseStockTransaction::query()
            ->with('user')
            ->orderBy('id', 'DESC')
            ->filter(request(['search']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'tx_no' => $transaction->tx_no,
                    'quantity' => $transaction->quantity,
                    'amount' => $transaction->amount ? Number::currency($transaction->amount, in: auth()->user()->store->currency) : number_format($transaction->amount, 2),
                    'status' => $transaction->status,
                    'notes' => $transaction->notes,
                    'created_by' => $transaction->user->name,
                    'created_at' => $transaction->created_at->format('M d, Y'),
                ];
        });

        return inertia('InhouseStockTransaction/Index', [
            'title' => 'Inhouse Stock Transaction',
            'transactions' => $transactions,
            'filter' => $request->only(['search','store','per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products =  Product::query()
            ->with(['store', 'price', 'stock','category'])
            ->where('usage_type', 'internal_use')
            ->whereHas('stock', fn($q) => $q->where('in_warehouse', '>', 0))
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
                    'image' => $product?->image ? asset('storage/' .$product?->image ) : asset('product.png'),
                    'category' => $product->category?->name,
                    'stocks' => $product->stock?->in_warehouse,
                    'price' => $product->price?->sale_price,
                ];
        });

        return inertia('InhouseStockTransaction/Create', [
            'title' => 'Add New Inhouse Stock Transaction',
            'products' => $products,
            'filter' => request()->only(['search','barcode']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInhouseTransactionRequest $request)
    {
        // auth()->user()->can('create', Purchase::class);

        $request->validated();

        $convertStringToNumber = new ConvertToNumber();

        $total = $convertStringToNumber->convertToNumber($request->total);

        $transactionAttributes = [
            'quantity' => $request->quantity,
            'amount' => $total,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
            'created_at' => $request->transaction_date,
        ];

        DB::beginTransaction();
        try{

            $transaction = InhouseStockTransaction::create($transactionAttributes);
            
            $transaction->update(['tx_no' => 'INH' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT)]);

            $items = [];

            foreach($request->items as $product){
                $items[] = [
                    'quantity' =>  $product['qty'],
                    'amount' =>  $product['price'],
                    'inhouse_stock_transaction_id' =>  $transaction->id,
                    'product_id' =>  $product['id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            InHouseTransactionItems::insert($items);

            if( $request->status === 'completed'){

                foreach($request->items as $item){
                    $product = Product::with('stock')->find($item['id']);

                    if ($product && $product->stock) {
                        // Calculate the new stock
                        $newStock = $product->stock->in_warehouse - $item['qty'];
                        // Update the stock in the database
                        $product->stock->update(['in_warehouse' => $newStock < 0 ? 0 : $newStock]);
                       
                    }
                }
                return redirect()->to('in_house');
            }

            DB::commit();

            return redirect()->back();

        }catch(\Exception $e){

            DB::rollBack();
            Log::error('Error recording transaction: ' .$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'An error occurred while recording the transaction. Please try again.'.$e->getMessage()]);
        }    
    }

    /**
     * Display the specified resource.
     */
    public function show(InhouseStockTransaction $inhouseStockTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InhouseStockTransaction $in_house)
    {
        $products =  Product::query()
            ->with(['store', 'price', 'stock','category'])
            ->where('usage_type', 'internal_use')
            ->whereHas('stock', fn($q) => $q->where('in_warehouse', '>', 0))
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
                    'image' => $product?->image ? asset('storage/' .$product?->image ) : asset('product.png'),
                    'category' => $product->category?->name,
                    'stocks' => $product->stock?->in_warehouse,
                    'price' => $product->price?->sale_price,
                ];
            });



        $items =  $in_house->used_items()->with('product')->get()->map(function($item){

            return [
                'id' => $item->product_id,
                'name' => $item->product?->name,
                'size' => $item->product?->size,
                'unit' => $item->product?->unit,
                'image' => $item->product?->image? asset('storage/' .$item->product?->image ) : asset('product.png'),
                'stocks' => $item->product?->stock?->in_store + $item->product?->stock?->in_warehouse,
                'price' =>  $item->amount,
                'qty' =>  $item->quantity,
            ];
        });

        return inertia('InhouseStockTransaction/Edit', [
            'title' => 'Edit Inhouse Stock Transaction',
            'products' => $products,
            'transaction' => $in_house,
            'items' => $items,
            'filter' => request()->only(['search','barcode']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreInhouseTransactionRequest $request, InhouseStockTransaction $in_house)
    {

        $request->validated();

        $convertStringToNumber = new ConvertToNumber();

        $total = $convertStringToNumber->convertToNumber($request->total);
        
        $transactionAttributes = [
            'quantity' => $request->quantity,
            'amount' => $total,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
            'created_at' => $request->transaction_date,
        ];

        DB::beginTransaction();
        try{

            $in_house->update($transactionAttributes);

            $items = [];

            InHouseTransactionItems::where('inhouse_stock_transaction_id', $in_house->id)->delete();

            foreach($request->items as $product){
                $items[] = [
                    'quantity' =>  $product['qty'],
                    'amount' =>  $product['price'],
                    'inhouse_stock_transaction_id' => $in_house->id,
                    'product_id' =>  $product['id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            InHouseTransactionItems::insert($items);

            if( $request->status == 'completed'){

                foreach($request->items as $item){
                    $product = Product::with('stock')->find($item['id']);

                    if ($product && $product->stock) {
                        // Calculate the new stock
                        $newStock = $product->stock->in_warehouse - $item['qty'];
                        // Update the stock in the database
                        $product->stock->update(['in_warehouse' => $newStock < 0 ? 0 : $newStock]);
                       
                    }

                }

                return redirect()->to('in_house');
            }

            DB::commit();

            return redirect()->back();

        }catch(\Exception $e){

            DB::rollBack();
            Log::error('Error recording transaction: ' .$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'An error occurred while recording the transaction. Please try again.'.$e->getMessage()]);
        }  
    }

    public function bulkDelete(Request $request)
    {
        // Gate::authorize('bulk_delete', Supplier::class);

        InhouseStockTransaction::whereIn('id',$request->transactions_id)->delete();
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        InhouseStockTransaction::find($request->inhouseStockTransaction)->delete();
        return redirect()->back();
    }
}
