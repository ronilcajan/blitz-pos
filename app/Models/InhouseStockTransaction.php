<?php

namespace App\Models;

use App\Models\Scopes\InhouseStockTransactionScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([InhouseStockTransactionScope::class])]
class InhouseStockTransaction extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table ='inhouse_stock_transactions';
    protected $guarded = [];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeFilter($query, array $filter){
        if(!empty($filter['search'])){

            $search = $filter['search'];

            $query->whereAny([
                'tx_no',
                'quantity',
                'amount',
                'status',
                'notes',
            ], 'LIKE', "%{$search}%")
            ->orWhereHas('user', function($q) use ($search){
                $q->where('name', $search);
            })
            ->orWhereHas('store', function($q) use ($search){
                $q->where('name', $search);
            });
        }

        if(!empty($filter['status'])){
            $status = $filter['status'];
            if($status != 'all'){
                $query->where('status', $status);
            }
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
            ->useLogName('InhouseStockTransaction')
            ->logOnly(['tx_no'])
            ->setDescriptionForEvent(fn(string $eventName) => "This data has been {$eventName}");
    }
}
