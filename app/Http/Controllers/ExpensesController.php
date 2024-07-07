<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpensesRequestForm;
use App\Models\Expenses;
use App\Models\ExpensesCategory;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Expenses::class);

        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? Expenses::count() : $request->per_page)
        : 10;

        $allExpenses = Expenses::query()
            ->with(['category','user','store'])
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store','category','status']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($expense) {
                return [
                    'id' => $expense->id,
                    'expenses_date' => date('M d, Y', strtotime($expense->expenses_date)),
                    'vendor' => $expense->vendor,
                    'amount' => $expense->amount,
                    'amount' => Number::currency($expense->amount, in: 'PHP'),
                    'attachments' => $expense->attachments,
                    'category' => $expense->category?->name,
                    'status' => $expense->status->getLabelText(),
                    'statusColor' => $expense->status->getLabelColor(),
                    'store' => $expense->store->name,
                    'user' => $expense->user?->name ,
                ];
        });

        $expenses = Expenses::query()
            ->with(['category','user','store'])
            ->get();

        $dailyExpensesTotal = Expenses::getDailyExpensesTotal();
        $approvedExpenses = Expenses::getApprovedExpenses();
        $pendingExpenses = Expenses::getPendingExpenses();
        $rejectedExpenses= Expenses::getRejectedExpenses();

        return inertia('Expenses/Index', [
            'title' => "Expenses",
            'expenses' => $allExpenses,
            'totalExpenses' => $expenses,
            'dailyExpensesTotal' => $dailyExpensesTotal,
            'approvedExpenses' => $approvedExpenses,
            'pendingExpenses' => $pendingExpenses,
            'rejectedExpenses' => $rejectedExpenses,
            'stores' => Store::select('id', 'name')->orderBy('id', 'DESC')->get(),
            'categories' => ExpensesCategory::select('id', 'name')->orderBy('id', 'DESC')->get(),
            'filter' => $request->only(['search','store','per_page','category','status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Expenses::class);

        return inertia('Expenses/Create', [
            'title' => "Add New Expenses",
            'stores' => Store::select('id', 'name')->orderBy('id', 'DESC')->get(),
            'categories' => ExpensesCategory::select('id', 'name')->orderBy('id', 'DESC')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ExpensesRequestForm $request)
    {
        Gate::authorize('create', Expenses::class);

        $data = $request->validated();

        if($request->hasFile('attachments')){
            $attachments = $request->file('attachments')->store('expenses','public');
            $data['attachments'] = asset('storage/'. $attachments);
        }

        $data['user_id'] = auth()->user()->id;

        Expenses::create($data);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Expenses $expenses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expenses $expense)
    {
        Gate::authorize('update', $expense);

        $data = [
            'id' => $expense->id,
            'expenses_date' => $expense->expenses_date,
            'vendor' => $expense->vendor,
            'amount' => $expense->amount,
            'notes' => $expense->notes,
            'attachments' => $expense->attachments,
            'status' => $expense->status->getLabelText(),
            'statusColor' => $expense->status->getLabelColor(),
            'expenses_category_id' => $expense->expenses_category_id,
            'store_id' => $expense->store->id,
        ];

        return inertia('Expenses/Edit', [
            'title' => "Edit Expenses",
            'expense' => $data,
            'stores' => Store::select('id', 'name')->orderBy('id', 'DESC')->get(),
            'categories' => ExpensesCategory::select('id', 'name')->orderBy('id', 'DESC')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ExpensesRequestForm $request)
    {
        $expense = Expenses::find($request->id);
        Gate::authorize('update', $expense);

        $validate = $request->validated();

        if($request->hasFile('attachments')){
            $attachments = $request->file('attachments')->store('expenses','public');
            $validate['attachments'] = asset('storage/'. $attachments);
        }

        $expense->update($validate);

        return redirect()->back();
    }

    public function change_status(Expenses $expense, Request $request)
    {
        Gate::authorize('update', $expense);
        $expense->update($request->all());
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function bulkDelete(Request $request)
    {
        Gate::authorize('bulk_delete', Expenses::class);

        Expenses::whereIn('id',$request->expenses_id)->delete();
        return redirect()->back();
    }

    public function destroy(Expenses $expense)
    {
        Gate::authorize('delete', $expense);

        $expense->delete();
        return redirect()->back();
    }
}
