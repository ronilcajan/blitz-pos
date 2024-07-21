<?php

namespace App\Http\Controllers;

use App\Imports\ImportProducts;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ImportProductController extends Controller
{
    public function __invoke(Request $request)
    {
        try {

            $filePath = $request->file('import_file')->store('temp');

            Excel::import(new ImportProducts, $filePath);

            Storage::delete($filePath);

            return redirect()->back();
        }
         catch (Exception $e) {
            
            Log::error('Error recording sales: ' .$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'An error occurred while importing the products. Please try again.'.$e->getMessage()]);
         }
    }
}
