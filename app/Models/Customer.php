<?php

namespace App\Models;

use App\Models\Scopes\CustomerScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([CustomerScope::class])]
class Customer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'customers';

    protected $guarded = [];

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    } 

    public function sales():HasMany
    {
        return $this->hasMany(Sale::class);
    } 

    public function scopeFilter($query, array $filter){
        if(!empty($filter['search'])){
            $search = $filter['search'];

            $query->whereAny([
                'name',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Customer')
            ->logOnly(['name'])
            ->setDescriptionForEvent(fn(string $eventName) => "The data has been {$eventName}");    
    }

    protected static function booted(): void
    {
        static::creating(function($model){
            $model->store_id = auth()->user()->store->id;
        });

        static::updating(function($model){
            $model->store_id = auth()->user()->store->id;
        });

    }

}
