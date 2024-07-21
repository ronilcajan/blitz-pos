<?php
namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ImportProducts implements ToModel, WithCalculatedFormulas, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Filter out empty values and check if the row is empty
        if (empty(array_filter($row))) {
            return null;
        }

        DB::beginTransaction();

        try {
            $category = ProductCategory::where('name', $row['category'])->first();

            if (!$category) {
                $category = ProductCategory::create([
                    'name' => $row['category'],
                    'store_id' => auth()->user()->store_id,
                ]);
            }

            // Now you can safely access $category->id
            $categoryId = $category->id;

            $productAttributes = [
                'name' => $row['name'],
                'barcode' => $row['barcode'],
                'size' => $row['size'],
                'color' => $row['color'],
                'dimension' => $row['dimension'],
                'unit' => $row['unit'],
                'usage_type' => $row['usage_type'],
                'brand' => $row['brand'],
                'visible' => $row['visible'],
                'description' => $row['description'],
                'product_category_id' => $categoryId,
                'store_id' => auth()->user()->store_id,
            ];

            $product = Product::updateOrCreate(['barcode' => $row['barcode']], $productAttributes);

            $productStocksAttributes = [
                'sku' => $row['sku'],
                'min_quantity' => $row['min_quantity'],
                'in_store' => $row['in_store'],
                'in_warehouse' => $row['in_warehouse'],
                'product_id' => $product->id
            ];

            $productPriceAttributes = [
                'base_price' => $row['base_price'],
                'markup_price' => $row['markup_price'],
                'discount_rate' => $row['discount_rate'],
                'discount_type' => $row['discount_type'],
                'tax_rate' => $row['tax_rate'],
                'tax_type' => $row['tax_type'],
                'sale_price' => $row['sale_price'],
                'product_id' => $product->id
            ];

            // Update or create price attributes
            $product->price()->updateOrCreate([], $productPriceAttributes);
            // Update or create stock attributes
            $product->stock()->updateOrCreate([], $productStocksAttributes);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
