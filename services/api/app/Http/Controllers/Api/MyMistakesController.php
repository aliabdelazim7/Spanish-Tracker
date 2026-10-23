<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearnerErrorConcept;
use App\Models\LearningRecommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyMistakesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $concepts = LearnerErrorConcept::where('user_id', $user->id)
            ->orderBy('total_errors_count', 'desc')
            ->orderBy('last_occurred_at', 'desc')
            ->get();

        $recommendations = LearningRecommendation::where('user_id', $user->id)
            ->where('is_dismissed', false)
            ->latest()
            ->limit(5)
            ->get();

        $categories = [
            'grammar' => $concepts->where('category', 'grammar')->count(),
            'verb_conjugation' => $concepts->where('category', 'verb_conjugation')->count(),
            'gender_agreement' => $concepts->where('category', 'gender_agreement')->count(),
            'vocabulary' => $concepts->where('category', 'vocabulary')->count(),
            'naturalness' => $concepts->where('category', 'naturalness')->count(),
        ];

        return response()->json([
            'concepts' => $concepts,
            'recommendations' => $recommendations,
            'categories' => $categories,
            'total_recorded_mistakes' => $concepts->sum('total_errors_count'),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $concept = LearnerErrorConcept::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'concept' => $concept,
        ]);
    }

    public function practice(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $concept = LearnerErrorConcept::where('user_id', $user->id)->findOrFail($id);

        $answer = trim((string) $request->input('answer', ''));

        // Targeted practice questions based on concept slug
        $practicePool = [
            'ser-vs-estar' => [
                'prompt' => 'Hoy yo ___ muy cansado por el trabajo.',
                'prompt_ar' => 'أنا اليوم متعب جداً بسبب العمل (حالة مؤقتة: اختر التصريف الصحيح لفعل estar)',
                'options' => ['estoy', 'soy', 'es', 'está'],
                'correct' => 'estoy',
                'explanation_ar' => 'التعب حالة صحية ومؤقتة، لذلك نستخدم معها دائماً تصريف فعل Estar: estoy cansado.',
            ],
            'verbo-gustar' => [
                'prompt' => 'A mí ___ mucho la comida española.',
                'prompt_ar' => 'يعجبني الطعام الإسباني كثيراً (اختر ضمير المفعول المناسب لفعل gustar)',
                'options' => ['me gusta', 'yo gusto', 'me gustan', 'gusto'],
                'correct' => 'me gusta',
                'explanation_ar' => 'المفعول به مفرد (la comida) لذلك نستخدم me gusta.',
            ],
            'gender-agreement' => [
                'prompt' => 'No entiendo ___ problema.',
                'prompt_ar' => 'لا أفهم المشكلة: اختر أداة التعريف المناسبة لكلمة problema المذكرة',
                'options' => ['el', 'la', 'los', 'las'],
                'correct' => 'el',
                'explanation_ar' => 'كلمة problema مذكرة وتأخذ أداة التعريف El.',
            ],
            'prepositions' => [
                'prompt' => 'El próximo mes voy ___ Madrid.',
                'prompt_ar' => 'الشهر القادم سأذهب إلى مدريد: اختر حرف الجر المناسب للذهاب',
                'options' => ['a', 'en', 'por', 'de'],
                'correct' => 'a',
                'explanation_ar' => 'مع أفعال الحركة والانتقال كالذهاب نستخدم حرف الجر a.',
            ],
        ];

        $quiz = $practicePool[$concept->concept_slug] ?? [
            'prompt' => '¿Cuál es la forma correcta?',
            'prompt_ar' => 'اختر الخيار الصحيح لتصحيح هذه القاعدة:',
            'options' => ['Opción correcta', 'Opción errónea'],
            'correct' => 'Opción correcta',
            'explanation_ar' => 'التطبيق السليم والممارسة المستمرة يرسخان القاعدة في الذاكرة.',
        ];

        // If user submitted an answer to check
        if (!empty($answer)) {
            $isCorrect = mb_strtolower($answer) === mb_strtolower($quiz['correct']);

            if ($isCorrect) {
                $concept->increment('successful_practices_count');
                $concept->last_practiced_at = now();
                $concept->mastery_score = min(100, $concept->mastery_score + 25);
                $concept->save();

                // Award XP for targeted practice
                $user->profile?->increment('total_xp', 15);
            }

            return response()->json([
                'is_correct' => $isCorrect,
                'correct_answer' => $quiz['correct'],
                'explanation_ar' => $quiz['explanation_ar'],
                'mastery_score' => $concept->mastery_score,
                'xp_awarded' => $isCorrect ? 15 : 0,
            ]);
        }

        // Otherwise return the quiz question
        return response()->json([
            'concept' => $concept,
            'quiz' => $quiz,
        ]);
    }
}
