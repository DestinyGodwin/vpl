<?php

namespace App\Services\v1\stores;

use App\Models\Store;
use Illuminate\Support\Facades\Auth;

class StoreService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
       
    }
    public function getAll()
    {
        return Store::with('university', 'user')->latest()->get();
    }

    public function create($request)
    {
        $user = Auth::user();

        $imagePath = $request->file('image')->store('stores', 'public');

        return Store::create([
            'user_id' => $user->id,
            'university_id' => $user->university_id,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'image' => $imagePath,
            'status' => 'active',
            'next_payment_due' => now()->addMonth(),
        ]);
    }

    public function findById($id)
    {
        return Store::with('university', 'user')->findOrFail($id);
    }

    public function update($id, $request)
    {
        $store = Store::findOrFail($id);

        if ($request->hasFile('image')) {
            $store->image = $request->file('image')->store('stores', 'public');
        }

        $store->update($request->only(['name', 'description', 'type', 'status']));

        return $store->fresh();
    }

    public function delete($id)
    {
        $store = Store::findOrFail($id);
        $store->delete();
    }

    public function getByUniversity($universityId)
    {
        return Store::where('university_id', $universityId)->with('user')->get();
    }

    public function getByCountry($countryId)
    {
        return Store::whereHas('university', fn($q) => $q->where('country_id', $countryId))
                    ->with('user', 'university')
                    ->get();
    }
}
