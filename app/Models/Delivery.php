<?php

namespace App\Models;

use App\Models\Scopes\DeliveryScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([DeliveryScope::class])]
class Delivery extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'deliveries';

    protected $guarded = [];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function delivery_items():HasMany
    {
        return $this->hasMany(DeliveryItems::class);
    }

    public function purchase_order():HasOne
    {
        return $this->hasOne(Purchase::class);
    }

    public function scopeFilter($query, array $filter){
        if(!empty($filter['search'])){

            $search = $filter['search'];

            $query->whereAny([
                'tx_no',
                'quantity',
                'discount',
                'amount',
                'total',
                'status',
            ], 'LIKE', "%{$search}%")
            ->orWhereHas('purchase', function($q) use ($search){
                $q->where('tx_no', $search);
            })
            ->orWhereHas('supplier', function($q) use ($search){
                $q->where('name', $search);
            })
            ->orWhereHas('store', function($q) use ($search){
                $q->where('name', $search);
            });
        }

        if(!empty($filter['suppliers'])){
            $supplier = $filter['suppliers'];

            $query->whereHas('supplier', function($q) use ($supplier){
                $q->where('name', $supplier);
            });
        }

        if(!empty($filter['store'])){
            $store = $filter['store'];

            $query->whereHas('store', function($q) use ($store){
                $q->where('name', $store);
            });
        }

        if(!empty($filter['from_date'])){
            $from_date = $filter['from_date'];
            $to_date = Carbon::parse($filter['to_date'])->endOfDay();

            $query->whereBetween('created_at',[$from_date, $to_date]);
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Delivery')
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "This data has been {$eventName}");
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
