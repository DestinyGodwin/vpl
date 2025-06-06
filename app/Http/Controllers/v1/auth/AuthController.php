<?php
namespace App\Http\Controllers\v1\auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\v1\auth\AuthService;
use App\Http\Resources\v1\UserResource;
use App\Http\Requests\v1\auth\LoginRequest;
use App\Http\Requests\v1\auth\SendOtpRequest;
use App\Http\Requests\v1\auth\VerifyOtpRequest;
use App\Http\Requests\v1\auth\RegisterUserRequest;
use App\Http\Requests\v1\auth\ResetPasswordRequest;
use App\Http\Requests\v1\auth\UpdateProfileRequest;
use App\Http\Requests\v1\auth\ChangePasswordRequest;
use App\Http\Requests\v1\auth\ForgotPasswordRequest;
use App\Notifications\v1\auth\EmailVerifiedSuccessNotification;
use App\Notifications\v1\auth\PasswordChangedSuccessNotification;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(RegisterUserRequest $request)
    {
        $user = $this->authService->register($request->validated());
        return response()->json(['message' => 'Registered. OTP sent.', 'user' => $user], 201);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();

        if ($this->authService->verifyOtp($user, $request->otp)) {
            $user->notify(new EmailVerifiedSuccessNotification());
            return response()->json(['message' => 'Email verified.']);
        }

        return response()->json(['message' => 'Invalid or expired OTP.'], 422);
    }

    public function resendOtp(SendOtpRequest $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();
        $this->authService->sendOtp($user, 'email_verification');
        return response()->json(['message' => 'OTP resent.']);
    }

     public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $ipAddress = $request->ip();
        
        $result = $this->authService->login($credentials, $ipAddress);
        
        return response()->json([
            'status' => 'success',
            'message' => 'User logged in successfully',
            'data' => $result
        ]);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'Logged out.']);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();
        $this->authService->sendOtp($user, 'password_reset');
        return response()->json(['message' => 'OTP sent to email.']);
    }

  
public function resetPassword(ResetPasswordRequest $request)
{
    $user = User::where('email', $request->email)->firstOrFail();

    if (!$this->authService->verifyOtp($user, $request->otp)) {
        return response()->json(['message' => 'Invalid OTP'], 422);
    }
    $user->update([
        'password' => Hash::make($request->password),
    ]);

    $user->notify(new PasswordChangedSuccessNotification());

    return response()->json(['message' => 'Password reset successful.']);
}

    public function updateProfile(UpdateProfileRequest $request){
        $this->authService->updateProfile($request);
        return response()->json(['profile updated successfully']);

    }
    public function getProfile()
{
    $user = $this->authService->getProfile();
    return new UserResource($user);
}
 public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        $result = $this->authService->changePassword(
            $user,
            $request->input('current_password'),
            $request->input('new_password')
        );

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }
        return response()->json(['message' => $result['message']], 200);
    }
}
