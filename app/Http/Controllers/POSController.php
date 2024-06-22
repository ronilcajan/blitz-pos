<?php

namespace App\Http\Controllers;

use App\Classes\ConvertToNumber;
use App\Classes\TransactionCodeGenerator;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SoldItems;
use App\Models\Store;
use Illuminate\Http\Request;

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

        $request->validated();

        $generator = new TransactionCodeGenerator();
        $convertStringToNumber = new ConvertToNumber();

        $sub_total = $convertStringToNumber->convertToNumber($request->sub_total);
        $discount = $convertStringToNumber->convertToNumber($request->discount);
        $tax = $convertStringToNumber->convertToNumber($request->tax);
        $total = $convertStringToNumber->convertToNumber($request->total);
        $payment_tender = $convertStringToNumber->convertToNumber($request->payment_tender);
        $payment_changed = $convertStringToNumber->convertToNumber($request->payment_changed);

        $salesttributes = [
            'tx_no' => "INV" .$generator->generate(),
            'quantity' => $request->quantity,
            'sub_total' => $sub_total,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'payment_tender' => $payment_tender,
            'payment_changed' => $payment_changed,
            'referrence' => $request->referrence,
            'notes' => $request->notes,
            'customer_id' => $request->customer_id,
            'store_id' => auth()->user()->store_id ?? 1,
            'user_id' =>  auth()->user()->id,
        ];

        $sales = Sale::create($salesttributes);

        $sold_items = [];

        foreach($request->items as $sold_item){
            $sold_items[] = [
                'quantity' =>  $sold_item['qty'],
                'store_id' =>  auth()->user()->store_id ?? 1,
                'sale_id' =>  $sales->id,
                'product_id' =>  $sold_item['product_id'],
                'created_at' => now(),
                'updated_at' => now()
            ];

        }
        SoldItems::insert($sold_items);

        return redirect()->back();

    }

}
