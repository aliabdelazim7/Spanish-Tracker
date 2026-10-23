<?php

namespace App\Services\Ai;

class MockLanguageTutorProvider implements LanguageTutorProviderInterface
{
    public function explainGrammar(string $concept, string $dialect = 'egyptian_arabic'): array
    {
        $normalized = mb_strtolower(trim($concept));

        if (str_contains($normalized, 'ser') && str_contains($normalized, 'estar')) {
            return [
                'concept' => 'Ser vs Estar',
                'explanation_ar' => 'في الإسبانية، فعل الكينونة بيتقسم لفعلين: Ser و Estar. ببساطة شديدة بالمصري: فعل Ser بيعبر عن "الأصل والصفات الثابتة" (زي جنسيتك: Soy egipcio، أو مهنتك: Soy médico). أما فعل Estar بيعبر عن "المكان أو الحالة المؤقتة" (زي مكانك: Estoy en Madrid، أو مزاجك وصحتك: Estoy cansado تعبان دلوقتي لكن مش صفة أبدية). افتكرها دايماً: Ser للهوية، وEstar للوقت والمكان!',
                'examples' => [
                    ['es' => 'Soy egipcio y soy profesor.', 'ar' => 'أنا مصري وأنا معلم (صفات دائمة وثابتة).'],
                    ['es' => 'Estoy en la cafetería porque estoy cansado.', 'ar' => 'أنا في الكافتيريا لأني تعبان دلوقتي (مكان وحالة مؤقتة).'],
                ],
                'common_mistakes_ar' => 'أكبر غلطة شائعة إنك تقول "Soy en Madrid" بدلاً من "Estoy en Madrid". دايماً المواقع الجغرافية بتاخد Estar.',
                'provider' => 'mock-local',
            ];
        }

        if (str_contains($normalized, 'gustar')) {
            return [
                'concept' => 'Verbo Gustar (الإعجاب)',
                'explanation_ar' => 'فعل Gustar في الإسبانية مش زي "I like" في الإنجليزي! في الإسبانية تركيبة الجملة بتكون: "الشيء ده بيعجبني" مش "أنا بحب الشيء". عشان كده بنقول: Me gusta el café (القهوة بتعجبني)، ولو حاجات جمع بنقول: Me gustan las manzanas (التفاحات بيعجبوني - الفعل جمع عشان التفاح جمع). الضمائر المستخدمة: me, te, le, nos, os, les.',
                'examples' => [
                    ['es' => 'Me gusta la música española.', 'ar' => 'تعجبني الموسيقى الإسبانية.'],
                    ['es' => '¿Te gustan las tapas?', 'ar' => 'هل تعجبك التاباس الإسبانية؟'],
                ],
                'common_mistakes_ar' => 'أوعى تقول "Yo gusto el café". الطريقة الصح دايماً: Me gusta el café.',
                'provider' => 'mock-local',
            ];
        }

        if (str_contains($normalized, 'genero') || str_contains($normalized, 'gender') || str_contains($normalized, 'مذكر') || str_contains($normalized, 'مؤنث')) {
            return [
                'concept' => 'Género en Español (المذكر والمؤنث)',
                'explanation_ar' => 'القاعدة العامة في الإسبانية: الكلمات اللي بتنتهي بـ -o غالباً مذكر (El chico, el libro) وبتاخد أداة التعريف El. والكلمات اللي بتنتهي بـ -a غالباً مؤنث (La chica, la casa) وبتاخد أداة التعريف La. لكن خلي بالك من الشواذ المشهورة: El día (اليوم مذكر رغم آخره a) و La mano (اليد مؤنث رغم آخره o) و El problema (المشكلة مذكر لأن أصلها يوناني).',
                'examples' => [
                    ['es' => 'El libro es muy interesante.', 'ar' => 'الكتاب ممتع جداً (مذكر).'],
                    ['es' => 'La casa es grande y bonita.', 'ar' => 'البيت كبير وجميل (مؤنث).'],
                ],
                'common_mistakes_ar' => 'الخلط في الكلمات اللي أصلها يوناني زي el problema و el tema - بتنتهي بـ a لكنها مذكر وبتاخد el.',
                'provider' => 'mock-local',
            ];
        }

        // Generic fallback educational explanation
        return [
            'concept' => $concept,
            'explanation_ar' => "شرح مبسط لقاعدة '{$concept}': في اللغة الإسبانية، بناء الجملة بسيط ومنظم، وتصريف الأفعال بيعتمد على نهاية الفعل الأساسي (-ar, -er, -ir). التطبيق العملي والممارسة اليومية مع نطق الكلمات بصوت عالي هو مفتاح التمكن.",
            'examples' => [
                ['es' => 'Hablo español todos los días.', 'ar' => 'أتحدث الإسبانية كل يوم.'],
                ['es' => 'Estudio con dedicación.', 'ar' => 'أدرس باجتهاد.'],
            ],
            'common_mistakes_ar' => 'تجنب الترجمة الحرفية من الإنجليزية أو العامية؛ اعتمد على تراكيب الجمل الإسبانية الأصيلة.',
            'provider' => 'mock-local',
        ];
    }

