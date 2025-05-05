<?php

namespace App\Http\Controllers\v1\stores;

use App\Http\Controllers\Controller;
use App\Services\v1\stores\StoreService;
use App\Http\Requests\v1\stores\StoreRequest;
use App\Http\Resources\v1\stores\StoreResource;

class StoreController extends Controller
{

    public function __construct(protected StoreService $storeService) {}

    public function index()
    {
        return StoreResource::collection($this->storeService->getAll());
    }

    public function regularStores()
    {
        return StoreResource::collection($this->storeService->getByType('regular'));
    }

    public function foodStores()
    {
        return StoreResource::collection($this->storeService->getByType('food'));
    }

    public function myStores()
    {
        return StoreResource::collection($this->storeService->getUserStores());
    }

    public function byUniversity($universityId)
    {
        return StoreResource::collection($this->storeService->getByUniversity($universityId));
    }

    public function regularByUniversity($universityId)
    {
        return StoreResource::collection($this->storeService->getByUniversity($universityId, 'regular'));
    }

    public function foodByUniversity($universityId)
    {
        return StoreResource::collection($this->storeService->getByUniversity($universityId, 'food'));
    }

    public function byCountry($countryId)
    {
        return StoreResource::collection($this->storeService->getByCountry($countryId));
    }

    public function regularByCountry($countryId)
    {
        return StoreResource::collection($this->storeService->getByCountry($countryId, 'regular'));
    }

    public function foodByCountry($countryId)
    {
        return StoreResource::collection($this->storeService->getByCountry($countryId, 'food'));
    }

    public function store(StoreRequest $request)
    {
        $store = $this->storeService->create($request);
        return new StoreResource($store);
    }

    public function show($id)
{
    $store = $this->storeService->findById($id);
    if (!$store) {
        return response()->json(['message' => 'Store not found.'], 404);
    }

    return new StoreResource($store);
}

public function update(StoreRequest $request, $id)
{
    $store = $this->storeService->updateByOwner($id, $request);
    if (!$store) {
        return response()->json(['message' => 'You do not own this store or it does not exist.'], 404);
    }

    return new StoreResource($store);
}

public function destroy($id)
{
    $deleted = $this->storeService->deleteByOwner($id);
    if (!$deleted) {
        return response()->json(['message' => 'Store not found or you are not authorized to delete it.'], 404);
    }

    return response()->json(['message' => 'Store deleted successfully.']);
}
}
