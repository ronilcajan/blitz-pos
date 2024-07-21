<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ProductsExport implements FromCollection, WithHeadings, WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Product::with(['price', 'stock','category'])
                    ->orderBy('name', 'ASC')
                    ->get()
                    ->map(function ($product) {
                        return [
                            'name' => $product->name,
                            'barcode' => $product->barcode,
                            'size' => $product->size,
                            'color' => $product->color,
                            'dimension' => $product->dimension,
                            'unit' => $product->unit,
                            'usage_type' => $product->usage_type,
                            'brand' => $product->brand,
                            'visible' => $product->visible,
                            'expiration_date' => $product->expiration_date,
                            'description' => $product->description,
                            'category' => $product->category?->name,
                            'sku' => $product->sku,
                            'min_quantity' => $product->stock?->in_store,
                            'in_store' => $product->stock?->in_store,
                            'in_warehouse' => $product->stock?->in_warehouse,
                            'base_price' => $product->price?->base_price,
                            'markup_price' =>$product->price?->markup_price,
                            'discount_rate' => $product->price?->discount_rate,
                            'discount_type' => $product->price?->discount_type,
                            'tax_rate' => $product->price?->tax_rate,
                            'tax_type' => $product->price?->tax_type,
                            'sale_price' => $product->price?->sale_price
                        ];
                    });
    }

    public function headings(): array
    {
        return [
           'name',
            'barcode',
            'size',
            'color',
            'dimension',
            'unit',
            'usage_type',
            'brand',
            'visible',
            'expiration_date',
            'description',
            'category',
            'sku',
            'min_quantity',
            'in_store',
            'in_warehouse',
            'base_price',
            'markup_price',
            'discount_rate',
            'discount_type',
            'tax_rate',
            'tax_type',
            'sale_price'
        ];
    }

    public function columnWidths(): array
    {
        $data = $this->collection();
        $headings = $this->headings();
        $columnWidths = [];

        foreach ($headings as $key => $heading) {
            $maxLength = strlen($heading); // Start with the heading length

            // Iterate through the data to find the maximum length in each column
            foreach ($data as $item) {
                $value = $item[$heading] ?? '';
                $maxLength = max($maxLength, strlen($value));
            }

            // Adjust the width based on the maximum length found
            $columnWidths[chr(65 + $key)] = $maxLength + 5; // Adding some extra padding
        }

        return $columnWidths;
    }
}
