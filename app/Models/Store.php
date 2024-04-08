<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Store extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes, LogsActivity;

    protected $table = 'stores';

    protected $guarded = [];

    protected $cascadeDeletes = [
        'users',
        'supplier',
        'customer',
        'expenses_category',
        'expenses',
        'product',
    ];

    public function users():HasMany
    {
        return $this->hasMany(User::class);
    }

    public function supplier():HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function customer():HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function product():HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function expenses_categories():HasMany
    {
        return $this->hasMany(ExpensesCategory::class);
    }
    
    public function expenses():HasMany
    {
        return $this->hasMany(Expenses::class);
    }

    public function scopeFilter($query, array $filter){
        if(!empty($filter['search'])){
            $search = $filter['search'];
            $query->whereAny([
                'name',
                'email',
                'contact',
                'address',
            ], 'LIKE', "%{$search}%");
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Store')
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "This data has been {$eventName}");    
    }
}
