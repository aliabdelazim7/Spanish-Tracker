import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { dashboardApi, DashboardData } from '../api/dashboard';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import { useTranslation } from 'react-i18next';
import {
  Compass,
  Flame,
  Sparkles,
  BookOpen,
  RotateCcw,
  BookMarked,
  ArrowRight,
  CheckCircle,
  TrendingUp,
  Clock,
  Award,
  PenTool,
  AlertTriangle,
  Target,
  ChevronLeft,
  ChevronRight,
  Calendar,
  CheckCircle2,
  Lock,
  Layers,
  GraduationCap
} from 'lucide-react';

export const DashboardPage: React.FC = () => {
  const { t, i18n } = useTranslation();
  const isEn = i18n.language === 'en';

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
      setError(err.message || (isEn ? 'Failed to load dashboard data' : 'تعذر تحميل بيانات لوحة التحكم'));
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchDashboard();
  }, []);

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col font-sans">
        <Navbar />
        <div className="flex-1 flex items-center justify-center gap-3">
          <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
          <p className="text-sm font-semibold text-stone-600">
            {isEn ? 'Loading your learning progress...' : 'جاري تحميل بياناتك التعليمية...'}
          </p>
        </div>
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col font-sans">
        <Navbar />
        <div className="flex-1 flex flex-col items-center justify-center space-y-4">
          <p className="text-sm text-red-600">{error || (isEn ? 'Something went wrong' : 'حدث خطأ ما')}</p>
          <button
            type="button"
            onClick={fetchDashboard}
            className="px-5 py-2.5 rounded-xl bg-[#1B4D3E] text-white text-xs font-bold"
          >
            {isEn ? 'Retry' : 'إعادة المحاولة'}
          </button>
        </div>
      </div>
    );
  }

  const {
    profile,
    stats,
    course_progress,
    daily_goal,
    recent_assessments,
    weak_areas,
    recommended_lesson,
    recent_activity,
  } = data;

  const currentLevel = stats.estimated_level || 'A1';

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col font-sans" dir={isEn ? 'ltr' : 'rtl'}>
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        {/* Sidebar */}
        <Sidebar />

        {/* Main Dashboard Content */}
        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8 max-w-5xl w-full pb-24 md:pb-8">
          {/* Welcome Banner */}
          <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs">
            <div className="space-y-1.5 text-start">
              <div className="flex flex-wrap items-center gap-2">
                <h1 className="text-2xl sm:text-3xl font-black text-stone-900">
                  ¡Hola! {isEn ? 'Welcome back' : 'أهلاً بك'}
                </h1>
                <span className="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-900 font-bold border border-emerald-300">
                  {isEn ? `Level ${currentLevel}` : `المستوى ${currentLevel}`}
                </span>
                {stats.max_unlocked_level && stats.max_unlocked_level !== currentLevel && (
                  <span className="text-xs px-2.5 py-0.5 rounded-full bg-stone-100 text-stone-600 font-medium">
                    {isEn ? `Unlocked up to ${stats.max_unlocked_level}` : `مفتوح حتى ${stats.max_unlocked_level}`}
                  </span>
                )}
              </div>
              <p className="text-xs sm:text-sm text-stone-500">
                {isEn
                  ? `Daily goal: ${daily_goal?.goal_minutes || stats.daily_goal_minutes} mins • Build steady Spanish retention!`
                  : `هدفك اليومي: ${daily_goal?.goal_minutes || stats.daily_goal_minutes} دقيقة • واصل مسيرتك لتنمية حصيلتك اللغوية!`}
              </p>
            </div>

            {/* Quick Streaks Card backed by real activity */}
            <div className="flex items-center gap-3.5 bg-orange-50 border border-orange-200/80 px-4 py-3 rounded-2xl self-stretch sm:self-auto justify-center sm:justify-start">
              <div className="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center shrink-0">
                <Flame className="w-6 h-6 fill-white" />
              </div>
              <div className="text-start">
                <div className="text-xl font-black text-orange-900">
                  {stats.current_streak} {isEn ? 'Days' : 'أيام'}
                </div>
                <div className="text-[11px] text-orange-700 font-medium">
                  {isEn ? 'Daily Study Streak' : 'سلسلة الالتزام الحقيقية'}
                </div>
              </div>
            </div>
          </div>

          {/* Daily Goal & Level Course Progress Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            {/* Daily Learning Goal Card */}
            <div className="p-5 sm:p-6 rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-3 text-start">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <Target className="w-5 h-5 text-amber-500" />
                  <h3 className="font-bold text-sm text-stone-900">
                    {isEn ? 'Today\'s Learning Goal' : 'الهدف اليومي للتعلم'}
                  </h3>
                </div>
                <span className="text-xs font-bold text-stone-500">
                  {daily_goal ? `${daily_goal.today_minutes} / ${daily_goal.goal_minutes} ${isEn ? 'mins' : 'دقيقة'}` : `${stats.daily_goal_minutes} ${isEn ? 'mins' : 'دقيقة'}`}
                </span>
              </div>

              <div className="w-full bg-stone-100 h-2.5 rounded-full overflow-hidden">
                <div
                  className={`h-full rounded-full transition-all duration-500 ${
                    daily_goal?.is_goal_met ? 'bg-emerald-500' : 'bg-amber-500'
                  }`}
                  style={{ width: `${daily_goal?.progress_percent || 0}%` }}
                />
              </div>

              <div className="flex justify-between items-center text-xs text-stone-400 pt-1">
                <span>
                  {daily_goal?.is_goal_met
                    ? (isEn ? 'Goal achieved today! 🎉' : 'أتممت هدفك اليوم! 🎉')
                    : (isEn ? 'Keep learning to meet today\'s goal' : 'أكمل درساً أو مراجعة لإتمام الهدف')}
                </span>
                <span className="font-semibold text-emerald-800">
                  +{daily_goal?.today_xp || 0} XP {isEn ? 'today' : 'اليوم'}
                </span>
              </div>
            </div>

            {/* Current Course Progress Card */}
            <div className="p-5 sm:p-6 rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-3 text-start">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <GraduationCap className="w-5 h-5 text-[#1B4D3E]" />
                  <h3 className="font-bold text-sm text-stone-900">
                    {isEn ? `Progress in ${currentLevel}` : `تقدمك في مستوى ${currentLevel}`}
                  </h3>
                </div>
                <span className="text-xs font-bold text-[#1B4D3E]">
                  {course_progress ? `${course_progress.progress_percent}%` : '0%'}
                </span>
              </div>

              <div className="w-full bg-stone-100 h-2.5 rounded-full overflow-hidden">
                <div
                  className="bg-[#1B4D3E] h-full rounded-full transition-all duration-500"
                  style={{ width: `${course_progress?.progress_percent || 0}%` }}
                />
              </div>

              <div className="flex justify-between items-center text-xs text-stone-400 pt-1">
                <span>
                  {course_progress
                    ? (isEn
                        ? `${course_progress.completed_lessons} of ${course_progress.total_lessons} lessons completed`
                        : `${course_progress.completed_lessons} من ${course_progress.total_lessons} درساً مكتملاً`)
                    : ''}
                </span>
                <Link
                  to="/learn"
                  className="font-bold text-emerald-800 hover:text-emerald-900 underline flex items-center gap-1"
                >
                  <span>{isEn ? 'View Path' : 'عرض المسار'}</span>
                </Link>
              </div>
            </div>
          </div>

          {/* Key Metric Cards */}
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div className="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-2 text-start">
              <div className="flex items-center justify-between text-stone-400">
                <span className="text-xs font-semibold">{isEn ? 'Total XP' : 'مجموع النقاط'}</span>
                <Sparkles className="w-4 h-4 text-amber-500" />
              </div>
              <div className="text-2xl font-black text-[#1B4D3E]">{stats.total_xp} XP</div>
              <div className="text-[11px] text-stone-500">{isEn ? 'Earned from lessons & tests' : 'مكافآت الإنجاز والدروس'}</div>
            </div>

            <div className="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-2 text-start">
              <div className="flex items-center justify-between text-stone-400">
                <span className="text-xs font-semibold">{isEn ? 'Lessons Completed' : 'الدروس المنجزة'}</span>
                <CheckCircle className="w-4 h-4 text-emerald-600" />
              </div>
              <div className="text-2xl font-black text-stone-900">{stats.lessons_completed_count}</div>
              <div className="text-[11px] text-stone-500">{isEn ? 'Persisted curriculum record' : 'منهج CEFR المعتمد'}</div>
            </div>

            <div className="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-2 text-start">
              <div className="flex items-center justify-between text-stone-400">
                <span className="text-xs font-semibold">{isEn ? 'Saved Vocabulary' : 'المفردات المحفوظة'}</span>
                <RotateCcw className="w-4 h-4 text-blue-500" />
              </div>
              <div className="text-2xl font-black text-stone-900">{stats.vocabulary_count}</div>
              <div className="text-[11px] text-stone-500">
                {stats.due_reviews_count > 0 ? (
                  <Link to="/review" className="text-orange-600 font-bold hover:underline">
                    {isEn ? `${stats.due_reviews_count} due for review` : `${stats.due_reviews_count} كلمة مستحقة للمراجعة`}
                  </Link>
                ) : (
                  <span>{isEn ? 'No overdue reviews' : 'لا توجد مراجعات متأخرة'}</span>
                )}
              </div>
            </div>

            <div className="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-2 text-start">
              <div className="flex items-center justify-between text-stone-400">
                <span className="text-xs font-semibold">{isEn ? 'Interactive Stories' : 'القصص التفاعلية'}</span>
                <BookMarked className="w-4 h-4 text-purple-500" />
              </div>
              <div className="text-2xl font-black text-stone-900">{stats.stories_completed_count}</div>
              <div className="text-[11px] text-stone-500">{isEn ? 'Real conversational stories' : 'قصص واقعية في إسبانيا'}</div>
            </div>
          </div>

          {/* Primary Recommended Lesson Action Card (Continue-learning) */}
          {recommended_lesson && (
            <div className="bg-gradient-to-r from-emerald-950 via-[#1B4D3E] to-emerald-900 text-white rounded-3xl p-6 sm:p-8 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
              <div className="space-y-2 text-start">
                <span className="inline-block px-3 py-1 rounded-full bg-emerald-800/80 text-emerald-200 text-xs font-bold border border-emerald-600/40">
                  {isEn ? 'Recommended Next Step' : 'خطوتك التالية الموصى بها'}
                </span>
                <h2 className="text-xl sm:text-2xl font-black">
                  {isEn ? (recommended_lesson.title || recommended_lesson.title_ar) : recommended_lesson.title_ar}
                </h2>
                <p className="text-xs sm:text-sm text-emerald-100 max-w-xl leading-relaxed">
                  {recommended_lesson.objective_ar}
                </p>
                <div className="text-xs text-emerald-300 font-medium pt-1">
                  {isEn
                    ? `Estimated: ${recommended_lesson.estimated_minutes} mins • Reward: +${recommended_lesson.xp_reward} XP`
                    : `المدة المتوقعة: ${recommended_lesson.estimated_minutes} دقائق • الجائزة: +${recommended_lesson.xp_reward} XP`}
                </div>
              </div>

              <Link
                to={`/learn/lessons/${recommended_lesson.id}`}
                className="w-full sm:w-auto px-8 py-4 rounded-2xl bg-amber-400 text-stone-950 font-bold text-sm shadow-lg hover:bg-amber-300 hover:scale-105 transition-all flex items-center justify-center gap-2 shrink-0"
              >
                <span>{isEn ? 'Continue Learning' : 'متابعة التعلم الآن'}</span>
                <ArrowRight className={`w-4 h-4 ${isEn ? '' : 'rotate-180'}`} />
              </Link>
            </div>
          )}

          {/* Assessments & Weak Areas Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            {/* Recent Assessment Results */}
            <div className="bg-white rounded-3xl p-6 border border-stone-200 shadow-2xs space-y-4 text-start">
              <div className="flex items-center justify-between border-b border-stone-100 pb-3">
                <div className="flex items-center gap-2">
                  <Award className="w-5 h-5 text-amber-500" />
                  <h3 className="font-bold text-stone-900 text-sm">
                    {isEn ? 'Recent Assessments' : 'نتائج الاختبارات الأخيرة'}
                  </h3>
                </div>
                <Link to="/placement-test" className="text-xs font-bold text-[#1B4D3E] hover:underline">
                  {isEn ? 'Take Test' : 'إجراء اختبار'}
                </Link>
              </div>

              <div className="space-y-3">
                {recent_assessments?.placement ? (
                  <div className="p-3.5 rounded-2xl bg-stone-50 border border-stone-200 flex items-center justify-between text-xs">
                    <div>
                      <div className="font-bold text-stone-900">
                        {isEn ? 'Placement Assessment' : 'اختبار تحديد المستوى الأول'}
                      </div>
                      <div className="text-[11px] text-stone-400">
                        {new Date(recent_assessments.placement.completed_at).toLocaleDateString()}
                      </div>
                    </div>
                    <div className="text-end">
                      <span className="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-900 font-black">
                        {recent_assessments.placement.estimated_level}
                      </span>
                      <div className="text-[10px] text-stone-500 pt-0.5">
                        {recent_assessments.placement.total_score} {isEn ? 'pts' : 'نقطة'}
                      </div>
                    </div>
                  </div>
                ) : (
                  <p className="text-xs text-stone-400 py-2">
                    {isEn ? 'No placement test completed yet.' : 'لم تجرِ اختبار تحديد المستوى بعد.'}
                  </p>
                )}

                {recent_assessments?.level_exam ? (
                  <div className="p-3.5 rounded-2xl bg-stone-50 border border-stone-200 flex items-center justify-between text-xs">
                    <div>
                      <div className="font-bold text-stone-900">
                        {isEn ? `Level Exam (${recent_assessments.level_exam.cefr_level})` : `اختبار اجتياز مستوى (${recent_assessments.level_exam.cefr_level})`}
                      </div>
                      <div className="text-[11px] text-stone-400">
                        {new Date(recent_assessments.level_exam.completed_at).toLocaleDateString()}
                      </div>
                    </div>
                    <div className="text-end">
                      <span
                        className={`px-2.5 py-1 rounded-lg font-black ${
                          recent_assessments.level_exam.passed
                            ? 'bg-emerald-100 text-emerald-900'
                            : 'bg-amber-100 text-amber-900'
                        }`}
                      >
                        {recent_assessments.level_exam.score_percent}%
                      </span>
                      <div className="text-[10px] text-stone-500 pt-0.5">
                        {recent_assessments.level_exam.passed ? (isEn ? 'Passed' : 'مجتاز بنجاح') : (isEn ? 'Needs Review' : 'يحتاج مراجعة')}
                      </div>
                    </div>
                  </div>
                ) : null}
              </div>
            </div>

            {/* Relevant Weak Areas Card */}
            <div className="bg-white rounded-3xl p-6 border border-stone-200 shadow-2xs space-y-4 text-start">
              <div className="flex items-center justify-between border-b border-stone-100 pb-3">
                <div className="flex items-center gap-2">
                  <AlertTriangle className="w-5 h-5 text-amber-600" />
                  <h3 className="font-bold text-stone-900 text-sm">
                    {isEn ? 'Target Areas for Improvement' : 'نقاط تحتاج ممارسة وتقوية'}
                  </h3>
                </div>
                <Link to="/my-mistakes" className="text-xs font-bold text-[#1B4D3E] hover:underline">
                  {isEn ? 'All Mistakes' : 'سجل أخطائي'}
                </Link>
              </div>

              {weak_areas && weak_areas.length > 0 ? (
                <div className="space-y-2.5">
                  {weak_areas.map((wa) => (
                    <div
                      key={wa.id}
                      className="p-3 rounded-2xl bg-stone-50 border border-stone-200 flex items-center justify-between text-xs"
                    >
                      <div className="space-y-0.5">
                        <span className="font-bold text-stone-900">
                          {isEn ? wa.concept_name_en : wa.concept_name_ar}
                        </span>
                        <div className="text-[10px] text-stone-400">
                          {isEn ? `Encountered ${wa.total_errors_count} times` : `تكررت ${wa.total_errors_count} مرات في كتابتك`}
                        </div>
                      </div>
                      <div className="text-end">
                        <span className="text-emerald-700 font-bold">{wa.mastery_score}% {isEn ? 'mastery' : 'إتقان'}</span>
                        <Link
                          to={`/my-mistakes/${wa.id}`}
                          className="block text-[11px] font-bold text-[#1B4D3E] hover:underline pt-0.5"
                        >
                          {isEn ? 'Practice →' : 'تدرب الآن ←'}
                        </Link>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="p-4 text-center text-xs text-stone-400 space-y-1">
                  <CheckCircle2 className="w-6 h-6 text-emerald-500 mx-auto" />
                  <p>{isEn ? 'No recurring weak areas recorded yet.' : 'سجلك نظيف! لم تُسجل أخطاء متكررة بعد.'}</p>
                </div>
              )}
            </div>
          </div>

          {/* Quick Learning Hub Grid */}
          <div className="space-y-4 text-start">
            <h3 className="text-base sm:text-lg font-bold text-stone-900">
              {isEn ? 'Learning Hub & Activities' : 'مسارات ومميزات التعلم الذكي'}
            </h3>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <Link
                to="/journal"
                className="p-5 rounded-2xl bg-white border border-stone-200 hover:border-emerald-400 hover:shadow-sm transition-all space-y-2 group"
              >
                <div className="w-10 h-10 rounded-xl bg-emerald-100 text-[#1B4D3E] flex items-center justify-center group-hover:scale-105 transition-transform">
                  <PenTool className="w-5 h-5" />
                </div>
                <div className="font-bold text-stone-900 text-sm">
                  {isEn ? 'Spanish Journal' : 'دفتر اليوميات الإسباني'}
                </div>
                <p className="text-xs text-stone-500 leading-relaxed">
                  {isEn
                    ? 'Write daily entries with deep grammatical and natural phrasing analysis.'
                    : 'اكتب عن يومك ودع المعلم الذكي يصحح لك ويصمم لك تمارين على أخطائك.'}
                </p>
              </Link>

              <Link
                to="/my-mistakes"
                className="p-5 rounded-2xl bg-white border border-stone-200 hover:border-emerald-400 hover:shadow-sm transition-all space-y-2 group"
              >
                <div className="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center group-hover:scale-105 transition-transform">
                  <AlertTriangle className="w-5 h-5" />
                </div>
                <div className="font-bold text-stone-900 text-sm">
                  {isEn ? 'My Mistakes Profile' : 'أخطائي وملاحظاتي'}
                </div>
                <p className="text-xs text-stone-500 leading-relaxed">
                  {isEn
                    ? 'Review recurring challenges and strengthen mastery through targeted exercises.'
                    : 'راجع الأخطاء الشائعة في كتابتك وتدرب عليها لترفع نسبة إتقانك.'}
                </p>
              </Link>

              <Link
                to="/review"
                className="p-5 rounded-2xl bg-white border border-stone-200 hover:border-emerald-400 hover:shadow-sm transition-all space-y-2 group"
              >
                <div className="w-10 h-10 rounded-xl bg-orange-100 text-orange-800 flex items-center justify-center group-hover:scale-105 transition-transform">
                  <RotateCcw className="w-5 h-5" />
                </div>
                <div className="font-bold text-stone-900 text-sm">
                  {isEn ? 'Spaced Repetition (SRS)' : 'مراجعة المفردات (SRS)'}
                </div>
                <p className="text-xs text-stone-500 leading-relaxed">
                  {isEn
                    ? 'SuperMemo-backed review cards to cement vocabulary in long-term memory.'
                    : 'راجع كلماتك الذكية بأسلوب البطاقات التكرارية SuperMemo لتثبيتها في الذاكرة.'}
                </p>
                {stats.due_reviews_count > 0 && (
                  <span className="inline-block text-[11px] font-bold text-orange-700 bg-orange-50 px-2.5 py-0.5 rounded-full">
                    {stats.due_reviews_count} {isEn ? 'words ready' : 'كلمات جاهزة'}
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
                <div className="font-bold text-stone-900 text-sm">
                  {isEn ? 'Madrid Stories' : 'قصص مدريد التفاعلية'}
                </div>
                <p className="text-xs text-stone-500 leading-relaxed">
                  {isEn
                    ? 'Read real conversations in cafes, airports, and streets with instant audio glosses.'
                    : 'اقرأ حوارات حية من شوارع ومقاهي ومطارات إسبانيا مع ترجمة فورية للكلمات.'}
                </p>
              </Link>
            </div>
          </div>

          {/* Recent Activity */}
          <div className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-2xs space-y-4 text-start">
            <h3 className="font-bold text-stone-900 text-base">
              {isEn ? 'Recent Learning Activity' : 'نشاطك التعليمي الأخير'}
            </h3>
            {recent_activity && recent_activity.length > 0 ? (
              <div className="divide-y divide-stone-100">
                {recent_activity.map((att: any) => (
                  <div key={att.id} className="py-3 flex items-center justify-between text-xs">
                    <div className="space-y-0.5">
                      <div className="font-bold text-stone-900">
                        {isEn ? (att.lesson?.title || att.lesson?.title_ar) : att.lesson?.title_ar}
                      </div>
                      <div className="text-stone-400">
                        {isEn ? (att.lesson?.unit?.title_en || att.lesson?.unit?.title_ar) : att.lesson?.unit?.title_ar}
                      </div>
                    </div>
                    <div className="text-end space-y-0.5">
                      <div className="font-bold text-emerald-700">
                        {isEn ? `Score: ${att.score_percent}%` : `الدرجة: ${att.score_percent}%`}
                      </div>
                      <div className="text-stone-400">+{att.xp_awarded} XP</div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-6 text-stone-400 text-xs">
                {isEn
                  ? 'No lessons completed yet. Start your first lesson today to build your learning log!'
                  : 'لم تسجل أي دروس بعد. ابدأ درسك الأول اليوم لبناء سجل نشاطك!'}
              </div>
            )}
          </div>
        </main>
      </div>
    </div>
  );
};
export default DashboardPage;
