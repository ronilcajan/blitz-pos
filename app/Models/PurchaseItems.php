<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseItems extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_items';

    protected $guarded = [];

    public function purchase():BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}
