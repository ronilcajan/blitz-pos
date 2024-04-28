<?php

namespace App\Models;

use App\Enum\ExpensesStatus;
use App\Models\Scopes\ExpensesScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([ExpensesScope::class])]
class Expenses extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'expenses';

    protected $guarded = [];

    protected $casts = [
        'status' => ExpensesStatus::class
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpensesCategory::class, 'expenses_category_id')->withoutGlobalScopes();
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ExpensesStatus::APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', ExpensesStatus::PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', ExpensesStatus::REJECTED);
    }

    public function scopeFilter($query, array $filter){
        if(!empty($filter['search'])){

            $search = $filter['search'];

            $query->whereAny([
                'expenses_date',
                'amount',
                'vendor',
                'notes',
            ], 'LIKE', "%{$search}%")
            ->orWhereHas('category', function($q) use ($search){
                $q->where('name', $search);
            })
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

        if(!empty($filter['category'])){
            $category = $filter['category'];

            $query->whereHas('category', function($q) use ($category){
                $q->where('name', $category);
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
            ->useLogName('Expenses')
            ->logOnly(['description'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "This data has been {$eventName}");
    }
}
