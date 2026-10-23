<?php

namespace App\Services\Ai;

class MockLanguageTutorProvider implements LanguageTutorProviderInterface
{
    /**
     * 1. Explain grammar in Arabic or English adapted to the CEFR level with contextual examples.
     */
    public function explainGrammar(
        string $concept,
        string $lang = 'ar',
        string $dialect = 'egyptian_arabic',
        string $level = 'A1',
        ?array $learnerContext = null
    ): array {
        $normalized = mb_strtolower(trim($concept));
        $isEn = ($lang === 'en');

        // Ser vs Estar
        if (str_contains($normalized, 'ser') && str_contains($normalized, 'estar')) {
            return [
                'concept' => 'Ser vs Estar',
                'level' => $level,
                'explanation' => $isEn
                    ? "In Spanish, the English verb 'to be' is split into SER and ESTAR. SER expresses permanent identity, nationality, profession, and inherent characteristics (e.g. Soy médico, La casa es blanca). ESTAR expresses location, temporary states, and physical/emotional conditions (e.g. Estoy en Madrid, Estoy cansado). Remember: Ser is for 'WHAT' something is; Estar is for 'HOW/WHERE' something is."
                    : ($dialect === 'egyptian_arabic'
                        ? 'في الإسبانية، فعل الكينونة بيتقسم لفعلين: Ser و Estar. ببساطة: فعل Ser بيعبر عن "الأصل والصفات الدائمة الثابتة" (جنسيتك: Soy egipcio، مهنتك: Soy médico). أما فعل Estar بيعبر عن "المكان والحالة المؤقتة" (مكانك: Estoy en Madrid، أو تعبان دلوقتي: Estoy cansado). افتكرها دايماً: Ser للهوية، وEstar للحال والمكان!'
                        : 'في اللغة الإسبانية، ينقسم فعل الكينونة إلى Ser و Estar. يُستخدم Ser للتعبير عن الهوية والمهنة والأصل والصفات الدائمة (Soy profesor). بينما يُستخدم Estar للموقع الجغرافي والحالات الصحية والنفسية المؤقتة (Estoy cansado, Estoy en la oficina).'),
                'examples' => [
                    [
                        'es' => 'Soy egipcio y soy profesor.',
                        'translation' => $isEn ? 'I am Egyptian and I am a teacher (permanent traits).' : 'أنا مصري وأنا معلم (صفات دائمة وثابتة).'
                    ],
                    [
                        'es' => 'Estoy en la cafetería porque hoy estoy muy cansado.',
                        'translation' => $isEn ? 'I am at the cafeteria because I am tired today (location and temporary state).' : 'أنا في المقهى لأني متعب اليوم (مكان وحالة مؤقتة).'
                    ],
                ],
                'common_mistakes' => $isEn
                    ? "Saying 'Soy en Madrid' instead of 'Estoy en Madrid'. Locations always use Estar."
                    : 'الخطأ الشائع هو قول "Soy en Madrid" بدلاً من "Estoy en Madrid". المواقع الجغرافية تأخذ دائماً Estar.',
                'regional_variations' => [
                    'es-ES' => 'Standard Peninsular usage uses Estar for geographic and temporary states.',
                    'latam' => 'In colloquial speech across parts of the Americas, Estar can occasionally appear with certain adjectives (e.g. está bueno).',
                ],
                'is_context_dependent' => true,
                'provider' => 'local-expert-tutor',
                'is_ai_generated' => false,
                'explanation_lang' => $lang,
            ];
        }

        // Por vs Para
        if (str_contains($normalized, 'por') && str_contains($normalized, 'para')) {
            return [
                'concept' => 'Por vs Para',
                'level' => in_array($level, ['A1', 'A2']) ? 'A2' : 'B1',
                'explanation' => $isEn
                    ? "POR and PARA both translate to 'for', but carry opposite vectors. POR looks BACKWARD to reason, cause, exchange, or duration (e.g., Gracias por todo, Estudio por vocación). PARA looks FORWARD to recipient, goal, purpose, or deadline (e.g., Este regalo es para ti, Estudio para aprobar el examen)."
                    : ($dialect === 'egyptian_arabic'
                        ? 'حرفين الجر Por و Para الاتنين معناهم "علشان"، بس الفرق جوهري: Por بتعبر عن "السبب اللي ورا الفعل" (بسببك: Por ti، شكراً على تعبك: Gracias por tu ayuda). أما Para بتعبر عن "الهدف المستقبلي والمستلم" (الهدية دي لِيك: Para ti، بذاكر علشان أنجح: Para aprobar).'
                        : 'يُستخدم Por للتعبير عن السبب، الدافع، التعويض، أو المدة الزمنية (Por ti). بينما يُستخدم Para للتعبير عن الهدف والغاية والمستلم والموعد النهائي (Para aprobar, Para ti).'),
                'examples' => [
                    [
                        'es' => 'Estudio mucho para conseguir una beca.',
                        'translation' => $isEn ? 'I study hard in order to get a scholarship (future goal = Para).' : 'أدرس بجد من أجل الحصول على منحة (هدف مستقبلي = Para).'
                    ],
                    [
                        'es' => 'Llegué tarde por el tráfico de la mañana.',
                        'translation' => $isEn ? 'I arrived late because of the morning traffic (cause = Por).' : 'وصلت متأخراً بسبب زحام الصباح (سبب ودافع = Por).'
                    ],
                ],
                'common_mistakes' => $isEn
                    ? "Saying 'por conseguir' when expressing future purpose. Purpose takes PARA + Infinitive."
                    : 'استخدام Por مع الأفعال للهدف؛ القاعدة هي دائماً Para + المصدر للهدف والغاية.',
                'regional_variations' => 'Both prepositions follow strict standard syntax across all Spanish-speaking countries.',
                'is_context_dependent' => false,
                'provider' => 'local-expert-tutor',
                'is_ai_generated' => false,
                'explanation_lang' => $lang,
            ];
        }

        // Subjunctive
        if (str_contains($normalized, 'subjuntivo') || str_contains($normalized, 'subjunctive') || str_contains($normalized, 'شك')) {
            return [
                'concept' => 'El Modo Subjuntivo',
                'level' => 'B1',
                'explanation' => $isEn
                    ? "The Subjunctive is a mood, not a tense. While Indicative declares facts and certainty (Creo que es verdad), the Subjunctive reflects subjectivity, wishes, doubt, emotion, and non-realized events (Espero que vengas, No creo que sea verdad). Trigger words include verbs of wishing (querer, esperar), doubt (dudar, no creer), and impersonal evaluations (es necesario que)."
                    : ($dialect === 'egyptian_arabic'
                        ? 'صيغة الـ Subjuntivo مش زمن عادي، ده "مزاج لغوي" بيعبر عن الشك، الرغبة، والتمني. لما تكون متأكد بتستخدم الحاضر العادي (Creo que viene). لكن لما تشك أو تتمنى أو تطلب، الفعل بيتحول لـ Subjuntivo (No creo que venga، Ojalá apruebe). نهايات الأفعال بتتعكس: أفعال -ar بتاخد -e وأفعال -er/-ir بتاخد -a!'
                        : 'صيغة الشك (Subjuntivo) تُستخدم للتعبير عن الرغبات، الآمال، الاحتمالات، والشك، بعد أدوات مثل Ojalá, Quiero que, No creo que.'),
                'examples' => [
                    [
                        'es' => 'Espero que tengas un buen viaje.',
                        'translation' => $isEn ? 'I hope you have a great trip.' : 'أتمنى لك رحلة موفقة.'
                    ],
                    [
                        'es' => 'No creo que él sepa la respuesta.',
                        'translation' => $isEn ? 'I do not believe he knows the answer.' : 'لا أعتقد أنه يعرف الإجابة.'
                    ],
                ],
                'common_mistakes' => $isEn
                    ? "Using the indicative after 'No creo que'. Remember: Creo que + Indicativo; No creo que + Subjuntivo."
                    : 'استخدام الحاضر بعد "No creo que"؛ اليقين يأخذ Indicativo والشك والنفي يأخذ Subjuntivo.',
                'regional_variations' => 'In Spain and parts of the Caribbean, present subjunctive with Ojalá is extremely frequent.',
                'is_context_dependent' => true,
                'provider' => 'local-expert-tutor',
                'is_ai_generated' => false,
                'explanation_lang' => $lang,
            ];
        }

        // Generic fallback adapted to level
        return [
            'concept' => $concept,
            'level' => $level,
            'explanation' => $isEn
                ? "Grammar explanation for '{$concept}' at CEFR {$level}: Spanish follows consistent subject-verb agreement and morphological patterns. Practicing full natural sentences rather than isolated words accelerates intuitive mastery."
                : ($dialect === 'egyptian_arabic'
                    ? "شرح مبسط لقاعدة '{$concept}' في مستوى {$level}: في اللغة الإسبانية، بناء الجملة منظم جداً، وتصريف الأفعال بيعتمد على نهايات الفعل (-ar, -er, -ir). الممارسة اليومية ونطق الجمل بصوت مسموع هو سر الطلاقة السريعة."
                    : "شرح تفصيلي لقاعدة '{$concept}' لمستوى {$level}: تتميز الإسبانية بنظام صرفي منظم وقواعد واضحة لتوافق الصفات والأسماء في التذكير والتأنيث والعدد."),
            'examples' => [
                [
                    'es' => 'Estudio español con entusiasmo cada día.',
                    'translation' => $isEn ? 'I study Spanish enthusiastically every day.' : 'أدرس الإسبانية بحماس كل يوم.'
                ],
                [
                    'es' => 'La práctica constante asegura el éxito.',
                    'translation' => $isEn ? 'Consistent practice ensures success.' : 'الممارسة المستمرة تضمن النجاح.'
                ],
            ],
            'common_mistakes' => $isEn
                ? 'Avoid literal word-by-word translation from your mother tongue.'
                : 'تجنب الترجمة الحرفية واحرص على استخدام التراكيب الإسبانية الأصلية.',
            'regional_variations' => 'Standard Castilian Spanish guidelines apply.',
            'is_context_dependent' => false,
            'provider' => 'local-expert-tutor',
            'is_ai_generated' => false,
            'explanation_lang' => $lang,
        ];
    }

