<?php

namespace App\Http\Controllers;

use App\Exports\SupplierExport;
use App\Exports\SuppliersExportTemplate;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportSupplierController extends Controller
{
    public function export_excel()
    {
        return Excel::download(new SupplierExport, 'suppliers.xlsx');
    }

    public function export_template(){
        return Excel::download(new SuppliersExportTemplate, 'suppliers_import_template.xlsx');
    }

    public function export_pdf(){
        $supplier = Supplier::with(['store']);
        $suppliers = $supplier
                ->orderBy('name','ASC')
                ->get()
                ->map(function($supplier){
                    return [
                       'id' => $supplier->id,
                        'name' => $supplier->name,
                        'contact_person' => $supplier->contact_person,
                        'email' => $supplier->email,
                        'phone' => $supplier->phone,
                        'address' => $supplier->address,
                        'logo' => $supplier->logo,
                        'store' => $supplier->store?->name,
                    ];
                });

        $pdf = Pdf::loadView('supplier.export_pdf', [
            'title' => "Supplier List - ".date('Y-m-d H:i:s'),
            'store' => auth()->user()->store,
            'suppliers' => $suppliers,
        ]);

        $filename = 'suppliers_pdf'.date('-Y-m-d').'.pdf';
        $pdf->setPaper('Legal', 'landscape');
        return $pdf->download($filename);
    }
}
