<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Number;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
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
                            'sku' => $product->sku,
                            'size' => $product->size,
                            'dimension' => $product->dimension,
                            'unit' => $product->unit,
                            'product_type' => $product->product_type,
                            'brand' => $product->brand,
                            'manufacturer' => $product->manufacturer,
                            'description' => $product->description,
                            'visible' => $product->visible,
                            'category' => $product->category?->name,
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
            'sku',
            'size',
            'dimension',
            'unit',
            'product_type',
            'brand',
            'manufacturer',
            'description',
            'visible',
            'category',
            'in_store',
            'in_warehouse',
            'base_price',
            'markup_price',
            'price',
        ];
    }
}
