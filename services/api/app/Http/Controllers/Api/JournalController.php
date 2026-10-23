<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\LearnerErrorConcept;
use App\Models\LearningRecommendation;
use App\Models\WritingSuggestion;
use App\Services\JournalPromptService;
use App\Services\WritingAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    protected WritingAnalysisService $analysisService;

    public function __construct(WritingAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * 1. List user's private journal entries.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = JournalEntry::where('user_id', $user->id)
            ->with(['analysis.suggestions'])
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $entries = $query->paginate(15);

        $stats = [
            'total_entries' => JournalEntry::where('user_id', $user->id)->count(),
            'total_words_written' => (int) JournalEntry::where('user_id', $user->id)->sum('word_count'),
            'analyzed_count' => JournalEntry::where('user_id', $user->id)->where('status', 'analyzed')->count(),
        ];

        return response()->json([
            'entries' => $entries,
            'stats' => $stats,
        ]);
    }

    /**
     * 2. Create new journal entry (free writing or guided prompt).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'nullable|string|max:200',
            'content' => 'required|string|min:3|max:10000',
            'prompt_topic' => 'nullable|string|max:100',
            'cefr_level' => 'nullable|string|max:4',
            'entry_date' => 'nullable|date',
        ]);

        $wordCount = count(preg_split('/\s+/u', trim($validated['content']), -1, PREG_SPLIT_NO_EMPTY));

        $entry = JournalEntry::create([
            'user_id' => $user->id,
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'prompt_topic' => $validated['prompt_topic'] ?? null,
            'cefr_level' => $validated['cefr_level'] ?? ($user->profile?->estimated_cefr_level ?? 'A1'),
            'status' => 'draft',
            'word_count' => $wordCount,
            'entry_date' => $validated['entry_date'] ?? now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'تم حفظ التدوينة بنجاح',
            'entry' => $entry,
        ], 201);
    }

    /**
     * 3. View single journal entry with strict user ownership.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('user_id', $request->user()->id)
            ->with(['analysis.suggestions'])
            ->findOrFail($id);

        return response()->json([
            'entry' => $entry,
        ]);
    }

    /**
     * 4. Update journal entry.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:200',
            'content' => 'required|string|min:3|max:10000',
            'entry_date' => 'nullable|date',
            'prompt_topic' => 'nullable|string|max:100',
        ]);

        $wordCount = count(preg_split('/\s+/u', trim($validated['content']), -1, PREG_SPLIT_NO_EMPTY));

        $entry->update([
            'title' => $validated['title'] ?? $entry->title,
            'content' => $validated['content'],
            'prompt_topic' => $validated['prompt_topic'] ?? $entry->prompt_topic,
            'word_count' => $wordCount,
            'entry_date' => $validated['entry_date'] ?? $entry->entry_date,
        ]);

        return response()->json([
            'message' => 'تم تحديث التدوينة بنجاح',
            'entry' => $entry,
        ]);
    }

    /**
     * 5. Delete journal entry.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('user_id', $request->user()->id)->findOrFail($id);
        $entry->delete();

        return response()->json([
            'message' => 'تم حذف التدوينة بنجاح',
        ]);
    }

    /**
     * 6. Analyze journal entry without modifying original writing.
     */
    public function analyze(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('user_id', $request->user()->id)->findOrFail($id);
        $lang = $request->input('lang', 'ar');

        $analysis = $this->analysisService->analyzeEntry($entry, $request->user(), $lang);

        return response()->json([
            'message' => 'تم فحص التدوينة وتحليل الأخطاء القواعدية والأسلوبية بنجاح',
            'analysis' => $analysis,
            'entry' => $entry->fresh(['analysis.suggestions']),
            'ai_status' => $this->analysisService->isExternalAiConfigured() ? 'active' : 'local_rules_engine',
            'is_ai_generated' => $analysis->is_ai_generated,
        ]);
    }

    /**
     * 7. Get feedback for journal entry.
     */
    public function feedback(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('user_id', $request->user()->id)
            ->with(['analysis.suggestions'])
            ->findOrFail($id);

        if (!$entry->analysis) {
            return response()->json([
                'message' => 'لم يتم تحليل هذه التدوينة بعد.',
                'has_analysis' => false,
            ], 404);
        }

        return response()->json([
            'analysis' => $entry->analysis,
            'entry' => $entry,
        ]);
    }

    /**
     * 8. Browse CEFR-aligned writing prompts.
     */
    public function prompts(Request $request, JournalPromptService $promptService): JsonResponse
    {
        $user = $request->user();
        $level = $request->query('level', $user->profile?->estimated_cefr_level ?? 'A1');
        $mode = $request->query('mode');
        $prompts = $promptService->getPromptsForLevel($level, $mode);
        $daily = $promptService->getDailyPrompt($level);

        return response()->json([
            'level' => $level,
            'prompts' => $prompts,
            'daily_prompt' => $daily,
        ]);
    }

    /**
     * 9. Get today's daily prompt for the learner's level.
     */
    public function dailyPrompt(Request $request, JournalPromptService $promptService): JsonResponse
    {
        $user = $request->user();
        $level = $request->query('level', $user->profile?->estimated_cefr_level ?? 'A1');
        $date = $request->query('date', now()->toDateString());
        $daily = $promptService->getDailyPrompt($level, $date);

        return response()->json([
            'daily_prompt' => $daily,
        ]);
    }

    /**
     * 10. Update suggestion status (mark understood / review later).
     */
    public function updateSuggestionStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Enforce ownership: suggestion must belong to an analysis owned by the authenticated user
        $suggestion = WritingSuggestion::whereHas('analysis', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:understood,review_later,pending',
            'user_notes' => 'nullable|string|max:1000',
        ]);

        $suggestion->update([
            'status' => $validated['status'],
            'is_understood' => ($validated['status'] === 'understood'),
            'user_notes' => $validated['user_notes'] ?? $suggestion->user_notes,
        ]);

        // If marked understood and has a tracked concept slug, boost mastery score
        if ($validated['status'] === 'understood' && $suggestion->related_concept_slug) {
            $concept = LearnerErrorConcept::where('user_id', $user->id)
                ->where('concept_slug', $suggestion->related_concept_slug)
                ->first();
            if ($concept) {
                $concept->increment('successful_practices_count');
                $concept->mastery_score = min(100, $concept->mastery_score + 15);
                $concept->save();
            }
        }

        return response()->json([
            'message' => 'تم تحديث حالة الملاحظة بنجاح',
            'suggestion' => $suggestion,
        ]);
    }

    /**
     * 11. Generate follow-up exercises derived from learner's past mistakes.
     */
    public function followUpExercises(Request $request): JsonResponse
    {
        $exercises = $this->analysisService->generateFollowUpExercises($request->user());

        return response()->json([
            'exercises' => $exercises,
        ]);
    }

    /**
     * 12. Submit follow-up exercise response.
     */
    public function submitFollowUp(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'concept_slug' => 'required|string|max:100',
            'user_answer' => 'required|string|max:200',
            'correct_answer' => 'required|string|max:200',
        ]);

        $isCorrect = mb_strtolower(trim($validated['user_answer'])) === mb_strtolower(trim($validated['correct_answer']));

        if ($isCorrect) {
            $concept = LearnerErrorConcept::where('user_id', $user->id)
                ->where('concept_slug', $validated['concept_slug'])
                ->first();

            if ($concept) {
                $concept->increment('successful_practices_count');
                $concept->last_practiced_at = now();
                $concept->mastery_score = min(100, $concept->mastery_score + 25);
                $concept->save();
            }

            // Mark pending suggestions for this concept as understood
            WritingSuggestion::whereHas('analysis', fn($q) => $q->where('user_id', $user->id))
                ->where('related_concept_slug', $validated['concept_slug'])
                ->where('status', 'pending')
                ->update(['status' => 'understood', 'is_understood' => true]);

            $user->profile?->increment('total_xp', 15);
        }

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_answer' => $validated['correct_answer'],
            'xp_awarded' => $isCorrect ? 15 : 0,
            'message' => $isCorrect ? 'إجابة صحيحة ومتقنة! تم ترقية مستوى تمكنك من القاعدة.' : 'إجابة غير صحيحة، حاول مجدداً وراجع التفسير القواعدي.',
        ]);
    }

    /**
     * 13. Identify recurring mistakes over time.
     */
    public function recurringMistakes(Request $request): JsonResponse
    {
        $user = $request->user();
        $concepts = LearnerErrorConcept::where('user_id', $user->id)
            ->orderBy('total_errors_count', 'desc')
            ->orderBy('last_occurred_at', 'desc')
            ->get();

        $recurring = $concepts->where('total_errors_count', '>=', 2)->values();

        return response()->json([
            'recurring_mistakes' => $recurring,
            'all_tracked_concepts' => $concepts,
            'total_errors' => $concepts->sum('total_errors_count'),
        ]);
    }

    /**
     * 14. Recommend relevant curriculum lessons based on actual error history.
     */
    public function recommendedLessons(Request $request): JsonResponse
    {
        $user = $request->user();
        $recommendations = LearningRecommendation::where('user_id', $user->id)
            ->where('action_type', 'take_lesson')
            ->where('is_dismissed', false)
            ->latest()
            ->take(6)
            ->get();

        return response()->json([
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * 15. Progress over time without false claims of complete fluency.
     */
    public function progressStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $totalEntries = JournalEntry::where('user_id', $user->id)->count();
        $totalWords = (int) JournalEntry::where('user_id', $user->id)->sum('word_count');
        $activeDays = JournalEntry::where('user_id', $user->id)->distinct('entry_date')->count('entry_date');
        $conceptsCount = LearnerErrorConcept::where('user_id', $user->id)->count();
        $masteredCount = LearnerErrorConcept::where('user_id', $user->id)->where('mastery_score', '>=', 80)->count();

        $recentTimeline = JournalEntry::where('user_id', $user->id)
            ->with('analysis')
            ->orderBy('entry_date', 'desc')
            ->take(10)
            ->get(['id', 'title', 'word_count', 'entry_date', 'status', 'created_at']);

        return response()->json([
            'stats' => [
                'total_entries' => $totalEntries,
                'total_words_written' => $totalWords,
                'active_writing_days' => $activeDays,
                'concepts_monitored' => $conceptsCount,
                'concepts_mastered' => $masteredCount,
                'average_words_per_entry' => $totalEntries > 0 ? (int) round($totalWords / $totalEntries) : 0,
            ],
            'recent_timeline' => $recentTimeline,
            'disclaimer_en' => 'Progress reflects active writing practice and targeted error correction over time. Writing samples measure productive accuracy, not complete conversational fluency.',
            'disclaimer_ar' => 'هذه المؤشرات تعكس انتظام الممارسة الكتابية والتركيز على تصحيح الأخطاء المتكررة عبر الوقت. العينات الكتابية تقيس دقة الإنتاج ومحاور التحسين، ولا تمثل تقييماً نهائياً للطلاقة الشاملة بمفردها.',
        ]);
    }
}
