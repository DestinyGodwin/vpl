<?php

namespace App\Http\Controllers\v1\stores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    public function index()
    {
        return StoreResource::collection($this->storeService->getAll());
    }

    public function store(StoreRequest $request)
    {
        $store = $this->storeService->create($request);
        return new StoreResource($store);
    }

    public function show($id)
    {
        $store = $this->storeService->findById($id);
        return new StoreResource($store);
    }

    public function update(StoreRequest $request, $id)
    {
        $store = $this->storeService->update($id, $request);
        return new StoreResource($store);
    }

    public function destroy($id)
    {
        $this->storeService->delete($id);
        return response()->json(['message' => 'Store deleted successfully.']);
    }

    public function getByUniversity($universityId)
    {
        return StoreResource::collection($this->storeService->getByUniversity($universityId));
    }

    public function getByCountry($countryId)
    {
        return StoreResource::collection($this->storeService->getByCountry($countryId));
    }
}
