<?php

namespace App\Services;

class JournalPromptService
{
    /**
     * Comprehensive CEFR-aligned prompt repository (A1 to C2)
     */
    protected array $prompts = [
        'A1' => [
            [
                'id' => 'prompt-a1-1',
                'level' => 'A1',
                'mode' => 'guided',
                'title_es' => 'Mi rutina matutina',
                'title_ar' => 'روتيني الصباحي',
                'title_en' => 'My Morning Routine',
                'description_es' => 'Describe lo que haces normalmente cada mañana desde que te despiertas hasta que empiezas a estudiar o trabajar.',
                'description_ar' => 'صف ما تفعله عادة كل صباح منذ استيقاظك وحتى بدء عملك أو دراستك.',
                'description_en' => 'Describe what you usually do every morning from waking up until starting work or study.',
                'starter_phrases' => [
                    'Normalmente me despierto a las...',
                    'Primero voy al baño y luego...',
                    'Para el desayuno, tomo...',
                    'Salgo de casa a las...',
                ],
                'suggested_vocabulary' => [
                    ['word' => 'despertarse', 'translation_ar' => 'الاستيقاظ', 'translation_en' => 'to wake up'],
                    ['word' => 'desayunar', 'translation_ar' => 'تناول الإفطار', 'translation_en' => 'to have breakfast'],
                    ['word' => 'ducharse', 'translation_ar' => 'الاستحمام', 'translation_en' => 'to shower'],
                    ['word' => 'café con leche', 'translation_ar' => 'قهوة بالحليب', 'translation_en' => 'coffee with milk'],
                    ['word' => 'temprano', 'translation_ar' => 'مبكراً', 'translation_en' => 'early'],
                ],
                'target_grammar' => 'Presente de indicativo (verbos reflexivos y regulares)',
                'target_word_count' => 50,
            ],
            [
                'id' => 'prompt-a1-2',
                'level' => 'A1',
                'mode' => 'guided',
                'title_es' => 'Mi ciudad y mi hogar',
                'title_ar' => 'مدينتي ومنزلي',
                'title_en' => 'My City and My Home',
                'description_es' => 'Escribe una breve descripción de tu ciudad y de tu casa o habitación.',
                'description_ar' => 'اكتب وصفاً موجزاً لمدينتك وغرفتك أو منزلك.',
                'description_en' => 'Write a short description of your city and your house or room.',
                'starter_phrases' => [
                    'Vivo en una ciudad que se llama...',
                    'Mi casa está en...',
                    'En mi habitación hay...',
                    'Lo que más me gusta es...',
                ],
                'suggested_vocabulary' => [
                    ['word' => 'vivir', 'translation_ar' => 'يعيش', 'translation_en' => 'to live'],
                    ['word' => 'habitacion', 'translation_ar' => 'غرفة', 'translation_en' => 'room'],
                    ['word' => 'tranquilo', 'translation_ar' => 'هادئ', 'translation_en' => 'quiet'],
                    ['word' => 'cerca de', 'translation_ar' => 'بالقرب من', 'translation_en' => 'close to'],
                ],
                'target_grammar' => 'Ser vs Estar y Hay (descripción de lugares)',
                'target_word_count' => 50,
            ],
            [
                'id' => 'prompt-a1-free',
                'level' => 'A1',
                'mode' => 'free',
                'title_es' => 'Escritura libre A1',
                'title_ar' => 'كتابة حرة للمبتدئين',
                'title_en' => 'A1 Free Writing',
                'description_es' => 'Escribe sobre cualquier tema que quieras usando frases simples en presente.',
                'description_ar' => 'اكتب عن أي موضوع تريده باستخدام جمل بسيطة في زمن المضارع.',
                'description_en' => 'Write about any topic you choose using simple present tense sentences.',
                'starter_phrases' => ['Hoy quiero hablar de...', 'Me gusta...'],
                'suggested_vocabulary' => [],
                'target_grammar' => 'Frases simples afirmativas y negativas',
                'target_word_count' => 40,
            ],
        ],

        'A2' => [
            [
                'id' => 'prompt-a2-1',
                'level' => 'A2',
                'mode' => 'guided',
                'title_es' => '¿Qué hiciste el fin de semana pasado?',
                'title_ar' => 'ماذا فعلت في عطلة نهاية الأسبوع الماضية؟',
                'title_en' => 'What did you do last weekend?',
                'description_es' => 'Cuenta tus actividades del sábado y domingo pasados en pasado indefinido.',
                'description_ar' => 'احكِ عن أنشطتك يومي السبت والأحد الماضيين باستخدام الماضي البسيط (Indefinido).',
                'description_en' => 'Narrate your activities last Saturday and Sunday using the preterite tense.',
                'starter_phrases' => [
                    'El sábado pasado fui a...',
                    'Por la tarde me encontré con...',
                    'Comí en un restaurante delicioso donde...',
                    'El domingo descansé y...',
                ],
                'suggested_vocabulary' => [
                    ['word' => 'el sábado pasado', 'translation_ar' => 'السبت الماضي', 'translation_en' => 'last Saturday'],
                    ['word' => 'visitar', 'translation_ar' => 'يزور', 'translation_en' => 'to visit'],
                    ['word' => 'divertido', 'translation_ar' => 'ممتع', 'translation_en' => 'fun'],
                    ['word' => 'descansar', 'translation_ar' => 'يستريح', 'translation_en' => 'to rest'],
                ],
                'target_grammar' => 'Pretérito indefinido (fui, comí, salí, descansé)',
                'target_word_count' => 80,
            ],
            [
                'id' => 'prompt-a2-2',
                'level' => 'A2',
                'mode' => 'guided',
                'title_es' => 'Mis planes para las próximas vacaciones',
                'title_ar' => 'خططي للعطلة القادمة',
                'title_en' => 'My Plans for the Next Vacation',
                'description_es' => 'Explica a dónde te gustaría viajar o qué proyectos tienes para el próximo verano.',
                'description_ar' => 'وضح إلى أين تود السفر أو ما هي خططك ومشاريعك للصيف القادم.',
                'description_en' => 'Explain where you would like to travel or what projects you have for next summer.',
                'starter_phrases' => [
                    'En mis próximas vacaciones voy a...',
                    'Tengo ganas de conocer...',
                    'Viajaré con...',
                ],
                'suggested_vocabulary' => [
                    ['word' => 'viajar', 'translation_ar' => 'يسافر', 'translation_en' => 'to travel'],
                    ['word' => 'hacer la maleta', 'translation_ar' => 'يحزم الحقائب', 'translation_en' => 'to pack luggage'],
                    ['word' => 'playa', 'translation_ar' => 'شاطئ', 'translation_en' => 'beach'],
                ],
                'target_grammar' => 'Futuro próximo (Ir a + infinitivo)',
                'target_word_count' => 80,
            ],
            [
                'id' => 'prompt-a2-free',
                'level' => 'A2',
                'mode' => 'free',
                'title_es' => 'Escritura libre A2',
                'title_ar' => 'كتابة حرة A2',
                'title_en' => 'A2 Free Writing',
                'description_es' => 'Escribe una entrada de diario combinando pasado y presente.',
                'description_ar' => 'اكتب تدوينة يومية تجمع بين أحداث الماضي ووقائع الحاضر.',
                'description_en' => 'Write a journal entry combining past events with present descriptions.',
                'starter_phrases' => ['Ayer fue un día especial porque...'],
                'suggested_vocabulary' => [],
                'target_grammar' => 'Contraste presente y pasado simple',
                'target_word_count' => 70,
            ],
        ],

        'B1' => [
            [
                'id' => 'prompt-b1-1',
                'level' => 'B1',
                'mode' => 'guided',
                'title_es' => 'Recuerdos de mi infancia: Antes vs Ahora',
                'title_ar' => 'ذكريات طفولتي: بين الماضي والحاضر',
                'title_en' => 'Childhood Memories: Then vs Now',
                'description_es' => 'Compara cómo era tu vida en la niñez con tu vida actual usando el imperfecto y el indefinido.',
                'description_ar' => 'قارن بين حياتك في مرحلة الطفولة وحياتك الآن باستخدام الماضي المستمر والماضي البسيط.',
                'description_en' => 'Compare your childhood life with your current life using the imperfect and preterite.',
                'starter_phrases' => [
                    'Cuando era niño/a, siempre solía...',
                    'Recuerdo un día en que...',
                    'Las cosas han cambiado mucho porque ahora...',
                ],
                'suggested_vocabulary' => [
                    ['word' => 'soler', 'translation_ar' => 'يعتاد على', 'translation_en' => 'to usually do'],
                    ['word' => 'nostalgia', 'translation_ar' => 'حنين إلى الماضي', 'translation_en' => 'nostalgia'],
                    ['word' => 'jugar al aire libre', 'translation_ar' => 'اللعب في الهواء الطلق', 'translation_en' => 'to play outdoors'],
                ],
                'target_grammar' => 'Contraste Imperfecto vs Indefinido (era / fue / jugaba / jugué)',
                'target_word_count' => 120,
            ],
            [
                'id' => 'prompt-b1-2',
                'level' => 'B1',
                'mode' => 'guided',
                'title_es' => 'Mis deseos y esperanzas para el futuro',
                'title_ar' => 'أمنياتي وتطلعاتي للمستقبل',
                'title_en' => 'My Wishes and Hopes for the Future',
                'description_es' => 'Expresa tus deseos personales y profesionales utilizando el presente de subjuntivo.',
                'description_ar' => 'عبر عن رغباتك وأهدافك الشخصية والمهنية باستخدام صيغة الشك والتمني (Subjuntivo).',
                'description_en' => 'Express your personal and professional wishes using the present subjunctive.',
                'starter_phrases' => [
                    'Espero que el próximo año...',
                    'Quiero que mi familia esté...',
                    'Ojalá pueda aprender...',
                ],
                'suggested_vocabulary' => [
                    ['word' => 'ojalá', 'translation_ar' => 'يا ليت / أتمنى', 'translation_en' => 'hopefully'],
                    ['word' => 'lograr metas', 'translation_ar' => 'تحقيق الأهداف', 'translation_en' => 'to achieve goals'],
                    ['word' => 'éxito profesional', 'translation_ar' => 'نجاح مهني', 'translation_en' => 'career success'],
                ],
                'target_grammar' => 'Presente de subjuntivo tras verbos de deseo (esperar que, querer que)',
                'target_word_count' => 120,
            ],
        ],

        'B2' => [
            [
                'id' => 'prompt-b2-1',
                'level' => 'B2',
                'mode' => 'guided',
                'title_es' => 'El impacto del teletrabajo y la inteligencia artificial',
                'title_ar' => 'أثر العمل عن بعد والذكاء الاصطناعي',
                'title_en' => 'The Impact of Remote Work and AI',
                'description_es' => 'Analiza las ventajas y desventajas de las nuevas tecnologías laborales estructurando tu opinión.',
                'description_ar' => 'حلل مزايا وعيوب التكنولوجيا الحديثة ونماذج العمل الرقمية مدعماً رأيك بحجج منطقية.',
                'description_en' => 'Analyze the pros and cons of new workplace technologies with structured arguments.',
                'starter_phrases' => [
                    'Por un lado, es indiscutible que...',
                    'Sin embargo, desde mi punto de vista...',
                    'A menos que tomemos medidas preventivas...',
                ],
                'suggested_vocabulary' => [
                    ['word' => 'ventajas e inconvenientes', 'translation_ar' => 'إيجابيات وسلبيات', 'translation_en' => 'pros and cons'],
                    ['word' => 'productividad', 'translation_ar' => 'إنتاجية', 'translation_en' => 'productivity'],
                    ['word' => 'desconexión digital', 'translation_ar' => 'الانفصال الرقمي عن العمل', 'translation_en' => 'digital disconnection'],
                ],
                'target_grammar' => 'Conectores discursivos de contraste y concesión (a pesar de que, sin embargo)',
                'target_word_count' => 160,
            ],
            [
                'id' => 'prompt-b2-2',
                'level' => 'B2',
                'mode' => 'guided',
                'title_es' => 'Si pudiera cambiar una decisión del pasado...',
                'title_ar' => 'لو كنت أستطيع تغيير قرار من الماضي...',
                'title_en' => 'If I Could Change a Past Decision...',
                'description_es' => 'Reflexiona sobre un momento crucial de tu vida y qué habría ocurrido en una situación hipotética.',
                'description_ar' => 'تأمل في قرار حاسم من حياتك وما كان ليحدث لو أنك اتخذت مساراً بديلاً.',
                'description_en' => 'Reflect on a pivotal moment in your life and what hypothetical outcomes would have followed.',
                'starter_phrases' => [
                    'Si hubiera sabido lo que sé hoy...',
                    'Habría tomado otro camino si...',
                    'No me arrepiento de nada, pero...',
                ],
                'suggested_vocabulary' => [
                    ['word' => 'arrepentirse', 'translation_ar' => 'يندم', 'translation_en' => 'to regret'],
                    ['word' => 'tomar un rumbo distinto', 'translation_ar' => 'سلوك مسار مختلف', 'translation_en' => 'to take a different course'],
                ],
                'target_grammar' => 'Condicional compuesto y pluscuamperfecto de subjuntivo (Si hubiera... habría...)',
                'target_word_count' => 160,
            ],
        ],

        'C1' => [
            [
                'id' => 'prompt-c1-1',
                'level' => 'C1',
                'mode' => 'guided',
                'title_es' => 'La gentrificación y la identidad urbana',
                'title_ar' => 'التحسين الحضري وتآكل الهوية المحلية',
                'title_en' => 'Gentrification and Urban Identity',
                'description_es' => 'Redacta un ensayo argumentativo sobre la transformación de los barrios históricos de las grandes ciudades.',
                'description_ar' => 'حرر مقالاً نقدياً يناقش تحولات الأحياء التاريخية وتأثير السياحة وتدفق رؤوس الأموال على النسيج الاجتماعي.',
                'description_en' => 'Compose an argumentative essay on the transformation of historical neighborhoods in major cities.',
                'starter_phrases' => [
                    'Resulta imperativo dilucidar hasta qué punto...',
                    'Es un fenómeno de doble filo que suscita...',
                    'En última instancia, el desafío estriba en...',
                ],
                'suggested_vocabulary' => [
                    ['word' => 'gentrificación', 'translation_ar' => 'التحسين الحضري/الاستطباق', 'translation_en' => 'gentrification'],
                    ['word' => 'tejido social', 'translation_ar' => 'النسيج الاجتماعي', 'translation_en' => 'social fabric'],
                    ['word' => 'dilucidar', 'translation_ar' => 'إيضاح وتبيان', 'translation_en' => 'to elucidate'],
                ],
                'target_grammar' => 'Subordinadas concesivas y causales avanzadas con modo subjuntivo',
                'target_word_count' => 220,
            ],
        ],

        'C2' => [
            [
                'id' => 'prompt-c2-1',
                'level' => 'C2',
                'mode' => 'guided',
                'title_es' => 'El lenguaje como artefacto del pensamiento y la cultura',
                'title_ar' => 'اللغة كمرآة للفكر وبناء للهوية الثقافية',
                'title_en' => 'Language as an Artifact of Thought and Culture',
                'description_es' => 'Ensayo ensayístico de registro culto sobre la relación simbiótica entre léxico, cognición y sociedad.',
                'description_ar' => 'مقال أدبي رصين يتناول العلاقة التبادلية بين تطور المفردات اللغوية والوعي الإنساني المعاصر.',
                'description_en' => 'Scholarly essay exploring the symbiotic relationship between lexicon, cognition, and society.',
                'starter_phrases' => [
                    'Huelga decir que el léxico de una lengua no sólo designa...',
                    'Bajo esta perspectiva caleidoscópica, se colige que...',
                ],
                'suggested_vocabulary' => [
                    ['word' => 'huelga decir', 'translation_ar' => 'غني عن البيان', 'translation_en' => 'it goes without saying'],
                    ['word' => 'colige', 'translation_ar' => 'يُستنتج / يُستخلص', 'translation_en' => 'it is deduced'],
                    ['word' => 'caleidoscópica', 'translation_ar' => 'متعددة الأبعاد والزوايا', 'translation_en' => 'kaleidoscopic'],
                ],
                'target_grammar' => 'Perífrasis cultas, inversión sintáctica y registros retóricos formales',
                'target_word_count' => 280,
            ],
        ],
    ];

    /**
     * Get prompts for a specific CEFR level and mode.
     */
    public function getPromptsForLevel(string $level = 'A1', ?string $mode = null): array
    {
        $level = strtoupper($level);
        $levelPrompts = $this->prompts[$level] ?? $this->prompts['A1'];

        if ($mode) {
            $levelPrompts = array_values(array_filter($levelPrompts, fn($p) => $p['mode'] === $mode));
        }

        return $levelPrompts;
    }

    /**
     * Get a daily prompt for the given level based on the day of the month/year.
     */
    public function getDailyPrompt(string $level = 'A1', ?string $date = null): array
    {
        $level = strtoupper($level);
        $prompts = $this->prompts[$level] ?? $this->prompts['A1'];

        $dayIndex = $date ? (int) date('z', strtotime($date)) : (int) date('z');
        $selected = $prompts[$dayIndex % count($prompts)];

        return array_merge($selected, [
            'date' => $date ?? now()->toDateString(),
            'is_daily_challenge' => true,
        ]);
    }
}
