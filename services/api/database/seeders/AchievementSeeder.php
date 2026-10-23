<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'code' => 'first_lesson',
                'title_ar' => 'الخطوة الأولى',
                'description_ar' => 'أكملت أول درس بنجاح وبدأت رحلتك الإسبانية.',
                'icon' => 'compass',
                'badge_category' => 'learning',
                'xp_reward' => 50,
                'threshold' => 1,
            ],
            [
                'code' => 'streak_3',
                'title_ar' => 'شعلة الالتزام',
                'description_ar' => 'حافظت على حماسك ودرست لـ 3 أيام متتالية!',
                'icon' => 'flame',
                'badge_category' => 'streak',
                'xp_reward' => 75,
                'threshold' => 3,
            ],
            [
                'code' => 'streak_7',
                'title_ar' => 'أسبوع من الإصرار',
                'description_ar' => 'أتممت 7 أيام متتالية من التعلم المنتظم دون انقطاع.',
                'icon' => 'trophy',
                'badge_category' => 'streak',
                'xp_reward' => 150,
                'threshold' => 7,
            ],
            [
                'code' => 'vocab_10',
                'title_ar' => 'جامع الكلمات',
                'description_ar' => 'حفظت أول 10 كلمات إسبانية في ذاكرتك الدائمة.',
                'icon' => 'book-open',
                'badge_category' => 'vocabulary',
                'xp_reward' => 60,
                'threshold' => 10,
            ],
            [
                'code' => 'story_reader',
                'title_ar' => 'قارئ القصص',
                'description_ar' => 'أنهيت أول قصة تفاعلية واستمتعت بالأحداث.',
                'icon' => 'sparkles',
                'badge_category' => 'stories',
                'xp_reward' => 50,
                'threshold' => 1,
            ],
            [
                'code' => 'placement_done',
                'title_ar' => 'مستكشف المستوى',
                'description_ar' => 'أجريت اختبار تحديد المستوى وحصلت على خطتك الشخصية.',
                'icon' => 'target',
                'badge_category' => 'placement',
                'xp_reward' => 50,
                'threshold' => 1,
            ],
        ];

        foreach ($badges as $b) {
            Achievement::updateOrCreate(
                ['code' => $b['code']],
                $b
            );
        }
    }
}
