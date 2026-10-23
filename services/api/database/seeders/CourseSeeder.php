<?php

namespace Database\Seeders;

use App\Models\CefrLevel;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonExercise;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::updateOrCreate(
            ['slug' => 'spanish-for-arabic-speakers'],
            [
                'title' => 'Español para Hispanohablantes Árabes',
                'title_ar' => 'منهج الإسبانية الشامل للناطقين بالعربية',
                'description_ar' => 'مسار تعليمي متكامل من الصفر وحتى الطلاقة، مبني على الإطار الأوروبي المشترك (CEFR)، بشروحات واضحة ونطق أصيل لكل كلمة.',
                'target_language' => 'es-ES',
                'source_language' => 'ar-EG',
                'is_published' => true,
            ]
        );

        // 6 CEFR Levels
        $levelsData = [
            ['code' => 'A1', 'name' => 'Principiante', 'name_ar' => 'مبتدئ أول', 'order' => 1, 'desc' => 'الأساسيات والتحيات وبناء الجمل اليومية البسيطة'],
            ['code' => 'A2', 'name' => 'Elemental', 'name_ar' => 'ابتدائي متقدم', 'order' => 2, 'desc' => 'المواقف اليومية، التسوق، السفر، وأساسيات زمن الماضي'],
            ['code' => 'B1', 'name' => 'Intermedio', 'name_ar' => 'متوسط', 'order' => 3, 'desc' => 'سرد القصص، التعبير عن الآراء، والأمنيات مع صيغة الشك'],
            ['code' => 'B2', 'name' => 'Intermedio Alto', 'name_ar' => 'فوق المتوسط', 'order' => 4, 'desc' => 'الطلاقة المهنية، المناظرات، واستيعاب الموضوعات المعقدة'],
            ['code' => 'C1', 'name' => 'Avanzado', 'name_ar' => 'متقدم', 'order' => 5, 'desc' => 'التعبيرات الاصطلاحية والمرونة التامة في مختلف السياقات'],
            ['code' => 'C2', 'name' => 'Maestría', 'name_ar' => 'إتقان تام', 'order' => 6, 'desc' => 'الفهم التلقائي الدقيق والفروق البلاغية واللغوية الدقيقة'],
        ];

        $levelMap = [];
        foreach ($levelsData as $ld) {
            $levelMap[$ld['code']] = CefrLevel::updateOrCreate(
                ['course_id' => $course->id, 'code' => $ld['code']],
                [
                    'name' => $ld['name'],
                    'name_ar' => $ld['name_ar'],
                    'description_ar' => $ld['desc'],
                    'order_index' => $ld['order'],
                    'is_published' => true,
                ]
            );
        }

        // =========================================================================
        // LEVEL A1: PRINCIPIANTE
        // =========================================================================

        // Unit A1_1: Saludos y Presentaciones
        $unitA1_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A1']->id, 'title' => 'Saludos y Presentaciones'],
            [
                'title_ar' => 'التحيات والتعارف واللباقة اليومية',
                'title_en' => 'Greetings and Introductions',
                'description_ar' => 'تعلم كيف تحيي الناس، تعرف عن نفسك وتسأل عن أحوالهم بأدب.',
                'description_en' => 'Learn how to greet people, introduce yourself, and ask how they are.',
                'icon' => 'sparkles',
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        $l_a1_1 = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_1->id, 'title' => '¡Hola! ¿Cómo te llamas?'],
            [
                'title_ar' => 'مرحباً! ما اسمك؟',
                'objective_ar' => 'التعرف على التحيات الأساسية وتقديم النفس باستخدام فعل llamarse.',
                'objective_en' => 'Learn basic greetings and introduce yourself using the verb llamarse.',
                'grammar_notes_ar' => 'في الإسبانية، فعل llamarse فعل انعكاسي: Me llamo (اسمي)، Te llamas (اسمك). علامة الاستفهام المقلوبة (¿) تسبق السؤال دائمًا.',
                'grammar_notes_en' => 'In Spanish, llamarse is reflexive: Me llamo (My name is), Te llamas (Your name is). The inverted question mark (¿) opens questions.',
                'skill_category' => 'vocabulary',
                'estimated_minutes' => 7,
                'xp_reward' => 20,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_1->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Cómo se dice "صباح الخير" en español?',
                'prompt_ar' => 'ما معنى "صباح الخير" بالإسبانية؟',
                'prompt_en' => 'How do you say "Good morning" in Spanish?',
                'options' => ['Buenos días', 'Buenas noches', 'Hola', 'Adiós'],
                'correct_answers' => ['Buenos días'],
                'explanation_ar' => 'Buenos días تُستخدم للتحية صباحاً حتى موعد الغداء.',
                'explanation_en' => 'Buenos días is used from morning until lunchtime.',
                'hint_ar' => 'كلمة días تعني أيام، وbuenos تعني جيدة.',
                'xp_value' => 5,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_1->id, 'order_index' => 2],
            [
                'exercise_type' => 'translation',
                'prompt' => 'Traduce: "مرحباً، أنا اسمي سارة"',
                'prompt_ar' => 'ترجم إلى الإسبانية: مرحباً، أنا اسمي سارة',
                'prompt_en' => 'Translate to Spanish: Hello, my name is Sara',
                'options' => null,
                'correct_answers' => ['Hola, me llamo Sara', 'Hola me llamo Sara', 'Hola, soy Sara', 'Hola soy Sara'],
                'explanation_ar' => 'نستخدم "Hola" للتحية و"me llamo" لقول اسمي.',
                'explanation_en' => 'Use "Hola" for hello and "me llamo" for my name is.',
                'hint_ar' => 'Hola, me llamo...',
                'xp_value' => 5,
            ]
        );

        $l_a1_2 = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_1->id, 'title' => '¿De dónde eres?'],
            [
                'title_ar' => 'من أين أنت؟ (الجنسيات وفعل الكينونة Ser)',
                'objective_ar' => 'التعبير عن الجنسية والبلد باستخدام تصريف فعل Ser.',
                'objective_en' => 'Express nationality and origin using the verb Ser.',
                'grammar_notes_ar' => 'فعل Ser يدل على الأصل الدائم: Yo soy egipcio (أنا مصري)، Tú eres de España (أنت من إسبانيا).',
                'grammar_notes_en' => 'Ser is used for permanent origin: Yo soy egipcio, Tú eres de España.',
                'skill_category' => 'grammar',
                'estimated_minutes' => 8,
                'xp_reward' => 20,
                'order_index' => 2,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_2->id, 'order_index' => 1],
            [
                'exercise_type' => 'fill_blank',
                'prompt' => 'Yo ___ de Egipto. (صرف فعل Ser)',
                'prompt_ar' => 'أنا من مصر: اختر تصريف فعل Ser مع الضمير Yo',
                'prompt_en' => 'I am from Egypt: choose conjugation of Ser for Yo',
                'options' => ['soy', 'eres', 'es', 'somos'],
                'correct_answers' => ['soy'],
                'explanation_ar' => 'مع الضمير Yo (أنا) التصريف الصحيح لفعل Ser هو soy.',
                'explanation_en' => 'With Yo, the correct form of Ser is soy.',
                'hint_ar' => 'Yo soy...',
                'xp_value' => 5,
            ]
        );

        // Lesson: SER vs ESTAR
        $l_a1_verbs = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_1->id, 'title' => 'Ser vs Estar: Diferencias Clave'],
            [
                'title_ar' => 'الفرق الجوهري بين Ser و Estar',
                'objective_ar' => 'التمييز بين الصفات الدائمة (Ser) والحالات المؤقتة والمواقع (Estar).',
                'objective_en' => 'Master the distinction between permanent traits (Ser) and temporary states/locations (Estar).',
                'grammar_notes_ar' => 'قاعدة ذهبية: Ser للهوية والمهنة والأصل الدائم (Soy médico, Soy de Egipto). Estar للحالة النفسية والجسدية والموقع الجغرافي (Estoy cansado, Madrid está en España).',
                'grammar_notes_en' => 'Ser is used for identity, professions, and origin. Estar is used for temporary conditions and locations.',
                'skill_category' => 'verbs',
                'estimated_minutes' => 9,
                'xp_reward' => 25,
                'order_index' => 3,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_verbs->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Cuál es la frase correcta para expresar que estás cansado hoy?',
                'prompt_ar' => 'كيف تعبر عن أنك تشعر بالتعب والإرهاق اليوم (حالة مؤقتة)؟',
                'prompt_en' => 'Which sentence correctly expresses feeling tired today (temporary state)?',
                'options' => ['Hoy estoy cansado', 'Hoy soy cansado', 'Hoy tengo cansado', 'Hoy hay cansado'],
                'correct_answers' => ['Hoy estoy cansado'],
                'explanation_ar' => 'التعب حالة جسدية مؤقتة، لذلك نستخدم دائماً فعل Estar: Estoy cansado.',
                'explanation_en' => 'Tiredness is a temporary state, so Estar must be used: Estoy cansado.',
                'hint_ar' => 'Estar للحالات المتغيرة.',
                'xp_value' => 5,
            ]
        );

        // Unit A1_2: En la Cafetería y Restaurante
        $unitA1_2 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A1']->id, 'title' => 'En la Cafetería y Restaurante'],
            [
                'title_ar' => 'في المقهى والمطعم',
                'title_en' => 'At the Cafe and Restaurant',
                'description_ar' => 'تعلم كيف تطلب المشروبات والمأكولات الإسبانية وتسأل عن الحساب.',
                'description_en' => 'Learn how to order food and drinks and ask for the bill.',
                'icon' => 'coffee',
                'order_index' => 2,
                'is_published' => true,
            ]
        );

        $l_a1_4 = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_2->id, 'title' => 'Pedir un café por favor'],
            [
                'title_ar' => 'طلب قهوة ومشروبات من فضلك',
                'objective_ar' => 'استخدام صيغ الطلب المهذب: Quiero / Para mí / Por favor.',
                'objective_en' => 'Use polite ordering phrases: Quiero / Para mí / Por favor.',
                'grammar_notes_ar' => 'لطلب شيء بأدب: "Un café con leche, por favor" أو "Para mí, un té".',
                'grammar_notes_en' => 'Polite requests: "Un café con leche, por favor" or "Para mí, un té".',
                'skill_category' => 'practical_expressions',
                'estimated_minutes' => 6,
                'xp_reward' => 20,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_4->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Cómo se pide "قهوة بحليب من فضلك"؟',
                'prompt_ar' => 'كيف تطلب "قهوة بحليب من فضلك"؟',
                'prompt_en' => 'How do you order "Coffee with milk please"?',
                'options' => ['Un café con leche, por favor', 'Un té con limón, gracias', 'Agua sin gas', 'La cuenta, por favor'],
                'correct_answers' => ['Un café con leche, por favor'],
                'explanation_ar' => 'Café con leche هو المشروب الأكثر شهرة في إسبانيا.',
                'explanation_en' => 'Café con leche is the standard Spanish coffee with milk.',
                'hint_ar' => 'leche = حليب.',
                'xp_value' => 5,
            ]
        );

        // Lesson: Listening & Dialogue
        $l_a1_listen = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_2->id, 'title' => 'Escucha: Conversación en el Bar'],
            [
                'title_ar' => 'استماع وفهم محادثة: في البار الإسباني',
                'objective_ar' => 'الاستماع إلى حوار واقعي في مقهى وفهم الأسعار والطلبات.',
                'objective_en' => 'Listen to a natural cafe dialogue and understand orders and pricing.',
                'grammar_notes_ar' => 'في إسبانيا، "la cuenta" تعني الفاتورة. و"¿Cuánto es?" تعني كم الحساب؟',
                'grammar_notes_en' => 'In Spain, "la cuenta" is the bill, and "¿Cuánto es?" means how much is it?',
                'skill_category' => 'listening',
                'estimated_minutes' => 8,
                'xp_reward' => 25,
                'order_index' => 2,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_listen->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => 'Escucha: "Camarero, ¿cuánto es el café y el cruasán?" - "Son tres euros cincuenta."',
                'prompt_ar' => 'كم ثمن القهوة والكرواسون في المحادثة؟',
                'prompt_en' => 'How much was the coffee and croissant in the dialogue?',
                'options' => ['3,50 € (tres euros cincuenta)', '2,50 € (dos euros cincuenta)', '5,00 € (cinco euros)', '1,50 € (un euro cincuenta)'],
                'correct_answers' => ['3,50 € (tres euros cincuenta)'],
                'explanation_ar' => 'tres euros cincuenta = ثلاثة يورو وخمسون سنتاً.',
                'explanation_en' => 'tres euros cincuenta means 3.50 euros.',
                'hint_ar' => 'tres = 3.',
                'xp_value' => 5,
            ]
        );

        // Unit A1_3: La Familia y Descripciones
        $unitA1_3 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A1']->id, 'title' => 'La Familia y Descripciones'],
            [
                'title_ar' => 'العائلة والصفات والمظهر',
                'title_en' => 'Family and Physical Descriptions',
                'description_ar' => 'التحدث عن أفراد الأسرة، والوصف الجسدي والشخصي.',
                'description_en' => 'Talk about family members and describe physical appearance.',
                'icon' => 'users',
                'order_index' => 3,
                'is_published' => true,
            ]
        );

        $l_a1_5 = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_3->id, 'title' => 'Mi Familia'],
            [
                'title_ar' => 'أفراد الأسرة وصفات الملكية',
                'objective_ar' => 'استخدام صفات الملكية mi / mis والتحدث عن الأب، الأم، والإخوة.',
                'objective_en' => 'Use possessive adjectives mi/mis and talk about family members.',
                'grammar_notes_ar' => 'صفات الملكية: Mi padre (والدي - مفرد)، Mis hermanos (إخوتي - جمع). لاحظ توافق الجمع.',
                'grammar_notes_en' => 'Possessives: Mi (singular), Mis (plural). Must agree in number.',
                'skill_category' => 'vocabulary',
                'estimated_minutes' => 8,
                'xp_reward' => 20,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_5->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Qué significa "hermana" en árabe?',
                'prompt_ar' => 'ما معنى كلمة "hermana" بالعربية؟',
                'prompt_en' => 'What does "hermana" mean in Arabic/English?',
                'options' => ['أخت (Sister)', 'أخ (Brother)', 'أم (Mother)', 'ابنة (Daughter)'],
                'correct_answers' => ['أخت (Sister)'],
                'explanation_ar' => 'Hermana = أخت، و Hermano = أخ.',
                'explanation_en' => 'Hermana means sister; hermano means brother.',
                'hint_ar' => 'مفرد مؤنث.',
                'xp_value' => 5,
            ]
        );

        // Lesson: Reading Passage
        $l_a1_read = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_3->id, 'title' => 'Lectura: La familia de Carlos en Madrid'],
            [
                'title_ar' => 'قراءة وفهم نص: عائلة كارلوس في مدريد',
                'objective_ar' => 'قراءة نص قصير واستخراج المعلومات الأساسية عن العائلة والمهن.',
                'objective_en' => 'Read a short passage and extract key details about family and jobs.',
                'grammar_notes_ar' => 'الأسماء والصفات تتوافق في الجنس والعدد: Mi madre es médica, mis hermanos son altos.',
                'grammar_notes_en' => 'Nouns and adjectives must agree in gender and number.',
                'skill_category' => 'reading',
                'estimated_minutes' => 9,
                'xp_reward' => 25,
                'order_index' => 2,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_read->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => 'Lee: "Carlos tiene una hermana menor llamada Lucía. Lucía estudia en la universidad y vive con sus padres." ¿Quién es Lucía?',
                'prompt_ar' => 'وفقاً للنص، من هي لوثيا؟',
                'prompt_en' => 'According to the text, who is Lucía?',
                'options' => ['La hermana de Carlos (أخت كارلوس)', 'La madre de Carlos (أم كارلوس)', 'La profesora (المعلمة)', 'La médica (الطبيبة)'],
                'correct_answers' => ['La hermana de Carlos (أخت كارلوس)'],
                'explanation_ar' => 'النص ذكر صراحة: "una hermana menor llamada Lucía".',
                'explanation_en' => 'The text directly states: "una hermana menor llamada Lucía".',
                'hint_ar' => 'hermana = أخت.',
                'xp_value' => 5,
            ]
        );

        // Unit A1_4: La Rutina Diaria y la Hora
        $unitA1_4 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A1']->id, 'title' => 'La Rutina Diaria y la Hora'],
            [
                'title_ar' => 'الروتين اليومي والساعة',
                'title_en' => 'Daily Routine and Telling Time',
                'description_ar' => 'كيف تسأل عن الوقت وتتحدث عن أنشطتك من الصباح للمساء.',
                'description_en' => 'Learn how to tell time and describe your daily activities.',
                'icon' => 'clock',
                'order_index' => 4,
                'is_published' => true,
            ]
        );

        $l_a1_6 = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_4->id, 'title' => '¿Qué hora es?'],
            [
                'title_ar' => 'كم الساعة؟ وتحديد المواعيد',
                'objective_ar' => 'قراءة الساعة بالإسبانية والتعبير عن التوقيت.',
                'objective_en' => 'Tell time in Spanish and set appointments.',
                'grammar_notes_ar' => 'للساعة 1 نستخدم: Es la una. ولباقي الساعات جمع: Son las dos, Son las tres... إلخ.',
                'grammar_notes_en' => 'For 1:00 use "Es la una". For all other hours use plural "Son las...".',
                'skill_category' => 'grammar',
                'estimated_minutes' => 7,
                'xp_reward' => 20,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_6->id, 'order_index' => 1],
            [
                'exercise_type' => 'fill_blank',
                'prompt' => '___ las tres de la tarde. (كم الساعة)',
                'prompt_ar' => 'إنها الثالثة عصراً: اختر الصيغة الصحيحة لفعل Ser',
                'prompt_en' => 'It is three in the afternoon: choose the correct form of Ser',
                'options' => ['Son', 'Es', 'Está', 'Hay'],
                'correct_answers' => ['Son'],
                'explanation_ar' => 'مع جميع الساعات ما عدا الواحدة نستخدم الجمع: Son las...',
                'explanation_en' => 'Hours other than 1:00 require plural: Son las...',
                'hint_ar' => 'Son las...',
                'xp_value' => 5,
            ]
        );

        // =========================================================================
        // LEVEL A2: ELEMENTAL
        // =========================================================================

        // Unit A2_1: En la Ciudad y Direcciones
        $unitA2_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A2']->id, 'title' => 'En la Ciudad y Direcciones'],
            [
                'title_ar' => 'في المدينة وسؤال المارة عن الاتجاهات',
                'title_en' => 'In the City and Asking for Directions',
                'description_ar' => 'تعلم كيف تسأل عن الأماكن وتفهم الإرشادات (يمين، يسار، إلى الأمام).',
                'description_en' => 'Learn how to ask for places and understand directions.',
                'icon' => 'map-pin',
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        $l_a2_1 = Lesson::updateOrCreate(
            ['unit_id' => $unitA2_1->id, 'title' => '¿Por dónde se va a la plaza?'],
            [
                'title_ar' => 'كيف أصل إلى الميدان؟ (الاتجاهات)',
                'objective_ar' => 'سؤال المارة: Todo recto (على طول), a la derecha (يمين), a la izquierda (شمال).',
                'objective_en' => 'Ask for directions: straight ahead, turn right, turn left.',
                'grammar_notes_ar' => 'فعل girar (ينعطف) و seguir (يواصل): Sigue todo recto y gira a la derecha.',
                'grammar_notes_en' => 'Direction verbs: girar (turn) and seguir (continue straight).',
                'skill_category' => 'practical_expressions',
                'estimated_minutes' => 9,
                'xp_reward' => 25,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a2_1->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Qué significa "gira a la derecha"?',
                'prompt_ar' => 'ما معنى "gira a la derecha"؟',
                'prompt_en' => 'What does "gira a la derecha" mean?',
                'options' => ['انعطف يميناً (Turn right)', 'انعطف يساراً (Turn left)', 'امشِ للأمام مباشرة', 'توقف هنا'],
                'correct_answers' => ['انعطف يميناً (Turn right)'],
                'explanation_ar' => 'derecha تعني اليمين، و izquierda تعني اليسار.',
                'explanation_en' => 'derecha = right, izquierda = left.',
                'hint_ar' => 'derecha = يمين.',
                'xp_value' => 5,
            ]
        );

        // Unit A2_2: De Compras y Ropa
        $unitA2_2 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A2']->id, 'title' => 'De Compras y Ropa'],
            [
                'title_ar' => 'التسوق والمقاسات والأسعار',
                'title_en' => 'Shopping and Clothing',
                'description_ar' => 'شراء الملابس، تجربة المقاس، والسؤال عن الألوان والتخفيضات.',
                'description_en' => 'Buy clothes, try sizes, and ask about colors and discounts.',
                'icon' => 'shopping-bag',
                'order_index' => 2,
                'is_published' => true,
            ]
        );

        $l_a2_2 = Lesson::updateOrCreate(
            ['unit_id' => $unitA2_2->id, 'title' => 'En una tienda de ropa'],
            [
                'title_ar' => 'في متجر الملابس: المقاس واللون',
                'objective_ar' => 'استخدام: ¿Tiene una talla más grande? و ¿Cuánto cuesta?',
                'objective_en' => 'Use shopping phrases for sizes and pricing.',
                'grammar_notes_ar' => 'Talla تعني مقاس الملابس. للأسعار نسأل: ¿Cuánto cuesta esto? (كم سعر هذا؟).',
                'grammar_notes_en' => 'Talla = clothing size. For price: ¿Cuánto cuesta esto?',
                'skill_category' => 'vocabulary',
                'estimated_minutes' => 8,
                'xp_reward' => 25,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a2_2->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Cómo se pregunta por el precio de una camisa?',
                'prompt_ar' => 'كيف تسأل عن سعر قميص؟',
                'prompt_en' => 'How do you ask for the price of a shirt?',
                'options' => [
                    '¿Cuánto cuesta esta camisa?',
                    '¿Dónde está la camisa?',
                    '¿De qué color es la camisa?',
                    '¿Es grande la camisa?'
                ],
                'correct_answers' => ['¿Cuánto cuesta esta camisa?'],
                'explanation_ar' => '¿Cuánto cuesta? هي الصيغة الأكثر استخداماً لمعرفة السعر.',
                'explanation_en' => '¿Cuánto cuesta? is the primary phrase for asking price.',
                'hint_ar' => 'Cuánto cuesta...',
                'xp_value' => 5,
            ]
        );

        // Unit A2_3: El Pasado - Pretérito Indefinido
        $unitA2_3 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A2']->id, 'title' => 'El Pasado - Vacaciones'],
            [
                'title_ar' => 'الزمن الماضي: عطلتي الأخيرة (Pretérito Indefinido)',
                'title_en' => 'The Past - Holidays and Pretérito Indefinido',
                'description_ar' => 'الحديث عما فعلته في عطلة نهاية الأسبوع أو رحلتك السابقة.',
                'description_en' => 'Talk about past events and what you did on your last vacation.',
                'icon' => 'calendar',
                'order_index' => 3,
                'is_published' => true,
            ]
        );

        $l_a2_3 = Lesson::updateOrCreate(
            ['unit_id' => $unitA2_3->id, 'title' => '¿Qué hiciste el fin de semana?'],
            [
                'title_ar' => 'ماذا فعلت في عطلة نهاية الأسبوع؟',
                'objective_ar' => 'تصريف الأفعال المنتظمة في الماضي البسيط: hablé, comí, viví.',
                'objective_en' => 'Conjugate regular verbs in Pretérito Indefinido: hablé, comí, viví.',
                'grammar_notes_ar' => 'نهايات الماضي المنتظم للأفعال (-ar): -é, -aste, -ó, -amos, -asteis, -aron. مثال: Visité Madrid.',
                'grammar_notes_en' => 'Past endings for -ar verbs: -é, -aste, -ó, -amos, -asteis, -aron.',
                'skill_category' => 'verbs',
                'estimated_minutes' => 10,
                'xp_reward' => 25,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a2_3->id, 'order_index' => 1],
            [
                'exercise_type' => 'fill_blank',
                'prompt' => 'El año pasado yo ___ a España. (verbo viajar en pasado)',
                'prompt_ar' => 'العام الماضي أنا سافرت إلى إسبانيا: اختر تصريف فعل viajar في الماضي',
                'prompt_en' => 'Last year I traveled to Spain: choose past form of viajar',
                'options' => ['viajé', 'viajo', 'viajaré', 'viajaba'],
                'correct_answers' => ['viajé'],
                'explanation_ar' => 'مع Yo في الماضي البسيط للأفعال المنتهية بـ -ar نضع النهاية -é مع نبرة: viajé.',
                'explanation_en' => 'With Yo in Pretérito Indefinido, -ar verbs take -é: viajé.',
                'hint_ar' => 'viajé...',
                'xp_value' => 5,
            ]
        );

        // Unit A2_4: Pasado Indefinido vs Imperfecto
        $unitA2_4 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A2']->id, 'title' => 'Narrar en Pasado: Indefinido vs Imperfecto'],
            [
                'title_ar' => 'سرد القصص في الماضي: Indefinido مقابل Imperfecto',
                'title_en' => 'Narrating the Past: Preterite vs Imperfect',
                'description_ar' => 'الفارق الأساسي بين الأحداث المكتملة المحددة والوصف والعادات المستمرة في الماضي.',
                'description_en' => 'Understand completed past actions versus ongoing background descriptions.',
                'icon' => 'book-open',
                'order_index' => 4,
                'is_published' => true,
            ]
        );

        $l_a2_verbs_contrast = Lesson::updateOrCreate(
            ['unit_id' => $unitA2_4->id, 'title' => 'Indefinido vs Imperfecto'],
            [
                'title_ar' => 'المقارنة بين زمنين من أزمنة الماضي',
                'objective_ar' => 'معرفة متى تستخدم Indefinido للأحداث القاطعة و Imperfecto للوصف والطقس والعادات.',
                'objective_en' => 'Distinguish punctiliar actions (Indefinido) from descriptive backdrop (Imperfecto).',
                'grammar_notes_ar' => 'المعادلة الأساسية: Imperfecto يصف المسرح والخلفية (Hacía sol, yo caminaba...) بينما Indefinido يمثل الحدث المفاجئ (De repente, vi a Juan).',
                'grammar_notes_en' => 'Imperfect sets background scenery; Preterite records punctual interruptions.',
                'skill_category' => 'verbs',
                'estimated_minutes' => 12,
                'xp_reward' => 30,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a2_verbs_contrast->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => 'Completa: "Ayer mientras yo caminaba por la calle, _____ a mi profesor." (ver / verbo en pasado)',
                'prompt_ar' => 'أكمل: بينما كنت أمشي في الشارع (حدث مستمر)، رأيت فجأة أستاذي (حدث قاطع):',
                'prompt_en' => 'Complete: While I was walking, I saw my teacher:',
                'options' => ['vi', 'veía', 'veo', 'veré'],
                'correct_answers' => ['vi'],
                'explanation_ar' => 'الحدث القاطع المفاجئ يصرف دائماً في Pretérito Indefinido: vi.',
                'explanation_en' => 'The interrupting action takes the Preterite: vi.',
                'hint_ar' => 'ver en indefinido con yo = vi.',
                'xp_value' => 5,
            ]
        );

        // =========================================================================
        // LEVEL B1: INTERMEDIO
        // =========================================================================

        // Unit B1_1: Expresar Opiniones y Deseos (Subjuntivo)
        $unitB1_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['B1']->id, 'title' => 'Opiniones y Deseos (Subjuntivo)'],
            [
                'title_ar' => 'التعبير عن الآراء والتمني (صيغة الشك Subjuntivo)',
                'title_en' => 'Opinions and Desires: The Subjunctive Mood',
                'description_ar' => 'مدخل أساسي لأهم قاعدة في المستوى المتوسط: التعبير عن المشاعر، التمني، والشك.',
                'description_en' => 'The core B1 pillar: expressing desires, doubt, and emotions with subjunctive.',
                'icon' => 'heart',
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        $l_b1_1 = Lesson::updateOrCreate(
            ['unit_id' => $unitB1_1->id, 'title' => 'Creo que sí vs No creo que sea'],
            [
                'title_ar' => 'الرأي والشك: Indicativo مقابل Subjuntivo',
                'objective_ar' => 'فهم متى نستخدم صيغة الشك مع أدوات النفي: Creo que es (يقين) مقابل No creo que sea (شك).',
                'objective_en' => 'Understand indicative for certainty vs subjunctive for negation/doubt.',
                'grammar_notes_ar' => 'عند اليقين نستخدم الحاضر العادي: Creo que tiene razón. وعند النفي والشك نستخدم Subjuntivo: No creo que tenga razón.',
                'grammar_notes_en' => 'Certainty triggers indicative (Creo que es); negation triggers subjunctive (No creo que sea).',
                'skill_category' => 'grammar',
                'estimated_minutes' => 12,
                'xp_reward' => 30,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_b1_1->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => 'Completa con subjuntivo: No creo que él ___ hoy.',
                'prompt_ar' => 'اختر التصريف الصحيح (صيغة الشك): لا أعتقد أنه سيأتي اليوم',
                'prompt_en' => 'Complete with subjunctive: I do not think he will come today.',
                'options' => ['venga', 'viene', 'vendrá', 'vino'],
                'correct_answers' => ['venga'],
                'explanation_ar' => 'بعد "No creo que" يلزم استخدام صيغة الـ Subjuntivo: venga (من فعل venir).',
                'explanation_en' => 'After "No creo que", the subjunctive mood is mandatory: venga.',
                'hint_ar' => 'venga...',
                'xp_value' => 5,
            ]
        );

        // Unit B1_2: POR vs PARA
        $unitB1_2 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['B1']->id, 'title' => 'El Uso Riguroso de Por y Para'],
            [
                'title_ar' => 'الاستخدام الدقيق لحرفي الجر Por و Para',
                'title_en' => 'Mastery of Por and Para',
                'description_ar' => 'التفرقة الدقيقة بين السبب والدافع (Por) والهدف والوجهة والموعد النهائي (Para).',
                'description_en' => 'Clear rules for cause/motivation (Por) vs objective/recipient/deadline (Para).',
                'icon' => 'compass',
                'order_index' => 2,
                'is_published' => true,
            ]
        );

        $l_b1_por_para = Lesson::updateOrCreate(
            ['unit_id' => $unitB1_2->id, 'title' => 'Por vs Para en Contexto'],
            [
                'title_ar' => 'حروف الجر Por و Para في السياق العملي',
                'objective_ar' => 'إتقان استخدام Por للسبب والوسيلة، و Para للغاية والمستلم والموعد.',
                'objective_en' => 'Master Por for reason/means and Para for destination/purpose.',
                'grammar_notes_ar' => 'Por = السبب (Gracias por todo, estudio por vocación). Para = الغاية والمستقبل (Este regalo es para ti, estudio para aprobar).',
                'grammar_notes_en' => 'Por denotes cause and medium; Para denotes destination and goal.',
                'skill_category' => 'grammar',
                'estimated_minutes' => 11,
                'xp_reward' => 30,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_b1_por_para->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => 'Elige entre Por o Para: "Estudio español _____ trabajar en una empresa internacional."',
                'prompt_ar' => 'أدرس الإسبانية من أجل (بهدف) العمل في شركة دولية: اختر بين Por أو Para',
                'prompt_en' => 'I study Spanish in order to work in an international firm: Por or Para?',
                'options' => ['para', 'por', 'de', 'con'],
                'correct_answers' => ['para'],
                'explanation_ar' => 'للتعبير عن الهدف والغاية المرجوة في المستقبل نستخدم دائماً Para.',
                'explanation_en' => 'For expressing the goal/purpose ahead, always use Para.',
                'hint_ar' => 'الهدف = Para.',
                'xp_value' => 5,
            ]
        );

        // Unit B1_3: Lectura y Sostenibilidad
        $unitB1_3 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['B1']->id, 'title' => 'Medio Ambiente y Sociedad'],
            [
                'title_ar' => 'البيئة والمجتمع والتنمية المستدامة',
                'title_en' => 'Environment and Society',
                'description_ar' => 'قراءة نصوص تحليلية ومناقشة قضايا الطاقة والتدوير والمناخ.',
                'description_en' => 'Read analytical texts and discuss climate, energy, and recycling.',
                'icon' => 'globe',
                'order_index' => 3,
                'is_published' => true,
            ]
        );

        $l_b1_read = Lesson::updateOrCreate(
            ['unit_id' => $unitB1_3->id, 'title' => 'Lectura: El reciclaje y las energías renovables'],
            [
                'title_ar' => 'قراءة وتحليل: الطاقات المتجددة في إسبانيا',
                'objective_ar' => 'قراءة مقال بيئي واستيعاب المصطلحات التخصصية وتأثيرها على المجتمع.',
                'objective_en' => 'Read an environmental article and understand key sustainability vocabulary.',
                'grammar_notes_ar' => 'استخدام صيغ التفضيل والمقارنة المتقدمة: cada vez más, tanto como.',
                'grammar_notes_en' => 'Advanced comparative constructions: cada vez más, tanto como.',
                'skill_category' => 'reading',
                'estimated_minutes' => 13,
                'xp_reward' => 30,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_b1_read->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => 'Lee: "España lidera la producción de energía eólica y solar en el sur de Europa. Sin embargo, los expertos señalan que el almacenamiento en baterías sigue siendo el gran reto tecnológico." ¿Cuál es el desafío principal?',
                'prompt_ar' => 'ما هو التحدي التكنولوجي الأكبر المذكور في النص؟',
                'prompt_en' => 'What is the main technological challenge mentioned in the text?',
                'options' => [
                    'El almacenamiento de energía en baterías (تخزين الطاقة في البطاريات)',
                    'Falta de sol y viento en España',
                    'Falta de ingenieros cualificados',
                    'El coste del transporte'
                ],
                'correct_answers' => ['El almacenamiento de energía en baterías (تخزين الطاقة في البطاريات)'],
                'explanation_ar' => 'النص نص صراحة على: "el almacenamiento en baterías sigue siendo el gran reto tecnológico".',
                'explanation_en' => 'The text explicitly identifies battery storage as the key technological hurdle.',
                'hint_ar' => 'almacenamiento = تخزين.',
                'xp_value' => 5,
            ]
        );

        // =========================================================================
        // LEVEL B2: INTERMEDIO ALTO
        // =========================================================================

        $unitB2_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['B2']->id, 'title' => 'El Mundo Laboral y Entrevistas'],
            [
                'title_ar' => 'عالم العمل والمقابلات الوظيفية',
                'title_en' => 'Professional Life and Job Interviews',
                'description_ar' => 'إعداد السيرة الذاتية بالإسبانية، مناقشة المهارات، واجتياز مقابلات العمل بنجاح.',
                'description_en' => 'Prepare CVs in Spanish, negotiate skills, and succeed in interviews.',
                'icon' => 'briefcase',
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        $l_b2_1 = Lesson::updateOrCreate(
            ['unit_id' => $unitB2_1->id, 'title' => 'La entrevista de trabajo'],
            [
                'title_ar' => 'مقابلة العمل: التحدث عن نقاط القوة والخبرات',
                'objective_ar' => 'استخدام مفردات مهنية: experiencia laboral, puntos fuertes, capacidad de liderazgo.',
                'objective_en' => 'Use professional corporate terminology in interview settings.',
                'grammar_notes_ar' => 'استخدام صيغة التأدب مع الشرط البسيط: Me gustaría formar parte de su equipo.',
                'grammar_notes_en' => 'Polite formal registers with conditional simple: Me gustaría...',
                'skill_category' => 'practical_expressions',
                'estimated_minutes' => 14,
                'xp_reward' => 35,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_b2_1->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Cómo expresas formalmente tu deseo de unirte al equipo?',
                'prompt_ar' => 'كيف تعبر بأسلوب مهني ولبق عن رغبتك بالانضمام للشركة؟',
                'prompt_en' => 'How do you politely express your desire to join the company team?',
                'options' => [
                    'Me gustaría mucho formar parte de su equipo.',
                    'Quiero trabajar aquí ya.',
                    'Deme el trabajo por favor.',
                    'Trabajaré con vosotros mañana.'
                ],
                'correct_answers' => ['Me gustaría mucho formar parte de su equipo.'],
                'explanation_ar' => 'صيغة الشرط "Me gustaría" هي الأكثر تهذيباً واحترافية في بيئات العمل.',
                'explanation_en' => 'The conditional form "Me gustaría" is the gold standard for formal interviews.',
                'hint_ar' => 'Me gustaría...',
                'xp_value' => 5,
            ]
        );

        // Unit B2_2: Subjuntivo Pasado y Condicionales
        $unitB2_2 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['B2']->id, 'title' => 'Subjuntivo Pasado e Hipótesis'],
            [
                'title_ar' => 'ماضي الشك والافتراضات غير الواقعية (Imperfecto de Subjuntivo)',
                'title_en' => 'Past Subjunctive and Hypothetical Conditions',
                'description_ar' => 'بناء الجمل الشرطية الافتراضية: "لو كنت أملك المال لسافرت حول العالم".',
                'description_en' => 'Build hypothetical conditional clauses with past subjunctive.',
                'icon' => 'zap',
                'order_index' => 2,
                'is_published' => true,
            ]
        );

        $l_b2_subj_past = Lesson::updateOrCreate(
            ['unit_id' => $unitB2_2->id, 'title' => 'Oraciones Condicionales Irreales: Si tuviera...'],
            [
                'title_ar' => 'صيغة الشرط مع ماضي الشك',
                'objective_ar' => 'تصريف Imperfecto de Subjuntivo (-ra / -se) والربط مع الشرط البسيط.',
                'objective_en' => 'Conjugate past subjunctive and pair with conditional simple.',
                'grammar_notes_ar' => 'القاعدة: Si + Imperfecto de Subjuntivo (tuviera) + Condicional Simple (viajaría).',
                'grammar_notes_en' => 'Formula: Si + Past Subjunctive + Conditional Simple.',
                'skill_category' => 'verbs',
                'estimated_minutes' => 15,
                'xp_reward' => 40,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_b2_subj_past->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => 'Completa la hipótesis: "Si yo _____ más tiempo libre, estudiaría dos carreras universitarias."',
                'prompt_ar' => 'أكمل الفرضية غير الواقعية: لو كان لدي وقت فراغ أكبر (فعل tener في ماضي الشك):',
                'prompt_en' => 'Complete the hypothesis: If I had more free time, I would study two degrees:',
                'options' => ['tuviera', 'tengo', 'tendría', 'tuve'],
                'correct_answers' => ['tuviera'],
                'explanation_ar' => 'بعد أداة الشرط Si في الافتراضات نستخدم دائماً Imperfecto de Subjuntivo: tuviera.',
                'explanation_en' => 'After conditional Si for hypothetical situations, use past subjunctive: tuviera.',
                'hint_ar' => 'tuviera...',
                'xp_value' => 5,
            ]
        );

        // =========================================================================
        // LEVEL C1: AVANZADO
        // =========================================================================

        $unitC1_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['C1']->id, 'title' => 'Modismos y Expresiones Idiomáticas'],
            [
                'title_ar' => 'الأمثال الشعبية والتعبيرات الاصطلاحية الإسبانية',
                'title_en' => 'Idioms and Figurative Spanish',
                'description_ar' => 'تجاوز الترجمة الحرفية واستيعاب ما يقوله الإسبان في حديثهم اليومي العفوي.',
                'description_en' => 'Go beyond literal translation and master colloquial and idiomatic Spanish.',
                'icon' => 'award',
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        $l_c1_1 = Lesson::updateOrCreate(
            ['unit_id' => $unitC1_1->id, 'title' => 'Expresiones coloquiales de España'],
            [
                'title_ar' => 'تعبيرات يومية دارجة لا غنى عنها في إسبانيا',
                'objective_ar' => 'فهم تعبيرات مثل: Tomar el pelo (يمزح / يسخر بلطف), Estar en las nubes (سارح الذهن).',
                'objective_en' => 'Understand culturally rich expressions: Tomar el pelo, Estar en las nubes.',
                'grammar_notes_ar' => 'التعبيرات الاصطلاحية (Modismos) لا تُترجم كلمة بكلمة بل تُفهم كوحدة دلالية واحدة.',
                'grammar_notes_en' => 'Idioms are non-compositional semantic units; master contextual resonance.',
                'skill_category' => 'vocabulary',
                'estimated_minutes' => 15,
                'xp_reward' => 40,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_c1_1->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Qué significa la expresión "me estás tomando el pelo"?',
                'prompt_ar' => 'ما معنى التعبير الإسباني الشهير "me estás tomando el pelo"؟',
                'prompt_en' => 'What does the idiom "me estás tomando el pelo" mean?',
                'options' => [
                    'أنت تمزح معي / تمازحني (You are pulling my leg)',
                    'أنت تقص شعري',
                    'أنت تغضبني بشدة',
                    'أنت لا تسمعني'
                ],
                'correct_answers' => ['أنت تمزح معي / تمازحني (You are pulling my leg)'],
                'explanation_ar' => 'Tomar el pelo تعني تمازحني أو تختبرني، وتوازي بالمصري "إنت بتشتغلني؟".',
                'explanation_en' => 'Tomar el pelo means to tease or pull someone\'s leg.',
                'hint_ar' => 'تعبير مجازي عن المزاح.',
                'xp_value' => 5,
            ]
        );

        // Unit C1_2: Argumentación y Retórica
        $unitC1_2 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['C1']->id, 'title' => 'Conectores Discursivos y Argumentación'],
            [
                'title_ar' => 'الروابط البلاغية وأساليب المحاججة والإقناع',
                'title_en' => 'Discourse Markers and Persuasive Argumentation',
                'description_ar' => 'صياغة أطروحات متماسكة واستخدام روابط متقدمة: Por consiguiente, No obstante, En resumidas cuentas.',
                'description_en' => 'Craft coherent thesis arguments with sophisticated discourse markers.',
                'icon' => 'layers',
                'order_index' => 2,
                'is_published' => true,
            ]
        );

        $l_c1_discourse = Lesson::updateOrCreate(
            ['unit_id' => $unitC1_2->id, 'title' => 'Conectores de Contraste y Consecuencia'],
            [
                'title_ar' => 'روابط الاستدراك والتعقيب المتقدمة',
                'objective_ar' => 'التمييز بين الروابط الرسمية: Sin embargo, En cambio, Por lo tanto, Por ende.',
                'objective_en' => 'Deploy nuanced cohesive linkers in formal oral and written discourse.',
                'grammar_notes_ar' => 'الروابط المنطقية تحدد درجة رصانة الخطاب وتمنح النص انسيابية وتماسكاً بنيوياً عالياً.',
                'grammar_notes_en' => 'Advanced discourse markers elevate text cohesion and stylistic register.',
                'skill_category' => 'grammar',
                'estimated_minutes' => 16,
                'xp_reward' => 45,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_c1_discourse->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => 'Elige el conector culto más adecuado para concluir una ponencia formal:',
                'prompt_ar' => 'اختر الرابط البلاغي الأكثر رصانة لخاتمة ورقة بحثية رسمية:',
                'prompt_en' => 'Choose the most cultured connector to conclude a formal paper:',
                'options' => [
                    'A modo de corolario (كنتيجة حتمية وخاتمة)',
                    'Bueno pues ya está',
                    'Para terminar rapidito',
                    'O sea que sí'
                ],
                'correct_answers' => ['A modo de corolario (كنتيجة حتمية وخاتمة)'],
                'explanation_ar' => 'A modo de corolario تعبير بلاغي أكاديمي رفيع يستهل النتائج النهائية المستخلصة.',
                'explanation_en' => 'A modo de corolario is a high academic register formula introducing conclusive outcomes.',
                'hint_ar' => 'corolario = نتيجة لازمة.',
                'xp_value' => 5,
            ]
        );

        // =========================================================================
        // LEVEL C2: MAESTRÍA
        // =========================================================================

        $unitC2_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['C2']->id, 'title' => 'Matices Lingüísticos y Dialectología'],
            [
                'title_ar' => 'الفروق البلاغية واللهجات المتنوعة',
                'title_en' => 'Stylistic Nuances and Hispanic Dialectology',
                'description_ar' => 'استيعاب دقيق للفروق الأسلوبية بين الإسبانية الأوروبية وأمريكا اللاتينية واللغة الأدبية الرفيعة.',
                'description_en' => 'Deep mastery of dialectological variation between Peninsular and Latin American Spanish.',
                'icon' => 'globe',
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        $l_c2_1 = Lesson::updateOrCreate(
            ['unit_id' => $unitC2_1->id, 'title' => 'Riqueza léxica y registros formales'],
            [
                'title_ar' => 'الثراء اللغوي والسجلات الرسمية الرفيعة',
                'objective_ar' => 'القدرة على تحليل النصوص الإسبانية المتقدمة واستخدام المترادفات الدقيقة.',
                'objective_en' => 'Analyze intricate literary texts and apply precise synonymous distinctions.',
                'grammar_notes_ar' => 'في مستوى C2، يتم التركيز على إتقان السجلات اللغوية المتعددة من العامية الشديدة وحتى البلاغة الأكاديمية والقانونية.',
                'grammar_notes_en' => 'At C2 level, command of registers ranges from intense colloquialism to legal-administrative eloquence.',
                'skill_category' => 'practical_expressions',
                'estimated_minutes' => 20,
                'xp_reward' => 50,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_c2_1->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Cuál es el sinónimo más culto y formal de "empezar"?',
                'prompt_ar' => 'ما هو المرادف الأكثر بلاغة ورسمية لفعل "empezar" (يبدأ)؟',
                'prompt_en' => 'What is the most elevated and formal synonym of "empezar"?',
                'options' => [
                    'Comenzar / Iniciar / Emprender',
                    'Terminar',
                    'Olvidar',
                    'Parar'
                ],
                'correct_answers' => ['Comenzar / Iniciar / Emprender'],
                'explanation_ar' => 'أفعال مثل Iniciar و Emprender تعكس سجلاً لغوياً رفيعاً ودقيقاً في السياقات المهنية والأكاديمية.',
                'explanation_en' => 'Iniciar and Emprender denote refined formal registers in academic and executive contexts.',
                'hint_ar' => 'أفعال تدل على الانطلاق والبدء الرسمي.',
                'xp_value' => 5,
            ]
        );

        // Unit C2_2: Lenguaje Jurídico y Clásico
        $unitC2_2 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['C2']->id, 'title' => 'El Lenguaje Jurídico-Institucional y Clásico'],
            [
                'title_ar' => 'اللغة القانونية والمؤسسية والأدب الكلاسيكي',
                'title_en' => 'Legal-Institutional Language and Classical Literature',
                'description_ar' => 'قراءة وفهم الوثائق القانونية الرسمية والمعاهدات والنصوص الأدبية الكلاسيكية.',
                'description_en' => 'Master legal-institutional treaties and Golden Age classical literature.',
                'icon' => 'book-open',
                'order_index' => 2,
                'is_published' => true,
            ]
        );

        $l_c2_legal = Lesson::updateOrCreate(
            ['unit_id' => $unitC2_2->id, 'title' => 'Análisis de textos normativos e institucionales'],
            [
                'title_ar' => 'تحليل النصوص التشريعية والقرارات القضائية',
                'objective_ar' => 'فهم التراكيب المطلقة والصيغ الثابتة في القانون الإسباني: Habiendo sido notificado, A tenor de lo dispuesto.',
                'objective_en' => 'Decipher legal statutory syntax: absolute participles and formulaic jurisprudential phrasing.',
                'grammar_notes_ar' => 'اللغة القانونية تستخدم جملاً مركبة طويلة مع صيغ اسم المفعول المطلق وتراكيب شرطية متداخلة.',
                'grammar_notes_en' => 'Legal Spanish relies on absolute participle clauses and hyperbaton syntax.',
                'skill_category' => 'reading',
                'estimated_minutes' => 22,
                'xp_reward' => 50,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_c2_legal->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Qué significa la locución jurídica española "a tenor de lo dispuesto"?',
                'prompt_ar' => 'ما معنى التعبير القانوني الرصين "a tenor de lo dispuesto"؟',
                'prompt_en' => 'What does the legal formula "a tenor de lo dispuesto" mean?',
                'options' => [
                    'وفقاً لما تنص عليه المادة / بمقتضى الأحكام المقررة',
                    'على عكس القانون تماماً',
                    'قبل إقرار التشريع رسمياً',
                    'بناءً على طلب المتهم الشخصي'
                ],
                'correct_answers' => ['وفقاً لما تنص عليه المادة / بمقتضى الأحكام المقررة'],
                'explanation_ar' => '"A tenor de lo dispuesto" صيغة قانونية قياسية توازي "وفقاً لمقتضى النص".',
                'explanation_en' => '"A tenor de lo dispuesto" means pursuant to / in accordance with the provisions established.',
                'hint_ar' => 'بمقتضى ما تقرر.',
                'xp_value' => 5,
            ]
        );

        $this->command->info("Seeded comprehensive multi-level structured curriculum (Units & Lessons) across A1-C2 successfully.");
    }
}
