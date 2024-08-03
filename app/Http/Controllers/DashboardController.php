<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expenses;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Number;

class DashboardController extends Controller
{

    public function index(){

        $products = Product::count();
        $customers = Customer::count();
        $expenses = Expenses::where('status','approved')->sum('amount');
        $sales = Sale::where('status','complete')->sum('total');

        $sales_current_year = Sale::getCurrentSales();
        $sales_previous_year = Sale::getPreviousSales();
        $expenses_data_chart = Expenses::getTotalExpenses();

        $sales_recent = Sale::query()
            ->with(['store','customer','user','customer'])
            ->orderBy('created_at', 'DESC')
            ->take(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'tx_no' => $sale->tx_no,
                    'quantity' => Number::format($sale->quantity).'  Items',
                    'discount' => !$sale->store->currency ? Number::format($sale->discount, 2) : Number::currency($sale->discount, in: $sale?->store?->currency),
                    'sub_total' => !$sale->store->currency ? Number::format($sale->sub_total, 2) : Number::currency($sale->sub_total, in: $sale?->store?->currency),
                    'total' => !$sale->store->currency ? Number::format($sale->total, 2) : Number::currency($sale->total, in: $sale?->store?->currency),
                    'payment_method' => $sale->payment_method,
                    'user' => $sale->user?->name,
                    'customer' => $sale->customer?->name ?? 'Walk-in',
                    'store' => $sale->store?->name,
                    'status' => $sale->status->getLabelText(),
                    'statusColor' => $sale->status->getLabelColor(),
                    'created_at' => $sale->created_at->tz(session('timezone') ?? 'UTC')->format('M d, Y h:i: A'),
                ];
        });

        // dd(auth()->user()->subscription());

        $stock_alert = Product::with(['stock','price'])
            ->orderBy('name', 'ASC')
            ->whereHas('stock', function($q){
                $q->whereColumn('in_store', '<=', 'min_quantity')
                ->whereColumn('in_warehouse', '<=', 'min_quantity');
            })
            ->take(15)
            ->get();

        return inertia('Dashboard',[
            'title' => "Dashboard",
            'products' => Number::format($products),
            'customers' => Number::format($customers),
            'expenses' => Number::format($expenses,2),
            'sales' => Number::format($sales,2),
            'sales_current_year' => $sales_current_year,
            'sales_previous_year' => $sales_previous_year,
            'sales_recent' => $sales_recent,
            'expenses_data_chart' => $expenses_data_chart,
            'stock_alert' => $stock_alert,
        ]);
    }
}
