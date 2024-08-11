<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductStock extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "product_stocks";
    protected $guarded = [];

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static $recordEvents = ['updated'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Product Stocks')
            ->logOnly(['id','in_store', 'in_warehouse'])
            ->setDescriptionForEvent(fn(string $eventName) => "The data has been {$eventName}");
    }
}
