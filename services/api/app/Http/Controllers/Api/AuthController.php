<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'learner',
        ]);

        // Initialize default profile
        UserProfile::create([
            'user_id' => $user->id,
            'learning_reason' => 'travel',
            'daily_goal_minutes' => 15,
            'experience_level' => 'zero',
            'explanation_dialect' => 'egyptian_arabic',
            'target_variant' => 'spain',
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'completed_levels' => [],
            'onboarding_completed' => false,
            'total_xp' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'timezone' => 'Africa/Cairo',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('profile'),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة، يرجى التأكد من البريد وكلمة المرور.'],
            ]);
        }

        // Revoke old tokens if any
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('profile'),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'user' => $user->load('profile'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        // Safe response without exposing user existence
        return response()->json([
            'message' => 'إذا كان البريد مسجلاً لدينا، فستتلقى رابط إعادة تعيين كلمة المرور.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json([
            'message' => 'تم تحديث كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول.',
        ]);
    }
}
