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

        // =========================================================
        // LEVEL A1: PRINCIPIANTE
        // =========================================================

        // Unit 1: Saludos y Presentaciones
        $unitA1_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A1']->id, 'title' => 'Saludos y Presentaciones'],
            [
                'title_ar' => 'التحيات والتعارف واللباقة اليومية',
                'description_ar' => 'تعلم كيف تحيي الناس، تعرف عن نفسك وتسأل عن أحوالهم بأدب.',
                'icon' => 'sparkles',
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        // Lesson 1: ¡Hola! ¿Cómo te llamas?
        $l_a1_1 = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_1->id, 'title' => '¡Hola! ¿Cómo te llamas?'],
            [
                'title_ar' => 'مرحباً! ما اسمك؟',
                'objective_ar' => 'التعرف على التحيات الأساسية وتقديم النفس باستخدام فعل llamarse.',
                'grammar_notes_ar' => 'في الإسبانية، فعل llamarse فعل انعكاسي: Me llamo (اسمي)، Te llamas (اسمك). علامة الاستفهام المقلوبة (¿) تسبق السؤال دائمًا.',
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
                'options' => ['Buenos días', 'Buenas noches', 'Hola', 'Adiós'],
                'correct_answers' => ['Buenos días'],
                'explanation_ar' => 'Buenos días تُستخدم للتحية صباحاً حتى موعد الغداء.',
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
                'options' => null,
                'correct_answers' => ['Hola, me llamo Sara', 'Hola me llamo Sara', 'Hola, soy Sara', 'Hola soy Sara'],
                'explanation_ar' => 'نستخدم "Hola" للتحية و"me llamo" لقول اسمي.',
                'hint_ar' => 'Hola, me llamo...',
                'xp_value' => 5,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_1->id, 'order_index' => 3],
            [
                'exercise_type' => 'fill_blank',
                'prompt' => 'Completa la frase: ¿Cómo ___ llamas?',
                'prompt_ar' => 'أكمل الفراغ: ما اسمك؟ (صيغة غير رسمية للمخاطب)',
                'options' => ['te', 'me', 'se', 'nos'],
                'correct_answers' => ['te'],
                'explanation_ar' => 'مع الضمير tú (أنت) نستخدم ضمير الانعكاس te.',
                'hint_ar' => 'الضمير المناسب للمخاطب أنت هو te.',
                'xp_value' => 5,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_1->id, 'order_index' => 4],
            [
                'exercise_type' => 'matching_pairs',
                'prompt' => 'Empareja cada saludo con su significado en árabe:',
                'prompt_ar' => 'صل كل تحية إسبانية بمعناها بالعربية:',
                'options' => [
                    'pairs' => [
                        ['es' => 'Hola', 'ar' => 'مرحباً'],
                        ['es' => 'Buenos días', 'ar' => 'صباح الخير'],
                        ['es' => 'Buenas tardes', 'ar' => 'مساء الخير'],
                        ['es' => 'Adiós', 'ar' => 'مع السلامة'],
                    ]
                ],
                'correct_answers' => [
                    'Hola' => 'مرحباً',
                    'Buenos días' => 'صباح الخير',
                    'Buenas tardes' => 'مساء الخير',
                    'Adiós' => 'مع السلامة',
                ],
                'explanation_ar' => 'التحيات الإسبانية ترتبط بأوقات النهار بدقة.',
                'hint_ar' => 'تذكر أن tardes تعني فترة الظهيرة والمساء.',
                'xp_value' => 5,
            ]
        );

        // Lesson 2: ¿De dónde eres?
        $l_a1_2 = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_1->id, 'title' => '¿De dónde eres?'],
            [
                'title_ar' => 'من أين أنت؟ (الجنسيات وفعل الكينونة Ser)',
                'objective_ar' => 'التعبير عن الجنسية والبلد باستخدام تصريف فعل Ser.',
                'grammar_notes_ar' => 'فعل Ser يدل على الأصل الدائم: Yo soy egipcio (أنا مصري)، Tú eres de España (أنت من إسبانيا).',
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
                'options' => ['soy', 'eres', 'es', 'somos'],
                'correct_answers' => ['soy'],
                'explanation_ar' => 'مع الضمير Yo (أنا) التصريف الصحيح لفعل Ser هو soy.',
                'hint_ar' => 'Yo soy...',
                'xp_value' => 5,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_2->id, 'order_index' => 2],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Cómo se dice "أنا مصري" (للمذكر)؟',
                'prompt_ar' => 'كيف تقول "أنا مصري" بالإسبانية؟',
                'options' => ['Soy egipcio', 'Soy egipcia', 'Estoy egipcio', 'Eres egipcio'],
                'correct_answers' => ['Soy egipcio'],
                'explanation_ar' => 'للجنسية نستخدم فعل Ser والصفة المذكرة تنتهي بـ o: Soy egipcio.',
                'hint_ar' => 'تذكر استخدام فعل Ser مع الجنسية.',
                'xp_value' => 5,
            ]
        );

        // Lesson 3: Números y Datos Personales
        $l_a1_3 = Lesson::updateOrCreate(
            ['unit_id' => $unitA1_1->id, 'title' => 'Números y Datos Personales'],
            [
                'title_ar' => 'الأرقام من 1 إلى 20 والعمر',
                'objective_ar' => 'العد بالإسبانية والسؤال عن العمر ورقم الهاتف.',
                'grammar_notes_ar' => 'في الإسبانية، للتعبير عن العمر نستخدم فعل Tener (يمتلك): Tengo 25 años (عمري 25 سنة، حرفياً: أمتلك 25 سنة).',
                'estimated_minutes' => 7,
                'xp_reward' => 20,
                'order_index' => 3,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_3->id, 'order_index' => 1],
            [
                'exercise_type' => 'multiple_choice',
                'prompt' => '¿Qué número es "quince"?',
                'prompt_ar' => 'ما هو الرقم المقابل للكلمة الإسبانية "quince"؟',
                'options' => ['15', '5', '50', '12'],
                'correct_answers' => ['15'],
                'explanation_ar' => 'quince هو الرقم 15 في الإسبانية.',
                'hint_ar' => 'يأتي بعد catorce (14).',
                'xp_value' => 5,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_3->id, 'order_index' => 2],
            [
                'exercise_type' => 'fill_blank',
                'prompt' => 'Yo ___ treinta años. (verbo tener)',
                'prompt_ar' => 'أنا عمري ثلاثون عاماً: اختر تصريف فعل Tener',
                'options' => ['tengo', 'tienes', 'tiene', 'tenemos'],
                'correct_answers' => ['tengo'],
                'explanation_ar' => 'Yo tengo تعني أنا أمتلك، وتستخدم مع العمر.',
                'hint_ar' => 'Tengo...',
                'xp_value' => 5,
            ]
        );

        // Unit 2: En la Cafetería y Restaurante
        $unitA1_2 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A1']->id, 'title' => 'En la Cafetería y Restaurante'],
            [
                'title_ar' => 'في المقهى والمطعم',
                'description_ar' => 'تعلم كيف تطلب المشروبات والمأكولات الإسبانية وتسأل عن الحساب.',
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
                'grammar_notes_ar' => 'لطلب شيء بأدب: "Un café con leche, por favor" أو "Para mí, un té".',
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
                'options' => [
                    'Un café con leche, por favor',
                    'Un té con limón, gracias',
                    'Agua sin gas',
                    'La cuenta, por favor'
                ],
                'correct_answers' => ['Un café con leche, por favor'],
                'explanation_ar' => 'Café con leche هو المشروب الأكثر شهرة في إسبانيا.',
                'hint_ar' => 'كلمة leche تعني حليب.',
                'xp_value' => 5,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a1_4->id, 'order_index' => 2],
            [
                'exercise_type' => 'word_order',
                'prompt' => 'Ordena las palabras:',
                'prompt_ar' => 'رتب الكلمات لتكوين: "الحساب من فضلك"',
                'options' => ['cuenta,', 'La', 'favor', 'por'],
                'correct_answers' => ['La cuenta, por favor', 'La cuenta por favor'],
                'explanation_ar' => 'La cuenta تعني الحساب / الفاتورة.',
                'hint_ar' => 'La cuenta...',
                'xp_value' => 5,
            ]
        );

        // Unit 3: La Familia y Descripciones
        $unitA1_3 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A1']->id, 'title' => 'La Familia y Descripciones'],
            [
                'title_ar' => 'العائلة والصفات والمظهر',
                'description_ar' => 'التحدث عن أفراد الأسرة، والوصف الجسدي والشخصي.',
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
                'grammar_notes_ar' => 'صفات الملكية: Mi padre (والدي - مفرد)، Mis hermanos (إخوتي - جمع). لاحظ توافق الجمع.',
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
                'options' => ['أخت', 'أخ', 'أم', 'ابنة'],
                'correct_answers' => ['أخت'],
                'explanation_ar' => 'Hermana = أخت، و Hermano = أخ.',
                'hint_ar' => 'الكلمة تنتهي بـ a فهي مؤنث.',
                'xp_value' => 5,
            ]
        );

        // Unit 4: La Rutina Diaria y la Hora
        $unitA1_4 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A1']->id, 'title' => 'La Rutina Diaria y la Hora'],
            [
                'title_ar' => 'الروتين اليومي والساعة',
                'description_ar' => 'كيف تسأل عن الوقت وتتحدث عن أنشطتك من الصباح للمساء.',
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
                'grammar_notes_ar' => 'للساعة 1 نستخدم: Es la una. ولباقي الساعات جمع: Son las dos, Son las tres... إلخ.',
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
                'options' => ['Son', 'Es', 'Está', 'Hay'],
                'correct_answers' => ['Son'],
                'explanation_ar' => 'مع جميع الساعات ما عدا الواحدة نستخدم الجمع: Son las...',
                'hint_ar' => 'Son las...',
                'xp_value' => 5,
            ]
        );

        // =========================================================
        // LEVEL A2: ELEMENTAL
        // =========================================================

        // Unit A2_1: En la Ciudad y Direcciones
        $unitA2_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A2']->id, 'title' => 'En la Ciudad y Direcciones'],
            [
                'title_ar' => 'في المدينة وسؤال المارة عن الاتجاهات',
                'description_ar' => 'تعلم كيف تسأل عن الأماكن وتفهم الإرشادات (يمين، يسار، إلى الأمام).',
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
                'grammar_notes_ar' => 'فعل girar (ينعطف) و seguir (يواصل): Sigue todo recto y gira a la derecha.',
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
                'options' => ['انعطف يميناً', 'انعطف يساراً', 'امشِ للأمام مباشرة', 'توقف هنا'],
                'correct_answers' => ['انعطف يميناً'],
                'explanation_ar' => 'derecha تعني اليمين، و izquierda تعني اليسار.',
                'hint_ar' => 'derecha = يمين.',
                'xp_value' => 5,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_a2_1->id, 'order_index' => 2],
            [
                'exercise_type' => 'translation',
                'prompt' => 'Traduce: "¿Dónde está la farmacia más cercana?"',
                'prompt_ar' => 'ترجم إلى العربية: ¿Dónde está la farmacia más cercana?',
                'options' => null,
                'correct_answers' => ['أين تقع أقرب صيدلية؟', 'أين تقع أقرب صيدلية', 'فين أقرب صيدلية؟', 'فين أقرب صيدلية'],
                'explanation_ar' => 'más cercana تعني الأقرب (صيغة التفضيل).',
                'hint_ar' => 'أين أقرب...',
                'xp_value' => 5,
            ]
        );

        // Unit A2_2: De Compras y Ropa
        $unitA2_2 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A2']->id, 'title' => 'De Compras y Ropa'],
            [
                'title_ar' => 'التسوق والمقاسات والأسعار',
                'description_ar' => 'شراء الملابس، تجربة المقاس، والسؤال عن الألوان والتخفيضات.',
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
                'grammar_notes_ar' => 'Talla تعني مقاس الملابس. للأسعار نسأل: ¿Cuánto cuesta esto? (كم سعر هذا؟).',
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
                'options' => [
                    '¿Cuánto cuesta esta camisa?',
                    '¿Dónde está la camisa?',
                    '¿De qué color es la camisa?',
                    '¿Es grande la camisa?'
                ],
                'correct_answers' => ['¿Cuánto cuesta esta camisa?'],
                'explanation_ar' => '¿Cuánto cuesta? هي الصيغة الأكثر استخداماً لمعرفة السعر.',
                'hint_ar' => 'Cuánto cuesta...',
                'xp_value' => 5,
            ]
        );

        // Unit A2_3: El Pasado - Mis Últimas Vacaciones
        $unitA2_3 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['A2']->id, 'title' => 'El Pasado - Vacaciones'],
            [
                'title_ar' => 'الزمن الماضي: عطلتي الأخيرة (Pretérito Indefinido)',
                'description_ar' => 'الحديث عما فعلته في عطلة نهاية الأسبوع أو رحلتك السابقة.',
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
                'grammar_notes_ar' => 'نهايات الماضي المنتظم للأفعال (-ar): -é, -aste, -ó, -amos, -asteis, -aron. مثال: Visité Madrid.',
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
                'options' => ['viajé', 'viajo', 'viajaré', 'viajaba'],
                'correct_answers' => ['viajé'],
                'explanation_ar' => 'مع Yo في الماضي البسيط للأفعال المنتهية بـ -ar نضع النهاية -é مع نبرة: viajé.',
                'hint_ar' => 'viajé...',
                'xp_value' => 5,
            ]
        );

        // =========================================================
        // LEVEL B1: INTERMEDIO
        // =========================================================

        // Unit B1_1: Expresar Opiniones y Deseos (Subjuntivo)
        $unitB1_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['B1']->id, 'title' => 'Opiniones y Deseos (Subjuntivo)'],
            [
                'title_ar' => 'التعبير عن الآراء والتمني (صيغة الشك Subjuntivo)',
                'description_ar' => 'مدخل أساسي لأهم قاعدة في المستوى المتوسط: التعبير عن المشاعر، التمني، والشك.',
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
                'grammar_notes_ar' => 'عند اليقين نستخدم الحاضر العادي: Creo que tiene razón. وعند النفي والشك نستخدم Subjuntivo: No creo que tenga razón.',
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
                'options' => ['venga', 'viene', 'vendrá', 'vino'],
                'correct_answers' => ['venga'],
                'explanation_ar' => 'بعد "No creo que" يلزم استخدام صيغة الـ Subjuntivo: venga (من فعل venir).',
                'hint_ar' => 'venga...',
                'xp_value' => 5,
            ]
        );

        // Unit B1_2: Recuerdos de la Infancia (Imperfecto)
        $unitB1_2 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['B1']->id, 'title' => 'Recuerdos de la Infancia'],
            [
                'title_ar' => 'حكايات الطفولة والماضي المستمر (Pretérito Imperfecto)',
                'description_ar' => 'وصف العادات والروتين القديم في الماضي: "عندما كنت صغيراً كنت ألعب كل يوم".',
                'icon' => 'book-open',
                'order_index' => 2,
                'is_published' => true,
            ]
        );

        $l_b1_2 = Lesson::updateOrCreate(
            ['unit_id' => $unitB1_2->id, 'title' => 'Cuando era niño'],
            [
                'title_ar' => 'عندما كنت طفلاً: وصف العادات القديمة',
                'objective_ar' => 'تصريف Imperfecto: era, tenía, jugaba, vivía.',
                'grammar_notes_ar' => 'يُستخدم Imperfecto لوصف حالة مستمرة أو عادة في الماضي بدون تحديد نهاية: Yo jugaba al fútbol todos los días.',
                'estimated_minutes' => 11,
                'xp_reward' => 30,
                'order_index' => 1,
                'is_published' => true,
            ]
        );

        LessonExercise::updateOrCreate(
            ['lesson_id' => $l_b1_2->id, 'order_index' => 1],
            [
                'exercise_type' => 'fill_blank',
                'prompt' => 'Cuando yo ___ diez años, vivía en El Cairo. (verbo tener)',
                'prompt_ar' => 'عندما كان عمري عشر سنوات كنت أعيش في القاهرة: اختر تصريف فعل tener في Imperfecto',
                'options' => ['tenía', 'tuve', 'tengo', 'tendré'],
                'correct_answers' => ['tenía'],
                'explanation_ar' => 'للعمر في الماضي نستخدم دائماً Imperfecto: tenía diez años.',
                'hint_ar' => 'tenía...',
                'xp_value' => 5,
            ]
        );

        // =========================================================
        // LEVEL B2: INTERMEDIO ALTO
        // =========================================================

        $unitB2_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['B2']->id, 'title' => 'El Mundo Laboral y Entrevistas'],
            [
                'title_ar' => 'عالم العمل والمقابلات الوظيفية',
                'description_ar' => 'إعداد السيرة الذاتية بالإسبانية، مناقشة المهارات، واجتياز مقابلات العمل بنجاح.',
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
                'grammar_notes_ar' => 'استخدام صيغة التأدب مع الشرط البسيط: Me gustaría formar parte de su equipo.',
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
                'options' => [
                    'Me gustaría mucho formar parte de su equipo.',
                    'Quiero trabajar aquí ya.',
                    'Deme el trabajo por favor.',
                    'Trabajaré con vosotros mañana.'
                ],
                'correct_answers' => ['Me gustaría mucho formar parte de su equipo.'],
                'explanation_ar' => 'صيغة الشرط "Me gustaría" هي الأكثر تهذيباً واحترافية في بيئات العمل.',
                'hint_ar' => 'Me gustaría...',
                'xp_value' => 5,
            ]
        );

        // =========================================================
        // LEVEL C1: AVANZADO
        // =========================================================

        $unitC1_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['C1']->id, 'title' => 'Modismos y Expresiones Idiomáticas'],
            [
                'title_ar' => 'الأمثال الشعبية والتعبيرات الاصطلاحية الإسبانية',
                'description_ar' => 'تجاوز الترجمة الحرفية واستيعاب ما يقوله الإسبان في حديثهم اليومي العفوي.',
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
                'grammar_notes_ar' => 'التعبيرات الاصطلاحية (Modismos) لا تُترجم كلمة بكلمة بل تُفهم كوحدة دلالية واحدة.',
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
                'options' => [
                    'أنت تمزح معي / تمازحني',
                    'أنت تقص شعري',
                    'أنت تغضبني بشدة',
                    'أنت لا تسمعني'
                ],
                'correct_answers' => ['أنت تمزح معي / تمازحني'],
                'explanation_ar' => 'Tomar el pelo تعني تمازحني أو تختبرني، وتوازي بالمصري "إنت بتشتغلني؟".',
                'hint_ar' => 'تعبير مجازي عن المزاح.',
                'xp_value' => 5,
            ]
        );

        // =========================================================
        // LEVEL C2: MAESTRÍA
        // =========================================================

        $unitC2_1 = Unit::updateOrCreate(
            ['cefr_level_id' => $levelMap['C2']->id, 'title' => 'Matices Lingüísticos y Dialectología'],
            [
                'title_ar' => 'الفروق البلاغية واللهجات المتنوعة',
                'description_ar' => 'استيعاب دقيق للفروق الأسلوبية بين الإسبانية الأوروبية وأمريكا اللاتينية واللغة الأدبية الرفيعة.',
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
                'grammar_notes_ar' => 'في مستوى C2، يتم التركيز على إتقان السجلات اللغوية المتعددة من العامية الشديدة وحتى البلاغة الأكاديمية.',
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
                'options' => [
                    'Comenzar / Iniciar / Emprender',
                    'Terminar',
                    'Olvidar',
                    'Parar'
                ],
                'correct_answers' => ['Comenzar / Iniciar / Emprender'],
                'explanation_ar' => 'أفعال مثل Iniciar و Emprender تعكس سجلاً لغوياً رفيعاً ودقيقاً في السياقات المهنية والأكاديمية.',
                'hint_ar' => 'أفعال تدل على الانطلاق والبدء الرسمي.',
                'xp_value' => 5,
            ]
        );
    }
}
