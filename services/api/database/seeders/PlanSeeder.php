<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free',
                'name_ar' => 'الباقة المجانية الاستكشافية',
                'name_en' => 'Free Starter',
                'description_ar' => 'لبدء التعلم واكتشاف أساسيات اللغة الإسبانية واختبار تحديد المستوى',
                'description_en' => 'Explore the basics, take the placement test, and start your journey',
                'price_egp' => 0,
                'price_usd' => 0,
                'billing_cycle' => 'free',
                'features_ar' => [
                    'اختبار تحديد المستوى الدقيق وتحليل نقاط الضعف',
                    'أول 3 وحدات في المستوى التأسيسي A1',
                    'تحدي الـ 3 دقائق الصباحي وبطاقة الإنجاز اليومية',
                    'مراجعة المفردات بنظام التكرار المتباعد (SRS محدود)',
                    'موقف محادثة صوتي واحد يومياً مع المعلم الذكي',
                ],
                'features_en' => [
                    'Comprehensive CEFR placement test & diagnostic report',
                    'First 3 units in Foundation A1 level',
                    'Daily 3-minute sprint & shareable streak cards',
                    'Spaced repetition flashcards (limited deck)',
                    '1 AI conversational roleplay scenario per day',
                ],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ],
            [
                'slug' => 'pro_monthly',
                'name_ar' => 'الباقة الاحترافية (شهري)',
                'name_en' => 'Pro Monthly',
                'description_ar' => 'الخيار الأوسع انتشاراً لتحقيق الطلاقة والتأهيل لوظائف وسياحة إسبانيا',
                'description_en' => 'Full access to all levels, unlimited roleplays, and career tracks',
                'price_egp' => 149.00,
                'price_usd' => 9.99,
                'billing_cycle' => 'monthly',
                'features_ar' => [
                    'فتح كافة المستويات التعليمية من A1 حتى C2 كاملاً',
                    'محاكي المواقف الحية بالذكاء الاصطناعي (غير محدود)',
                    'تحليل النطق الصوتي وتصحيح مخارج الحروف القشتالية',
                    'دفتر اليوميات الإسبانية وتصحيح الأخطاء الكتابية المستمر',
                    'مسار إسبانية كرة القدم (الكلاسيكو) والتراث الأندلسي',
                    'اختبارات الترقية والشهادات الرقمية لكل مستوى',
                    'دعم الدفع بفودافون كاش وميزة والبطاقات البنكية',
                ],
                'features_en' => [
                    'Full unlock of all CEFR levels (A1 through C2)',
                    'Unlimited interactive AI roleplay simulations',
                    'Native Castilian speech recognition & pronunciation scoring',
                    'Interactive Spanish journal with grammar feedback',
                    'Football (El Clásico) and Andalusian cultural tracks',
                    'Official in-app CEFR exams & digital completion certificates',
                    'Support for local wallets, Meeza, and international cards',
                ],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'pro_annual',
                'name_ar' => 'الباقة الاحترافية السنوية (وفر شهرين)',
                'name_en' => 'Pro Annual (Best Value)',
                'description_ar' => 'استثمارك الأذكى لإتقان تام على مدار العام مع توفير شهرين كاملين',
                'description_en' => '12 full months of learning for the price of 10. Maximum commitment.',
                'price_egp' => 990.00,
                'price_usd' => 69.99,
                'billing_cycle' => 'annual',
                'features_ar' => [
                    'كل ميزات الباقة الاحترافية Pro لمدة 12 شهراً كاملاً',
                    'توفير شهرين مجاناً مقارنة بالدفع الشهري',
                    'مسار خاص للتحضير لامتحانات DELE و SIELE الرسمية',
                    'مسار محاكاة مقابلات عمل الكول سنتر الإسباني (BPO Hiring)',
                    'شارة المشترك الملكي VIP في الملف الشخصي وبطاقات الإنجاز',
                    'أولوية الدعم الفني والمجتمع الخاص للمتعلمين',
                ],
                'features_en' => [
                    'All Pro features for 12 complete months',
                    '2 months free compared to monthly billing',
                    'Official DELE / SIELE exam preparation track',
                    'Spanish Call Center (BPO) job interview simulator',
                    'VIP badge in profile and viral achievement cards',
                    'Priority support and exclusive learner community access',
                ],
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
