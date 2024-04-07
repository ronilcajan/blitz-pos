<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpensesRequestForm;
use App\Models\Expenses;
use App\Models\ExpensesCategory;
use App\Models\Store;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Expenses::class);

        $expenses = Expenses::query()
            ->with(['category','user','store'])
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store']))
            ->paginate($request->per_page ? ($request->per_page == 'All' ? Expenses::count()->get() : $request->per_page) : 10)
            ->withQueryString()
            ->through(function ($expense) {
                return [
                    'id' => $expense->id,
                    'expenses_date' => $expense->expenses_date,
                    'description' => $expense->description,
                    'amount' => number_format($expense->amount,2),
                    'notes' => $expense->notes,
                    'attachments' => $expense->attachments,
                    'category' => $expense->category->name,
                    'store' => $expense->store->name,
                    'user' => $expense->user->name,
                    'created_at' => $expense->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('Expenses/Index', [
            'title' => "Expenses",
            'expenses' => $expenses,
            'stores' => Store::select('id', 'name')->get(),
            'categories' => ExpensesCategory::select('id', 'name')->get(),
            'filter' => $request->only(['search','store','per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $this->authorize('create', Expenses::class);

        return inertia('Expenses/Create', [
            'title' => "Add New Expenses",
            'stores' => Store::select('id', 'name')->get(),
            'categories' => ExpensesCategory::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ExpensesRequestForm $request)
    {
        // $this->authorize('create', Expenses::class);

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
        // $this->authorize('update', $expense);

        $data = [
            'id' => $expense->id,
            'expenses_date' => $expense->expenses_date,
            'description' => $expense->description,
            'amount' => $expense->amount,
            'notes' => $expense->notes,
            'attachments' => $expense->attachments,
            'expenses_category_id' => $expense->expenses_category_id,
            'stores_id' => $expense->store->id ,
            'users_id' => $expense->users_id,
        ];

        return inertia('Expenses/Edit', [
            'title' => "Edit Expenses",
            'expense' => $data,
            'stores' => Store::select('id', 'name')->get(),
            'categories' => ExpensesCategory::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expenses $expenses)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function bulkDelete(Request $request)
    {
        // $this->authorize('bulk_delete', Expenses::class);

        Expenses::whereIn('id',$request->expenses_id)->delete();
        return redirect()->back();
    }

    public function destroy(Expenses $expense)
    {
        // $this->authorize('delete', $expense);
        
        $expense->delete();
        return redirect()->back();
    }
}
