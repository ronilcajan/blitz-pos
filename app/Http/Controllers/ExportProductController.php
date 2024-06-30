<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\ProductsExportTemplate;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportProductController extends Controller
{
    public function export_excel()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function export_template(){
        return Excel::download(new ProductsExportTemplate, 'products_import_template.xlsx');
    }

    public function export_pdf(){
        $product = Product::with(['store','price', 'stock','category']);
        $items = $product
                ->orderBy('name','ASC')
                ->get()
                ->map(function($product){
                    return [
                        'name' => $product->name,
                        'barcode' => $product->barcode,
                        'size' => $product->size,
                        'unit' => $product->unit,
                        'product_type' => $product->product_type,
                        'brand' => $product->brand,
                        'expiration_date' => $product->expiration_date ? date('y-m-d',strtotime($product->expiration_date)) : '',
                        'description' => $product->description,
                        'category' => $product->category?->name,
                        'sku' => $product->sku,
                        'in_store' => number_format($product->stock?->in_store),
                        'in_warehouse' => number_format($product->stock?->in_warehouse),
                        'base_price' => number_format($product->price?->base_price,2),
                        'markup_price' => number_format($product->price?->markup_price,2),
                        'price' =>  number_format($product->price?->discount_price,2),
                    ];
                });

        $pdf = Pdf::loadView('products.export_products_pdf', [
            'title' => "Products Inventory",
            'store' => auth()->user()->store,
            'items' => $items,
        ]);

        $filename = 'products_pdf'.date('-Y-m-d').'.pdf';
        $pdf->setPaper('Legal', 'landscape');
        return $pdf->download($filename);
    }
}
