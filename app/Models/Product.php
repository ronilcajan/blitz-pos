<?php

namespace App\Models;

use App\Models\Scopes\ProductScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([ProductScope::class])]
class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'products';

    protected $guarded = [];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    } 

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductSupplier::class);
    }

    public function scopeFilter($query, array $filter){
        if(!empty($filter['search'])){
            $search = $filter['search'];

            $query->whereAny([
                'name',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Product')
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "This data has been {$eventName}");    
    }
}
