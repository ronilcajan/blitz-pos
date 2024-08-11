<?php

namespace App\Models;

use App\Models\Scopes\ExpensesCategoryScope;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([ExpensesCategoryScope::class])]
class ExpensesCategory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, CascadeSoftDeletes;

    protected $table = 'expenses_categories';

    protected $guarded = []; 

    protected $cascadeDeletes = ['expenses'];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expenses::class);
    } 

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
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
            ->useLogName('Expenses Category')
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "The data has been {$eventName}");    
    }
}
