<?php

namespace App\Models;

use App\Enum\SalesStatus;
use App\Models\Scopes\SaleScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([SaleScope::class])]
class Sale extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'sales';

    protected $guarded = [];

    protected $casts = [
        'status' => SalesStatus::class
    ];

    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer():BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sold_items(): HasMany
    {
        return $this->hasMany(SoldItems::class);
    }

    public function scopeFilter($query, array $filter){
        if(!empty($filter['search'])){

            $search = $filter['search'];

            $query->whereAny([
                'tx_no',
                'created_at',
                'sub_total',
                'discount',
                'total',
            ], 'LIKE', "%{$search}%")
            ->orWhereHas('customer', function($q) use ($search){
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

        if(!empty($filter['customer'])){
            $store = $filter['customer'];

            $query->whereHas('customer', function($q) use ($store){
                $q->where('name', $store);
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

    public static function getDailySalesTotal(){
        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();

        $dailySalesTotal = static::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('status','complete')
            ->sum('total');

        return $dailySalesTotal;
    }

    public static function getWeeklySalesTotal(){
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $weeklySalesTotal = static::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('status','complete')
            ->sum('total');

        return $weeklySalesTotal;
    }

    public static function getMonthlySalesTotal(){
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlySalesTotal = static::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status','complete')
            ->sum('total');

        return $monthlySalesTotal;
    }

    public static function getYearlySalesTotal(){
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();

        $yearlySalesTotal = static::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('status','complete')
            ->sum('total');

        return $yearlySalesTotal;
    }

    public static function getCurrentSales(){
        $currentYear = Carbon::now()->year;

        $salesData = static::select(DB::raw('MONTH(created_at) as month'),   DB::raw('SUM(total) as total_sales'))
            ->whereYear('created_at', $currentYear)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total_sales', 'month');

        $salesArray = [];
        for ($month = 1; $month <= 12; $month++) {
            $salesArray[] = $salesData->get($month, 0);
        }

        return $salesArray;

    }

    public static function getPreviousSales(){
        $currentYear = Carbon::now()->year - 1;

        $salesData = static::select(DB::raw('MONTH(created_at) as month'),   DB::raw('SUM(total) as total_sales'))
            ->whereYear('created_at', $currentYear)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total_sales', 'month');

            $salesArray = [];
            for ($month = 1; $month <= 12; $month++) {
                $salesArray[] = $salesData->get($month, 0);
            }

        return $salesArray;

    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Sale')
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "The data has been {$eventName}");
    }
}
