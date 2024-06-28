<?php

namespace App\Models;

use App\Models\Scopes\SoldItemsScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([SoldItemsScope::class])]
class SoldItems extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'sold_items';

    protected $guarded = [];

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sale():BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stock():BelongsTo
    {
        return $this->belongsTo(ProductStock::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('SoldItems')
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "This data has been {$eventName}");
    }
}
