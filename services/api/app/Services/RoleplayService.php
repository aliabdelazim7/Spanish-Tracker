<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class RoleplayService
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Get all available communicative roleplay scenarios.
     */
    public function getScenarios(?string $level = null): array
    {
        $scenarios = [
            [
                'id' => 'restaurant_madrid',
                'title_es' => 'En el Restaurante Madrileño',
                'title_ar' => 'طلب الطعام في مطعم مدريدي أصيل',
                'description_ar' => 'تحدث مع النادل الإسباني كارلوس، اطلب طاولة واختبر مهاراتك في اختيار الأطباق الإسبانية وحساب الفاتورة.',
                'emoji' => '🥘',
                'location' => 'Plaza Mayor, Madrid',
                'character_name' => 'Carlos (El Camarero)',
                'cefr_level' => 'A1',
                'total_turns' => 4,
                'xp_reward' => 35,
                'turns' => [
                    [
                        'turn_index' => 1,
                        'character_text_es' => '¡Buenas tardes! Bienvenidos al restaurante La Campana. ¿Tienen reserva o mesa para cuántos?',
                        'character_text_ar' => 'مساء الخير! مرحباً بكم في مطعم لا كامبانا. هل لديكم حجز مسبق أم طاولة لكم شخص؟',
                        'prompt_ar' => 'حيّي النادل بأدب واطلب طاولة لشخصين في التراس أو الداخل.',
                        'suggested_options' => [
                            '¡Buenas tardes! Una mesa para dos personas, por favor.',
                            'Hola, no tenemos reserva. ¿Tienen mesa para dos en la terraza?',
                            'Buenas tardes, quisiéramos una mesa tranquila para dos, por favor.',
                        ],
                        'keywords' => ['mesa', 'dos', 'tardes', 'hola', 'favor', 'reserva'],
                        'hint_ar' => 'استخدم التحية "Buenas tardes" واطلب طاولة لشخصين: "una mesa para dos, por favor".',
                    ],
                    [
                        'turn_index' => 2,
                        'character_text_es' => 'Perfecto, por aquí por favor. Tomen asiento. ¿Qué les gustaría tomar para beber mientras miran la carta?',
                        'character_text_ar' => 'ممتاز، تفضلوا من هنا واجلسوا. ماذا تحبون أن تشربوا بينما تطالعون قائمة الطعام؟',
                        'prompt_ar' => 'اطلب مشروبين (ماء بارد وعصير برتقال أو ماء غازي).',
                        'suggested_options' => [
                            'Para beber, una botella de agua mineral fría y un zumo de naranja, por favor.',
                            'Queremos dos botellas de agua con gas y limón, por favor.',
                            'Un agua sin gas y una limonada bien fría, gracias.',
                        ],
                        'keywords' => ['agua', 'zumo', 'beber', 'botella', 'fría', 'por favor', 'naranja', 'limón'],
                        'hint_ar' => 'عبارة الطلب: "Para beber, un agua mineral y un zumo de naranja, por favor".',
                    ],
                    [
                        'turn_index' => 3,
                        'character_text_es' => 'Marchando las bebidas. Y de comer, hoy tenemos una paella valenciana recién hecha riquísima. ¿Qué les pongo de plato principal?',
                        'character_text_ar' => 'أحضر المشروبات فوراً. وبالنسبة للطعام، لدينا اليوم باييا بلنسية طازجة وشهية جداً. ماذا أحضر لكم كطبق رئيسي؟',
                        'prompt_ar' => 'اطلب الباييا وسلطة خضراء خفيفة لمشاركتها.',
                        'suggested_options' => [
                            'Nos gustaría probar la paella valenciana y una ensalada mixta para compartir.',
                            'Tráiganos la paella para dos y una ensalada fresca, por favor.',
                            'Queremos la paella de la casa, tiene una pinta estupenda.',
                        ],
                        'keywords' => ['paella', 'ensalada', 'comer', 'plato', 'probar', 'compartir', 'gustaría'],
                        'hint_ar' => 'يمكنك قول: "Nos gustaría la paella valenciana y una ensalada mixta, por favor".',
                    ],
                    [
                        'turn_index' => 4,
                        'character_text_es' => '¡Excelente elección! Espero que hayan disfrutado mucho de la comida. ¿Desean algún postre o café, o les traigo la cuenta?',
                        'character_text_ar' => 'اختيار ممتاز! أتمنى أن تكونوا قد استمتعتم بالطعام. هل ترغبون بحلوى أو قهوة، أم أحضر لكم الحساب؟',
                        'prompt_ar' => 'اشكر النادل وأخبره أن الطعام كان لذيذاً واطلب الحساب للدفع نقداً أو بالبطاقة.',
                        'suggested_options' => [
                            'Todo estuvo delicioso, muchas gracias. ¿Nos trae la cuenta, por favor? Vamos a pagar con tarjeta.',
                            'Estaba riquísimo todo. La cuenta, por favor. ¿Se puede pagar con tarjeta?',
                            'Muchas gracias, no queremos postre. La cuenta, por favor.',
                        ],
                        'keywords' => ['cuenta', 'delicioso', 'gracias', 'tarjeta', 'pagar', 'favor', 'riquísimo'],
                        'hint_ar' => 'عبارة إنهاء الوجبة الشهيرة: "Estuvo delicioso, muchas gracias. ¿Nos trae la cuenta, por favor?".',
                    ],
                ],
            ],

            [
                'id' => 'airport_checkin',
                'title_es' => 'En el Control de Pasaportes',
                'title_ar' => 'مكتب الجوازات في مطار مدريد',
                'description_ar' => 'حوار رسمي مع ضابطة الجوازات إيلينا عند وصولك إلى مطار مدريد-باراخاس الدولي.',
                'emoji' => '✈️',
                'location' => 'Aeropuerto Adolfo Suárez Madrid-Barajas',
                'character_name' => 'Inspectora Elena',
                'cefr_level' => 'A1',
                'total_turns' => 3,
                'xp_reward' => 30,
                'turns' => [
                    [
                        'turn_index' => 1,
                        'character_text_es' => 'Buenos días. Su pasaporte y la tarjeta de embarque, por favor.',
                        'character_text_ar' => 'صباح الخير. جواز سفرك وبطاقة صعود الطائرة، لو سمحت.',
                        'prompt_ar' => 'حيّ الضابطة وقدم جواز سفرك بأدب.',
                        'suggested_options' => [
                            'Buenos días. Aquí tiene mi pasaporte y la tarjeta de embarque.',
                            'Hola, buenos días. Tome mi pasaporte.',
                            'Buenos días agente, aquí están todos mis documentos.',
                        ],
                        'keywords' => ['buenos', 'días', 'pasaporte', 'aquí', 'tiene', 'tarjeta'],
                        'hint_ar' => 'قل باحترام: "Buenos días. Aquí tiene mi pasaporte, por favor".',
                    ],
                    [
                        'turn_index' => 2,
                        'character_text_es' => 'Gracias. ¿Cuál es el motivo principal de su visita a España y cuánto tiempo piensa quedarse?',
                        'character_text_ar' => 'شكراً لك. ما هو الغرض الرئيسي من زيارتك لإسبانيا وكم المدة التي تنوي الإقامة فيها؟',
                        'prompt_ar' => 'اشرح أنك قادم للسياحة وتعلم الإسبانية لمدة أسبوعين.',
                        'suggested_options' => [
                            'Vengo por turismo y para practicar español durante dos semanas.',
                            'El motivo es vacaciones y hacer un curso de español de quince días.',
                            'Vengo de viaje turístico por dos semanas en Madrid y Andalucía.',
                        ],
                        'keywords' => ['turismo', 'español', 'semanas', 'dos', 'vacaciones', 'aprender', 'quince', 'días'],
                        'hint_ar' => 'استخدم كلمة السياحة والمدة: "Vengo por turismo y para aprender español durante dos semanas".',
                    ],
                    [
                        'turn_index' => 3,
                        'character_text_es' => 'Muy bien. ¿Tiene ya reserva de hotel para su estancia y billete de vuelta?',
                        'character_text_ar' => 'جيد جداً. هل لديك حجز فندقي مسبق لإقامتك وتذكرة عودة مؤكدة؟',
                        'prompt_ar' => 'أكّد أن لديك حجزاً فندقياً وتذكرة عودة في اليوم الرابع عشر.',
                        'suggested_options' => [
                            'Sí, tengo la reserva del hotel en el centro y mi billete de vuelta para el día quince.',
                            'Sí, aquí tiene la confirmación del hotel y el billete de regreso.',
                            'Sí, todo está reservado. Me hospedo en un hotel cerca de la Gran Vía.',
                        ],
                        'keywords' => ['sí', 'reserva', 'hotel', 'billete', 'vuelta', 'centro', 'tengo'],
                        'hint_ar' => 'التأكيد: "Sí, tengo la reserva del hotel y mi billete de vuelta. Todo en orden".',
                    ],
                ],
            ],

            [
                'id' => 'hotel_granvia',
                'title_es' => 'Check-in en el Hotel Gran Vía',
                'title_ar' => 'تسجيل الدخول في فندق غران فيا',
                'description_ar' => 'تحدث مع موظفة الاستقبال لوسيا، أكد تفاصيل حجزك واستلم مفتاح غرفتك وكلمة سر الواي فاي.',
                'emoji' => '🏨',
                'location' => 'Gran Vía, Madrid',
                'character_name' => 'Lucía (Recepción)',
                'cefr_level' => 'A1',
                'total_turns' => 3,
                'xp_reward' => 30,
                'turns' => [
                    [
                        'turn_index' => 1,
                        'character_text_es' => '¡Buenas tardes! Bienvenido al Hotel Gran Vía. ¿En qué le puedo ayudar hoy?',
                        'character_text_ar' => 'مساء الخير! مرحباً بكم في فندق غران فيا. كيف يمكنني مساعدتكم اليوم؟',
                        'prompt_ar' => 'أخبرها بأن لديك حجزاً مسبقاً باسمك لغرفة مزدوجة.',
                        'suggested_options' => [
                            '¡Buenas tardes! Tengo una reserva confirmada a mi nombre para tres noches.',
                            'Hola, buenas tardes. Vengo a hacer el check-in, tengo una reserva.',
                            'Buenas tardes. Tengo una habitación reservada para dos personas.',
                        ],
                        'keywords' => ['tengo', 'reserva', 'nombre', 'habitación', 'tardes', 'check-in'],
                        'hint_ar' => 'ابدأ بـ: "Buenas tardes, tengo una reserva a mi nombre para una habitación".',
                    ],
                    [
                        'turn_index' => 2,
                        'character_text_es' => 'Efectivamente, aquí tengo su reserva: habitación con vistas exteriores y desayuno incluido. ¿Me permite su documento de identidad o pasaporte?',
                        'character_text_ar' => 'بالتأكيد، حجزي يظهر هنا: غرفة بإطلالة خارجية مع إفطار مشمول. هل تسمح لي ببطاقة هويتك أو جواز سفرك؟',
                        'prompt_ar' => 'قدم جواز سفرك واسأل عن موعد تقديم وجبة الإفطار.',
                        'suggested_options' => [
                            'Sí, por supuesto, aquí tiene mi pasaporte. ¿A qué hora es el desayuno?',
                            'Tome mi pasaporte. ¿A qué hora empieza el desayuno por las mañanas?',
                            'Aquí tiene. ¿En qué planta se sirve el desayuno y cuál es el horario?',
                        ],
                        'keywords' => ['pasaporte', 'aquí', 'hora', 'desayuno', 'tiene', 'supuesto'],
                        'hint_ar' => 'قدم الجواز واسأل: "Aquí tiene mi pasaporte. ¿A qué hora se sirve el desayuno?".',
                    ],
                    [
                        'turn_index' => 3,
                        'character_text_es' => 'El desayuno es de siete a diez y media en la primera planta. Aquí tiene su tarjeta llave para la habitación 402 en la cuarta planta y la clave del wifi. ¡Que disfrute de su estancia!',
                        'character_text_ar' => 'الإفطار من السابعة حتى العاشرة والنصف في الطابق الأول. تفضل بطاقة غرفتك 402 بالطابق الرابع وكلمة سر الواي فاي. إقامة سعيدة!',
                        'prompt_ar' => 'اشكرها بأدب وتمنَّ لها يوماً سعيداً.',
                        'suggested_options' => [
                            '¡Muchísimas gracias por su amabilidad! Que tenga una buena tarde.',
                            'Muchas gracias por todo. Hasta luego.',
                            'Perfecto, muy amable. ¡Que tenga un excelente día!',
                        ],
                        'keywords' => ['gracias', 'amable', 'tarde', 'día', 'muchas', 'hasta'],
                        'hint_ar' => 'الشكر والتوديع: "Muchísimas gracias, muy amable. ¡Que tenga un buen día!".',
                    ],
                ],
            ],

            [
                'id' => 'taxi_sevilla',
                'title_es' => 'En el Taxi por Sevilla',
                'title_ar' => 'تاكسي في شوارع إشبيلية الأندلسية',
                'description_ar' => 'حوار حي مع مانولو سائق التاكسي، حدد وجهتك، تبادل أطراف الحديث عن سحر الأندلس وادفع الأجرة.',
                'emoji' => '🚕',
                'location' => 'Sevilla, Andalucía',
                'character_name' => 'Manolo (El Taxista)',
                'cefr_level' => 'A2',
                'total_turns' => 3,
                'xp_reward' => 30,
                'turns' => [
                    [
                        'turn_index' => 1,
                        'character_text_es' => '¡Hola, buenas! ¿A dónde le llevo hoy amigo?',
                        'character_text_ar' => 'أهلاً وسهلاً! إلى أين أوصلك اليوم يا صديقي؟',
                        'prompt_ar' => 'حيّ السائق واطلب الذهاب إلى ساحة إسبانيا الشهيرة (Plaza de España) واسأله إن كانت المسافة بعيدة.',
                        'suggested_options' => [
                            '¡Hola, buenas! A la Plaza de España, por favor. ¿Está muy lejos de aquí?',
                            'Buenas tardes. Lléveme a la Plaza de España, por favor. ¿Cuánto se tarda?',
                            'Hola, por favor a la Plaza de España. ¿Hay mucho tráfico hoy?',
                        ],
                        'keywords' => ['plaza', 'españa', 'lejos', 'favor', 'tarda', 'hola', 'buenas'],
                        'hint_ar' => 'الوجهة: "A la Plaza de España, por favor. ¿Está muy lejos?".',
                    ],
                    [
                        'turn_index' => 2,
                        'character_text_es' => 'Para nada, en diez minutitos estamos allí. Sevilla está preciosa en esta época del año. ¿Es su primera vez visitando Andalucía?',
                        'character_text_ar' => 'على الإطلاق، في عشر دقائق سنكون هناك. إشبيلية رائعة في هذا الوقت من السنة. هل هذه أول زيارة لك للأندلس؟',
                        'prompt_ar' => 'أخبره بأنها زيارتك الأولى وأنك معجب جداً بالهندسة المعمارية والأجواء الدافئة.',
                        'suggested_options' => [
                            'Sí, es mi primera vez y me parece una ciudad fascinante con una arquitectura bellísima.',
                            'Sí, es mi primer viaje a Andalucía y la gente aquí es súper amable y alegre.',
                            'Es mi primera vez. Me encanta el clima y la historia de Sevilla.',
                        ],
                        'keywords' => ['primera', 'vez', 'ciudad', 'arquitectura', 'bonita', 'encanta', 'gente', 'historia'],
                        'hint_ar' => 'التعبير عن الإعجاب: "Sí, es mi primera vez. Me parece una ciudad preciosa y la gente es muy amable".',
                    ],
                    [
                        'turn_index' => 3,
                        'character_text_es' => '¡Hombre, claro que sí! Ya hemos llegado a la entrada principal del parque de María Luisa. Son ocho euros con cincuenta.',
                        'character_text_ar' => 'يا سلام، بكل تأكيد! لقد وصلنا الآن للمدخل الرئيسي لحديقة ماريا لويزا. الأجرة ثمانية يورو وخمسون سنتاً.',
                        'prompt_ar' => 'ادفع له 10 يورو واطلب منه الاحتفاظ بالباقي كإكرامية مع شكره.',
                        'suggested_options' => [
                            'Aquí tiene diez euros. Quédese con la vuelta para usted, muchas gracias por la charla.',
                            'Tome diez euros, el cambio es propina para usted. ¡Hasta luego!',
                            'Muchas gracias. Aquí tiene diez euros, quédese con el cambio.',
                        ],
                        'keywords' => ['diez', 'euros', 'cambio', 'vuelta', 'quédese', 'gracias', 'propina'],
                        'hint_ar' => 'دفع الأجرة والإكرامية: "Aquí tiene diez euros. Quédese con el cambio, muchas gracias".',
                    ],
                ],
            ],

            [
                'id' => 'market_shopping',
                'title_es' => 'En el Mercado Tradicional',
                'title_ar' => 'التسوق في سوق الفواكه الإسباني',
                'description_ar' => 'تسوق مع بائعة الفواكه الخبيرة دونيا كارمن، اطلب بالوزن واسأل عن أسعار الفواكه الطازجة.',
                'emoji' => '🍊',
                'location' => 'Mercado de San Miguel, Madrid',
                'character_name' => 'Doña Carmen',
                'cefr_level' => 'A1',
                'total_turns' => 3,
                'xp_reward' => 30,
                'turns' => [
                    [
                        'turn_index' => 1,
                        'character_text_es' => '¡Buenos días guapo! Mira qué naranjas y manzanas más ricas tenemos hoy de Valencia. ¿Qué te pongo?',
                        'character_text_ar' => 'صباح الخير يا طيب! انظر ما أجمل البرتقال والتفاح القادم اليوم من فالنسيا. ماذا أحضر لك؟',
                        'prompt_ar' => 'حيّ البائعة واطلب كيلو من البرتقال ونصف كيلو من التفاح.',
                        'suggested_options' => [
                            '¡Buenos días! Póngame un kilo de naranjas y medio kilo de manzanas, por favor.',
                            'Hola, quisiera un kilo de naranjas valencianas y medio de manzanas.',
                            'Buenos días Doña Carmen. Me llevo un kilo de naranjas y algunas manzanas rojas.',
                        ],
                        'keywords' => ['kilo', 'naranjas', 'manzanas', 'medio', 'póngame', 'quisiera', 'por favor'],
                        'hint_ar' => 'طلب الوزن: "Buenos días. Quisiera un kilo de naranjas y medio kilo de manzanas, por favor".',
                    ],
                    [
                        'turn_index' => 2,
                        'character_text_es' => 'Aquí tienes las naranjas y manzanas, dulces como la miel. ¿Te apetece también un poco de queso manchego o jamón serrano?',
                        'character_text_ar' => 'ها هو البرتقال والتفاح، حلو كالعسل. هل ترغب أيضاً بقطعة من جبن المانشيجو أو الخبز الطازج؟',
                        'prompt_ar' => 'اشكرها بأدب، واعتذر عن الجبن واسألها عن الحساب الإجمالي.',
                        'suggested_options' => [
                            'No gracias, hoy solo fruta. ¿Cuánto es todo en total?',
                            'Por ahora nada más, gracias. ¿Cuánto le debo?',
                            'Muchas gracias, hoy solo esto. ¿Cuánto cuesta todo?',
                        ],
                        'keywords' => ['cuánto', 'total', 'nada', 'más', 'debo', 'gracias', 'cuesta'],
                        'hint_ar' => 'السؤال عن الإجمالي: "No, gracias, solo esto por hoy. ¿Cuánto es en total?".',
                    ],
                    [
                        'turn_index' => 3,
                        'character_text_es' => 'Pues son cuatro euros con setenta céntimos en total, mi cielo.',
                        'character_text_ar' => 'الحساب أربعة يورو وسبعون سنتاً في المجموع يا بني.',
                        'prompt_ar' => 'أعطها 5 يورو واشكرها وتمنَّ لها يوماً مباركاً.',
                        'suggested_options' => [
                            'Aquí tiene cinco euros. Muchas gracias y que tenga un buen día.',
                            'Tome cinco euros. Quédese con los treinta céntimos, gracias.',
                            'Aquí tiene exacto. Muchas gracias por su atención Doña Carmen.',
                        ],
                        'keywords' => ['cinco', 'euros', 'aquí', 'gracias', 'tenga', 'día'],
                        'hint_ar' => 'الدفع والشكر: "Aquí tiene cinco euros. Muchas gracias y que pase un buen día".',
                    ],
                ],
            ],
        ];

        if ($level && $level !== 'ALL') {
            return array_values(array_filter($scenarios, fn($s) => $s['cefr_level'] === $level));
        }

        return $scenarios;
    }

    /**
     * Evaluate a turn response from the learner in real-time.
     */
    public function evaluateTurn(?User $user, string $scenarioId, int $turnIndex, string $userInput, string $lang = 'ar'): array
    {
        $scenarios = $this->getScenarios();
        $scenario = null;
        foreach ($scenarios as $s) {
            if ($s['id'] === $scenarioId) {
                $scenario = $s;
                break;
            }
        }

        if (!$scenario) {
            return [
                'success' => false,
                'message' => 'السيناريو المطلوب غير موجود.',
            ];
        }

        $turns = $scenario['turns'];
        $currentTurn = null;
        foreach ($turns as $t) {
            if ($t['turn_index'] === $turnIndex) {
                $currentTurn = $t;
                break;
            }
        }

        if (!$currentTurn) {
            return [
                'success' => false,
                'message' => 'مرحلة الحوار غير مطابقة.',
            ];
        }

        $cleanInput = mb_strtolower(trim($userInput));
        $cleanInput = trim($cleanInput, " ¿?!¡.,");

        // Keyword density and intent matching
        $matchedKeywords = 0;
        $totalKeywords = count($currentTurn['keywords']);
        foreach ($currentTurn['keywords'] as $kw) {
            if (mb_strpos($cleanInput, mb_strtolower($kw)) !== false) {
                $matchedKeywords++;
            }
        }

        $matchPercent = $totalKeywords > 0
            ? min(100, (int) round(($matchedKeywords / min($totalKeywords, 3)) * 100))
            : 80;

        // Base score: input length + matched keywords
        $wordCount = count(preg_split('/\s+/u', $cleanInput, -1, PREG_SPLIT_NO_EMPTY));
        $score = min(100, max(45, (int) round(($matchPercent * 0.7) + min(30, $wordCount * 6))));

        $isPassed = $score >= 60;
        $isLastTurn = $turnIndex >= $scenario['total_turns'];

        $xpAwarded = 0;
        if ($isLastTurn && $isPassed && $user) {
            $xpAwarded = $scenario['xp_reward'];
            $this->gamificationService->awardXp(
                $user,
                $xpAwarded,
                'roleplay_dialogue',
                null,
                "إكمال محاكاة حوار: {$scenario['title_ar']}"
            );
        }

        // Construct constructive, warm pedagogical feedback
        $feedbackAr = '';
        if ($score >= 85) {
            $feedbackAr = 'نطق وتعبير ممتاز وطبيعي جداً! استخدمت العبارات الملائمة بدقة وأسلوب إسباني أصيل. 👏🇪🇸';
        } elseif ($score >= 60) {
            $feedbackAr = 'إجابة مفهومة وجيدة جداً وتفي بالغرض في الموقف الحقيقي. يمكنك صياغتها بشكل أكثر طلاقة كما هو موضح بالخيارات المقترحة.';
        } else {
            $feedbackAr = 'حاول تضمين كلمات الموقف الأساسية مثل (' . implode('، ', array_slice($currentTurn['keywords'], 0, 3)) . ') لتكون إجابتك مفهومة للنادل أو الضابط.';
        }

        return [
            'success' => true,
            'is_passed' => $isPassed,
            'score' => $score,
            'user_input' => $userInput,
            'turn_index' => $turnIndex,
            'is_last_turn' => $isLastTurn,
            'xp_awarded' => $xpAwarded,
            'feedback_ar' => $feedbackAr,
            'suggested_options' => $currentTurn['suggested_options'],
            'next_turn_index' => $isLastTurn ? null : $turnIndex + 1,
            'scenario_completed' => $isLastTurn && $isPassed,
        ];
    }
}
