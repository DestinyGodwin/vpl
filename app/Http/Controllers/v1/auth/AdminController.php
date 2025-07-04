<?php

namespace App\Http\Controllers\v1\auth;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\v1\auth\AdminService;
use App\Http\Resources\v1\UserResource;
use App\Http\Requests\v1\PaginationRequest;
use App\Http\Requests\v1\admin\DeleteUsersRequest;
use App\Http\Requests\v1\admin\GetUserByIdRequest;
use App\Http\Requests\v1\admin\NotifyUsersRequest;
use App\Http\Requests\v1\admin\GetUserByEmailRequest;
use App\Http\Requests\v1\admin\AdminNotificationRequest;
use App\Http\Requests\v1\admin\NotifyUsersByEmailRequest;

class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function allUsers(PaginationRequest $request)
    {
        return UserResource::collection($this->adminService->getAllUsers($request->getPerPage()));
    }

    public function usersByUniversity(PaginationRequest $request, string $universityId)
    {
        return UserResource::collection($this->adminService->getUsersByUniversity($universityId, $request->getPerPage()));
    }
    public function getById(GetUserByIdRequest $request)
    {
        return new UserResource($this->adminService->getUserById($request->input('id')));
    }

    public function getByEmail(GetUserByEmailRequest $request)
    {
        return new UserResource($this->adminService->getUserByEmail($request->input('email')));
    }

    public function usersByState(PaginationRequest $request, string $state)
    {
        return UserResource::collection($this->adminService->getUsersByState($state, $request->getPerPage()));
    }

    public function usersByCountry(PaginationRequest $request, string $country)
    {
        return UserResource::collection($this->adminService->getUsersByCountry($country, $request->getPerPage()));
    }

    public function notifyUsers(NotifyUsersRequest $request): JsonResponse
    {
        $users = User::whereIn('id', $request->user_ids)->get();
        $this->adminService->notifyUsers($users, $request->title, $request->message);

        return response()->json(['message' => 'Notifications sent successfully']);
    }

    public function notifyUniversity(AdminNotificationRequest $request, string $universityId): JsonResponse
    {
        $this->adminService->notifyUsersByUniversity($universityId, $request->title, $request->message);

        return response()->json(['message' => 'Notification sent to university users successfully']);
    }

    public function notifyState(AdminNotificationRequest $request, string $state): JsonResponse
    {
        $this->adminService->notifyUsersByState($state, $request->title, $request->message);

        return response()->json(['message' => 'Notification sent to state users successfully']);
    }

    public function notifyCountry(AdminNotificationRequest $request, string $country): JsonResponse
    {
        $this->adminService->notifyUsersByCountry($country, $request->title, $request->message);

        return response()->json(['message' => 'Notification sent to country users successfully']);
    }
    public function notifyUsersByEmail(NotifyUsersByEmailRequest $request): JsonResponse
    {
        $users = User::whereIn('email', $request->emails)->get();

        $this->adminService->notifyUsersByEmail($users, $request->title, $request->message);

        return response()->json(['message' => 'Notifications sent successfully']);
    }
    public function deleteUsers(DeleteUsersRequest $request): JsonResponse
    {
        $deletedCount = $this->adminService->deleteUsers($request->input('user_ids'));
        return response()->json([
            'message' => "{$deletedCount} user(s) deleted successfully.",
        ]);
    }
}
