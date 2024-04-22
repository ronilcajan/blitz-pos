<?php

namespace App\Http\Controllers;

use App\Models\ExpensesCategory;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExpensesCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ExpensesCategory::class);

        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? ExpensesCategory::count() : $request->per_page)
        : 10;

        $expenses_categories = ExpensesCategory::query()
            ->with(['store'])
            ->orderBy('id', 'DESC')
            ->filter(request(['search','store']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'store' => $category->store->name,
                    'created_at' => $category->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('ExpensesCategory/Index', [
            'title' => 'Expenses Category',
            'expenses_categories' => $expenses_categories,
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'filters' => $request->only(['search']),
            'per_page' => $request->only(['per_page'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', ExpensesCategory::class);

        $data = $request->validate([
            'name' => 'required'
        ]);

        $data['store_id'] = auth()->user()->store_id ?? 1;

        ExpensesCategory::create($data);

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $category = ExpensesCategory::find($request->id);
        Gate::authorize('create', $category);

        $data = $request->validate([
            'name' => 'required'
        ]);

        $category->update($data);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function bulkDelete(Request $request)
    {
        Gate::authorize('bulk_delete', ExpensesCategory::class);

        ExpensesCategory::whereIn('id',$request->categories_id)->delete();
        return redirect()->back();
    }

    public function destroy(ExpensesCategory $expenses_category)
    {
        Gate::authorize('delete', $expenses_category);
        ExpensesCategory::find($expenses_category->id)->delete();
        return redirect()->back();
    }
}