    /**
     * 2. Correct text with detailed error categorization (grammar, spelling, unnatural phrasing, regional).
     */
    public function correctText(
        string $userSpanishText,
        ?string $context = null,
        string $lang = 'ar',
        string $level = 'A1'
    ): array {
        $text = trim($userSpanishText);
        $errors = [];
        $corrected = $text;
        $isEn = ($lang === 'en');

        // 1. Grammar Error: Ser vs Estar for location
        if (preg_match('/\b(yo soy en|soy en)\b/i', $text)) {
            $corrected = preg_replace('/\b(yo soy en|soy en)\b/i', 'estoy en', $corrected);
            $errors[] = [
                'error_category' => 'grammar_error',
                'original' => 'soy en',
                'correction' => 'estoy en',
                'reason' => $isEn
                    ? 'In Spanish, location and physical presence require the verb ESTAR, never SER.'
                    : 'للتعبير عن التواجد في مكان أو مدينة نستخدم دائماً فعل Estar وليس Ser.',
                'is_context_dependent' => false,
                'regional_note' => null,
            ];
        }

        // 2. Grammar Error: Gustar syntax
        if (preg_match('/\b(yo gusto)\b/i', $text)) {
            $corrected = preg_replace('/\b(yo gusto)\b/i', 'me gusta', $corrected);
            $errors[] = [
                'error_category' => 'grammar_error',
                'original' => 'yo gusto',
                'correction' => 'me gusta',
                'reason' => $isEn
                    ? 'The verb GUSTAR functions inversely: it takes indirect object pronouns (me, te, le), not subject pronoun (yo).'
                    : 'مع فعل الإعجاب Gustar نستخدم ضمير المفعول به me (Me gusta) وليس ضمير الفاعل yo.',
                'is_context_dependent' => false,
                'regional_note' => null,
            ];
        }

        // 3. Gender Agreement Error (Greek origin noun)
        if (preg_match('/\b(la problema)\b/i', $text)) {
            $corrected = preg_replace('/\b(la problema)\b/i', 'el problema', $corrected);
            $errors[] = [
                'error_category' => 'grammar_error',
                'original' => 'la problema',
                'correction' => 'el problema',
                'reason' => $isEn
                    ? 'The noun "problema" is masculine because of its Greek origin, taking the masculine article "el".'
                    : 'كلمة problema اسم مذكر ذو أصل يوناني تأخذ أداة التعريف El رغم انتهائها بحرف a.',
                'is_context_dependent' => false,
                'regional_note' => null,
            ];
        }

        // 4. Spelling Mistake: Missing accent or typo
        if (preg_match('/\b(tambien)\b/i', $text)) {
            $corrected = preg_replace('/\b(tambien)\b/i', 'también', $corrected);
            $errors[] = [
                'error_category' => 'spelling_mistake',
                'original' => 'tambien',
                'correction' => 'también',
                'reason' => $isEn
                    ? 'Acute accent required: "también" carries a tilde on the "e" as an acute word ending in -n.'
                    : 'كلمة también تحتاج نبرة مكتوبة (tilde) على حرف e لأنها كلمة حادة تنتهي بحرف n.',
                'is_context_dependent' => false,
                'regional_note' => null,
            ];
        }

        // 5. Regional Variation: Car / Computer (Spain vs Latam)
        if (preg_match('/\b(el carro|la computadora)\b/i', $text)) {
            $errors[] = [
                'error_category' => 'regional_variation',
                'original' => preg_match('/\b(el carro)\b/i', $text) ? 'el carro' : 'la computadora',
                'correction' => preg_match('/\b(el carro)\b/i', $text) ? 'el coche' : 'el ordenador',
                'reason' => $isEn
                    ? "In Spain, 'el coche' and 'el ordenador' are standard, while 'el carro/auto' and 'la computadora' are typical in Latin America."
                    : 'في إسبانيا يُفضل استخدام "el coche" للسيارة و "el ordenador" للحاسوب، بينما يشيع "el carro" و "la computadora" في أمريكا اللاتينية.',
                'is_context_dependent' => true,
                'regional_note' => 'Válido en Hispanoamérica, pero en España predomina "el coche" / "el ordenador".',
            ];
        }

        // 6. Unnatural phrasing: literal translation of English "I am 20 years old"
        if (preg_match('/\b(soy (\d+) anos|soy (\d+) años|estoy (\d+) años)\b/i', $text)) {
            $corrected = preg_replace('/\b(soy|estoy) (\d+) (anos|años)\b/i', 'tengo $2 años', $corrected);
            $errors[] = [
                'error_category' => 'unnatural_phrasing',
                'original' => 'soy/estoy años',
                'correction' => 'tengo ... años',
                'reason' => $isEn
                    ? 'In Spanish, age is expressed using TENER (to have), not SER or ESTAR.'
                    : 'في الإسبانية، للتعبير عن العمر نستخدم دائماً فعل Tener (أمتلك سنوات) وليس فعل الكينونة Ser.',
                'is_context_dependent' => false,
                'regional_note' => null,
            ];
        }

        $isCorrect = empty($errors);

        return [
            'original_text' => $text,
            'corrected_text' => $corrected,
            'is_grammatically_correct' => $isCorrect,
            'feedback' => $isCorrect
                ? ($isEn ? 'Flawless writing! Natural phrasing and accurate syntax.' : 'صياغة ممتازة وسليمة 100%! الجملة منسابة وطبيعية ومفهومة لأي متحدث أصيل.')
                : ($isEn ? 'A few notes identified below to make your sentence sound natural and authentic.' : 'تم رصد بعض الملاحظات القواعدية والتعبيرية لتجعل جملتك أكثر دقة وطبيعية.'),
            'errors' => $errors,
            'natural_alternative' => $corrected,
            'provider' => 'local-expert-tutor',
            'is_ai_generated' => false,
        ];
    }

