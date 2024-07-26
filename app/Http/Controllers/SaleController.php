<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSaleRequest;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
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
            ->filter(request(['search','store','supplier','customer','from_date','to_date']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($sale) {
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
                    'created_at' => $sale->created_at->format('M d, Y h:i: A'),
                ];
        });

        $dailySalesTotal = Sale::getDailySalesTotal();
        $weeklySalesTotal = Sale::getWeeklySalesTotal();
        $monthlySalesTotal = Sale::getMonthlySalesTotal();
        $yearlySalesTotal = Sale::getYearlySalesTotal();

        return inertia('Sale/Index', [
            'title' => "Sales",
            'sales' => $sales,
            'dailySalesTotal' => $dailySalesTotal,
            'weeklySalesTotal' => $weeklySalesTotal,
            'monthlySalesTotal' => $monthlySalesTotal,
            'yearlySalesTotal' => $yearlySalesTotal,
            'customers' => Customer::select('id', 'name')->orderBy('name','ASC')->get(),
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'filters' => $request->only(['search','store','per_page']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale = Sale::with(['store','customer'])->find($sale->id);

        $sale->quantity = Number::format($sale->quantity);
        $sale->tax = $sale->store->currency.' '.Number::format($sale->tax, precision:2);
        $sale->discount = $sale->store->currency.' '.Number::format($sale->discount, precision:2);
        $sale->sub_total = $sale->store->currency.' '.Number::format($sale->sub_total, precision:2);
        $sale->total = $sale->store->currency.' '.Number::format($sale->total, precision:2);

        $pdf = Pdf::loadView('sale.pdf_receipt', [
            'title' => "Sales Acknowledgement",
            'sale' => $sale,
            'items' => $sale->sold_items
        ]);


        $paperSize = [0,0,227,800];

        $GLOBALS['bodyHeight'] = 0;

        $pdf->setPaper($paperSize);

        // getting the height of the whole page
        $pdf->getDomPDF()->setCallbacks([
            'myCallbacks' => [
               'event' => 'end_frame', 'f' => function ($frame) {
                $node = $frame->get_node();

                if (strtolower($node->nodeName) === "body") {
                    $padding_box = $frame->get_content_box();
                    $GLOBALS['bodyHeight'] += $padding_box['h'];
                }
            }]
        ]);

        $pdf->render();
        unset($pdf);

        $docHeight = $GLOBALS['bodyHeight'] + 20;

        $pdf = Pdf::loadView('sale.pdf_receipt', [
            'title' => "Sales Acknowledgement",
            'sale' => $sale,
            'items' => $sale->sold_items
        ]);
        $pdf->setPaper(array(0,0,227,  $docHeight));

        $pdf->render();

        return response($pdf->output(), 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="filtered-data.pdf"');

    }

    public function downloadSalesInvoice(Sale $sale)
    {
        $items = $sale->sold_items()->get()->map(function($item){
            return [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'size' => $item->product->size,
                'unit' => $item->product->unit,
                'image' => $item->product->image,
                'stocks' => $item->product->stock?->in_store + $item->product->stock?->in_warehouse,
                'price' =>  $item->price,
                'qty' =>  Number::format($item->quantity).' '.$item->product->unit,
                'total' =>  $item->sale->store->currency.' '.Number::format($item->price * $item->quantity, precision:2),
            ];
        });

        $sale = Sale::with(['store','customer'])->find($sale->id);

        $sale->quantity = Number::format($sale->quantity);
        $sale->discount = $sale->store->currency.' '.Number::format($sale->discount, precision:2);
        $sale->sub_total = $sale->store->currency.' '.Number::format($sale->sub_total, precision:2);
        $sale->total = $sale->store->currency.' '.Number::format($sale->total, precision:2);


        $pdf = Pdf::loadView('sale.sales_invoice', [
            'title' => "Sales Invoice",
            'sale' => $sale,
            'sold_items' => $items,
        ]);

        $filename = $sale->tx_no.date('-Y-m-d').'.pdf';
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream($filename);
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

    public function updateStatus(Request $request)
    {
        $sale = Sale::find($request->id);

        DB::beginTransaction();

        try {
            // if status is void add stocks back to the store
            if($request->status == 'void'){
                foreach($sale->sold_items as $item){
                    $item->product->stock->update([
                        'in_store' => $item->product->in_store + $item->quantity
                    ]);
                }
            }

            // if status is complete deduct stocks to the store
            if($request->status == 'complete'){
                foreach($sale->sold_items as $item){
                    $item->product->stock->update([
                        'in_store' => $item->product->in_store - $item->quantity
                    ]);
                }
            }

            $sale->update(['status' => $request->status]);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating sales status: ' .$e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while recording the sale status. Please try again.']);
        }

        return redirect()->back();
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
