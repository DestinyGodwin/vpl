<?php

namespace App\Http\Controllers\v1;

use App\Models\University;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\university\StoreUniversityRequest;

class UniversityController extends Controller
{
    public function index()
    {
        return response()->json(University::all());
    }

    public function store(StoreUniversityRequest $request)
    {
        $university = University::create($request->validated());

        return response()->json(['message' => 'University created.', 'university' => $university]);
    }

    public function show(University $university)
    {
        return response()->json($university);
    }

    public function update(StoreUniversityRequest $request, University $university)
    {
        $university->update($request->validated());

        return response()->json(['message' => 'University updated.', 'university' => $university]);
    }

    public function destroy(University $university)
    {
        $university->delete();

        return response()->json(['message' => 'University deleted.']);
    }
}