    /**
     * 3. Generate exercises with pedagogical criteria and hints.
     */
    public function generatePractice(
        string $topic,
        string $level = 'A1',
        string $lang = 'ar',
        ?array $learnerContext = null
    ): array {
        $isEn = ($lang === 'en');

        return [
            'topic' => $topic,
            'level' => $level,
            'questions' => [
                [
                    'id' => 1,
                    'prompt' => 'Completa la frase con la forma correcta de "ser" o "estar":',
                    'prompt_translated' => $isEn ? 'Complete the sentence with the correct form of ser or estar:' : 'أكمل الفراغ بالتصريف المناسب لفعل ser أو estar:',
                    'sentence' => 'María ___ muy contenta con su nuevo trabajo.',
                    'options' => ['está', 'es', 'tiene', 'hay'],
                    'correct' => 'está',
                    'hint' => $isEn ? 'Feeling happy (contenta) is an emotional state, not an identity.' : 'السعادة والرضا حالة عاطفية مؤقتة وليست هوية ثابتة.',
                    'explanation' => $isEn ? 'Temporary emotional states require ESTAR: María está contenta.' : 'الحالات المزاجية المؤقتة تأخذ دائماً فعل Estar.',
                ],
                [
                    'id' => 2,
                    'prompt' => 'Elige la preposición adecuada (Por o Para):',
                    'prompt_translated' => $isEn ? 'Choose the appropriate preposition (Por or Para):' : 'اختر حرف الجر المناسب (Por أو Para):',
                    'sentence' => 'Salgo ahora mismo ___ llegar a tiempo al aeropuerto.',
                    'options' => ['para', 'por', 'de', 'con'],
                    'correct' => 'para',
                    'hint' => $isEn ? 'The sentence expresses a future purpose or destination goal.' : 'الجملة تعبر عن هدف مستقبلي وغاية (الوصول في الموعد).',
                    'explanation' => $isEn ? 'Purpose takes PARA: para llegar a tiempo.' : 'الهدف والغاية المستقبلية تأخذ دائماً Para.',
                ],
            ],
            'provider' => 'local-expert-tutor',
            'is_ai_generated' => false,
        ];
    }

