<?php

namespace Database\Seeders;

use App\Models\Story;
use App\Models\StoryQuestion;
use App\Models\StorySegment;
use Illuminate\Database\Seeder;

class StorySeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------------
        // STORY 1: Llegada a Madrid (A1)
        // -------------------------------------------------------------
        $story1 = Story::updateOrCreate(
            ['title_es' => 'Llegada a Madrid'],
            [
                'cefr_level' => 'A1',
                'title_ar' => 'الوصول إلى مدريد',
                'summary_ar' => 'يصل طارق إلى مطار باراخاس بمدريد ويبدأ أول محادثة له بالإسبانية لشراء تذكرة مترو والوصول إلى وسط المدينة.',
                'topic' => 'travel',
                'estimated_minutes' => 5,
                'difficulty' => 'easy',
                'xp_reward' => 25,
                'is_published' => true,
            ]
        );

        $segments1 = [
            [
                'order_index' => 1,
                'speaker_name' => 'Tariq',
                'spanish_text' => 'Hola, buenas tardes. ¿Dónde puedo comprar un billete para el metro?',
                'arabic_translation' => 'مرحباً، مساء الخير. أين يمكنني شراء تذكرة للمترو؟',
                'explanation_ar' => 'billete في إسبانيا تعني تذكرة (أما في أمريكا اللاتينية تستخدم boleto).',
                'vocabulary_highlights' => ['billete', 'metro', 'comprar'],
            ],
            [
                'order_index' => 2,
                'speaker_name' => 'Empleado',
                'spanish_text' => 'Buenas tardes. En aquellas máquinas rojas puedes comprar la tarjeta Multi.',
                'arabic_translation' => 'مساء الخير. في تلك الآلات الحمراء يمكنك شراء بطاقة "مولتي" للمواصلات.',
                'explanation_ar' => 'tarjeta Multi هي البطاقة الذكية القابلة للشحن لمترو وحافلات مدريد.',
                'vocabulary_highlights' => ['máquinas', 'tarjeta'],
            ],
            [
                'order_index' => 3,
                'speaker_name' => 'Tariq',
                'spanish_text' => 'Muchas gracias. ¿Qué línea va directamente a la Puerta del Sol?',
                'arabic_translation' => 'شكراً جزيلاً. ما هو الخط الذي يتجه مباشرة إلى بويرتا ديل سول؟',
                'explanation_ar' => 'Puerta del Sol هي الساحة المركزية الشهيرة ومركز إسبانيا الكيلومتري.',
                'vocabulary_highlights' => ['línea', 'directamente'],
            ],
            [
                'order_index' => 4,
                'speaker_name' => 'Empleado',
                'spanish_text' => 'Debes tomar la línea ocho hasta Nuevos Ministerios y luego cambiar a la línea diez.',
                'arabic_translation' => 'عليك أن تأخذ الخط 8 حتى محطة "نويفوس مينيستيريوس" ثم التحويل إلى الخط 10.',
                'explanation_ar' => 'فعل cambiar يعني يغير أو يبدّل (التحويل بين خطوط المترو).',
                'vocabulary_highlights' => ['tomar', 'cambiar'],
            ],
        ];

        foreach ($segments1 as $seg) {
            StorySegment::updateOrCreate(
                ['story_id' => $story1->id, 'order_index' => $seg['order_index']],
                $seg
            );
        }

        StoryQuestion::updateOrCreate(
            ['story_id' => $story1->id, 'after_segment_index' => 4],
            [
                'question_es' => '¿Dónde puede comprar Tariq el billete de metro?',
                'question_ar' => 'أين يستطيع طارق شراء تذكرة المترو بحسب الموظف؟',
                'options' => [
                    'En las máquinas rojas',
                    'En el autobús',
                    'En el avión',
                    'En un hotel'
                ],
                'correct_answer' => 'En las máquinas rojas',
                'explanation_ar' => 'قال له الموظف: "En aquellas máquinas rojas puedes comprar la tarjeta Multi".',
            ]
        );

        // -------------------------------------------------------------
        // STORY 2: En el Restaurante Tradicional (A1)
        // -------------------------------------------------------------
        $story2 = Story::updateOrCreate(
            ['title_es' => 'En el Restaurante Tradicional'],
            [
                'cefr_level' => 'A1',
                'title_ar' => 'وجبة الغداء في مطعم تقليدي',
                'summary_ar' => 'يتعرف طارق على مفهوم "قائمة طعام اليوم" (Menú del día) ويطلب وجبة غداء إسبانية متكاملة.',
                'topic' => 'food_drink',
                'estimated_minutes' => 6,
                'difficulty' => 'easy',
                'xp_reward' => 25,
                'is_published' => true,
            ]
        );

        $segments2 = [
            [
                'order_index' => 1,
                'speaker_name' => 'Camarero',
                'spanish_text' => '¡Hola! ¿Tienen mesa reservada para comer?',
                'arabic_translation' => 'أهلاً! هل لديكم طاولة محجوزة لتناول الغداء؟',
                'explanation_ar' => 'Comer في إسبانيا تعني تناول وجبة الغداء تحديداً، لأنها الوجبة الرئيسية في اليوم.',
                'vocabulary_highlights' => ['mesa', 'reservada', 'comer'],
            ],
            [
                'order_index' => 2,
                'speaker_name' => 'Tariq',
                'spanish_text' => 'No tenemos reserva. Una mesa para dos personas, por favor.',
                'arabic_translation' => 'ليس لدينا حجز. طاولة لشخصين من فضلك.',
                'explanation_ar' => 'Por favor صيغة الأدب التي يحرص الإسبان على سماعها دائماً.',
                'vocabulary_highlights' => ['reserva', 'personas', 'favor'],
            ],
            [
                'order_index' => 3,
                'speaker_name' => 'Camarero',
                'spanish_text' => 'Perfecto, pasen por aquí. Hoy tenemos menú del día por doce euros.',
                'arabic_translation' => 'تمام، تفضلوا من هنا. اليوم لدينا قائمة اليوم الكاملة بـ 12 يورو.',
                'explanation_ar' => 'Menú del día يشتمل عادة على طبق أول، طبق رئيسي، خبز، مشروب، وحلوى بسعر اقتصادي ثابت.',
                'vocabulary_highlights' => ['menú', 'euros'],
            ],
            [
                'order_index' => 4,
                'speaker_name' => 'Tariq',
                'spanish_text' => 'Excelente. De primero queremos ensalada mixta y de segundo pescado a la plancha.',
                'arabic_translation' => 'رائع. كطبق أول نريد سلطة مشكلة، وكطبق ثانٍ سمك مشوي.',
                'explanation_ar' => 'A la plancha تعني مشوي على الصاج أو الجريل بدون دهون زائدة.',
                'vocabulary_highlights' => ['ensalada', 'pescado', 'plancha'],
            ],
        ];

        foreach ($segments2 as $seg) {
            StorySegment::updateOrCreate(
                ['story_id' => $story2->id, 'order_index' => $seg['order_index']],
                $seg
            );
        }

        StoryQuestion::updateOrCreate(
            ['story_id' => $story2->id, 'after_segment_index' => 4],
            [
                'question_es' => '¿Cuánto cuesta el menú del día?',
                'question_ar' => 'كم تبلغ تكلفة قائمة طعام اليوم في المطعم؟',
                'options' => [
                    'Doce euros',
                    'Veinte euros',
                    'Diez euros',
                    'Quince euros'
                ],
                'correct_answer' => 'Doce euros',
                'explanation_ar' => 'ذكر النادل: "menú del día por doce euros" (12 يورو).',
            ]
        );

        // -------------------------------------------------------------
        // STORY 3: Un café en Malasaña (A2)
        // -------------------------------------------------------------
        $story3 = Story::updateOrCreate(
            ['title_es' => 'Un café en Malasaña'],
            [
                'cefr_level' => 'A2',
                'title_ar' => 'قهوة في حي مالاسانيا',
                'summary_ar' => 'يجلس طارق في مقهى تقليدي في حي مالاسانيا النابض بالفن ويطلب قهوته ويتحدث مع النادل عن الأماكن الثقافية.',
                'topic' => 'culture',
                'estimated_minutes' => 6,
                'difficulty' => 'medium',
                'xp_reward' => 30,
                'is_published' => true,
            ]
        );

        $segments3 = [
            [
                'order_index' => 1,
                'speaker_name' => 'Tariq',
                'spanish_text' => 'Buenas tardes. Me gustaría un café con leche templada y una tostada con tomate.',
                'arabic_translation' => 'مساء الخير. أود قهوة بحليب دافئ وتوست مع معجون الطماطم وزيت الزيتون.',
                'explanation_ar' => 'tostada con tomate y aceite هي الفطور والعصرونية الإسبانية الكلاسيكية.',
                'vocabulary_highlights' => ['templada', 'tostada', 'tomate'],
            ],
            [
                'order_index' => 2,
                'speaker_name' => 'Camarera',
                'spanish_text' => '¡Marchando! ¿Es tu primera vez en este barrio?',
                'arabic_translation' => 'حاضر من عيوني! هل هذه مرتك الأولى في هذا الحي؟',
                'explanation_ar' => '¡Marchando! تعبير إسباني شهير في المقاهي والمطاعم يعني "طلبك قيد التحضير فوراً!".',
                'vocabulary_highlights' => ['marchando', 'barrio'],
            ],
            [
                'order_index' => 3,
                'speaker_name' => 'Tariq',
                'spanish_text' => 'Sí, me han dicho que Malasaña tiene librerías muy bonitas e historia fascinante.',
                'arabic_translation' => 'نعم، أخبروني أن مالاسانيا بها مكتبات جميلة وتاريخ ساحر.',
                'explanation_ar' => 'حرف العطف y يصبح e قبل الكلمات التي تبدأ بـ i أو hi (مثل: e historia).',
                'vocabulary_highlights' => ['librerías', 'historia'],
            ],
            [
                'order_index' => 4,
                'speaker_name' => 'Camarera',
                'spanish_text' => 'Totalmente cierto. Tienes que visitar la Plaza del Dos de Mayo, está aquí al lado.',
                'arabic_translation' => 'صحيح تماماً. عليك بزيارة ساحة دوز دي مايو، إنها هنا بجوارنا مباشرة.',
                'explanation_ar' => 'al lado تعني بجوار أو على مقربة.',
                'vocabulary_highlights' => ['visitar', 'lado'],
            ],
        ];

        foreach ($segments3 as $seg) {
            StorySegment::updateOrCreate(
                ['story_id' => $story3->id, 'order_index' => $seg['order_index']],
                $seg
            );
        }

        StoryQuestion::updateOrCreate(
            ['story_id' => $story3->id, 'after_segment_index' => 4],
            [
                'question_es' => '¿Qué plaza recomendó la camarera visitar?',
                'question_ar' => 'ما هو الميدان أو الساحة التي نصحت النادلة طارق بزيارتها؟',
                'options' => [
                    'La Plaza del Dos de Mayo',
                    'La Plaza Mayor',
                    'La Puerta del Sol',
                    'La Plaza de España'
                ],
                'correct_answer' => 'La Plaza del Dos de Mayo',
                'explanation_ar' => 'قالت النادلة: "Tienes que visitar la Plaza del Dos de Mayo".',
            ]
        );

        // -------------------------------------------------------------
        // STORY 4: Una noche de Tapas en Sevilla (A2)
        // -------------------------------------------------------------
        $story4 = Story::updateOrCreate(
            ['title_es' => 'Una noche de Tapas en Sevilla'],
            [
                'cefr_level' => 'A2',
                'title_ar' => 'ليلة مقبلات التاباس في إشبيلية',
                'summary_ar' => 'جولة ممتعة في حي سانتا كروز بإشبيلية لتذوق أطباق التاباس الأندلسية الشهيرة مع الأصدقاء.',
                'topic' => 'culture',
                'estimated_minutes' => 7,
                'difficulty' => 'medium',
                'xp_reward' => 30,
                'is_published' => true,
            ]
        );

        $segments4 = [
            [
                'order_index' => 1,
                'speaker_name' => 'Manuel',
                'spanish_text' => '¡Bienvenidos a Sevilla! Esta noche vamos a "tapear" por el barrio de Santa Cruz.',
                'arabic_translation' => 'أهلاً بكم في إشبيلية! الليلة سنقوم بجولة لتناول التاباس في حي سانتا كروز التاريخي.',
                'explanation_ar' => 'Tapear فعل إسباني أصيل يعني الانتقال من بار لآخر وتناول طبق مقبلات صغير في كل مكان.',
                'vocabulary_highlights' => ['tapear', 'barrio'],
            ],
            [
                'order_index' => 2,
                'speaker_name' => 'Tariq',
                'spanish_text' => 'Me encanta la idea. ¿Qué tapas me recomiendas probar primero?',
                'arabic_translation' => 'فكرة رائعة تعجبني جداً. ما هي أطباق التاباس التي تنصحني بتجربتها أولاً؟',
                'explanation_ar' => 'Me encanta تعني يعجبني بشدة / أعشق ذلك.',
                'vocabulary_highlights' => ['encanta', 'recomiendas'],
            ],
            [
                'order_index' => 3,
                'speaker_name' => 'Manuel',
                'spanish_text' => 'Sin duda la tortilla de patatas y el salmorejo cordobés bien fresquito.',
                'arabic_translation' => 'بلا شك عجة البطاطس الإسبانية، وحساء السالموريخو البارد المنعش.',
                'explanation_ar' => 'Salmorejo هو حساء طماطم وخبز أندلسي بارد وغني القوام.',
                'vocabulary_highlights' => ['tortilla', 'fresquito'],
            ],
        ];

        foreach ($segments4 as $seg) {
            StorySegment::updateOrCreate(
                ['story_id' => $story4->id, 'order_index' => $seg['order_index']],
                $seg
            );
        }

        StoryQuestion::updateOrCreate(
            ['story_id' => $story4->id, 'after_segment_index' => 3],
            [
                'question_es' => '¿Qué significa el verbo "tapear" en España?',
                'question_ar' => 'ماذا يعني فعل "tapear" في الثقافة الإسبانية؟',
                'options' => [
                    'الانتقال وتناول مقبلات التاباس في أماكن متعددة',
                    'شرب القهوة فقط',
                    'النوم بعد الظهر',
                    'ركوب الخيل'
                ],
                'correct_answer' => 'الانتقال وتناول مقبلات التاباس في أماكن متعددة',
                'explanation_ar' => 'Tapear تعني الخروج والاستمتاع بأطباق التاباس الصغيرة والتنقل بين الحانات.',
            ]
        );

        // -------------------------------------------------------------
        // STORY 5: La entrevista de trabajo en Valencia (B1)
        // -------------------------------------------------------------
        $story5 = Story::updateOrCreate(
            ['title_es' => 'La entrevista de trabajo en Valencia'],
            [
                'cefr_level' => 'B1',
                'title_ar' => 'مقابلة العمل في فالنسيا',
                'summary_ar' => 'يجري طارق مقابلة عمل لشركة تكنولوجيا ناشئة في فالنسيا، ويناقش خبراته ومشاريعه السابقة بثقة ومهنية.',
                'topic' => 'career',
                'estimated_minutes' => 8,
                'difficulty' => 'hard',
                'xp_reward' => 35,
                'is_published' => true,
            ]
        );

        $segments5 = [
            [
                'order_index' => 1,
                'speaker_name' => 'Entrevistadora',
                'spanish_text' => 'Buenos días, Tariq. Cuéntame sobre tu experiencia desarrollando aplicaciones web.',
                'arabic_translation' => 'صباح الخير يا طارق. حدثني عن خبرتك في تطوير تطبيقات الويب.',
                'explanation_ar' => 'Cuéntame صيغة أمر مهذبة من فعل contar (احكِ لي أو حدثني).',
                'vocabulary_highlights' => ['experiencia', 'desarrollando'],
            ],
            [
                'order_index' => 2,
                'speaker_name' => 'Tariq',
                'spanish_text' => 'Llevo cuatro años trabajando con tecnologías modernas y me apasiona resolver problemas complejos.',
                'arabic_translation' => 'أعمل منذ أربع سنوات بالتقنيات الحديثة، ولدي شغف كبير بحل المشكلات المعقدة.',
                'explanation_ar' => 'Llevar + gerundio (llevo trabajando) تعني "أعمل منذ فترة ومستمر حتى الآن".',
                'vocabulary_highlights' => ['llevo', 'apasiona', 'complejos'],
            ],
            [
                'order_index' => 3,
                'speaker_name' => 'Entrevistadora',
                'spanish_text' => 'Excelente. Buscamos a alguien que se adapte rápido al equipo y aprenda continuamente.',
                'arabic_translation' => 'ممتاز. نحن نبحث عن شخص يتأقلم سريعاً مع الفريق ويتعلم باستمرار.',
                'explanation_ar' => 'استخدام Subjuntivo (se adapte / aprenda) لأن الشخص المطلوب غير محدد بعد.',
                'vocabulary_highlights' => ['adapte', 'aprenda'],
            ],
        ];

        foreach ($segments5 as $seg) {
            StorySegment::updateOrCreate(
                ['story_id' => $story5->id, 'order_index' => $seg['order_index']],
                $seg
            );
        }

        StoryQuestion::updateOrCreate(
            ['story_id' => $story5->id, 'after_segment_index' => 3],
            [
                'question_es' => '¿Cuántos años de experiencia tiene Tariq según la conversación?',
                'question_ar' => 'كم سنة من الخبرة ذكر طارق أن لديه في مجال العمل؟',
                'options' => [
                    'Cuatro años',
                    'Dos años',
                    'Diez años',
                    'Un año'
                ],
                'correct_answer' => 'Cuatro años',
                'explanation_ar' => 'قال طارق: "Llevo cuatro años trabajando..." (أربع سنوات).',
            ]
        );

        // -------------------------------------------------------------
        // STORY 6: El Clásico: Pasión en el Santiago Bernabéu (A2)
        // -------------------------------------------------------------
        $story6 = Story::updateOrCreate(
            ['title_es' => 'El Clásico: Pasión en el Santiago Bernabéu'],
            [
                'cefr_level' => 'A2',
                'title_ar' => 'الكلاسيكو: الشغف في السانتياغو برنابيو',
                'summary_ar' => 'يحضر طارق أول مباراة كلاسيكو له بين ريال مدريد وبرشلونة في مدريد، ويتعلم مصطلحات التعليق الرياضي وهتافات الجماهير بالإسبانية.',
                'topic' => 'football',
                'estimated_minutes' => 6,
                'difficulty' => 'medium',
                'xp_reward' => 30,
                'is_published' => true,
            ]
        );

        $segments6 = [
            [
                'order_index' => 1,
                'speaker_name' => 'Aficionado',
                'spanish_text' => '¡Mira qué ambientazo hay en el estadio hoy! El Clásico es el partido más emocionante del planeta.',
                'arabic_translation' => 'انظر إلى الأجواء الخرافية في الملعب اليوم! الكلاسيكو هو المباراة الأكثر إثارة على هذا الكوكب.',
                'explanation_ar' => 'ambientazo هي صيغة تكبير لكلمة ambiente وتعني "أجواء حماسية استثنائية".',
                'vocabulary_highlights' => ['ambientazo', 'estadio', 'emocionante', 'partido'],
            ],
            [
                'order_index' => 2,
                'speaker_name' => 'Comentarista',
                'spanish_text' => '¡El delantero regatea al defensa dentro del área, chuta a portería y... qué paradón del guardameta!',
                'arabic_translation' => 'المهاجم يراوغ المدافع داخل منطقة الجزاء، يسدد بقوة على المرمى ويا له من تصدٍ إعجازي من حارس المرمى!',
                'explanation_ar' => 'مصطلحات كروية: regatear (يراوغ)، chutar (يسدد)، paradón (تصدٍ رائع للحارس).',
                'vocabulary_highlights' => ['delantero', 'regatea', 'defensa', 'chuta', 'portería', 'guardameta'],
            ],
            [
                'order_index' => 3,
                'speaker_name' => 'Tariq',
                'spanish_text' => '¡Fue una jugada increíble! Me encanta la velocidad y la técnica de los futbolistas de LaLiga.',
                'arabic_translation' => 'كانت لعبة خرافية! تعجبني سرعة ومهارة لاعبي الدوري الإسباني كثيراً.',
                'explanation_ar' => 'Jugada تعني لعبة أو هجمة منظمة في كرة القدم.',
                'vocabulary_highlights' => ['jugada', 'increíble', 'velocidad', 'técnica'],
            ],
            [
                'order_index' => 4,
                'speaker_name' => 'Aficionado',
                'spanish_text' => '¡Gooool en el último minuto de descuento! ¡Qué victoria tan merecida para la afición!',
                'arabic_translation' => 'هدف في الدقيقة الأخيرة من الوقت بدل الضائع! يا له من انتصار مستحق للجماهير الوفية!',
                'explanation_ar' => 'tiempo de descuento تعني الوقت بدل الضائع، و afición تعني رابطة المشجعين والجمهور.',
                'vocabulary_highlights' => ['descuento', 'victoria', 'afición'],
            ],
        ];

        foreach ($segments6 as $seg) {
            StorySegment::updateOrCreate(
                ['story_id' => $story6->id, 'order_index' => $seg['order_index']],
                $seg
            );
        }

        StoryQuestion::updateOrCreate(
            ['story_id' => $story6->id, 'after_segment_index' => 4],
            [
                'question_es' => '¿Qué significa "guardameta" en el contexto del partido?',
                'question_ar' => 'ماذا تعني كلمة "guardameta" في سياق مباراة كرة القدم؟',
                'options' => [
                    'حارس المرمى (portero)',
                    'المهاجم الرئيسي',
                    'حكم الساحة',
                    'المدرب الفني'
                ],
                'correct_answer' => 'حارس المرمى (portero)',
                'explanation_ar' => 'Guardameta هي المرادف الفصيح لكلمة Portero وتعني حارس المرمى.',
            ]
        );

        // -------------------------------------------------------------
        // STORY 7: El Legado Andalusí en la Alhambra (A2)
        // -------------------------------------------------------------
        $story7 = Story::updateOrCreate(
            ['title_es' => 'El Legado Andalusí en la Alhambra'],
            [
                'cefr_level' => 'A2',
                'title_ar' => 'سحر قصر الحمراء والروابط العربية الإسبانية',
                'summary_ar' => 'جولة في قصر الحمراء بغرناطة واكتشاف الكلمات الإسبانية ذات الأصول العربية مثل Aceite و Azúcar و Ojalá.',
                'topic' => 'culture',
                'estimated_minutes' => 6,
                'difficulty' => 'medium',
                'xp_reward' => 30,
                'is_published' => true,
            ]
        );

        $segments7 = [
            [
                'order_index' => 1,
                'speaker_name' => 'Guía Carmen',
                'spanish_text' => 'Bienvenidos a la Alhambra de Granada. ¿Sabían que más de cuatro mil palabras en español provienen del árabe?',
                'arabic_translation' => 'مرحباً بكم في قصر الحمراء بغرناطة. هل تعلمون أن أكثر من أربعة آلاف كلمة في الإسبانية تنحدر من أصول عربية؟',
                'explanation_ar' => 'provienen de تعني "تنحدر من أو أصلها من".',
                'vocabulary_highlights' => ['Alhambra', 'palabras', 'provienen', 'árabe'],
            ],
            [
                'order_index' => 2,
                'speaker_name' => 'Tariq',
                'spanish_text' => '¡Sí! Palabras cotidianas como azúcar, aceite, almohada y alcalde tienen raíces claramente árabes.',
                'arabic_translation' => 'نعم! كلمات يومية مثل azúcar (سكر)، aceite (زيت)، almohada (مخدة)، و alcalde (عمدة/قاضي) لها جذور عربية واضحة.',
                'explanation_ar' => 'معظم الكلمات الإسبانية التي تبدأ بـ al- أصلها أداة التعريف العربية (الـ).',
                'vocabulary_highlights' => ['azúcar', 'aceite', 'almohada', 'raíces'],
            ],
            [
                'order_index' => 3,
                'speaker_name' => 'Guía Carmen',
                'spanish_text' => 'Incluso la expresión de esperanza "¡Ojalá!" viene de la frase árabe "In sha Allah" (si Dios quiere).',
                'arabic_translation' => 'حتى تعبير الرجاء والتمني الشهير "!Ojalá" مشتق من العبارة العربية "إن شاء الله".',
                'explanation_ar' => 'Ojalá يتبعها دائماً صيغة الشك (Subjuntivo) وتفيد التمني القوي.',
                'vocabulary_highlights' => ['esperanza', 'ojalá', 'expresión'],
            ],
        ];

        foreach ($segments7 as $seg) {
            StorySegment::updateOrCreate(
                ['story_id' => $story7->id, 'order_index' => $seg['order_index']],
                $seg
            );
        }

        StoryQuestion::updateOrCreate(
            ['story_id' => $story7->id, 'after_segment_index' => 3],
            [
                'question_es' => '¿De qué expresión árabe proviene la palabra española "¡Ojalá!"?',
                'question_ar' => 'من أي تعبير عربي مشتقة كلمة التمني الإسبانية "¡Ojalá!"؟',
                'options' => [
                    'إن شاء الله',
                    'الحمد لله',
                    'صباح الخير',
                    'شكراً جزيلاً'
                ],
                'correct_answer' => 'إن شاء الله',
                'explanation_ar' => 'كلمة Ojalá في الإسبانية مشتقة من العبارة العربية "إن شاء الله" للتعبير عن الرجاء والأمل.',
            ]
        );
    }
}
