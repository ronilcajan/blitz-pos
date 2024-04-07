<?php

namespace App\Http\Controllers;

use App\Models\ExpensesCategory;
use Illuminate\Http\Request;

class ExpensesCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $expenses_categories = ExpensesCategory::query()
            ->orderBy('id', 'DESC')
            ->filter(request(['search']))
            ->paginate($request->per_page ? ($request->per_page == 'All' ? ExpensesCategory::count() : $request->per_page) : 10)
            ->withQueryString()
            ->through(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'created_at' => $category->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('ExpensesCategory/Index', [
            'title' => 'Expenses Category',
            'expenses_categories' => $expenses_categories,
            'filters' => $request->only(['search']),
            'per_page' => $request->only(['per_page'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required'
        ]);

        $data['store_id'] = auth()->user()->store_id ?? 1;

        ExpensesCategory::create($data);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpensesCategory $expensesCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpensesCategory $expensesCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpensesCategory $expensesCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpensesCategory $expensesCategory)
    {
        //
    }
}