    /**
     * Normalize accents and diacritics for speech comparison.
     */
    protected function normalizeAccents(string $str): string
    {
        $unwanted = [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ü'=>'u', 'ñ'=>'n',
            'Á'=>'a', 'É'=>'e', 'Í'=>'i', 'Ó'=>'o', 'Ú'=>'u', 'Ü'=>'u', 'Ñ'=>'n',
        ];
        return strtr($str, $unwanted);
    }

    /**
     * 4. Evaluate speaking and pronunciation similarity.
     */
    public function evaluateSpeaking(string $transcript, string $targetPhrase, string $lang = 'ar'): array
    {
        $cleanT = trim(preg_replace('/[¿?¡!.,]/u', '', mb_strtolower($transcript)));
        $cleanP = trim(preg_replace('/[¿?¡!.,]/u', '', mb_strtolower($targetPhrase)));
        $isEn = ($lang === 'en');

        if (mb_strlen($cleanT) < 2) {
            return [
                'transcript' => $transcript,
                'target_phrase' => $targetPhrase,
                'similarity_score' => 0,
                'passed' => false,
                'feedback' => $isEn
                    ? 'No clear audio captured. Please check microphone permissions and speak clearly.'
                    : 'لم نتمكن من سماع صوت واضح. يرجى التأكد من تفعيل صلاحية الميكروفون والتحدث بصوت مسموع.',
                'words_analysis' => [],
                'provider' => 'speech-evaluator',
            ];
        }

        $targetWords = array_values(array_filter(explode(' ', $cleanP)));
        $heardWords = array_values(array_filter(explode(' ', $cleanT)));

        $wordsAnalysis = [];
        $matchedCount = 0;

        foreach ($targetWords as $word) {
            $isFound = false;
            $foldWord = $this->normalizeAccents($word);

            foreach ($heardWords as $hWord) {
                $foldHWord = $this->normalizeAccents($hWord);
                if ($word === $hWord || $foldWord === $foldHWord || levenshtein($foldWord, $foldHWord) <= 1) {
                    $isFound = true;
                    break;
                }
            }

            if ($isFound) {
                $matchedCount++;
                $wordsAnalysis[] = ['word' => $word, 'status' => 'correct'];
            } else {
                $wordsAnalysis[] = ['word' => $word, 'status' => 'missed'];
            }
        }

        similar_text($this->normalizeAccents($cleanT), $this->normalizeAccents($cleanP), $charSimilarity);
        $wordSimilarity = count($targetWords) > 0 ? ($matchedCount / count($targetWords)) * 100 : 0;
        $score = (int) round(($wordSimilarity * 0.7) + ($charSimilarity * 0.3));
        $passed = $score >= 70 && $matchedCount >= ceil(count($targetWords) * 0.6);

        if ($score >= 85) {
            $feedback = $isEn
                ? 'Outstanding pronunciation! Clear enunciation and natural inflection.'
                : 'نطقك ممتاز! مخارج الحروف واضحة ونبرة الكلام طالعة طبيعية ومريحة جداً للأذن.';
        } elseif ($score >= 70) {
            $feedback = $isEn
                ? 'Very good attempt! Message was clear. Pay attention to missed highlighted words.'
                : 'محاولة كويسة جداً والرسالة واضحة! بص على الكلمات المتعلمة وحاول تنطقها بهدوء.';
        } else {
            $feedback = $isEn
                ? 'A few sounds differed. Listen again with the slow audio button (🐢) and repeat.'
                : 'في بعض الحروف تاهت منك. جرب تسمع النطق بالزر البطيء (🐢) وكرر وراه بهدوء.';
        }

        return [
            'transcript' => $transcript,
            'target_phrase' => $targetPhrase,
            'similarity_score' => $score,
            'passed' => $passed,
            'feedback' => $feedback,
            'words_analysis' => $wordsAnalysis,
            'provider' => 'speech-evaluator',
        ];
    }

