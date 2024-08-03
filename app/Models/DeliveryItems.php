<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryItems extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'delivery_items';

    protected $guarded = [];

    public function delivery():BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
