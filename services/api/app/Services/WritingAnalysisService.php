<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\LearnerErrorConcept;
use App\Models\LearningRecommendation;
use App\Models\User;
use App\Models\WritingAnalysis;
use App\Models\WritingSuggestion;
use Illuminate\Support\Facades\DB;

class WritingAnalysisService
{
    /**
     * Analyze a journal entry, produce structured feedback, and update learner error profile.
     */
    public function analyzeEntry(JournalEntry $entry, User $user): WritingAnalysis
    {
        return DB::transaction(function () use ($entry, $user) {
            $text = $entry->content;
            $suggestions = $this->detectErrorsAndSuggestions($text);

            $errorCount = count(array_filter($suggestions, fn($s) => $s['severity'] === 'error'));
            $suggestionCount = count(array_filter($suggestions, fn($s) => $s['severity'] === 'suggestion'));

            // Calculate natural fluency score: 100 minus weighted errors
            $wordCount = max(str_word_count($text), 1);
            $penalty = ($errorCount * 6) + ($suggestionCount * 2);
            $fluencyScore = max(40, min(100, 100 - (int) round(($penalty / $wordCount) * 40)));

            // Reconstruct corrected text safely without overwriting original
            $corrected = $text;
            foreach ($suggestions as $s) {
                if ($s['severity'] === 'error') {
                    $corrected = str_ireplace($s['original_text'], $s['suggested_text'], $corrected);
                }
            }

            // Overall feedback summaries
            $feedbackAr = $errorCount === 0
                ? 'كتابة ممتازة وسلسة للغاية! تعابيرك الإسبانية مفهومة ودقيقة وتراعي السياق الطبيعي.'
                : "تمت مراجعة نصك وتحديد {$errorCount} ملاحظات قواعدية و{$suggestionCount} اقتراحات لتحسين الأسلوب وجعله أكثر طبيعية.";

            $feedbackEn = $errorCount === 0
                ? 'Excellent and natural writing! Your Spanish expressions are clear, contextual, and accurate.'
                : "Your entry has been analyzed: {$errorCount} grammar notes and {$suggestionCount} stylistic suggestions were identified.";

            // Save Analysis
            $analysis = WritingAnalysis::create([
                'journal_entry_id' => $entry->id,
                'user_id' => $user->id,
                'corrected_content' => $corrected,
                'overall_feedback_ar' => $feedbackAr,
                'overall_feedback_en' => $feedbackEn,
                'fluency_score' => $fluencyScore,
                'total_errors' => $errorCount,
                'total_suggestions' => $suggestionCount,
            ]);

            // Save individual suggestions
            foreach ($suggestions as $s) {
                WritingSuggestion::create([
                    'writing_analysis_id' => $analysis->id,
                    'original_text' => $s['original_text'],
                    'suggested_text' => $s['suggested_text'],
                    'error_category' => $s['error_category'],
                    'severity' => $s['severity'],
                    'explanation_ar' => $s['explanation_ar'],
                    'explanation_en' => $s['explanation_en'],
                    'example_sentence_es' => $s['example_sentence_es'],
                    'related_concept_slug' => $s['related_concept_slug'],
                ]);

                // Update Learner Error Profile
                if (!empty($s['related_concept_slug'])) {
                    $this->updateErrorConcept($user, $s);
                }
            }

            // Update entry status
            $entry->update(['status' => 'analyzed']);

            return $analysis->load('suggestions');
        });
    }

    /**
     * Linguistic pattern detection engine.
     */
    protected function detectErrorsAndSuggestions(string $text): array
    {
        $suggestions = [];

        // 1. Ser vs Estar for Location (yo soy en -> estoy en)
        if (preg_match('/\b(yo soy en|soy en)\b/iu', $text, $m)) {
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => 'estoy en',
                'error_category' => 'grammar',
                'severity' => 'error',
                'explanation_ar' => 'في الإسبانية، للتعبير عن التواجد في مكان نستخدم دائماً فعل Estar وليس Ser (Estoy en Madrid وليس Soy en Madrid).',
                'explanation_en' => 'In Spanish, location always uses the verb "estar" rather than "ser" (e.g. "estoy en Madrid").',
                'example_sentence_es' => 'Estoy en la oficina ahora mismo.',
                'related_concept_slug' => 'ser-vs-estar',
                'concept_name_ar' => 'الفرق بين فعل Ser وفعل Estar',
                'concept_name_en' => 'Ser vs Estar Distinction',
            ];
        }