    /**
     * 5. Progressive hints without giving away the full answer immediately.
     */
    public function generateHint(
        string $questionOrConcept,
        string $level = 'A1',
        string $lang = 'ar',
        int $hintStage = 1
    ): array {
        $isEn = ($lang === 'en');
        $normalized = mb_strtolower($questionOrConcept);

        if (str_contains($normalized, 'ser') || str_contains($normalized, 'estar')) {
            $hint = ($hintStage === 1)
                ? ($isEn
                    ? "Think about whether this sentence describes a permanent quality or a temporary condition/location."
                    : "فكر فيما إذا كانت الجملة تعبر عن صفة دائمة أو حالة مؤقتة/موقع جغرافي.")
                : ($isEn
                    ? "Notice that emotional and physical conditions always take ESTAR (e.g. cansado, contento)."
                    : "تذكر أن الحالات المزاجية والجسدية تأخذ دائماً فعل Estar (مثل cansado, contento).");
        } elseif (str_contains($normalized, 'por') || str_contains($normalized, 'para')) {
            $hint = ($hintStage === 1)
                ? ($isEn
                    ? "Ask yourself: Is this sentence pointing toward a future goal, or looking back at a cause?"
                    : "اسأل نفسك: هل الجملة تشير إلى هدف مستقبلي غائي، أم سبب ودافع سابق؟")
                : ($isEn
                    ? "Future goals and recipient destinations always use PARA + infinitive."
                    : "الهدف والغاية المستقبلية تأخذ دائماً Para متبوعة بالمصدر.");
        } else {
            $hint = ($hintStage === 1)
                ? ($isEn
                    ? "Observe the grammatical gender and number of the subject to match the verb ending."
                    : "انتبه لجنس وعدد الفاعل لتحديد تصريف الفعل أو أداة التعريف المناسبة.")
                : ($isEn
                    ? "Check regular conjugations: -ar verbs typically end in -a/-an for 3rd person."
                    : "راجع نهايات التصريف المنتظمة مع الضمير المطلوب.");
        }

        return [
            'concept_or_question' => $questionOrConcept,
            'hint' => $hint,
            'hint_stage' => $hintStage,
            'provider' => 'local-expert-tutor',
            'is_ai_generated' => false,
        ];
    }

