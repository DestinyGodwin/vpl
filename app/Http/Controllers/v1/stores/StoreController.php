<?php

namespace App\Http\Controllers\v1\stores;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\v1\stores\StoreService;
use App\Http\Requests\v1\stores\StoreRequest;
use App\Http\Resources\v1\stores\StoreResource;
use App\Http\Requests\v1\stores\UpdateStoreRequest;

class StoreController extends Controller
{
    public function __construct(protected StoreService $storeService) {}

    public function myStores(Request $request)
    {
        $perPage = $request->query('per_page');
        return StoreResource::collection($this->storeService->getUserStores($perPage));
    }

    public function store(StoreRequest $request)
    {
        $store = $this->storeService->create($request);
        return new StoreResource($store);
    }

    public function update(UpdateStoreRequest $request, $id)
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

    public function index(Request $request)
    {
        $perPage = $request->query('per_page');
        return StoreResource::collection($this->storeService->getAll($perPage));
    }

    public function byType(Request $request, string $type)
    {
        $perPage = $request->query('per_page');
        $stores = $this->storeService->getByType($type, $perPage);

        if ($stores->isEmpty()) {
            return response()->json(['message' => 'No stores found for the given type.'], 404);
        }

        return StoreResource::collection($stores);
    }

    public function byUniversity(Request $request, string $universityId, string $type = null)
    {
        $perPage = $request->query('per_page');
        $stores = $this->storeService->getByUniversity($universityId, $type, $perPage);

        if ($stores->isEmpty()) {
            return response()->json(['message' => 'No stores found for the given university or type.'], 404);
        }

        return StoreResource::collection($stores);
    }

    public function byCountry(Request $request, string $countryId, string $type = null)
    {
        $perPage = $request->query('per_page');
        $stores = $this->storeService->getByCountry($countryId, $type, $perPage);

        if ($stores->isEmpty()) {
            return response()->json(['message' => 'No stores found for the given country or type.'], 404);
        }

        return StoreResource::collection($stores);
    }

    public function show($id)
    {
        $store = $this->storeService->findById($id);
        if (!$store) {
            return response()->json(['message' => 'Store not found.'], 404);
        }

        return new StoreResource($store);
    }
}
