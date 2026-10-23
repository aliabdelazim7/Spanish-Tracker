<?php

namespace Database\Seeders;

use App\Models\PlacementQuestion;
use Illuminate\Database\Seeder;

class PlacementQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'skill_category' => 'vocabulary',
                'difficulty_level' => 'A1',
                'question_type' => 'multiple_choice',
                'question_text' => '¿Cuál de las siguientes palabras significa "شكراً" en español?',
                'context_text' => null,
                'prompt_ar' => 'أي من الكلمات التالية تعني "شكراً" بالإسبانية؟',
                'audio_url' => null,
                'options' => ['Gracias', 'Por favor', 'De nada', 'Perdón'],
                'correct_answer' => 'Gracias',
                'explanation_ar' => 'Gracias تعني شكراً، بينما Por favor تعني من فضلك، وDe nada تعني عفواً.',
                'points' => 10,
            ],
            [
                'skill_category' => 'grammar',
                'difficulty_level' => 'A1',
                'question_type' => 'multiple_choice',
                'question_text' => 'Completa: Hoy nosotros ___ en la biblioteca para estudiar.',
                'context_text' => null,
                'prompt_ar' => 'اختر التصريف الصحيح للتعبير عن التواجد في مكان (نحن في المكتبة):',
                'audio_url' => null,
                'options' => ['estamos', 'somos', 'estáis', 'son'],
                'correct_answer' => 'estamos',
                'explanation_ar' => 'للتعبير عن التواجد في مكان نستخدم فعل Estar وتصريفه مع Nosotros هو estamos.',
                'points' => 10,
            ],
            [
                'skill_category' => 'grammar',
                'difficulty_level' => 'A1',
                'question_type' => 'multiple_choice',
                'question_text' => '¿Cuál es el artículo correcto para la palabra "problema"?',
                'context_text' => null,
                'prompt_ar' => 'ما هي أداة التعريف الصحيحة لكلمة "problema"؟',
                'audio_url' => null,
                'options' => ['El', 'La', 'Los', 'Las'],
                'correct_answer' => 'El',
                'explanation_ar' => 'كلمة problema مذكرة رغم أنها تنتهي بحرف a لأن أصلها يوناني: El problema.',
                'points' => 10,
            ],
            [
                'skill_category' => 'grammar',
                'difficulty_level' => 'A2',
                'question_type' => 'multiple_choice',
                'question_text' => 'A mí no me ___ los lunes porque tengo mucho trabajo.',
                'context_text' => null,
                'prompt_ar' => 'اختر صيغة فعل Gustar الصحيحة (أيام الاثنين لا تعجبني):',
                'audio_url' => null,
                'options' => ['gustan', 'gusta', 'gusto', 'gustas'],
                'correct_answer' => 'gustan',
                'explanation_ar' => 'نستخدم gustan بصيغة الجمع لأن الفاعل هو los lunes (أيام الاثنين - جمع).',
                'points' => 10,
            ],
            [
                'skill_category' => 'vocabulary',
                'difficulty_level' => 'A2',
                'question_type' => 'multiple_choice',
                'question_text' => 'El hermano de mi padre es mi ___.',
                'context_text' => null,
                'prompt_ar' => 'شقيق والدي هو ... (قرابة عائلية):',
                'audio_url' => null,
                'options' => ['tío', 'abuelo', 'primo', 'sobrino'],
                'correct_answer' => 'tío',
                'explanation_ar' => 'Tío تعني العم أو الخال. Primo تعني ابن العم، وAbuelo تعني الجد.',
                'points' => 10,
            ],
            [
                'skill_category' => 'reading',
                'difficulty_level' => 'A2',
                'question_type' => 'reading_comprehension',
                'question_text' => '¿A qué hora y dónde va a almorzar María?',
                'context_text' => "Hola Pedro, hoy salgo del trabajo a las dos de la tarde. Voy a comer con mis compañeros en un restaurante cerca de la Puerta del Sol porque preparan una paella riquísima. ¿Te apetece venir con nosotros?",
                'prompt_ar' => 'اقرأ الرسالة السابقة، أين وفي أي ساعة ستتناول ماريا الغداء؟',
                'audio_url' => null,
                'options' => [
                    'A las dos en un restaurante cerca de la Puerta del Sol',
                    'A las tres en su casa',
                    'A la una en la oficina',
                    'A las ocho en una cafetería'
                ],
                'correct_answer' => 'A las dos en un restaurante cerca de la Puerta del Sol',
                'explanation_ar' => 'ذكرت ماريا: "salgo del trabajo a las dos... voy a comer... en un restaurante cerca de la Puerta del Sol".',
                'points' => 15,
            ],
            [
                'skill_category' => 'grammar',
                'difficulty_level' => 'B1',
                'question_type' => 'multiple_choice',
                'question_text' => 'Ayer Juan no ___ a clase porque estaba enfermo.',
                'context_text' => null,
                'prompt_ar' => 'اختر التصريف الصحيح في الماضي البسيط (Ayer):',
                'audio_url' => null,
                'options' => ['fue', 'va', 'ha ido', 'iba'],
                'correct_answer' => 'fue',
                'explanation_ar' => 'كلمة Ayer تدل على الماضي البسيط التام (Pretérito Indefinido)، وتصريف فعل ir مع Juan هو fue.',
                'points' => 15,
            ],
            [
                'skill_category' => 'grammar',
                'difficulty_level' => 'B1',
                'question_type' => 'multiple_choice',
                'question_text' => 'Espero que mañana no ___ tanto calor.',
                'context_text' => null,
                'prompt_ar' => 'اختر صيغة الفعل المناسبة بعد فعل التمني (Espero que):',
                'audio_url' => null,
                'options' => ['haga', 'hace', 'hará', 'hacía'],
                'correct_answer' => 'haga',
                'explanation_ar' => 'بعد أفعال التمني والرجاء مثل esperar que، نستخدم دائماً صيغة الشك (Subjuntivo): haga.',
                'points' => 20,
            ],
        ];

        foreach ($questions as $q) {
            PlacementQuestion::updateOrCreate(
                ['question_text' => $q['question_text']],
                $q
            );
        }
    }
}