    public function correctText(string $userSpanishText, ?string $context = null): array
    {
        $text = trim($userSpanishText);
        $errors = [];
        $corrected = $text;

        // Basic heuristic corrections for common beginner mistakes
        if (preg_match('/\b(yo soy en|soy en)\b/i', $text)) {
            $corrected = preg_replace('/\b(yo soy en|soy en)\b/i', 'estoy en', $corrected);
            $errors[] = [
                'type' => 'grammar',
                'original' => 'soy en',
                'correction' => 'estoy en',
                'reason_ar' => 'في الإسبانية، للتعبير عن التواجد في مكان نستخدم دائماً فعل Estar وليس Ser.',
            ];
        }

        if (preg_match('/\b(yo gusto)\b/i', $text)) {
            $corrected = preg_replace('/\b(yo gusto)\b/i', 'me gusta', $corrected);
            $errors[] = [
                'type' => 'grammar',
                'original' => 'yo gusto',
                'correction' => 'me gusta',
                'reason_ar' => 'مع فعل الإعجاب Gustar نستخدم ضمير المفعول به (me) وليس ضمير الفاعل (yo).',
            ];
        }

        if (preg_match('/\b(la problema)\b/i', $text)) {
            $corrected = preg_replace('/\b(la problema)\b/i', 'el problema', $corrected);
            $errors[] = [
                'type' => 'gender_agreement',
                'original' => 'la problema',
                'correction' => 'el problema',
                'reason_ar' => 'كلمة problema مذكرة في الإسبانية وتأخذ أداة التعريف El.',
            ];
        }

        $isPerfect = empty($errors);

        return [
            'original_text' => $text,
            'corrected_text' => $corrected,
            'is_grammatically_correct' => $isPerfect,
            'detected_language' => 'es',
            'explanation_ar' => $isPerfect
                ? 'صياغة ممتازة وسليمة 100%! الجملة ماشية طبيعية وسلسة، ومفهومة لأي متحدث إسباني أصيل.'
                : 'في شوية تفاصيل قواعدية بسيطة محتاجة تتظبط عشان الجملة تطلع على لسانك طبيعية ومن غير تكلف.',
            'errors' => $errors,
            'natural_alternative' => $corrected,
            'provider' => 'mock-local',
        ];
    }

    public function generatePractice(string $topic, string $level = 'A1'): array
    {
        return [
            'topic' => $topic,
            'level' => $level,
            'questions' => [
                [
                    'id' => 1,
                    'prompt' => 'Completa la frase con la forma correcta de "ser":',
                    'prompt_ar' => 'أكمل الفراغ بالتصريف المناسب لفعل ser:',
                    'sentence' => 'Nosotros ___ estudiantes de español.',
                    'options' => ['somos', 'estamos', 'son', 'sois'],
                    'correct' => 'somos',
                    'explanation_ar' => 'مع الضمير Nosotros (نحن) تصريف فعل ser هو somos.',
                ],
                [
                    'id' => 2,
                    'prompt' => '¿Cómo se dice "صباح الخير" en español?',
                    'prompt_ar' => 'كيف تقول "صباح الخير" بالإسبانية؟',
                    'sentence' => '___, ¿cómo estás?',
                    'options' => ['Buenos días', 'Buenas noches', 'Adiós', 'Por favor'],
                    'correct' => 'Buenos días',
                    'explanation_ar' => 'Buenos días تعني صباح الخير وتستخدم حتى فترة الظهيرة.',
                ],
            ],
            'provider' => 'mock-local',
        ];
    }

    public function evaluateSpeaking(string $transcript, string $targetPhrase): array
    {
        $cleanT = trim(preg_replace('/[¿?¡!.,]/u', '', mb_strtolower($transcript)));
        $cleanP = trim(preg_replace('/[¿?¡!.,]/u', '', mb_strtolower($targetPhrase)));

        // If no speech captured or empty transcript
        if (mb_strlen($cleanT) < 2) {
            return [
                'transcript' => $transcript,
                'target_phrase' => $targetPhrase,
                'similarity_score' => 0,
                'passed' => false,
                'feedback_ar' => 'لم نتمكن من سماع أي صوت واضح. تأكد من تفعيل صلاحية الميكروفون والتحدث بصوت مسموع أمام الجهاز.',
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
            foreach ($heardWords as $hWord) {
                if ($word === $hWord || levenshtein($word, $hWord) <= 1) {
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

        similar_text($cleanT, $cleanP, $charSimilarity);
        $wordSimilarity = count($targetWords) > 0 ? ($matchedCount / count($targetWords)) * 100 : 0;
        
        // Final score combines word accuracy and character similarity
        $score = (int) round(($wordSimilarity * 0.7) + ($charSimilarity * 0.3));

        $passed = $score >= 70 && $matchedCount >= ceil(count($targetWords) * 0.6);

        if ($score >= 85) {
            $feedback = 'نطقك ممتاز! مخارج الحروف واضحة ونبرة الكلام طالعة طبيعية ومريحة جداً للأذن.';
        } elseif ($score >= 70) {
            $feedback = 'محاولة كويسة جداً والرسالة واضحة! بص على الكلمات اللي متعلم عليها بالبرتقالي وحاول تنطقها بهدوء أكتر.';
        } elseif ($score >= 40) {
            $feedback = 'قريب، بس في كام حرف تاهوا منك وإنت بتتكلم. جرب تسمع النطق بالزر البطيء (🐢) وكرر وراه بهدوء.';
        } else {
            $feedback = 'الكلام اللي اتقال مش مطابق للجملة المطلوبة، أو صوت المايك مكنش واضح. خد نفس، اسمع الجملة مرة تانية، وقولها براحتك.';
        }

        return [
            'transcript' => $transcript,
            'target_phrase' => $targetPhrase,
            'similarity_score' => $score,
            'passed' => $passed,
            'feedback_ar' => $feedback,
            'words_analysis' => $wordsAnalysis,
            'provider' => 'speech-evaluator',
        ];
    }
}
