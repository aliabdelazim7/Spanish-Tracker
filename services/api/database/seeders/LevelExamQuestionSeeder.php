<?php

namespace Database\Seeders;

use App\Models\LevelExamQuestion;
use Illuminate\Database\Seeder;

class LevelExamQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questionsA1 = [
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
                'skill_category' => 'grammar',
                'question_text' => 'Completa la frase con el verbo correcto: "Yo _____ estudiante de español."',
                'prompt_ar' => 'اختر التصريف الصحيح لفعل الكينونة الدائم مع الضمير Yo:',
                'audio_url' => null,
                'options' => ['eres', 'somos', 'soy', 'es'],
                'correct_answer' => 'soy',
                'explanation_ar' => 'تصريف الفعل Ser مع ضمير المتكلم Yo هو siempre "soy" (Yo soy = أنا أكون).',
                'points' => 10,
                'order_index' => 2,
            ],
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
                'order_index' => 3,
            ],
            [
                'cefr_level' => 'A1',
                'skill_category' => 'grammar',
                'question_text' => '¿Cuál es la diferencia? "La farmacia _____ en la esquina."',
                'prompt_ar' => 'اختر الفعل الصحيح للتعبير عن موقع ومكان الصيدلية:',
                'audio_url' => null,
                'options' => ['es', 'está', 'son', 'hay'],
                'correct_answer' => 'está',
                'explanation_ar' => 'لتحديد مكان أو موقع شيء معرف ومحدد نستخدم فعل Estar (La farmacia está...).',
                'points' => 10,
                'order_index' => 4,
            ],
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
                'order_index' => 5,
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
                'order_index' => 6,
            ],
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
                'order_index' => 7,
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
                'order_index' => 8,
            ],
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
                'order_index' => 9,
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
                'order_index' => 10,
            ],
        ];

        foreach ($questionsA1 as $q) {
            LevelExamQuestion::updateOrCreate(
                [
                    'cefr_level' => $q['cefr_level'],
                    'order_index' => $q['order_index'],
                ],
                $q
            );
        }

        $this->command->info("Seeded Level Exam Questions for A1 successfully.");
    }
}
