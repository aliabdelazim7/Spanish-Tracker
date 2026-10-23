<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\LearnerErrorConcept;
use App\Models\LearningRecommendation;
use App\Models\Lesson;
use App\Models\User;
use App\Models\WritingAnalysis;
use App\Models\WritingSuggestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WritingAnalysisService
{
    protected AiTutorService $tutorService;

    public function __construct(?AiTutorService $tutorService = null)
    {
        $this->tutorService = $tutorService ?? app(AiTutorService::class);
    }

    /**
     * Check whether external AI provider is configured and operational.
     */
    public function isExternalAiConfigured(): bool
    {
        return $this->tutorService->isExternalAiOperational();
    }

    /**
     * Analyze a journal entry, produce structured feedback, and update learner error profile.
     * Preserves the learner's original entry content verbatim.
     */
    public function analyzeEntry(JournalEntry $entry, User $user, string $lang = 'ar'): WritingAnalysis
    {
        return DB::transaction(function () use ($entry, $user, $lang) {
            $text = $entry->content;
            $isEn = ($lang === 'en');
            $isAiConfigured = $this->isExternalAiConfigured();
            $provider = $isAiConfigured ? 'openai' : 'local_rules_engine';
            $isAiGenerated = $isAiConfigured;

            $suggestions = [];

            // If real external AI is configured, query external AI provider
            if ($isAiConfigured) {
                try {
                    $aiResult = $this->tutorService->correctUserText($user, $text, "Journal entry: {$entry->title}", $lang);
                    if (!empty($aiResult['errors'])) {
                        foreach ($aiResult['errors'] as $err) {
                            $suggestions[] = [
                                'original_text' => $err['original'] ?? '',
                                'suggested_text' => $err['correction'] ?? '',
                                'error_category' => $err['error_category'] ?? 'grammar',
                                'severity' => 'error',
                                'explanation_ar' => $err['reason'] ?? '',
                                'explanation_en' => $err['reason'] ?? '',
                                'example_sentence_es' => $err['natural_alternative'] ?? null,
                                'related_concept_slug' => $err['related_concept_slug'] ?? 'grammar-correction',
                                'natural_alternative' => $err['natural_alternative'] ?? null,
                                'concept_name_ar' => 'تصحيح لغوي دقيق',
                                'concept_name_en' => 'Linguistic Correction',
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('External AI analysis failed, falling back to local linguistic engine: ' . $e->getMessage());
                    $isAiGenerated = false;
                    $provider = 'local_rules_engine';
                    $suggestions = $this->detectErrorsAndSuggestions($text);
                }
            } else {
                // Real AI not configured: use transparent offline grammatical engine
                $suggestions = $this->detectErrorsAndSuggestions($text);
            }

            $errorCount = count(array_filter($suggestions, fn($s) => $s['severity'] === 'error'));
            $suggestionCount = count(array_filter($suggestions, fn($s) => $s['severity'] === 'suggestion'));

            // Calculate transparent practice score: 100 minus weighted penalties per word count
            $wordCount = max(str_word_count($text), 1);
            $penalty = ($errorCount * 6) + ($suggestionCount * 2);
            $fluencyScore = max(40, min(100, 100 - (int) round(($penalty / $wordCount) * 40)));

            // Reconstruct corrected version without altering original entry content
            $corrected = $text;
            foreach ($suggestions as $s) {
                if ($s['severity'] === 'error' && !empty($s['original_text']) && !empty($s['suggested_text'])) {
                    $corrected = str_ireplace($s['original_text'], $s['suggested_text'], $corrected);
                }
            }

            // Overall feedback summaries with honest transparency
            $engineNoticeAr = $isAiConfigured
                ? ''
                : ' (تم الفحص عبر المحرك اللغوي المدمج لعدم توفر مفتاح AI خارجي)';
            $engineNoticeEn = $isAiConfigured
                ? ''
                : ' (Analyzed via local linguistic engine; external AI not configured)';

            $feedbackAr = $errorCount === 0
                ? "كتابة ممتازة وسلسة للغاية! تعابيرك الإسبانية مفهومة ودقيقة وتراعي السياق الطبيعي.{$engineNoticeAr}"
                : "تمت مراجعة نصك وتحديد {$errorCount} ملاحظات قواعدية و{$suggestionCount} اقتراحات لتحسين الأسلوب وجعله أكثر طبيعية.{$engineNoticeAr}";

            $feedbackEn = $errorCount === 0
                ? "Excellent and natural writing! Your Spanish expressions are clear and accurate.{$engineNoticeEn}"
                : "Your entry has been analyzed: {$errorCount} grammar notes and {$suggestionCount} stylistic suggestions were identified.{$engineNoticeEn}";

            // Delete any existing analysis for this entry to prevent duplicates on re-analyze
            if ($entry->analysis) {
                $entry->analysis->suggestions()->delete();
                $entry->analysis()->delete();
            }

            // Save structured WritingAnalysis
            $analysis = WritingAnalysis::create([
                'journal_entry_id' => $entry->id,
                'user_id' => $user->id,
                'corrected_content' => $corrected,
                'overall_feedback_ar' => $feedbackAr,
                'overall_feedback_en' => $feedbackEn,
                'fluency_score' => $fluencyScore,
                'total_errors' => $errorCount,
                'total_suggestions' => $suggestionCount,
                'is_ai_generated' => $isAiGenerated,
                'provider' => $provider,
            ]);

            // Save individual structured WritingSuggestion records
            foreach ($suggestions as $s) {
                WritingSuggestion::create([
                    'writing_analysis_id' => $analysis->id,
                    'original_text' => $s['original_text'],
                    'suggested_text' => $s['suggested_text'],
                    'error_category' => $s['error_category'],
                    'severity' => $s['severity'],
                    'explanation_ar' => $s['explanation_ar'],
                    'explanation_en' => $s['explanation_en'] ?? null,
                    'example_sentence_es' => $s['example_sentence_es'] ?? null,
                    'related_concept_slug' => $s['related_concept_slug'] ?? null,
                    'natural_alternative' => $s['natural_alternative'] ?? null,
                    'is_understood' => false,
                    'status' => 'pending',
                ]);

                // Update Learner Error Profile and generate actionable curriculum recommendations
                if (!empty($s['related_concept_slug'])) {
                    $this->updateErrorConceptAndRecommendation($user, $s);
                }
            }

            // Mark journal entry status as analyzed while keeping original content intact
            $entry->update(['status' => 'analyzed']);

            return $analysis->load('suggestions');
        });
    }

    /**
     * Local linguistic rule engine covering:
     * - Grammar mistakes
     * - Incorrect verb conjugations
     * - Vocabulary errors (false friends)
     * - Spelling and accent errors
     * - Unnatural phrasing
     * - Sentence structure and word order
     */
    public function detectErrorsAndSuggestions(string $text): array
    {
        $suggestions = [];

        // 1. Grammar: Ser vs Estar for Location (yo soy en -> estoy en)
        if (preg_match('/\b(yo soy en|soy en)\b/iu', $text, $m)) {
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => 'estoy en',
                'error_category' => 'grammar',
                'severity' => 'error',
                'explanation_ar' => 'في الإسبانية، للتعبير عن التواجد في مكان أو مدينة نستخدم دائماً فعل Estar وليس Ser (Estoy en Madrid وليس Soy en Madrid).',
                'explanation_en' => 'In Spanish, location always uses the verb "estar" rather than "ser" (e.g. "estoy en Madrid").',
                'example_sentence_es' => 'Estoy en la oficina ahora mismo.',
                'related_concept_slug' => 'ser-vs-estar',
                'concept_name_ar' => 'الفرق بين فعل Ser وفعل Estar',
                'concept_name_en' => 'Ser vs Estar Distinction',
                'natural_alternative' => 'Actualmente me encuentro en...',
            ];
        }

        // 2. Grammar: Por vs Para purpose confusion (estudio por trabajar -> estudio para trabajar)
        if (preg_match('/\b(estudio por|trabajo por|salgo por)\s+([a-z]+ar|[a-z]+er|[a-z]+ir)\b/iu', $text, $m)) {
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => str_ireplace(' por ', ' para ', $m[0]),
                'error_category' => 'grammar',
                'severity' => 'error',
                'explanation_ar' => 'للتعبير عن الهدف والغاية مع المصدر (Infinitive) نستخدم حرف الجر PARA وليس POR.',
                'explanation_en' => 'To express purpose or objective before an infinitive verb, use PARA instead of POR.',
                'example_sentence_es' => 'Estudio español para conseguir un buen trabajo.',
                'related_concept_slug' => 'por-vs-para',
                'concept_name_ar' => 'استخدامات حروف الجر Por و Para',
                'concept_name_en' => 'Por vs Para Usage',
                'natural_alternative' => 'Estudio con el objetivo de...',
            ];
        }

        // 3. Verb Conjugation: Gustar verb construct (yo gusto -> me gusta)
        if (preg_match('/\b(yo gusto|yo gusta)\b/iu', $text, $m)) {
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => 'me gusta',
                'error_category' => 'verb_conjugation',
                'severity' => 'error',
                'explanation_ar' => 'فعل Gustar لا يأخذ ضمير الفاعل Yo، بل يُصاغ دائماً بضمير المفعول غير المباشر: Me gusta el café.',
                'explanation_en' => 'The verb "gustar" takes indirect object pronouns like "me" rather than the subject pronoun "yo".',
                'example_sentence_es' => 'Me gusta mucho estudiar español.',
                'related_concept_slug' => 'verbo-gustar',
                'concept_name_ar' => 'صياغة فعل الإعجاب Gustar',
                'concept_name_en' => 'Verbo Gustar Construction',
                'natural_alternative' => 'A mí me apasiona...',
            ];
        }

        // 4. Verb Conjugation: Radical vowel change error (yo dormo -> yo duermo, yo podo -> yo puedo)
        if (preg_match('/\b(yo dormo|dormo|yo podo|podo)\b/iu', $text, $m)) {
            $suggested = str_ireplace(['dormo', 'podo'], ['duermo', 'puedo'], $m[0]);
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => $suggested,
                'error_category' => 'verb_conjugation',
                'severity' => 'error',
                'explanation_ar' => 'أفعال Dormir و Poder هي أفعال شاذة في جذرها حيث ينقلب حرف o إلى ue في الحاضر (yo duermo / yo puedo).',
                'explanation_en' => 'The verbs "dormir" and "poder" have an o -> ue stem change in the present indicative tense.',
                'example_sentence_es' => 'Normalmente duermo ocho horas diarias.',
                'related_concept_slug' => 'stem-changing-verbs',
                'concept_name_ar' => 'الأفعال متغيرة الجذر (Stem-Changing)',
                'concept_name_en' => 'Stem Changing Verbs (o->ue)',
                'natural_alternative' => null,
            ];
        }

        // 5. Vocabulary: False friend "embarazado" (embarrassed -> con vergüenza / avergonzado)
        if (preg_match('/\b(estoy embarazado|estoy embarazada)\b/iu', $text, $m)) {
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => 'tengo vergüenza / estoy avergonzado',
                'error_category' => 'vocabulary',
                'severity' => 'error',
                'explanation_ar' => 'كلمة embarazada في الإسبانية تعني "حامل"، ولا تعني خجول أو محرج (False Friend). للتعبير عن الخجل نقول "Tengo vergüenza" أو "Estoy avergonzado/a".',
                'explanation_en' => '"Embarazada" is a famous false cognate meaning "pregnant". To say you are embarrassed, use "tengo vergüenza" or "estoy avergonzado".',
                'example_sentence_es' => 'Cuando cometí el error, me dio mucha vergüenza.',
                'related_concept_slug' => 'false-friends',
                'concept_name_ar' => 'الكلمات الخادعة الشائعة (Falsos Amigos)',
                'concept_name_en' => 'Common False Friends',
                'natural_alternative' => 'Me sentí algo avergonzado por la situación.',
            ];
        }

        // 6. Spelling and Accent: Missing written accent (tambien -> también, mas o menos -> más o menos)
        if (preg_match('/\b(tambien|facil|arbol|ano)\b/iu', $text, $m)) {
            $map = ['tambien' => 'también', 'facil' => 'fácil', 'arbol' => 'árbol', 'ano' => 'año'];
            $matchedWord = mb_strtolower($m[0]);
            $correction = $map[$matchedWord] ?? $m[0];
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => $correction,
                'error_category' => 'spelling',
                'severity' => 'error',
                'explanation_ar' => "الكلمة تحتاج إلى نبرة مكتوبة (Tilde) أو حرف ñ: '{$correction}' لتمييز النطق والمعنى السليم.",
                'explanation_en' => "Missing written accent (tilde) or letter ñ: '{$correction}'.",
                'example_sentence_es' => "Yo también quiero aprender más.",
                'related_concept_slug' => 'spelling-and-accents',
                'concept_name_ar' => 'قواعد النبرات وعلامات التشكيل (Tildes)',
                'concept_name_en' => 'Spanish Accents and Diacritics',
                'natural_alternative' => null,
            ];
        }

        // 7. Unnatural Phrasing: Literal translation of English/Arabic age "soy 20 años" -> "tengo 20 años"
        if (preg_match('/\b(soy (\d+) anos|soy (\d+) años|estoy (\d+) años)\b/iu', $text, $m)) {
            $correctedAge = preg_replace('/\b(soy|estoy) (\d+) (anos|años)\b/iu', 'tengo $2 años', $m[0]);
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => $correctedAge,
                'error_category' => 'unnatural_phrasing',
                'severity' => 'error',
                'explanation_ar' => 'في الإسبانية، للتعبير عن السن والعمر نستخدم دائماً فعل Tener (الملكية) وليس فعل Ser (الكينونة): Tengo 25 años.',
                'explanation_en' => 'In Spanish, age is expressed with the verb "tener" (to have), not "ser" (to be): "Tengo 20 años".',
                'example_sentence_es' => 'Tengo veinticinco años y vivo en Valencia.',
                'related_concept_slug' => 'idiomatic-tener-expressions',
                'concept_name_ar' => 'تعبيرات فعل Tener الاصطلاحية',
                'concept_name_en' => 'Idiomatic Expressions with Tener',
                'natural_alternative' => 'Cumpliré pronto más años.',
            ];
        }

        // 8. Sentence Structure and Word Order: Pre-verbal negation order ("no yo quiero" -> "yo no quiero" / "no quiero")
        if (preg_match('/\b(no yo quiero|no yo puedo|no yo voy)\b/iu', $text, $m)) {
            $reordered = str_ireplace(
                ['no yo quiero', 'no yo puedo', 'no yo voy'],
                ['yo no quiero', 'yo no puedo', 'yo no voy'],
                $m[0]
            );
            $suggestions[] = [
                'original_text' => $m[0],
                'suggested_text' => $reordered,
                'error_category' => 'sentence_structure',
                'severity' => 'error',
                'explanation_ar' => 'أداة النفي (No) تأتي مباشرة قبل الفعل المصرف، ولا تسبق ضمير الفاعل: "Yo no quiero" أو الاختصار الطبيعي "No quiero".',
                'explanation_en' => 'In Spanish negative sentences, the negation "no" must directly precede the conjugated verb.',
                'example_sentence_es' => 'Yo no quiero llegar tarde a la clase.',
                'related_concept_slug' => 'sentence-structure-negation',
                'concept_name_ar' => 'ترتيب الجملة المنفية وتراكيب الكلمات',
                'concept_name_en' => 'Sentence Structure & Negation Placement',
                'natural_alternative' => 'Prefiero no ir hoy.',
            ];
        }

        // 9. Gender agreement exception: "la problema" -> "el problema"
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
                'natural_alternative' => 'No representa ningún inconveniente.',
            ];
        }

        // 10. Stylistic connector suggestion: "pero también" -> "asimismo"
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
                'natural_alternative' => 'De igual manera...',
            ];
        }

        return $suggestions;
    }

    /**
     * Update the learner's persistent error profile and link targeted curriculum lessons.
     */
    protected function updateErrorConceptAndRecommendation(User $user, array $suggestion): void
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

        // Dynamic lookup for curriculum lesson in the database
        $lessonKeywords = [
            'ser-vs-estar' => 'Ser vs Estar',
            'por-vs-para' => 'Por vs Para',
            'verbo-gustar' => 'llamarse',
            'gender-agreement' => 'Familia',
            'past_tenses' => 'Indefinido',
            'discourse-markers' => 'Conectores',
            'subjunctive_mood' => 'Subjuntivo',
        ];

        $keyword = $lessonKeywords[$slug] ?? 'Ser vs Estar';
        $targetLesson = Lesson::where('title', 'like', "%{$keyword}%")
            ->orWhere('title_ar', 'like', "%{$keyword}%")
            ->first() ?? Lesson::first();

        // Create or update actionable curriculum recommendation
        LearningRecommendation::updateOrCreate(
            [
                'user_id' => $user->id,
                'action_type' => 'take_lesson',
                'title_ar' => "درس مقترح: {$nameAr}",
            ],
            [
                'title_en' => "Recommended Lesson: {$nameEn}",
                'reason_ar' => "لاحظنا تكرار هذه الملاحظة أثناء كتابتك في دفتر اليوميات. ننصحك بمراجعة درس '{$targetLesson?->title}' لترسيخ القاعدة.",
                'reason_en' => "We noticed this pattern in your Spanish journal writing. Reviewing lesson '{$targetLesson?->title}' will help reinforce this rule.",
                'action_payload' => [
                    'concept_slug' => $slug,
                    'category' => $category,
                    'lesson_id' => $targetLesson?->id ?? 15,
                    'lesson_title' => $targetLesson?->title ?? 'Ser vs Estar',
                ],
                'is_dismissed' => false,
            ]
        );
    }

    /**
     * Generate interactive follow-up exercises derived from the learner's actual encountered mistakes.
     */
    public function generateFollowUpExercises(User $user): array
    {
        $errorConcepts = LearnerErrorConcept::where('user_id', $user->id)
            ->where('total_errors_count', '>=', 1)
            ->orderBy('last_occurred_at', 'desc')
            ->take(5)
            ->get();

        $exercises = [];

        // Dynamic exercises bank keyed by error concepts
        $exerciseTemplates = [
            'ser-vs-estar' => [
                'concept_slug' => 'ser-vs-estar',
                'type' => 'fill_blank',
                'prompt_es' => 'Completa la frase con la forma correcta de "ser" o "estar":',
                'prompt_ar' => 'أكمل الجملة بالتصريف الصحيح لفعل ser أو estar وفق السياق الجغرافي:',
                'prompt_en' => 'Complete the sentence with the correct form of ser or estar for location:',
                'sentence' => 'Ahora mismo nosotros ___ en el centro de Madrid.',
                'options' => ['estamos', 'somos', 'están', 'son'],
                'correct_answer' => 'estamos',
                'explanation_ar' => 'التواجد في مكان أو مدينة يأخذ دائماً فعل Estar: estamos en Madrid.',
                'explanation_en' => 'Location requires ESTAR: estamos en Madrid.',
            ],
            'por-vs-para' => [
                'concept_slug' => 'por-vs-para',
                'type' => 'fill_blank',
                'prompt_es' => 'Elige la preposición adecuada (Por o Para):',
                'prompt_ar' => 'اختر حرف الجر المناسب للتعبير عن الهدف أو الغاية المستمرة:',
                'prompt_en' => 'Choose the appropriate preposition expressing future purpose:',
                'sentence' => 'Estudio dos horas cada noche ___ aprobar el examen DELE.',
                'options' => ['para', 'por', 'de', 'en'],
                'correct_answer' => 'para',
                'explanation_ar' => 'الهدف والغاية المستقبلية مع المصدر تأخذ دائماً Para.',
                'explanation_en' => 'Purpose before an infinitive verb takes PARA.',
            ],
            'verbo-gustar' => [
                'concept_slug' => 'verbo-gustar',
                'type' => 'error_correction',
                'prompt_es' => 'Identifica la forma correcta del verbo gustar:',
                'prompt_ar' => 'اختر الصياغة السليمة لفعل الإعجاب مع المفعول المفرد:',
                'prompt_en' => 'Choose the grammatically correct construct for the verb gustar:',
                'sentence' => 'A mí ___ viajar y conocer nuevas culturas.',
                'options' => ['me gusta', 'yo gusto', 'me gustan', 'gusto yo'],
                'correct_answer' => 'me gusta',
                'explanation_ar' => 'فعل gustar يصاغ بضمير المفعول me gusta مع الأفعال المصدرية.',
                'explanation_en' => 'Verbo gustar takes the indirect object pronoun: me gusta.',
            ],
            'gender-agreement' => [
                'concept_slug' => 'gender-agreement',
                'type' => 'fill_blank',
                'prompt_es' => 'Elige el artículo correcto para esta palabra de origen griego:',
                'prompt_ar' => 'اختر أداة التعريف الصحيحة لكلمة problema المذكرة:',
                'prompt_en' => 'Choose the correct masculine article for "problema":',
                'sentence' => '___ problema principal ya tiene una solución rápida.',
                'options' => ['El', 'La', 'Las', 'Los'],
                'correct_answer' => 'El',
                'explanation_ar' => 'كلمة problema مذكرة وتأخذ أداة التذكير El.',
                'explanation_en' => '"Problema" is masculine and takes the article EL.',
            ],
            'spelling-and-accents' => [
                'concept_slug' => 'spelling-and-accents',
                'type' => 'fill_blank',
                'prompt_es' => 'Elige la palabra correctamente acentuada:',
                'prompt_ar' => 'اختر الكلمة ذات النبرة المكتوبة (Tilde) الصحيحة:',
                'prompt_en' => 'Choose the word with the correct accent mark:',
                'sentence' => 'Ella ___ quiere acompañarnos a la reunión.',
                'options' => ['también', 'tambien', 'tambíen', 'tambièn'],
                'correct_answer' => 'también',
                'explanation_ar' => 'كلمة también تأخذ نبرة حادة على حرف e.',
                'explanation_en' => '"También" requires an acute accent on the letter e.',
            ],
        ];

        // If user has recorded mistakes, match against templates
        foreach ($errorConcepts as $concept) {
            if (isset($exerciseTemplates[$concept->concept_slug])) {
                $item = $exerciseTemplates[$concept->concept_slug];
                $item['source_concept_id'] = $concept->id;
                $item['error_count'] = $concept->total_errors_count;
                $exercises[] = $item;
            }
        }

        // If no specific error concepts found yet, provide foundational exercises
        if (empty($exercises)) {
            $exercises[] = $exerciseTemplates['ser-vs-estar'];
            $exercises[] = $exerciseTemplates['por-vs-para'];
        }

        return $exercises;
    }
}
