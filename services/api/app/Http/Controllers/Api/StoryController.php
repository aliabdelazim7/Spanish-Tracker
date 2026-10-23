<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoryProgressRequest;
use App\Models\Story;
use App\Models\StoryProgress;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoryController extends Controller
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $stories = Story::where('is_published', true)
            ->withCount(['segments', 'questions'])
            ->orderBy('cefr_level')
            ->orderBy('id')
            ->get();

        $userProgress = StoryProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('story_id');

        $mapped = $stories->map(function ($s) use ($userProgress) {
            $prog = $userProgress->get($s->id);
            return [
                'id' => $s->id,
                'cefr_level' => $s->cefr_level,
                'title_es' => $s->title_es,
                'title_ar' => $s->title_ar,
                'summary_ar' => $s->summary_ar,
                'topic' => $s->topic,
                'estimated_minutes' => $s->estimated_minutes,
                'difficulty' => $s->difficulty,
                'xp_reward' => $s->xp_reward,
                'segments_count' => $s->segments_count,
                'questions_count' => $s->questions_count,
                'is_completed' => (bool) ($prog?->is_completed ?? false),
                'current_segment_index' => $prog?->current_segment_index ?? 0,
            ];
        });

        return response()->json([
            'stories' => $mapped,
        ]);
    }

    public function show(Request $request, int $storyId): JsonResponse
    {
        $user = $request->user();

        $story = Story::where('is_published', true)
            ->with([
                'segments' => fn($q) => $q->orderBy('order_index'),
                'questions' => fn($q) => $q->orderBy('after_segment_index'),
            ])
            ->findOrFail($storyId);

        $progress = StoryProgress::firstOrCreate(
            ['user_id' => $user->id, 'story_id' => $story->id],
            ['current_segment_index' => 0, 'is_completed' => false]
        );

        return response()->json([
            'story' => $story,
            'progress' => $progress,
        ]);
    }

    public function updateProgress(StoryProgressRequest $request, int $storyId): JsonResponse
    {
        $user = $request->user();
        $story = Story::where('is_published', true)->findOrFail($storyId);

        $progress = StoryProgress::updateOrCreate(
            ['user_id' => $user->id, 'story_id' => $story->id],
            ['current_segment_index' => $request->current_segment_index]
        );

        return response()->json([
            'progress' => $progress,
        ]);
    }

    public function complete(Request $request, int $storyId): JsonResponse
    {
        $user = $request->user();
        $story = Story::where('is_published', true)->findOrFail($storyId);

        return DB::transaction(function () use ($user, $story) {
            $progress = StoryProgress::firstOrCreate(
                ['user_id' => $user->id, 'story_id' => $story->id]
            );

            $xpAwarded = 0;
            if (!$progress->is_reward_claimed) {
                $xpAwarded = $story->xp_reward;
                $this->gamificationService->awardXp(
                    $user,
                    $xpAwarded,
                    'story',
                    $story->id,
                    "إكمال قصة تفاعلية: {$story->title_ar}"
                );
                $progress->is_reward_claimed = true;
            }

            $progress->is_completed = true;
            $progress->completed_at = Carbon::now();
            $progress->save();

            return response()->json([
                'message' => 'أحسنت! أكملت قراءة القصة بنجاح.',
                'progress' => $progress,
                'xp_awarded' => $xpAwarded,
            ]);
        });
    }
}
