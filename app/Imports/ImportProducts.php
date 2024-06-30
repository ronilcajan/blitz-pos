<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportProducts implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $category = ProductCategory::where('name', $row['category'])->first();

        $productAttributes = [
            'name' => $row['name'],
            'barcode' => $row['barcode'],
            'size' => $row['size'],
            'color' => '',
            'dimension' => $row['dimension'],
            'unit' => $row['unit'],
            'product_type' => $row['product_type'],
            'brand' => $row['brand'],
            'manufacturer' => $row['manufacturer'],
            'description' => $row['description'],
            'product_category_id' => $category->id,
            'store_id' => auth()->user()->store_id,
        ];

        $product = Product::create($productAttributes);

        $productPriceAttributes = [
            'base_price' => $row['base_price'],
            'markup_price' => $row['markup_price'],
            'sale_price' => $row['base_price'] + $row['markup_price'],
            'discount' => 0,
            'manual_percentage' => 'manual',
            'discount_price' => $row['base_price'] + $row['markup_price'],
            'product_id' => $product->id
        ];

        $productStocksAttributes = [
            'sku' => $row['sku'],
            'min_quantity' => $row['min_quantity'],
            'in_store' => $row['in_store'],
            'in_warehouse' => $row['in_warehouse'],
            'product_id' => $product->id
        ];

         // Update or create price attributes
         $product->price()->updateOrCreate([], $productPriceAttributes);
         // Update or create stock attributes
         $product->stock()->updateOrCreate([], $productStocksAttributes);
    }

    public function batchSize(): int{
        return 1000;
    }
}
