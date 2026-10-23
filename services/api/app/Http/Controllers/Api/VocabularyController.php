<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VocabularyReviewRequest;
use App\Models\UserVocabulary;
use App\Models\VocabularyItem;
use App\Services\SpacedRepetitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VocabularyController extends Controller
{
    protected SpacedRepetitionService $srsService;

    public function __construct(SpacedRepetitionService $srsService)
    {
        $this->srsService = $srsService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Paginated user vocabulary items
        $userVocab = UserVocabulary::where('user_id', $user->id)
            ->with('vocabularyItem')
            ->orderBy('next_review_at', 'asc')
            ->paginate(20);

        // Catalog of available items to learn
        $savedIds = UserVocabulary::where('user_id', $user->id)->pluck('vocabulary_item_id')->toArray();
        $query = VocabularyItem::where('is_published', true)->whereNotIn('id', $savedIds);

        if ($request->has('level')) {
            $query->where('cefr_level', $request->query('level'));
        }

        $availableItems = $query->limit(100)->get();

        $stats = [
            'total_saved' => count($savedIds),
            'mastered_count' => UserVocabulary::where('user_id', $user->id)->where('mastery_level', 'mastered')->count(),
            'learning_count' => UserVocabulary::where('user_id', $user->id)->where('mastery_level', 'learning')->count(),
            'due_count' => UserVocabulary::where('user_id', $user->id)->where('next_review_at', '<=', now())->count(),
        ];

        return response()->json([
            'user_vocabulary' => $userVocab,
            'available_catalog' => $availableItems,
            'stats' => $stats,
        ]);
    }

    public function due(Request $request): JsonResponse
    {
        $dueItems = $this->srsService->getDueReviews($request->user());

        return response()->json([
            'due_items' => $dueItems,
            'count' => $dueItems->count(),
        ]);
    }

    public function saveItem(Request $request, int $vocabularyId): JsonResponse
    {
        $item = VocabularyItem::where('is_published', true)->findOrFail($vocabularyId);
        $userVocab = $this->srsService->saveVocabulary($request->user(), $item->id);

        return response()->json([
            'message' => 'تمت إضافة الكلمة إلى قائمة مراجعاتك اليومية',
            'user_vocabulary' => $userVocab->load('vocabularyItem'),
        ]);
    }

    public function unsaveItem(Request $request, int $vocabularyId): JsonResponse
    {
        UserVocabulary::where('user_id', $request->user()->id)
            ->where('vocabulary_item_id', $vocabularyId)
            ->delete();

        return response()->json([
            'message' => 'تمت إزالة الكلمة من قائمة المراجعة',
        ]);
    }

    public function reviewItem(VocabularyReviewRequest $request, int $vocabularyId): JsonResponse
    {
        $user = $request->user();

        // vocabularyId here is the user_vocabulary id or item id
        $userVocab = UserVocabulary::where('user_id', $user->id)
            ->where(function ($q) use ($vocabularyId) {
                $q->where('id', $vocabularyId)
                  ->orWhere('vocabulary_item_id', $vocabularyId);
            })
            ->firstOrFail();

        $result = $this->srsService->processReview($user, $userVocab->id, $request->quality_rating);

        return response()->json([
            'message' => 'تم تسجيل المراجعة وتحديث جدول التكرار المتباعد',
            'result' => $result,
        ]);
    }
}
