<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\ProductsExportTemplate;
use Maatwebsite\Excel\Facades\Excel;

class ExportProductController extends Controller
{
    public function show()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function export_template(){
        return Excel::download(new ProductsExportTemplate, 'products_import_template.xlsx');
    }
}
