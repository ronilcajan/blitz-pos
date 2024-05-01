<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormRequest;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Store::class);

        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? Store::count() : $request->per_page)
        : 10;

        $transformedStore = Store::query()
            ->orderBy('id', 'DESC')
            ->filter(request(['search']))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'address' => $store->address,
                    'contact' => $store->contact,
                    'email' => $store->email,
                    'logo' => $store->logo,
                    'created_at' => $store->created_at->format('M d, Y h:i: A'),
                ];
        });

        return inertia('Store/Index', [
            'title' => "Store",
            'stores' => $transformedStore,
            'filters' => $request->only(['search']),
            'per_page' => $request->only(['per_page'])
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFormRequest $request)
    {
        Gate::authorize('create', Store::class);

        $validate = $request->validated();

        if($request->hasFile('logo')){
            $logo = $request->file('logo')->store('store','public');
            $validate['logo'] = asset('storage/'. $logo);
        }

        Store::create($validate);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        $api_key = env('COUNTRY_API_KEY');
        $response = Http::get('https://countryapi.io/api/all?apikey='.$api_key)->json();

        $countries = [];

        foreach($response as $country){
            $currencies = $country['currencies'];
            $currencyCode = array_keys($currencies)[0]; // Get the first currency code
            $currencyInfo = $currencies[$currencyCode];

            $countries[] = [
                'name' => $country['name'],
                'flag' => $country['flag']['small'],
                'alpha3Code' => $country['alpha3Code'],
                'timezone' => $country['timezones'][0],
                'currency_code' => $currencyCode,
                'currency_name' => $currencyInfo['name'] ?? null,
                'currency_symbol' => $currencyInfo['symbol'] ?? null,
            ];
        }

        $store = [
            'id' => $store->id,
            'name' => $store->name,
            'address' => $store->address,
            'contact' => $store->contact,
            'email' => $store->email,
            'logo' => $store->logo,
            'created_at' => $store->created_at->format('M d, Y h:i: A'),
        ];


       return inertia('Store/Show', [
            'title' => 'Store Details',
            'store' => $store,
            'countries' => $countries,
       ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(StoreFormRequest $request)
    {
        $store = Store::find($request->id);

        Gate::authorize('create', $store);

        $validate = $request->validated();

        if($request->hasFile('logo')){
            $logo = $request->file('logo')->store('stores','public');
            $validate['logo'] = asset('storage/'.$logo);
        }

        $store->update($validate);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function bulkDelete(Request $request)
    {
        Gate::authorize('bulk_delete',  Store::class);

        Store::whereIn('id',$request->store_id)->delete();
        return redirect()->back();
    }

    public function destroy(Store $store)
    {
        Gate::authorize('delete', $store);
        $store->delete();
        return redirect()->back();
    }
}