    /**
     * 6. Explain why an answer is incorrect rather than simply showing the correct answer.
     */
    public function evaluateExerciseResponse(
        string $question,
        string $userAnswer,
        string $correctAnswer,
        string $level = 'A1',
        string $lang = 'ar'
    ): array {
        $isEn = ($lang === 'en');
        $cleanUser = trim(mb_strtolower($userAnswer));
        $cleanCorrect = trim(mb_strtolower($correctAnswer));
        $isCorrect = ($cleanUser === $cleanCorrect);

        if ($isCorrect) {
            return [
                'is_correct' => true,
                'why_incorrect' => null,
                'guidance' => $isEn ? 'Spot-on! Perfect application of the grammatical rule.' : 'إجابة نموذجية وصحيحة تماماً!',
                'provider' => 'local-expert-tutor',
            ];
        }

        // Detailed contextual explanation of why the user's specific answer is wrong
        $why = $isEn
            ? "Your answer '{$userAnswer}' does not match the required syntactic agreement. The correct form is '{$correctAnswer}'."
            : "إجابتك '{$userAnswer}' غير صحيحة سياقياً؛ الإجابة الصحيحة هي '{$correctAnswer}'.";

        if (str_contains($cleanCorrect, 'está') && str_contains($cleanUser, 'es')) {
            $why = $isEn
                ? "You chose 'es' (from Ser), which implies a permanent, unchangeable identity. Because the sentence refers to a state or location, 'está' (from Estar) is required."
                : "اخترت الفعل 'es' التابع لـ Ser، وهو مخصص للصفات الدائمة والهوية. بما أن الجملة تعبر عن حالة أو موقع، فالصواب استخدام 'está' من فعل Estar.";
        } elseif (str_contains($cleanCorrect, 'para') && str_contains($cleanUser, 'por')) {
            $why = $isEn
                ? "You selected 'por' which expresses retrospective cause. The context here requires 'para' to express prospective intention and future purpose."
                : "اخترت 'por' التي تدل على السبب والدافع؛ بينما السياق هنا يطلب 'para' لأنها تعبر عن الهدف والغاية المستقبلية.";
        }

        return [
            'is_correct' => false,
            'why_incorrect' => $why,
            'guidance' => $isEn
                ? "Review the rule and try to connect the trigger word with the correct mood or preposition."
                : "راجع القاعدة وحاول ربط الكلمة المفتاحية بحرف الجر أو الفعل المناسب.",
            'provider' => 'local-expert-tutor',
            'is_ai_generated' => false,
        ];
    }
}
