<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InHouseTransactionItems extends Model
{
    use HasFactory, SoftDeletes;

    protected $table ='in_house_transaction_items';
    protected $guarded = [];

    public function in_house_transaction():BelongsTo
    {
        return $this->belongsTo(InhouseStockTransaction::class);
    }

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
