<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
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

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = JournalEntry::where('user_id', $user->id)
            ->with('analysis')
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $entries = $query->paginate(15);

        // Calculate journal stats for learner
        $stats = [
            'total_entries' => JournalEntry::where('user_id', $user->id)->count(),
            'total_words_written' => JournalEntry::where('user_id', $user->id)->sum('word_count'),
            'analyzed_count' => JournalEntry::where('user_id', $user->id)->where('status', 'analyzed')->count(),
        ];

        return response()->json([
            'entries' => $entries,
            'stats' => $stats,
        ]);
    }

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

        $wordCount = str_word_count($validated['content']);

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

    public function show(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('user_id', $request->user()->id)
            ->with(['analysis.suggestions'])
            ->findOrFail($id);

        return response()->json([
            'entry' => $entry,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:200',
            'content' => 'required|string|min:3|max:10000',
            'entry_date' => 'nullable|date',
        ]);

        $wordCount = str_word_count($validated['content']);

        $entry->update([
            'title' => $validated['title'] ?? $entry->title,
            'content' => $validated['content'],
            'word_count' => $wordCount,
            'entry_date' => $validated['entry_date'] ?? $entry->entry_date,
        ]);

        return response()->json([
            'message' => 'تم تحديث التدوينة',
            'entry' => $entry,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('user_id', $request->user()->id)->findOrFail($id);
        $entry->delete();

        return response()->json([
            'message' => 'تم حذف التدوينة بنجاح',
        ]);
    }

    public function analyze(Request $request, int $id): JsonResponse
    {
        $entry = JournalEntry::where('user_id', $request->user()->id)->findOrFail($id);

        $analysis = $this->analysisService->analyzeEntry($entry, $request->user());

        return response()->json([
            'message' => 'تم فحص التدوينة وتحليل الأخطاء القواعدية والأسلوبية بنجاح',
            'analysis' => $analysis,
            'entry' => $entry->fresh(['analysis.suggestions']),
        ]);
    }

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
}
