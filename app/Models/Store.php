<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $table = 'stores';

    protected $guarded = [];

    protected $cascadeDeletes = ['users'];

    public function users():HasMany
    {
        return $this->hasMany(User::class);
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
}
