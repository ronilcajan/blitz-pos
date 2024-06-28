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
        auth()->user()->can('viewAny', Store::class);

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
                    'avatar' => asset('storage/'.$store->avatar),
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
        auth()->user()->can('create', Store::class);

        $validate = $request->validated();

        if($request->hasFile('file')){
            $avatar = $request->file('file')->store('store','public');
            $validate['avatar'] = $avatar;
        }

        Store::create($validate);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        auth()->user()->can('update', $store);

        $response = Http::get('https://api.ipgeolocation.io/ipgeo',[
            'apiKey' => '8aa952f04a194d639a762c8e66425c46',
        ])->json();

        $country = [
            'name' => $response['country_name'],
            'code' => $response['country_code3'],
            'flag' => $response['country_flag'],
            'currency' => $response['currency']['code'],
            'currency_symbol' => $response['currency']['symbol'],
            'time_zone' => $response['time_zone']['name'],
        ];

        $store = [
            'id' => $store->id,
            'name' => $store->name,
            'founder' => $store->founder,
            'tagline' => $store->tagline,
            'email' => $store->email,
            'contact' => $store->contact,
            'website' => $store->website,
            'industry' => $store->industry,
            'country' => $store->country,
            'country_code' => $store->country_code,
            'timezone' => $store->timezone,
            'currency' => $store?->currency,
            'flag' => $store->flag,
            'tax' => $store->tax,
            'description' => $store->description,
            'address' => $store->address,
            'avatar' => $store->avatar,
        ];

       return inertia('Store/Show', [
            'title' => 'Store Details',
            'store' => $store,
            'country' => $country,
       ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(StoreFormRequest $request)
    {
        $store = Store::find($request->id);

        auth()->user()->can('update', $store);

        $validate = $request->validated();

        if($request->hasFile('avatar')){
            $avatar = $request->file('avatar')->store('stores','public');
            $validate['avatar'] = $avatar;
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
