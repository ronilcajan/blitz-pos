<?php

namespace App\Http\Controllers;

use App\Classes\TransactionCodeGenerator;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SoldItems;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth()->user()->can('viewAny', Purchase::class);

        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? Sale::count() : $request->per_page)
        : 10;

        $sales = Sale::query()
            ->with(['store','customer','user','customer'])
            ->orderBy('created_at', 'DESC')
            ->filter(request(['search','store','supplier','customer']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($sale) {
                return [
                    'id' => $sale->id,
                    'tx_no' => $sale->tx_no,
                    'quantity' => Number::format($sale->quantity).'  Items',
                    'discount' => Number::currency($sale->discount, in: $sale->store->currency),
                    'sub_total' => Number::currency($sale->sub_total, in: $sale->store->currency),
                    'total' =>  Number::currency($sale->total, in: $sale->store->currency),
                    'payment_method' => $sale->payment_method,
                    'status' => $sale->status,
                    'user' => $sale->user?->name,
                    'customer' => $sale->customer?->name,
                    'store' => $sale->store?->name,
                    'created_at' => $sale->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('Sale/Index', [
            'title' => "Sales",
            'sales' => $sales,
            'customers' => Customer::select('id', 'name')->orderBy('name','ASC')->get(),
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'filters' => $request->only(['search','store','per_page']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request){

        $validatedData = $request->validated();

        $generator = new TransactionCodeGenerator();

        $salesttributes = [
            'tx_no' => "INV" .$generator->generate(),
            'quantity' => $request->quantity,
            'sub_total' => $request->sub_total,
            'discount' => $request->discount,
            'tax' => $request->tax,
            'total' => $request->total,
            'payment_method' => $request->payment_method,
            'payment_tender' => $request->payment_tender,
            'payment_changed' => $request->payment_changed,
            'referrence' => $validatedData['referrence'],
            'notes' => $validatedData['notes'],
            'customer_id' => $validatedData['customer_id'],
            'store_id' => auth()->user()->store_id ?? 1,
            'user_id' =>  auth()->user()->id,
        ];

        DB::beginTransaction();
        try{

            $sale = Sale::create($salesttributes);
            // $sale = auth()->user()->sales->create($salesttributes);

             // Prepare sold items data
            $soldItems = [];
            $currentTimestamp = now();

            foreach ($request->items as $item) {
                $soldItems[] = [
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'store_id' => auth()->user()->store_id ?? 1,
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp,
                ];
            }

            SoldItems::insert($soldItems);

            DB::commit();

            if($request->print){
                return inertia('Pos/Index', [
                    'sales_id' => $sale->id
                ]);
            }

            return redirect()->back();

        }catch(Exception $e){
            DB::rollBack();
            Log::error('Error recording sales: ' .$e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while recording the sale. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        return view('sale.print_receipt', [
            'title' => 'Print Sales Acknowledgement',
            'sale' => $sale,
            'items' => $sale->sold_items
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        // auth()->user()->can('delete', $sale);

        $sale->delete();
        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        // auth()->user()->can('create', Purchase::class);

        Sale::whereIn('id',$request->sales_id)->delete();
        return redirect()->back();
    }
}
