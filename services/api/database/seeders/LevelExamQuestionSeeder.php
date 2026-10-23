<?php

namespace Database\Seeders;

use App\Models\LevelExamQuestion;
use Illuminate\Database\Seeder;

class LevelExamQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questionsA1 = [
            // VOCABULARY (4)
            [
                'cefr_level' => 'A1',
                'skill_category' => 'vocabulary',
                'question_text' => '¿Cómo se dice "صباح الخير" en español?',
                'prompt_ar' => 'اختر التحية الإسبانية الصحيحة لـ "صباح الخير":',
                'audio_url' => null,
                'options' => ['Buenas noches', 'Buenos días', 'Buenas tardes', 'Hasta luego'],
                'correct_answer' => 'Buenos días',
                'explanation_ar' => 'التحية الصباحية في إسبانيا هي "Buenos días" وتستخدم حتى وقت الظهيرة.',
                'points' => 10,
                'order_index' => 1,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'vocabulary',
                'question_text' => '¿Qué número es "treinta y cinco"?',
                'prompt_ar' => 'ما هو الرقم الإسباني "treinta y cinco" بالأرقام؟',
                'audio_url' => null,
                'options' => ['25', '35', '45', '53'],
                'correct_answer' => '35',
                'explanation_ar' => 'Treinta (30) + cinco (5) = 35.',
                'points' => 10,
                'order_index' => 2,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'vocabulary',
                'question_text' => '¿Cómo se dice "قطار" en español?',
                'prompt_ar' => 'اختر الكلمة الصحيحة لـ "قطار":',
                'audio_url' => null,
                'options' => ['Avión', 'Tren', 'Coche', 'Autobús'],
                'correct_answer' => 'Tren',
                'explanation_ar' => 'كلمة Tren تعني قطار، بينما Avión طائرة و Coche سيارة.',
                'points' => 10,
                'order_index' => 3,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'vocabulary',
                'question_text' => '¿Qué día de la semana va después de "miércoles"?',
                'prompt_ar' => 'ما هو اليوم الذي يأتي مباشرة بعد الأربعاء (miércoles)؟',
                'audio_url' => null,
                'options' => ['Martes', 'Jueves', 'Viernes', 'Sábado'],
                'correct_answer' => 'Jueves',
                'explanation_ar' => 'أيام الأسبوع بالترتيب: Lunes, Martes, Miércoles, Jueves (الخميس).',
                'points' => 10,
                'order_index' => 4,
            ],

            // GRAMMAR (4)
            [
                'cefr_level' => 'A1',
                'skill_category' => 'grammar',
                'question_text' => 'Completa la frase con el verbo correcto: "Yo _____ estudiante de español."',
                'prompt_ar' => 'اختر التصريف الصحيح لفعل الكينونة الدائم مع الضمير Yo:',
                'audio_url' => null,
                'options' => ['eres', 'somos', 'soy', 'es'],
                'correct_answer' => 'soy',
                'explanation_ar' => 'تصريف الفعل Ser مع ضمير المتكلم Yo هو دائماً "soy" (Yo soy = أنا أكون).',
                'points' => 10,
                'order_index' => 5,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'grammar',
                'question_text' => '¿Cuál es el artículo determinado correcto? "_____ manzana es roja."',
                'prompt_ar' => 'اختر أداة التعريف المناسبة لكلمة manzana (تفاحة):',
                'audio_url' => null,
                'options' => ['El', 'La', 'Los', 'Un'],
                'correct_answer' => 'La',
                'explanation_ar' => 'كلمة manzana اسم مفرد مؤنث تأخذ أداة التعريف La.',
                'points' => 10,
                'order_index' => 6,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'grammar',
                'question_text' => 'Elige entre SER o ESTAR: "El museo _____ cerca de la estación."',
                'prompt_ar' => 'اختر الفعل المناسب لتحديد موقع ومكان المتحف:',
                'audio_url' => null,
                'options' => ['es', 'está', 'hay', 'tiene'],
                'correct_answer' => 'está',
                'explanation_ar' => 'لتحديد مكان أو موقع شيء معرف ومحدد نستخدم فعل Estar (El museo está...).',
                'points' => 10,
                'order_index' => 7,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'grammar',
                'question_text' => '¿Cómo se forma el plural de "el lápiz"?',
                'prompt_ar' => 'ما هو جمع كلمة el lápiz (قلم رصاص)؟',
                'audio_url' => null,
                'options' => ['Los lápizes', 'Los lápices', 'Las lápices', 'Los lápizs'],
                'correct_answer' => 'Los lápices',
                'explanation_ar' => 'الكلمات التي تنتهي بـ z تتحول في الجمع إلى ces (lápiz -> lápices).',
                'points' => 10,
                'order_index' => 8,
            ],

            // VERBS & CONJUGATION (4)
            [
                'cefr_level' => 'A1',
                'skill_category' => 'verbs',
                'question_text' => 'Elige el verbo adecuado: "Nosotros _____ tres hermanos."',
                'prompt_ar' => 'اختر الفعل المعبر عن الامتلاك مع ضمير الجمع Nosotros:',
                'audio_url' => null,
                'options' => ['tengo', 'tenemos', 'tienen', 'tiene'],
                'correct_answer' => 'tenemos',
                'explanation_ar' => 'فعل الملكية Tener مع Nosotros يصرف هكذا: "tenemos".',
                'points' => 10,
                'order_index' => 9,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'verbs',
                'question_text' => '¿Cómo se conjuga el verbo IR con "Ellos"? "Ellos _____ a la playa."',
                'prompt_ar' => 'اختر تصريف فعل الذهاب Ir مع ضمير الغائب الجمع Ellos:',
                'audio_url' => null,
                'options' => ['va', 'vamos', 'van', 'voy'],
                'correct_answer' => 'van',
                'explanation_ar' => 'تصريف الفعل الشاذ Ir مع Ellos هو "van".',
                'points' => 10,
                'order_index' => 10,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'verbs',
                'question_text' => '¿Cuál es la forma correcta? "María _____ en un hospital."',
                'prompt_ar' => 'اختر تصريف فعل Trabajar مع María (هي):',
                'audio_url' => null,
                'options' => ['trabajo', 'trabajas', 'trabaja', 'trabajan'],
                'correct_answer' => 'trabaja',
                'explanation_ar' => 'مع الضمير الغائب المفرد (ella) ينتهي الفعل المنتظم بـ a (trabaja).',
                'points' => 10,
                'order_index' => 11,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'verbs',
                'question_text' => '¿Cómo se dice "أريد قهوة" con el verbo Querer?',
                'prompt_ar' => 'اختر التصريف الصحيح لفعل Querer مع ضمير المتكلم Yo:',
                'audio_url' => null,
                'options' => ['Quiero un café', 'Quieres un café', 'Quiere un café', 'Queremos un café'],
                'correct_answer' => 'Quiero un café',
                'explanation_ar' => 'فعل Querer شاذ ويتحول مع Yo إلى Quiero (Yo quiero).',
                'points' => 10,
                'order_index' => 12,
            ],

            // LISTENING & AUDITORY (4)
            [
                'cefr_level' => 'A1',
                'skill_category' => 'listening',
                'question_text' => 'Escucha la frase: "¿A qué hora llega el tren de Madrid?"',
                'prompt_ar' => 'عن أي شيء يستفسر المتحدث في الجملة؟',
                'audio_url' => null,
                'options' => ['سعر تذكرة القطار', 'موعد وصول قطار مدريد', 'رقم رصيف القطار', 'وجهة الحافلة'],
                'correct_answer' => 'موعد وصول قطار مدريد',
                'explanation_ar' => 'السؤال "¿A qué hora llega...?" يعني "في أي ساعة (موعد) يصل القطار...؟".',
                'points' => 10,
                'order_index' => 13,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'listening',
                'question_text' => 'Escucha con atención: "La farmacia de guardia está cerrada hoy."',
                'prompt_ar' => 'ما حالة الصيدلية المناوبة وفق الجملة؟',
                'audio_url' => null,
                'options' => ['مفتوحة الآن', 'مغلقة اليوم', 'قريبة جداً', 'غير موجودة'],
                'correct_answer' => 'مغلقة اليوم',
                'explanation_ar' => 'كلمة cerrada تعني مغلقة، و hoy تعني اليوم.',
                'points' => 10,
                'order_index' => 14,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'listening',
                'question_text' => 'Escucha: "El vuelo con destino a Barcelona sale a las diez."',
                'prompt_ar' => 'ما هي وجهة الرحلة الجوية المذكورة؟',
                'audio_url' => null,
                'options' => ['Madrid', 'Valencia', 'Barcelona', 'Sevilla'],
                'correct_answer' => 'Barcelona',
                'explanation_ar' => 'ذكرت الجملة صراحة: "destino a Barcelona".',
                'points' => 10,
                'order_index' => 15,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'listening',
                'question_text' => 'Escucha: "¿Cuánto cuesta esta camiseta blanca?"',
                'prompt_ar' => 'عن أي قطعة يسأل المشتري؟',
                'audio_url' => null,
                'options' => ['حذاء أسود', 'قميص تي شيرت أبيض', 'بنطال أزرق', 'قبعة حمراء'],
                'correct_answer' => 'قميص تي شيرت أبيض',
                'explanation_ar' => 'Camiseta تعني قميص تي شيرت، و blanca تعني بيضاء.',
                'points' => 10,
                'order_index' => 16,
            ],

            // READING & COMPREHENSION (4)
            [
                'cefr_level' => 'A1',
                'skill_category' => 'reading',
                'question_text' => 'Lee el texto: "Hola, me llamo Carlos. Vivo en un piso céntrico en Valencia con mi amigo David. Los dos estudiamos medicina y trabajamos por las tardes." ¿Dónde vive Carlos?',
                'prompt_ar' => 'بناءً على النص القرائي، أين يعيش كارلوس؟',
                'audio_url' => null,
                'options' => ['En Madrid', 'En Valencia', 'En Barcelona', 'En El Cairo'],
                'correct_answer' => 'En Valencia',
                'explanation_ar' => 'ذكر النص صراحة: "Vivo en un piso céntrico en Valencia".',
                'points' => 10,
                'order_index' => 17,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'reading',
                'question_text' => 'Lee el texto: "El restaurante El Rincón abre de martes a domingo desde las 13:00 hasta las 23:00. Los lunes está cerrado por descanso." ¿Qué día NO puedes comer allí?',
                'prompt_ar' => 'في أي يوم يكون المطعم مغلقاً ولا يمكنك الأكل فيه؟',
                'audio_url' => null,
                'options' => ['El martes', 'El viernes', 'El domingo', 'El lunes'],
                'correct_answer' => 'El lunes',
                'explanation_ar' => 'النص يوضح: "Los lunes está cerrado por descanso".',
                'points' => 10,
                'order_index' => 18,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'reading',
                'question_text' => 'Lee el aviso: "Atención: El tren con destino a Sevilla tiene un retraso de 20 minutos debido a la lluvia." ¿Por qué se retrasa el tren?',
                'prompt_ar' => 'لماذا تأخر قطار إشبيلية حسب التنبيه؟',
                'audio_url' => null,
                'options' => ['بسبب عطل ميكانيكي', 'بسبب هطول الأمطار', 'بسبب إضراب العمال', 'بسبب الازدحام'],
                'correct_answer' => 'بسبب هطول الأمطار',
                'explanation_ar' => 'كلمة "lluvia" تعني المطر في الإسبانية.',
                'points' => 10,
                'order_index' => 19,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'reading',
                'question_text' => 'Lee el cartel: "Piso en alquiler: 2 habitaciones, 1 baño, salón soleado y cocina equipada. 750 euros al mes. Metro a 3 minutos." ¿Cuántos dormitorios tiene el piso?',
                'prompt_ar' => 'كم عدد غرف النوم في الشقة المعروضة للإيجار؟',
                'audio_url' => null,
                'options' => ['Una habitación', 'Dos habitaciones', 'Tres habitaciones', 'Cuatro habitaciones'],
                'correct_answer' => 'Dos habitaciones',
                'explanation_ar' => 'ذكر الإعلان: "2 habitaciones".',
                'points' => 10,
                'order_index' => 20,
            ],

            // PRACTICAL & EVERYDAY PHRASES (4)
            [
                'cefr_level' => 'A1',
                'skill_category' => 'practical',
                'question_text' => 'En un restaurante en España, ¿cómo pides la cuenta educadamente?',
                'prompt_ar' => 'في المطعم، كيف تطلب الحساب بأسلوب مهذب؟',
                'audio_url' => null,
                'options' => ['La cuenta, por favor', 'Dame el dinero', 'Quiero pagar ya', 'Adiós camarero'],
                'correct_answer' => 'La cuenta, por favor',
                'explanation_ar' => 'العبارة المعيارية والمهذبة لطلب الفاتورة في إسبانيا هي "La cuenta, por favor".',
                'points' => 10,
                'order_index' => 21,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'practical',
                'question_text' => 'Si una persona te dice "Muchas gracias por tu ayuda", ¿qué respondes?',
                'prompt_ar' => 'إذا قال لك شخص شكراً جزيلاً لمساعدتك، فما هو الرد اللبق المناسب؟',
                'audio_url' => null,
                'options' => ['De nada', 'Por favor', 'Perdón', 'Lo siento'],
                'correct_answer' => 'De nada',
                'explanation_ar' => 'الرد الطبيعي والمهذب على الشكر (Gracias) هو دائماً "De nada" (عفواً / على الرحب والسعة).',
                'points' => 10,
                'order_index' => 22,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'practical',
                'question_text' => 'Estás perdido en Madrid. ¿Cómo preguntas por la estación de metro más cercana?',
                'prompt_ar' => 'أنت تائه في مدريد، كيف تسأل عن أقرب محطة مترو؟',
                'audio_url' => null,
                'options' => [
                    '¿Dónde está la estación de metro más cercana?',
                    '¿Qué hora es en el metro?',
                    '¿Cuánto cuesta un billete de avión?',
                    'Quiero un café con leche'
                ],
                'correct_answer' => '¿Dónde está la estación de metro más cercana?',
                'explanation_ar' => 'السؤال عن المكان يبدأ بـ "¿Dónde está...?" ومعنى más cercana الأقرب.',
                'points' => 10,
                'order_index' => 23,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'practical',
                'question_text' => 'En una tienda de ropa, quieres saber el precio de una chaqueta. ¿Qué dices?',
                'prompt_ar' => 'في محل ملابس، كيف تسأل عن سعر السترة؟',
                'audio_url' => null,
                'options' => [
                    '¿Cuánto cuesta esta chaqueta?',
                    '¿Dónde vive la chaqueta?',
                    '¿Cómo se llama la chaqueta?',
                    'Tengo frío'
                ],
                'correct_answer' => '¿Cuánto cuesta esta chaqueta?',
                'explanation_ar' => 'السؤال عن السعر في الإسبانية هو "¿Cuánto cuesta...?".',
                'points' => 10,
                'order_index' => 24,
            ],
        ];

        foreach ($questionsA1 as $q) {
            LevelExamQuestion::updateOrCreate(
                [
                    'cefr_level' => $q['cefr_level'],
                    'question_text' => $q['question_text'],
                ],
                $q
            );
        }

        // Also seed Level A2 questions (12 questions)
        $questionsA2 = [
            [
                'cefr_level' => 'A2',
                'skill_category' => 'vocabulary',
                'question_text' => '¿Qué significa la palabra "aduana" en un aeropuerto?',
                'prompt_ar' => 'ما معنى كلمة aduana في المطار؟',
                'audio_url' => null,
                'options' => ['جمارك', 'بوابة الصعود', 'مكتب الاستعلامات', 'صرف العملات'],
                'correct_answer' => 'جمارك',
                'explanation_ar' => 'Aduana تعني الجمارك ونقاط التفتيش الحدودية.',
                'points' => 10,
                'order_index' => 1,
            ],
            [
                'cefr_level' => 'A2',
                'skill_category' => 'verbs',
                'question_text' => 'Completa en pretérito indefinido: "Ayer yo _____ (visitar) el museo del Prado."',
                'prompt_ar' => 'صرف الفعل في الماضي البسيط مع Yo:',
                'audio_url' => null,
                'options' => ['visité', 'visitó', 'visitaba', 'he visitado'],
                'correct_answer' => 'visité',
                'explanation_ar' => 'في الماضي البسيط Indefinido مع Ayer و Yo ينتهي بـ é (visité).',
                'points' => 10,
                'order_index' => 2,
            ],
            [
                'cefr_level' => 'A2',
                'skill_category' => 'grammar',
                'question_text' => 'Elige el conector correcto: "Estudió mucho _____ aprobó el examen con éxito."',
                'prompt_ar' => 'اختر الرابط المنطقي (لذلك / علشان كده):',
                'audio_url' => null,
                'options' => ['por eso', 'sin embargo', 'aunque', 'pero'],
                'correct_answer' => 'por eso',
                'explanation_ar' => 'Por eso تعني "لذلك" وتدل على النتيجة المنطقية.',
                'points' => 10,
                'order_index' => 3,
            ],
            [
                'cefr_level' => 'A2',
                'skill_category' => 'practical',
                'question_text' => 'En el hotel, quieres una habitación para dos personas con vista al mar. ¿Cómo lo pides?',
                'prompt_ar' => 'كيف تطلب غرفة مزدوجة بإطلالة على البحر؟',
                'audio_url' => null,
                'options' => [
                    'Quisiera una habitación doble con vistas al mar',
                    'Quiero un billete de tren al mar',
                    'La cuenta por favor',
                    'Estoy cansado'
                ],
                'correct_answer' => 'Quisiera una habitación doble con vistas al mar',
                'explanation_ar' => 'صيغة Quisiera هي الأكثر تهذيباً، و habitación doble تعني غرفة مزدوجة لشخصين.',
                'points' => 10,
                'order_index' => 4,
            ]
        ];

        foreach ($questionsA2 as $q) {
            LevelExamQuestion::updateOrCreate(
                [
                    'cefr_level' => $q['cefr_level'],
                    'question_text' => $q['question_text'],
                ],
                $q
            );
        }

        $this->command->info("Seeded balanced Level Exam Questions pool successfully (A1: 24 questions, A2: 4 questions).");
    }
}
