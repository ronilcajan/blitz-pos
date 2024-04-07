<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Pivot
{
    use HasFactory, SoftDeletes;

    protected $table = 'supplier';

    protected $guarded = [];

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    } 

    public function scopeFilter($query, array $filter){
        if(!empty($filter['search'])){
            $search = $filter['search'];

            $query->whereAny([
                'name',
                'contact_person',
                'email',
                'phone', 
                'address',
            ], 'LIKE', "%{$search}%")
            ->orWhereHas('store', function($q) use ($search){
                $q->where('name', $search);
            });
        }

        if(!empty($filter['store'])){
            $store = $filter['store'];

            $query->whereHas('store', function($q) use ($store){
                $q->where('name', $store);
            });
        }
    }
}
