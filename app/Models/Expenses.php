<?php

namespace App\Models;

use App\Enum\ExpensesStatus;
use App\Models\Scopes\ExpensesScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
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

    public static function getDailyExpensesTotal(){
        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();

        $dailyExpensesTotal = static::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('status','approved')
            ->sum('amount');

        return $dailyExpensesTotal;
    }

    public static function getApprovedExpenses(){

        $approveExpensesTotal = static::where('status','approved')
            ->sum('amount');

        return $approveExpensesTotal;
    }

    public static function getPendingExpenses(){

        $pendingExpensesTotal = static::where('status','pending')
            ->sum('amount');

        return $pendingExpensesTotal;
    }

    public static function getRejectedExpenses(){

        $rejectedExpensesTotal = static::where('status','rejected')
            ->sum('amount');

        return $rejectedExpensesTotal;
    }


    public static function getTotalExpenses() {
        $expensesData = static::select('status', DB::raw('SUM(amount) as total_expenses'))
            ->groupBy('status')
            ->pluck('total_expenses', 'status');

        $formattedData = [
            $expensesData->get('approved', 0),
            $expensesData->get('pending', 0),
            $expensesData->get('rejected', 0),
        ];

        return $formattedData;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Expenses')
            ->logOnly(['description'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "The data has been {$eventName}");
    }
}
