<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OnboardingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->profile;

        return response()->json([
            'profile' => $profile,
        ]);
    }

    public function update(OnboardingRequest $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        $profile->update([
            'learning_reason' => $request->learning_reason,
            'daily_goal_minutes' => $request->daily_goal_minutes,
            'experience_level' => $request->experience_level,
            'explanation_dialect' => $request->explanation_dialect,
            'target_variant' => $request->target_variant,
            'onboarding_completed' => true,
        ]);

        return response()->json([
            'message' => 'تم حفظ تفضيلاتك بنجاح',
            'profile' => $profile,
        ]);
    }
}
