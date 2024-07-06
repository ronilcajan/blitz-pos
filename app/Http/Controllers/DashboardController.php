<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Expenses;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Number;

class DashboardController extends Controller
{
    public function index(){

        $products = Product::count();
        $delivery = Delivery::whereNot('status','pending')->count();
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
                    'discount' => Number::currency($sale->discount, in: $sale->store->currency),
                    'sub_total' => Number::currency($sale->sub_total, in: $sale->store->currency),
                    'total' =>  Number::currency($sale->total, in: $sale->store->currency),
                    'payment_method' => $sale->payment_method,
                    'user' => $sale->user?->name,
                    'customer' => $sale->customer?->name,
                    'store' => $sale->store?->name,
                    'status' => $sale->status->getLabelText(),
                    'statusColor' => $sale->status->getLabelColor(),
                    'created_at' => $sale->created_at->format('M d, Y h:i: A'),
                ];
        });

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
            'delivery' => Number::format($delivery),
            'expenses' => number_format($expenses,2),
            'sales' => number_format($sales,2),
            'sales_current_year' => $sales_current_year,
            'sales_previous_year' => $sales_previous_year,
            'sales_recent' => $sales_recent,
            'expenses_data_chart' => $expenses_data_chart,
            'stock_alert' => $stock_alert,
        ]);
    }
}
