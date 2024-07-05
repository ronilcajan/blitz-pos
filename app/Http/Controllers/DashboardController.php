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
        $expenses = Expenses::whereNot('status','pending')->sum('amount');
        $sales = Sale::where('status','complete')->sum('total');

        $user = auth()->user();

        return inertia('Dashboard',[
            'title' => "Dashboard",
            'products' => Number::format($products),
            'delivery' => Number::format($delivery),
            'expenses' => number_format($expenses,2),
            'sales' => number_format($sales,2),
        ]);
    }
}
