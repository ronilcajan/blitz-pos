<?php

namespace App\Models;

use App\Models\Scopes\PurchaseScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([PurchaseScope::class])]
class Purchase extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'purchases';

    protected $guarded = [];

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier():BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items():HasMany
    {
        return $this->hasMany(PurchaseItems::class);
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
            ->orWhereHas('supplier', function($q) use ($search){
                $q->where('name', $search);
            })
            ->orWhereHas('store', function($q) use ($search){
                $q->where('name', $search);
            });
        }

        if(!empty($filter['supplier'])){
            $category = $filter['supplier'];
            $query->whereHas('supplier', function($q) use ($category){
                $q->where('name', $category);
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
            ->useLogName('Purchase')
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "This data has been {$eventName}");
    }
}
