import React from 'react';
import { Compass, Heart } from 'lucide-react';

export const Footer: React.FC = () => {
  return (
    <footer className="bg-stone-900 text-stone-300 py-12 border-t border-stone-800">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white">
                <Compass className="w-5 h-5 text-amber-300" />
              </div>
              <span className="font-bold text-lg text-white">Ruta Español</span>
            </div>
            <p className="text-sm text-stone-400 leading-relaxed">
              منصة تعليمية عربية احترافية مبنية طبقاً للإطار الأوروبي المرجعي المشترك للغات (CEFR) لتمكين المتعلمين من إتقان اللغة الإسبانية بثقة وسهولة.
            </p>
          </div>

          <div>
            <h4 className="font-bold text-white text-sm mb-3">المستويات المعتمدة</h4>
            <ul className="space-y-2 text-sm text-stone-400">
              <li>A1 - مبتدئ أول (الأساسيات)</li>
              <li>A2 - ابتدائي (التواصل اليومي)</li>
              <li>B1 - متوسط (الاستقلالية اللغوية)</li>
              <li>B2 - فوق المتوسط (الطلاقة المهنية)</li>
              <li>C1 & C2 - متقدم وإتقان</li>
            </ul>
          </div>

          <div>
            <h4 className="font-bold text-white text-sm mb-3">أدوات التعلم</h4>
            <ul className="space-y-2 text-sm text-stone-400">
              <li>اختبار تحديد المستوى التكيفي</li>
              <li>محرك التكرار المتباعد (SM-2)</li>
              <li>القصص التفاعلية في شوارع مدريد</li>
              <li>معلم الذكاء الاصطناعي الصوتي</li>
            </ul>
          </div>

          <div>
            <h4 className="font-bold text-white text-sm mb-3">الخصوصية والأمان</h4>
            <p className="text-xs text-stone-400 leading-relaxed mb-3">
              بياناتك مشفرة بالكامل. لا نقوم بتسجيل أو تخزين المقاطع الصوتية الحساسة دون موافقتك الصريحة.
            </p>
            <div className="text-xs text-stone-500">
              الإصدار: 1.0.0 (Sanctum + Supabase Ready)
            </div>
          </div>
        </div>

        <div className="pt-8 border-t border-stone-800 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500 gap-4">
          <div>
            جميع الحقوق محفوظة © {new Date().getFullYear()} Ruta Español.
          </div>
          <div className="flex items-center gap-1">
            صُنع بعناية للمتعلمين في مصر والعالم العربي
            <Heart className="w-3.5 h-3.5 text-red-500 fill-red-500 inline ml-1" />
          </div>
        </div>
      </div>
    </footer>
  );
};
