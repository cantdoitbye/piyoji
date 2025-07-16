<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class AuthController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * User login API
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'device_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Rate limiting
        $throttleKey = $this->throttleKey($request);
        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            // Log failed attempt
            $this->logFailedAttempt($request, 'Too many attempts');
            
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Increment rate limiter
            RateLimiter::hit($throttleKey, 300); // 5 minutes
            
            // Log failed attempt
            $this->logFailedAttempt($request, 'Invalid credentials');

            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Check if user is active
        if (!$user->isActive()) {
            // Log failed attempt
            $this->logFailedAttempt($request, 'Account inactive');
            
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact administrator.'
            ], 403);
        }

        // Clear rate limiter
        RateLimiter::clear($throttleKey);

        // Revoke all existing tokens for this device
        $user->tokens()->where('name', $request->device_name)->delete();

        // Create new token with abilities based on user permissions
        $token = $user->createToken($request->device_name, $this->getUserAbilities($user))->plainTextToken;

        // Update last login
        $this->userRepository->updateLastLogin($user->id);

        // Log successful login
        $this->logSuccessfulLogin($request, $user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'role_text' => $user->role_text,
                    'department' => $user->department,
                    'permissions' => $user->permissions ?? [],
                    'status' => $user->status,
                    'last_login_at' => $user->last_login_at?->toISOString(),
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'abilities' => $this->getUserAbilities($user),
            ]
        ]);
    }

    /**
     * User logout API
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            // Update logout time in login log
            $this->userRepository->updateLogoutTime($user->id);
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'role_text' => $user->role_text,
                    'department' => $user->department,
                    'permissions' => $user->permissions ?? [],
                    'status' => $user->status,
                    'status_text' => $user->status_text,
                    'last_login_at' => $user->last_login_at?->toISOString(),
                    'created_at' => $user->created_at->toISOString(),
                ]
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'current_password' => 'required_with:password|string',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // If password change is requested, verify current password
        if ($request->has('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }
        }

        $data = $request->only(['name', 'phone']);
        
        if ($request->has('password')) {
            $data['password'] = $request->password;
        }

        try {
            $this->userRepository->update($user->id, $data);
            
            // Refresh user data
            $user->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                        'role_text' => $user->role_text,
                        'department' => $user->department,
                        'permissions' => $user->permissions ?? [],
                        'status' => $user->status,
                        'status_text' => $user->status_text,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken($request->device_name, $this->getUserAbilities($user))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'abilities' => $this->getUserAbilities($user),
            ]
        ]);
    }

    /**
     * Check if user has specific permission
     */
    public function checkPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $hasPermission = $user->hasPermission($request->permission);

        return response()->json([
            'success' => true,
            'data' => [
                'permission' => $request->permission,
                'has_permission' => $hasPermission
            ]
        ]);
    }

    /**
     * Get user abilities for token
     */
    private function getUserAbilities(User $user): array
    {
        $abilities = ['user:profile'];
        
        if ($user->permissions) {
            foreach ($user->permissions as $permission) {
                $abilities[] = 'user:' . $permission;
            }
        }
        
        return $abilities;
    }

    /**
     * Get the rate limiting throttle key for the given request.
     */
    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }

    /**
     * Log successful login attempt
     */
    protected function logSuccessfulLogin(Request $request, $user): void
    {
        $this->userRepository->createLoginLog([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_status' => 'success',
            'login_at' => now(),
        ]);
    }

    /**
     * Log failed login attempt
     */
    protected function logFailedAttempt(Request $request, string $reason): void
    {
        $email = $request->input('email');
        $user = $this->userRepository->findByEmail($email);

        if ($user) {
            $this->userRepository->createLoginLog([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_status' => 'failed',
                'failure_reason' => $reason,
                'login_at' => now(),
            ]);
        }
    }
}