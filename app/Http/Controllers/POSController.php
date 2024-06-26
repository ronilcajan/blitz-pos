<?php

namespace App\Http\Controllers;

use App\Classes\TransactionCodeGenerator;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SoldItems;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class POSController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $products =  Product::query()
            ->with(['price','stock','category'])
            ->orderBy('name', 'ASC')
            ->where('visible','published')
            ->filter(request(['search']))
            ->paginate(24)
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

        $store = Store::find($user->store_id);

        // $orders = DraftOrder::select('id','name')->orderBy('name','ASC')->get();
        $customers = Customer::select('id','name')->orderBy('name','ASC')->get();
        $units = ProductUnit::select('id','name')->orderBy('name', 'ASC')->get();
        $categories = ProductCategory::select('id','name')->orderBy('name', 'ASC')->get();
        $stores = Store::select('id','name')->orderBy('name', 'ASC')->get();

        return inertia('Pos/Index', [
            'title' => "Point of Sale",
            'store' => $store,
            'stores' => $stores,
            'customers' => $customers,
            'products' => $products,
            'units' => $units,
            'categories' => $categories,
            'filter' => $request->only(['search','store','page']),
        ]);
    }

    public function store(StoreSaleRequest $request){

        $validatedData = $request->validated();

        $salesttributes = [
            'tx_no' => '1',
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
            $sale->update(['tx_no' => 'INV' . str_pad($sale->id, 5, '0', STR_PAD_LEFT)]);

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

}
