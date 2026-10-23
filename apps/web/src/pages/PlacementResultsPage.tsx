import React, { useEffect, useState } from 'react';
import { useLocation, useNavigate, Link } from 'react-router-dom';
import { placementApi } from '../api/placement';
import { PlacementAttempt } from '../types';
import { Navbar } from '../components/Navbar';
import {
  Compass,
  Award,
  CheckCircle2,
  TrendingUp,
  ArrowRight,
  BookOpen,
  Sparkles,
  RefreshCw
} from 'lucide-react';
import confetti from 'canvas-confetti';

export const PlacementResultsPage: React.FC = () => {
  const location = useLocation();
  const navigate = useNavigate();

  const [attempt, setAttempt] = useState<PlacementAttempt | null>(location.state?.attempt || null);
  const [isLoading, setIsLoading] = useState(!attempt);

  useEffect(() => {
    if (!attempt) {
      loadLatestHistory();
    } else {
      confetti({ particleCount: 70, spread: 60, origin: { y: 0.6 } });
    }
  }, []);

  const loadLatestHistory = async () => {
    setIsLoading(true);
    try {
      const data = await placementApi.getHistory();
      if (data.attempts && data.attempts.length > 0) {
        setAttempt(data.attempts[0]);
        confetti({ particleCount: 70, spread: 60, origin: { y: 0.6 } });
      }
    } catch (err) {
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col items-center justify-center gap-3">
        <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
        <p className="text-sm font-semibold text-stone-600">جاري تحميل نتائج الاختبار...</p>
      </div>
    );
  }

  if (!attempt) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col items-center justify-center space-y-4">
        <p className="text-stone-700 font-semibold">لم يتم العثور على نتائج اختبار سابقة.</p>
        <Link to="/placement-test" className="px-6 py-2.5 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm">
          أجرِ الاختبار الآن
        </Link>
      </div>
    );
  }

  const level = attempt.estimated_level || 'A1';
  const breakdown = attempt.skill_breakdown || {};

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <main className="flex-1 max-w-4xl mx-auto w-full px-4 sm:px-6 py-10 space-y-8">
        {/* Header Celebration Card */}
        <div className="bg-gradient-to-br from-[#1B4D3E] to-[#12362b] text-white rounded-3xl p-8 sm:p-10 shadow-xl text-center space-y-4 relative overflow-hidden">
          <div className="w-16 h-16 rounded-2xl bg-amber-400 text-stone-950 flex items-center justify-center mx-auto shadow-md">
            <Award className="w-9 h-9" />
          </div>

          <span className="inline-block px-3.5 py-1 rounded-full bg-emerald-700/60 text-emerald-100 text-xs font-bold border border-emerald-500/40">
            اكتمل التقييم التشخيصي بنجاح
          </span>

          <h1 className="text-3xl sm:text-4xl font-black">
            مستواك المقدر: <span className="text-amber-300 font-black">{level}</span>
          </h1>

          <p className="text-emerald-100 max-w-xl mx-auto text-sm sm:text-base leading-relaxed">
            {attempt.summary_report_ar}
          </p>

          <div className="pt-2 flex items-center justify-center gap-4 text-xs text-emerald-200">
            <span>الدرجة الكلية: {attempt.total_score}%</span>
            <span>•</span>
            <span>+50 XP مكافأة الإكمال</span>
          </div>
        </div>

        {/* Skill Breakdown Grid */}
        <div className="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-stone-200/80 space-y-6">
          <h2 className="text-lg font-bold text-stone-900 flex items-center gap-2">
            <TrendingUp className="w-5 h-5 text-[#1B4D3E]" />
            <span>تحليل أداء المهارات التفصيلي</span>
          </h2>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
            {[
              { key: 'vocabulary', label: 'المفردات والثروة اللغوية', color: 'bg-emerald-500' },
              { key: 'grammar', label: 'القواعد وتصريف الأفعال', color: 'bg-teal-500' },
              { key: 'reading', label: 'الاستيعاب وفهم المقروء', color: 'bg-blue-500' },
            ].map((sk) => {
              const data = breakdown[sk.key] || { percentage: 0, correct: 0, count: 0 };
              return (
                <div key={sk.key} className="p-4 rounded-2xl bg-stone-50 border border-stone-200/60 space-y-3">
                  <div className="flex items-center justify-between text-xs font-bold text-stone-600">
                    <span>{sk.label}</span>
                    <span className="text-sm font-black text-stone-900">{data.percentage}%</span>
                  </div>

                  <div className="w-full bg-stone-200 h-2.5 rounded-full overflow-hidden">
                    <div
                      className={`${sk.color} h-full transition-all duration-500`}
                      style={{ width: `${data.percentage}%` }}
                    />
                  </div>

                  <div className="text-[11px] text-stone-400">
                    أجبت بشكل صحيح على {data.correct} من أصل {data.count} سؤال
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Recommended Starting Point Card */}
        <div className="bg-amber-50/80 border border-amber-200/80 rounded-3xl p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-2xs">
          <div className="space-y-2 text-right">
            <div className="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800">
              <Sparkles className="w-4 h-4 text-amber-600" />
              <span>نقطة البداية الموصى بها</span>
            </div>
            <h3 className="text-xl font-bold text-stone-900">
              {attempt.recommended_unit?.title_ar || 'الوحدة الأولى: التحيات والتعارف'}
            </h3>
            <p className="text-xs text-stone-600 max-w-lg leading-relaxed">
              تمت ملاءمة هذه الوحدة خصيصاً لسد الفجوات اللغوية التي تم رصدها خلال الاختبار وتثبيت مهاراتك.
            </p>
          </div>

          <div className="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <Link
              to="/learn"
              className="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] transition-all flex items-center justify-center gap-2"
            >
              <span>ابدأ الوحدة الموصى بها</span>
              <ArrowRight className="w-4 h-4 rotate-180" />
            </Link>

            <Link
              to="/learn"
              className="w-full sm:w-auto px-4 py-3.5 rounded-xl bg-white border border-stone-200 text-stone-700 font-bold text-xs hover:bg-stone-50 transition-colors text-center"
            >
              البدء من الصفر (A1)
            </Link>
          </div>
        </div>
      </main>
    </div>
  );
};
