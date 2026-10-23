import React, { useEffect, useState } from 'react';
import { dashboardApi } from '../api/dashboard';
import { Achievement } from '../types';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import {
  Compass,
  Award,
  Sparkles,
  TrendingUp,
  Flame,
  CheckCircle2,
  Lock,
  BookOpen,
  Calendar
} from 'lucide-react';

export const ProgressPage: React.FC = () => {
  const [skills, setSkills] = useState<{
    reading: number;
    vocabulary: number;
    grammar: number;
    listening: number;
    speaking: number;
  }>({ reading: 0, vocabulary: 0, grammar: 0, listening: 0, speaking: 0 });

  const [achievements, setAchievements] = useState<Achievement[]>([]);
  const [recentXp, setRecentXp] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadProgress();
  }, []);

  const loadProgress = async () => {
    setIsLoading(true);
    try {
      const [progRes, achRes] = await Promise.all([
        dashboardApi.getProgress(),
        dashboardApi.getAchievements(),
      ]);
      setSkills(progRes.skills);
      setRecentXp(progRes.recent_xp || []);
      setAchievements(achRes.achievements || []);
    } catch (err) {
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
        <Navbar />
        <div className="flex-1 flex items-center justify-center gap-3">
          <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
          <p className="text-sm font-semibold text-stone-600">جاري تحميل الإحصائيات والأوسمة...</p>
        </div>
      </div>
    );
  }

  const skillCards = [
    { key: 'reading', label: 'القراءة وفهم النصوص', value: skills.reading, color: 'bg-emerald-500' },
    { key: 'vocabulary', label: 'المفردات والثروة اللغوية', value: skills.vocabulary, color: 'bg-teal-500' },
    { key: 'grammar', label: 'القواعد وتصريف الأفعال', value: skills.grammar, color: 'bg-blue-500' },
    { key: 'listening', label: 'الاستماع والتمييز الصوتي', value: skills.listening, color: 'bg-purple-500' },
    { key: 'speaking', label: 'المحادثة والنطق الصوتي', value: skills.speaking, color: 'bg-orange-500' },
  ];

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8 max-w-5xl w-full pb-24 md:pb-8">
          <div className="space-y-1">
            <h1 className="text-3xl font-black text-stone-900">
              إحصائيات التقدم والأوسمة
            </h1>
            <p className="text-xs sm:text-sm text-stone-600">
              تتبع نمو مهاراتك اللغوية وسجل إنجازاتك اليومية المحققة.
            </p>
          </div>

          {/* Skills Breakdown */}
          <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs space-y-6">
            <h2 className="text-lg font-bold text-stone-900 flex items-center gap-2">
              <TrendingUp className="w-5 h-5 text-[#1B4D3E]" />
              <span>مؤشرات الكفاءة حسب المهارات</span>
            </h2>

            <div className="space-y-4">
              {skillCards.map((sk) => (
                <div key={sk.key} className="space-y-1.5">
                  <div className="flex items-center justify-between text-xs font-bold text-stone-700">
                    <span>{sk.label}</span>
                    <span className="text-stone-900">{sk.value}%</span>
                  </div>
                  <div className="w-full bg-stone-100 h-3 rounded-full overflow-hidden">
                    <div
                      className={`${sk.color} h-full transition-all duration-500`}
                      style={{ width: `${sk.value}%` }}
                    />
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Achievements Gallery */}
          <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs space-y-6">
            <h2 className="text-lg font-bold text-stone-900 flex items-center gap-2">
              <Award className="w-5 h-5 text-amber-500" />
              <span>أوسمة الإنجاز والتحفيز</span>
            </h2>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {achievements.map((ach) => {
                const isUnlocked = ach.is_unlocked;

                return (
                  <div
                    key={ach.id}
                    className={`p-5 rounded-2xl border-2 transition-all flex flex-col justify-between space-y-3 ${
                      isUnlocked
                        ? 'border-amber-300 bg-amber-50/50 shadow-xs'
                        : 'border-stone-200 bg-stone-50/50 opacity-60'
                    }`}
                  >
                    <div className="space-y-2 text-right">
                      <div className="flex items-center justify-between">
                        <div
                          className={`w-10 h-10 rounded-xl flex items-center justify-center ${
                            isUnlocked ? 'bg-amber-400 text-stone-950 font-bold' : 'bg-stone-200 text-stone-400'
                          }`}
                        >
                          {isUnlocked ? <Award className="w-5 h-5" /> : <Lock className="w-4 h-4" />}
                        </div>

                        <span className="text-xs font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full">
                          +{ach.xp_reward} XP
                        </span>
                      </div>

                      <div className="font-bold text-stone-900 text-sm">{ach.title_ar}</div>
                      <p className="text-xs text-stone-500 leading-relaxed">{ach.description_ar}</p>
                    </div>

                    <div className="text-[11px] font-semibold text-stone-400 pt-2 border-t border-stone-100">
                      {isUnlocked ? (
                        <span className="text-emerald-700 flex items-center gap-1">
                          <CheckCircle2 className="w-3.5 h-3.5" />
                          <span>تم الحصول عليه</span>
                        </span>
                      ) : (
                        'قيد الإنجاز'
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};
