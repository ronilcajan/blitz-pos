<?php

namespace App\Models;

use App\Models\Scopes\SaleScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([SaleScope::class])]
class Sale extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'sales';

    protected $guarded = [];

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer():BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sold_items(): HasMany
    {
        return $this->hasMany(SoldItems::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Sale')
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "This data has been {$eventName}");
    }
}
