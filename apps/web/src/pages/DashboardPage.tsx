import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { dashboardApi, DashboardData } from '../api/dashboard';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import {
  Compass,
  Flame,
  Sparkles,
  BookOpen,
  RotateCcw,
  BookMarked,
  Mic,
  ArrowRight,
  CheckCircle,
  TrendingUp,
  Clock,
  Award,
  PenTool,
  AlertTriangle
} from 'lucide-react';

export const DashboardPage: React.FC = () => {
  const [data, setData] = useState<DashboardData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchDashboard = async () => {
    setIsLoading(true);
    setError(null);
    try {
      const res = await dashboardApi.getDashboard();
      setData(res);
    } catch (err: any) {
      setError(err.message || 'تعذر تحميل بيانات لوحة التحكم');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchDashboard();
  }, []);

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
        <Navbar />
        <div className="flex-1 flex items-center justify-center gap-3">
          <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
          <p className="text-sm font-semibold text-stone-600">جاري تحميل بياناتك التعليمية...</p>
        </div>
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
        <Navbar />
        <div className="flex-1 flex flex-col items-center justify-center space-y-4">
          <p className="text-sm text-red-600">{error || 'حدث خطأ ما'}</p>
          <button
            onClick={fetchDashboard}
            className="px-5 py-2.5 rounded-xl bg-[#1B4D3E] text-white text-xs font-bold"
          >
            إعادة المحاولة
          </button>
        </div>
      </div>
    );
  }

  const { profile, stats, recommended_lesson, recent_activity } = data;

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        {/* Sidebar */}
        <Sidebar />

        {/* Main Dashboard Content */}
        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8 max-w-5xl w-full pb-24 md:pb-8">
          {/* Welcome Banner */}
          <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs">
            <div className="space-y-1.5">
              <div className="flex items-center gap-2">
                <h1 className="text-2xl sm:text-3xl font-black text-stone-900">
                  ¡Hola! أهلاً بك
                </h1>
                <span className="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-900 font-bold border border-emerald-300">
                  مستوى {stats.estimated_level}
                </span>
              </div>
              <p className="text-sm text-stone-500">
                هدفك اليومي: {stats.daily_goal_minutes} دقيقة • واصل مسيرتك لتنمية حصيلتك اللغوية!
              </p>
            </div>

            {/* Quick Streaks Card */}
            <div className="flex items-center gap-4 bg-orange-50 border border-orange-200/80 px-4 py-3 rounded-2xl">
              <div className="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center">
                <Flame className="w-6 h-6 fill-white" />
              </div>
              <div>
                <div className="text-xl font-black text-orange-900">{stats.current_streak} أيام</div>
                <div className="text-[11px] text-orange-700 font-medium">سلسلة الالتزام</div>
              </div>
            </div>
          </div>

          {/* Key Metric Cards */}
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div className="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-2">
              <div className="flex items-center justify-between text-stone-400">
                <span className="text-xs font-semibold">مجموع النقاط</span>
                <Sparkles className="w-4 h-4 text-amber-500" />
              </div>
              <div className="text-2xl font-black text-[#1B4D3E]">{stats.total_xp} XP</div>
              <div className="text-[11px] text-stone-500">مكافآت الإنجاز والدروس</div>
            </div>

            <div className="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-2">
              <div className="flex items-center justify-between text-stone-400">
                <span className="text-xs font-semibold">الدروس المنجزة</span>
                <CheckCircle className="w-4 h-4 text-emerald-600" />
              </div>
              <div className="text-2xl font-black text-stone-900">{stats.lessons_completed_count}</div>
              <div className="text-[11px] text-stone-500">منهج CEFR المعتمد</div>
            </div>

            <div className="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-2">
              <div className="flex items-center justify-between text-stone-400">
                <span className="text-xs font-semibold">المفردات المحفوظة</span>
                <RotateCcw className="w-4 h-4 text-blue-500" />
              </div>
              <div className="text-2xl font-black text-stone-900">{stats.vocabulary_count}</div>
              <div className="text-[11px] text-stone-500">
                {stats.due_reviews_count > 0 ? (
                  <span className="text-orange-600 font-bold">{stats.due_reviews_count} كلمة مستحقة للمراجعة</span>
                ) : (
                  'لا توجد مراجعات متأخرة'
                )}
              </div>
            </div>

            <div className="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-2">
              <div className="flex items-center justify-between text-stone-400">
                <span className="text-xs font-semibold">القصص التفاعلية</span>
                <BookMarked className="w-4 h-4 text-purple-500" />
              </div>
              <div className="text-2xl font-black text-stone-900">{stats.stories_completed_count}</div>
              <div className="text-[11px] text-stone-500">قصص واقعية في إسبانيا</div>
            </div>
          </div>

          {/* Primary Recommended Lesson Action Card */}
          {recommended_lesson && (
            <div className="bg-gradient-to-r from-emerald-900 to-[#1B4D3E] text-white rounded-3xl p-6 sm:p-8 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
              <div className="space-y-2 text-right">
                <span className="inline-block px-3 py-1 rounded-full bg-emerald-800/80 text-emerald-200 text-xs font-bold border border-emerald-600/40">
                  خطوتك التالية الموصى بها
                </span>
                <h2 className="text-2xl font-black">
                  {recommended_lesson.title_ar}
                </h2>
                <p className="text-xs sm:text-sm text-emerald-100 max-w-xl leading-relaxed">
                  {recommended_lesson.objective_ar}
                </p>
                <div className="text-xs text-emerald-300 font-medium pt-1">
                  المدة المتوقعة: {recommended_lesson.estimated_minutes} دقائق • الجائزة: +{recommended_lesson.xp_reward} XP
                </div>
              </div>

              <Link
                to={`/learn/lessons/${recommended_lesson.id}`}
                className="w-full sm:w-auto px-8 py-4 rounded-2xl bg-amber-400 text-stone-950 font-bold text-sm shadow-lg hover:bg-amber-300 hover:scale-105 transition-all flex items-center justify-center gap-2 flex-shrink-0"
              >
                <span>ابدأ الدرس الآن</span>
                <ArrowRight className="w-4 h-4 rotate-180" />
              </Link>
            </div>
          )}

          {/* Quick Learning Hub Grid */}
          <div className="space-y-4">
            <h3 className="text-lg font-bold text-stone-900">مسارات ومميزات التعلم الذكي</h3>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <Link
                to="/journal"
                className="p-5 rounded-2xl bg-white border border-stone-200 hover:border-emerald-400 hover:shadow-sm transition-all space-y-2 group"
              >
                <div className="w-10 h-10 rounded-xl bg-emerald-100 text-[#1B4D3E] flex items-center justify-center group-hover:scale-105 transition-transform">
                  <PenTool className="w-5 h-5" />
                </div>
                <div className="font-bold text-stone-900 text-sm">دفتر اليوميات الإسباني</div>
                <p className="text-xs text-stone-500 leading-relaxed">
                  اكتب عن يومك ودع المعلم الذكي يصحح لك ويصمم لك تمارين على أخطائك.
                </p>
              </Link>

              <Link
                to="/my-mistakes"
                className="p-5 rounded-2xl bg-white border border-stone-200 hover:border-emerald-400 hover:shadow-sm transition-all space-y-2 group"
              >
                <div className="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center group-hover:scale-105 transition-transform">
                  <AlertTriangle className="w-5 h-5" />
                </div>
                <div className="font-bold text-stone-900 text-sm">أخطائي وملاحظاتي</div>
                <p className="text-xs text-stone-500 leading-relaxed">
                  راجع الأخطاء الشائعة في كتابتك وتدرب عليها لترفع نسبة إتقانك.
                </p>
              </Link>

              <Link
                to="/review"
                className="p-5 rounded-2xl bg-white border border-stone-200 hover:border-emerald-400 hover:shadow-sm transition-all space-y-2 group"
              >
                <div className="w-10 h-10 rounded-xl bg-orange-100 text-orange-800 flex items-center justify-center group-hover:scale-105 transition-transform">
                  <RotateCcw className="w-5 h-5" />
                </div>
                <div className="font-bold text-stone-900 text-sm">مراجعة المفردات (SRS)</div>
                <p className="text-xs text-stone-500 leading-relaxed">
                  راجع كلماتك الذكية بأسلوب البطاقات التكرارية SuperMemo لتثبيتها في الذاكرة.
                </p>
                {stats.due_reviews_count > 0 && (
                  <span className="inline-block text-[11px] font-bold text-orange-700 bg-orange-50 px-2.5 py-0.5 rounded-full">
                    {stats.due_reviews_count} كلمات جاهزة
                  </span>
                )}
              </Link>

              <Link
                to="/stories"
                className="p-5 rounded-2xl bg-white border border-stone-200 hover:border-emerald-400 hover:shadow-sm transition-all space-y-2 group"
              >
                <div className="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center group-hover:scale-105 transition-transform">
                  <BookMarked className="w-5 h-5" />
                </div>
                <div className="font-bold text-stone-900 text-sm">قصص مدريد التفاعلية</div>
                <p className="text-xs text-stone-500 leading-relaxed">
                  اقرأ حوارات حية من شوارع ومقاهي ومطارات إسبانيا مع ترجمة فورية للكلمات.
                </p>
              </Link>
            </div>
          </div>

          {/* Recent Activity */}
          <div className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-2xs space-y-4">
            <h3 className="font-bold text-stone-900 text-base">نشاطك التعليمي الأخير</h3>
            {recent_activity && recent_activity.length > 0 ? (
              <div className="divide-y divide-stone-100">
                {recent_activity.map((att: any) => (
                  <div key={att.id} className="py-3 flex items-center justify-between text-xs">
                    <div className="space-y-0.5">
                      <div className="font-bold text-stone-900">{att.lesson?.title_ar}</div>
                      <div className="text-stone-400">{att.lesson?.unit?.title_ar}</div>
                    </div>
                    <div className="text-left space-y-0.5">
                      <div className="font-bold text-emerald-700">الدرجة: {att.score_percent}%</div>
                      <div className="text-stone-400">+{att.xp_awarded} XP</div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-6 text-stone-400 text-xs">
                لم تسجل أي دروس بعد. ابدأ درسك الأول اليوم لبناء سجل نشاطك!
              </div>
            )}
          </div>
        </main>
      </div>
    </div>
  );
};
