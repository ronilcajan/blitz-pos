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
                            'product_type' => $product->product_type,
                            'brand' => $product->brand,
                            'manufacturer' => $product->manufacturer,
                            'visible' => $product->visible,
                            'expiration_date' => $product->expiration_date,
                            'description' => $product->description,
                            'category' => $product->category?->name,
                            'sku' => $product->sku,
                            'min_quantity' => $product->stock?->in_store,
                            'in_store' => $product->stock?->in_store,
                            'in_warehouse' => $product->stock?->in_warehouse,
                            'base_price' => $product->price?->base_price,
                            'markup_price' => $product->price?->markup_price,
                            'price' =>  $product->price?->discount_price,
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
            'product_type',
            'brand',
            'manufacturer',
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
            'price',
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
