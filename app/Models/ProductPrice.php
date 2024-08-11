<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductPrice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = "product_price";

    protected $guarded = [];

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static $recordEvents = ['updated'];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Product Price')
            ->logOnly(['id','sale_price'])
            ->setDescriptionForEvent(fn(string $eventName) => "The data has been {$eventName}");
    }
}
