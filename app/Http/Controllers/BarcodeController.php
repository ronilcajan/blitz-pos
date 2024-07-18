<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BarcodeController extends Controller
{
    public function checkBarcode(Request $request)
    {
        $barcode = $request->barcode;
        $isUnique = !Product::with('stock')
            ->where('barcode', $barcode)
            ->exists();

        return response()->json(['isUnique' => $isUnique]);
    }

    public function getProducts(Request $request){
        $barcode = $request->input('barcode');
        $productDetails = $this->getProduct($barcode);

        return response()->json($productDetails);
    }

    // public function getProducts(Request $request)
    // {
    //     $barcode = $request->input('barcode');

    //     try {
    //         $response = Http::get('https://api.upcdatabase.org/products/' . $barcode);

    //         // Check if the request was successful (status code 200)
    //         if ($response->successful()) {
    //             // Decode the JSON response and return it
    //             return response()->json($response->json());
    //         } else {
    //             // Handle errors, if any (e.g., server errors)
    //             return response()->json(['error' => 'Failed to fetch product data'], $response->status());
    //         }
    //     } catch (\Exception $e) {
    //         // Handle exceptions (e.g., network issues, timeouts)
    //         return response()->json(['error' => 'Failed to fetch product data: ' . $e->getMessage()], 500);
    //     }
    // }

    public function getProduct($barcode){
        $response = Http::get('https://barcode.monster/api/' . $barcode);

        if ($response->successful()) {
            return $response->json();
        } else {
            // Handle the error
            return response()->json(['error' => 'Unable to fetch product details'], $response->status());
        }
    }
}
