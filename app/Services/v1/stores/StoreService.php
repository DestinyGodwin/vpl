<?php

namespace App\Services\v1\stores;

use Storage;
use App\Models\Store;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Notifications\v1\StoreCreatedNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StoreService
{
    /**
     * Create a new class instance.
     */
    public function create($request)
    {
        $user = Auth::user();
        $imagePath = $request->file('image')->store('stores', 'public');

        $store = $user->stores()->create([
            'university_id' => $user->university_id,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'image' => $imagePath,
            'next_payment_due' => now()->addMonth(),
        ]);
        try {
          
            $user->notify((new StoreCreatedNotification($store))->delay(now()->addSeconds(5)));
        } catch (\Throwable $e) {
         
            Log::error('Failed to send store creation notification: ' . $e->getMessage(), [
                'store_id' => $store->id,
                'user_id' => $user->id,
            ]);
        }

        return $store->load('university', 'user');
    }
    public function updateByOwner($id, $request)
    {
        $user = Auth::user();

        if (!$user || !is_string($id) || !preg_match('/^[\w-]{36}$/', $id)) {
            return false;
        }

        try {
            $store = $user->stores()->findOrFail($id);

            // Update image if provided
            if ($request->hasFile('image')) {
                if ($store->image && \Storage::disk('public')->exists($store->image)) {
                    \Storage::disk('public')->delete($store->image);
                }

                $store->image = $request->file('image')->store('stores', 'public');
            }

            $fieldsToUpdate = collect(['name', 'description', 'type', 'status'])
                ->filter(fn($field) => $request->filled($field))
                ->all();
            $store->update($request->only($fieldsToUpdate));
            return $store->fresh();
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    public function deleteByOwner($id)
    {
        $user = Auth::user();

        if (!$user || !is_string($id) || !preg_match('/^[\w-]{36}$/', $id)) {

            return false;
        }
        try {
            $store = Auth::user()->stores()->findOrFail($id);
            $store->delete();
            return true;
        } catch (ModelNotFoundException) {
            return false;
        }
    }
public function toggleStatusByOwner($id)
{
    $user = Auth::user();

    try {
        $store = $user->stores()->findOrFail($id);

        $store->status = $store->status === 'is_active' ? 'is_inactive' : 'is_active';
        $store->save();

        return $store->fresh();
    } catch (ModelNotFoundException) {
        return null;
    }
}

    public function findById($id)
    {
        if (!is_string($id) || !preg_match('/^[\w-]{36}$/', $id)) {
            return null;
        }

        try {
            return Store::with('university', 'user')->findOrFail($id);
        } catch (ModelNotFoundException) {
            return null;
        }
    }
    public function getUserStores()
{
    $query = Auth::user()->stores()->with('university');
 return $query->get();
}


public function getAll($perPage = null)
{
    $query = Store::with('university', 'user')->latest();
    return $query->paginate($perPage ?? 50);
}

public function getByType(string $type, $perPage = 50)
{
    $allowedTypes = ['regular', 'food'];
    if (!in_array($type, $allowedTypes)) {
        return collect(); // invalid type
    }

    $query = Store::where('type', $type)->with('university', 'user')->latest();
    return $query->paginate($perPage ?? 50);
}

public function getByUniversity($universityId, $type = null, $perPage = 50)
{
    $allowedTypes = ['regular', 'food'];
    $isValidType = $type === null || in_array($type, $allowedTypes);

    if (!$isValidType) {
        return collect();
    }

    $query = Store::when($type, fn($q) => $q->where('type', $type))
                  ->where('university_id', $universityId)
                  ->with('user');

    return $query->paginate($perPage ?? 50);
}

public function getByCountry($countryId, $type = null, $perPage = 50)
{
    $allowedTypes = ['regular', 'food'];
    $isValidType = $type === null || in_array($type, $allowedTypes);

    if (!$isValidType) {
        return collect();
    }

    $query = Store::whereHas('university', fn($q) => $q->where('country_id', $countryId))
                  ->when($type, fn($q) => $q->where('type', $type))
                  ->with('user', 'university');

    return $query->paginate($perPage ?? 50);
}

}
