<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportSaleController extends Controller
{
    public function export_excel(Request $request)
    {
        $date = [
            'from_date' => $request->from_date,
            'to_date' => $request->to_date
        ];

        $filename = 'sales-'.date('Y-m-d').'.xlsx';

        return Excel::download(new SalesExport($date), $filename);
    }

    public function export_pdf(Request $request){

        $from_date = $request->from_date;
        $to_date = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;

        $query = Sale::with(['customer', 'user']);

        $description = "Sales Information";
        // Apply date filtering only if both dates are provided
        if ($from_date && $to_date) {
            $query->whereBetween('created_at', [$from_date, $to_date]);
            $description .= " from ".date('m/d/Y', strtotime($from_date)).' to '.date('m/d/Y', strtotime($to_date)).'.';
        }

        $sales = $query->get()->map(function ($sale) {
            return [
                'transaction' => $sale->tx_no,
                'payment_method' => $sale->payment_method,
                'items' => $sale->quantity,
                'discount' => $sale->discount,
                'amount' =>  number_format($sale->total,2),
                'status' => $sale->status->getLabelText(),
                'customer' => $sale->customer?->name,
                'cashier' => $sale->user?->name,
                'created_at' => $sale->created_at->tz(session('timezone'))->format('M d, Y h:i A'),
            ];
        });


        $pdf = Pdf::loadView('sale.export_sales_pdf', [
            'title' => "Sales Export Data",
            'description' => $description,
            'store' => auth()->user()->store,
            'sales' => $sales,
        ]);

        $filename = 'sales_pdf'.date('-Y-m-d').'.pdf';
        $pdf->setPaper('Legal', 'landscape');
        return $pdf->download($filename);
    }
}
