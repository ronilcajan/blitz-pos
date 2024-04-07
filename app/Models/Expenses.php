<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expenses extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'expenses';

    protected $guarded = [];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    } 

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    } 

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpensesCategory::class, 'expenses_category_id');
    }

    public function scopeFilter($query, array $filter){
        if(!empty($filter['search'])){
            
            $search = $filter['search'];

            $query->whereAny([
                'expenses_date',
                'amount',
                'description',
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

        if(!empty($filter['store'])){
            $category = $filter['store'];

            $query->whereHas('store', function($q) use ($category){
                $q->where('name', $category);
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