        // 2. Gustar verb construct (yo gusto -> me gusta)
        if (preg_match('/\b(yo gusto|yo gusta)\b/iu', $text, $m)) {
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => 'me gusta',
                'error_category' => 'verb_conjugation',
                'severity' => 'error',
                'explanation_ar' => 'فعل Gustar لا يأخذ ضمير الفاعل Yo، بل يُصاغ دائماً بضمير المفعول به: Me gusta el café.',
                'explanation_en' => 'The verb "gustar" takes indirect object pronouns like "me" rather than the subject pronoun "yo".',
                'example_sentence_es' => 'Me gusta mucho estudiar español.',
                'related_concept_slug' => 'verbo-gustar',
                'concept_name_ar' => 'صياغة فعل الإعجاب Gustar',
                'concept_name_en' => 'Verbo Gustar Construction',
            ];
        }

        // 3. Gender agreement exception: "la problema" -> "el problema"
        if (preg_match('/\b(la problema|una problema)\b/iu', $text, $m)) {
            $replace = stripos($m[0], 'una') !== false ? 'un problema' : 'el problema';
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => $replace,
                'error_category' => 'gender_agreement',
                'severity' => 'error',
                'explanation_ar' => 'كلمة problema مذكرة في الإسبانية رغم انتهائها بحرف a (لأن أصلها يوناني) وتأخذ أداة التذكير El.',
                'explanation_en' => 'The word "problema" is masculine despite ending in -a (Greek origin) and takes "el".',
                'example_sentence_es' => 'No hay ningún problema, todo está resuelto.',
                'related_concept_slug' => 'gender-agreement',
                'concept_name_ar' => 'تطابق المذكر والمؤنث والشواذ',
                'concept_name_en' => 'Gender Agreement & Exceptions',
            ];
        }

        // 4. Preposition error: "viajar en España" -> "viajar a España"
        if (preg_match('/\b(viajar en|voy en|ir en)\s+(españa|madrid|barcelona|egipto)\b/iu', $text, $m)) {
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => str_ireplace(' en ', ' a ', $m[0]),
                'error_category' => 'grammar',
                'severity' => 'error',
                'explanation_ar' => 'مع أفعال الحركة والانتقال كالذهاب والسفر نستخدم حرف الجر "a" وليس "en" (Voy a España).',
                'explanation_en' => 'Movement verbs like "ir" and "viajar" take the preposition "a" rather than "en".',
                'example_sentence_es' => 'El próximo verano viajaré a España.',
                'related_concept_slug' => 'prepositions',
                'concept_name_ar' => 'حروف الجر مع أفعال الحركة (A vs En)',
                'concept_name_en' => 'Prepositions with Movement (A vs En)',
            ];
        }

        // 5. Stylistic suggestion: Natural phrase connector
        if (preg_match('/\b(pero también|además de eso)\b/iu', $text, $m)) {
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => 'asimismo',
                'error_category' => 'naturalness',
                'severity' => 'suggestion',
                'explanation_ar' => 'يمكنك استخدام أدوات ربط أكثر بلاغة وسلاسة مثل "asimismo" أو "por otra parte" لرفع رقي التعبير.',
                'explanation_en' => 'Consider using more natural transition connectors such as "asimismo" or "por otra parte".',
                'example_sentence_es' => 'Estudió gramática; asimismo practicó la conversación.',
                'related_concept_slug' => 'discourse-markers',
                'concept_name_ar' => 'أدوات الربط والأسلوب البلاغي',
                'concept_name_en' => 'Discourse Markers & Style',
            ];
        }

        return $suggestions;
    }

    /**
     * Update the learner's persistent error profile and create targeted recommendation.
     */
    protected function updateErrorConcept(User $user, array $suggestion): void
    {
        $slug = $suggestion['related_concept_slug'];
        $nameAr = $suggestion['concept_name_ar'] ?? $slug;
        $nameEn = $suggestion['concept_name_en'] ?? $slug;
        $category = $suggestion['error_category'];

        $concept = LearnerErrorConcept::firstOrNew([
            'user_id' => $user->id,
            'concept_slug' => $slug,
        ]);

        $concept->concept_name_ar = $nameAr;
        $concept->concept_name_en = $nameEn;
        $concept->category = $category;
        $concept->total_errors_count = ($concept->total_errors_count ?? 0) + 1;
        $concept->last_occurred_at = now();
        $concept->mastery_score = max(10, min(100, (int) round(($concept->successful_practices_count / max(1, $concept->total_errors_count + $concept->successful_practices_count)) * 100)));
        $concept->save();

        // Create an explainable learning recommendation if this error repeated
        if ($concept->total_errors_count >= 1) {
            LearningRecommendation::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'action_type' => 'practice_concept',
                    'title_ar' => "تدريب مستهدف: {$nameAr}",
                ],
                [
                    'title_en' => "Targeted Practice: {$nameEn}",
                    'reason_ar' => "لاحظنا تكرار هذه الملاحظة أثناء كتابتك في دفتر اليوميات. تدرب على القاعدة لتثبيتها في ذاكرتك.",
                    'reason_en' => "We noticed this pattern in your Spanish journal writing. Practicing this rule will help reinforce it.",
                    'action_payload' => ['concept_slug' => $slug, 'category' => $category],
                    'is_dismissed' => false,
                ]
            );
        }
    }
}
