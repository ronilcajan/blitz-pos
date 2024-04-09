<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSupplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_supplier';

    protected $guarded = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function supplier():BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function scopeFilter($query, array $filter){
        if(!empty($filter['search'])){
            $search = $filter['search'];

            $query->whereAny([
                'unit_price',
                ], 'LIKE', "%{$search}%")
            ->orWhereHas('product', function($q) use ($search){
                $q->where('name', $search);
            });
        }

        if(!empty($filter['store'])){
            $store = $filter['store'];

            $query->whereHas('product.store', function($q) use ($store){
                $q->where('name', $store);
            });
        }

        if(!empty($filter['supplier'])){
            $supplier = $filter['supplier'];

            $query->whereHas('supplier', function($q) use ($supplier){
                $q->where('name', $supplier);
            });
        }

        if(!empty($filter['category'])){
            $category = $filter['category'];

            $query->whereHas('product.category', function($q) use ($category){
                $q->where('name', $category);
            });
        }
    }
}
