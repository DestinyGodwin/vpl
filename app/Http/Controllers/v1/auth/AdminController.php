<?php

namespace App\Http\Controllers\v1\auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\v1\auth\AdminService;
use App\Http\Requests\v1\PaginationRequest;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('admin');
        $this->adminService = $adminService;
    }

    public function allUsers(PaginationRequest $request)
    {
        return response()->json($this->adminService->getAllUsers($request->getPerPage()));
    }

    public function usersByUniversity(PaginationRequest $request, $universityId)
    {
        return response()->json($this->adminService->getUsersByUniversity($universityId, $request->getPerPage()));
    }

    public function usersByState(PaginationRequest $request, $state)
    {
        return response()->json($this->adminService->getUsersByState($state, $request->getPerPage()));
    }

    public function usersByCountry(PaginationRequest $request, $country)
    {
        return response()->json($this->adminService->getUsersByCountry($country, $request->getPerPage()));
    }

    public function notifyUsers(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $this->adminService->notifyUsers($users, $request->title, $request->message);

        return response()->json(['message' => 'Notifications sent successfully']);
    }
}
