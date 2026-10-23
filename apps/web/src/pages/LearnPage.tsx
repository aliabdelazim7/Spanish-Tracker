import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { curriculumApi } from '../api/curriculum';
import { Course, Unit, Lesson, CefrLevel } from '../types';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import {
  Compass,
  CheckCircle2,
  Lock,
  Play,
  Sparkles,
  BookOpen,
  ArrowRight,
  Award,
  ChevronRight,
  Headphones,
  BookText,
  Layers,
  Zap,
  MessageCircle,
  HelpCircle
} from 'lucide-react';
import { useTranslation } from 'react-i18next';

export const LearnPage: React.FC = () => {
  const { t, i18n } = useTranslation();
  const isRtl = i18n.language === 'ar';

  const [course, setCourse] = useState<Course | null>(null);
  const [completedLessonIds, setCompletedLessonIds] = useState<number[]>([]);
  const [userLevel, setUserLevel] = useState('A1');
  const [maxUnlockedLevel, setMaxUnlockedLevel] = useState('A1');
  const [completedLevels, setCompletedLevels] = useState<string[]>([]);
  const [selectedLevelFilter, setSelectedLevelFilter] = useState<string>('A1');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadCurriculum();
  }, []);

  const loadCurriculum = async () => {
    setIsLoading(true);
    try {
      const data = await curriculumApi.getLearningPath();
      setCourse(data.course);
      setCompletedLessonIds(data.completed_lesson_ids || []);
      setUserLevel(data.user_level || 'A1');
      const maxUnlocked = (data as any).max_unlocked_level || data.user_level || 'A1';
      setMaxUnlockedLevel(maxUnlocked);
      setCompletedLevels((data as any).completed_levels || []);
      setSelectedLevelFilter(maxUnlocked);
    } catch (err) {
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  const getSkillCategoryBadge = (category?: string) => {
    switch (category) {
      case 'vocabulary':
        return { label: 'مفردات وكلمات', color: 'bg-blue-100 text-blue-800 border-blue-200', icon: Layers };
      case 'grammar':
        return { label: 'قواعد وتراكيب', color: 'bg-purple-100 text-purple-800 border-purple-200', icon: BookOpen };
      case 'verbs':
        return { label: 'تصريف الأفعال', color: 'bg-amber-100 text-amber-800 border-amber-200', icon: Zap };
      case 'listening':
        return { label: 'استماع ونطق', color: 'bg-emerald-100 text-emerald-800 border-emerald-200', icon: Headphones };
      case 'reading':
        return { label: 'قراءة وفهم نصوص', color: 'bg-indigo-100 text-indigo-800 border-indigo-200', icon: BookText };
      case 'phrases':
        return { label: 'مواقف ومحادثة', color: 'bg-teal-100 text-teal-800 border-teal-200', icon: MessageCircle };
      default:
        return { label: 'وحدة تعليمية', color: 'bg-gray-100 text-gray-800 border-gray-200', icon: BookOpen };
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
        <Navbar />
        <div className="flex-1 flex items-center justify-center gap-3">
          <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
          <p className="text-sm font-semibold text-stone-600">جاري تحميل المسار التعليمي والمستويات...</p>
        </div>
      </div>
    );
  }

  const completedSet = new Set(completedLessonIds);

  const displayedLevels = course?.levels?.filter(
    (lvl) => selectedLevelFilter === 'ALL' || lvl.code === selectedLevelFilter
  );

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8 max-w-5xl w-full pb-24 md:pb-8">
          {/* Header */}
          <div className="space-y-3">
            <div className="flex flex-wrap items-center gap-2">
              <span className="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold border border-emerald-300">
                مستواك الحالي: {maxUnlockedLevel}
              </span>
              <span className="text-xs px-3 py-1 rounded-full bg-stone-100 text-stone-600 font-semibold">
                6 مستويات معتمدة (A1 - C2) • منهج تدريجي متكامل
              </span>
              {completedLevels.length > 0 && (
                <span className="text-xs px-3 py-1 rounded-full bg-amber-100 text-amber-900 font-bold border border-amber-300">
                  المستويات المجتازة: {completedLevels.join(', ')}
                </span>
              )}
            </div>

            <h1 className="text-2xl sm:text-3xl font-black text-stone-900">
              مسار تعلّم الإسبانية الشامل (من A1 إلى C2)
            </h1>
            <p className="text-sm text-stone-600 leading-relaxed max-w-2xl">
              كل مستوى مقسم لوحدات تعليمية تغطي: المفردات، القواعد، تصريف الأفعال، الاستماع، القراءة، والجمل اليومية.
              عند إكمال دروس المستوى، اجتز اختبار التخرج لفتح المستوى التالي تلقائياً!
            </p>
          </div>

          {/* CEFR Level Tabs */}
          <div className="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
            {['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'ALL'].map((lvlCode) => {
              const targetLevel = course?.levels?.find((l) => l.code === lvlCode);
              const isUnlocked = lvlCode === 'ALL' || targetLevel?.is_unlocked;
              const isCompleted = completedLevels.includes(lvlCode);
              const isSelected = selectedLevelFilter === lvlCode;

              return (
                <button
                  key={lvlCode}
                  type="button"
                  onClick={() => setSelectedLevelFilter(lvlCode)}
                  className={`px-4 py-2.5 rounded-2xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer whitespace-nowrap shadow-xs ${
                    isSelected
                      ? 'bg-[#1B4D3E] text-white shadow-md scale-105'
                      : isCompleted
                      ? 'bg-emerald-50 border border-emerald-200 text-emerald-800 hover:bg-emerald-100'
                      : isUnlocked
                      ? 'bg-white border border-stone-200 text-stone-700 hover:border-emerald-300'
                      : 'bg-stone-100 border border-stone-200 text-stone-400 opacity-75'
                  }`}
                >
                  {lvlCode !== 'ALL' && (
                    <span>
                      {isCompleted ? (
                        <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600" />
                      ) : isUnlocked ? (
                        <span className="w-2 h-2 rounded-full bg-emerald-500" />
                      ) : (
                        <Lock className="w-3.5 h-3.5 text-stone-400" />
                      )}
                    </span>
                  )}
                  <span>{lvlCode === 'ALL' ? 'عرض كل المستويات' : `المستوى ${lvlCode}`}</span>
                </button>
              );
            })}
          </div>

          {/* Levels & Content */}
          <div className="space-y-12">
            {displayedLevels?.map((lvl: CefrLevel) => {
              const isUnlocked = lvl.is_unlocked;
              const isCompleted = completedLevels.includes(lvl.code);
              const progress = lvl.progress_percent || 0;

              return (
                <div key={lvl.id} className="space-y-6">
                  {/* Level Header Card */}
                  <div className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-xs space-y-4">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                      <div className="flex items-center gap-3">
                        <div
                          className={`w-12 h-12 rounded-2xl font-black text-lg flex items-center justify-center shadow-xs ${
                            isCompleted
                              ? 'bg-emerald-600 text-white'
                              : isUnlocked
                              ? 'bg-[#1B4D3E] text-amber-300'
                              : 'bg-stone-200 text-stone-500'
                          }`}
                        >
                          {lvl.code}
                        </div>
                        <div>
                          <div className="flex items-center gap-2">
                            <h2 className="text-xl font-bold text-stone-900">
                              {lvl.name_ar} ({lvl.name})
                            </h2>
                            {isCompleted ? (
                              <span className="text-[11px] font-bold px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-full">
                                تم الاجتياز ✅
                              </span>
                            ) : isUnlocked ? (
                              <span className="text-[11px] font-bold px-2.5 py-0.5 bg-amber-100 text-amber-800 rounded-full">
                                مفتوح حالياً 🔓
                              </span>
                            ) : (
                              <span className="text-[11px] font-bold px-2.5 py-0.5 bg-stone-100 text-stone-500 rounded-full flex items-center gap-1">
                                <Lock className="w-3 h-3" />
                                مغلق
                              </span>
                            )}
                          </div>
                          <p className="text-xs text-stone-500 mt-1">{lvl.description_ar}</p>
                        </div>
                      </div>

                      {/* Level Stats */}
                      <div className="text-start sm:text-end shrink-0">
                        <div className="text-xs font-bold text-stone-500">
                          نسبة الإنجاز: {progress}%
                        </div>
                        <div className="text-xs text-stone-400">
                          {lvl.completed_lessons_count || 0} من {lvl.total_lessons_count || 0} دروس مكتملة
                        </div>
                      </div>
                    </div>

                    {/* Level Progress Bar */}
                    <div className="w-full bg-stone-100 h-2 rounded-full overflow-hidden">
                      <div
                        className={`h-full rounded-full transition-all duration-500 ${
                          isCompleted ? 'bg-emerald-500' : 'bg-amber-500'
                        }`}
                        style={{ width: `${progress}%` }}
                      />
                    </div>
                  </div>

                  {/* Units List */}
                  {isUnlocked ? (
                    <div className="space-y-6">
                      {lvl.units?.map((unit: Unit) => {
                        const badge = getSkillCategoryBadge(unit.skill_category);
                        const IconComponent = badge.icon;

                        return (
                          <div
                            key={unit.id}
                            className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-xs space-y-4"
                          >
                            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                              <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                                  <IconComponent className="w-5 h-5" />
                                </div>
                                <div>
                                  <div className="flex items-center gap-2">
                                    <h3 className="text-lg font-bold text-stone-900">{unit.title_ar}</h3>
                                    <span
                                      className={`text-[10px] font-bold px-2 py-0.5 rounded-md border ${badge.color}`}
                                    >
                                      {badge.label}
                                    </span>
                                  </div>
                                  <div className="text-xs text-stone-400 font-semibold" dir="ltr">
                                    {unit.title}
                                  </div>
                                </div>
                              </div>

                              <span className="text-xs font-bold text-stone-500 bg-stone-100 px-3 py-1 rounded-full self-start sm:self-auto">
                                {unit.lessons?.length || 0} دروس
                              </span>
                            </div>

                            {/* Lessons Grid */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                              {unit.lessons?.map((lesson: Lesson) => {
                                const isDone = completedSet.has(lesson.id);
                                // Identify if this is the first uncompleted lesson in the unlocked path
                                const isNextRecommended = !isDone && !completedSet.has(lesson.id) && (
                                  !unit.lessons?.some((prevL) => prevL.id < lesson.id && !completedSet.has(prevL.id))
                                );

                                return (
                                  <Link
                                    key={lesson.id}
                                    to={`/learn/lessons/${lesson.id}`}
                                    aria-label={`${lesson.title_ar} - ${isDone ? 'مكتمل' : isNextRecommended ? 'خطوتك التالية الموصى بها' : 'متاح'}`}
                                    className={`p-4 rounded-2xl border-2 transition-all flex items-center justify-between group focus:outline-none focus:ring-2 focus:ring-[#1B4D3E] ${
                                      isDone
                                        ? 'border-emerald-200 bg-emerald-50/40 hover:border-emerald-300'
                                        : isNextRecommended
                                        ? 'border-[#1B4D3E] bg-white ring-2 ring-emerald-500/30 shadow-md'
                                        : 'border-stone-200 bg-stone-50/60 hover:border-[#1B4D3E] hover:bg-white shadow-2xs'
                                    }`}
                                  >
                                    <div className="space-y-1 text-start">
                                      <div className="flex flex-wrap items-center gap-2">
                                        <span className="font-bold text-sm text-stone-900 group-hover:text-[#1B4D3E] transition-colors">
                                          {lesson.title_ar}
                                        </span>
                                        {isDone ? (
                                          <span className="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                            <CheckCircle2 className="w-3 h-3" />
                                            <span>مكتمل</span>
                                          </span>
                                        ) : isNextRecommended ? (
                                          <span className="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-400 text-stone-950 animate-pulse">
                                            <Sparkles className="w-3 h-3" />
                                            <span>خطوتك التالية</span>
                                          </span>
                                        ) : (
                                          <span className="text-[10px] font-medium text-stone-400">
                                            متاح
                                          </span>
                                        )}
                                      </div>
                                      <div className="text-xs text-stone-500 font-medium" dir="ltr">
                                        {lesson.title}
                                      </div>
                                      <div className="text-[11px] text-stone-400">
                                        {lesson.estimated_minutes} د • +{lesson.xp_reward} XP
                                      </div>
                                    </div>

                                    <div
                                      className={`w-9 h-9 rounded-xl border flex items-center justify-center transition-all shadow-2xs shrink-0 ${
                                        isDone
                                          ? 'bg-emerald-100 text-emerald-800 border-emerald-300'
                                          : isNextRecommended
                                          ? 'bg-[#1B4D3E] text-white border-[#1B4D3E] scale-105'
                                          : 'bg-white border-stone-200 text-stone-600 group-hover:bg-[#1B4D3E] group-hover:text-white'
                                      }`}
                                    >
                                      {isDone ? (
                                        <CheckCircle2 className="w-4 h-4" />
                                      ) : (
                                        <Play className="w-4 h-4 ml-0.5 fill-current" />
                                      )}
                                    </div>
                                  </Link>
                                );
                              })}
                            </div>
                          </div>
                        );
                      })}

                      {/* Level Graduation Exam Card */}
                      <div className="p-6 rounded-3xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-emerald-500/10 border-2 border-amber-300/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div className="flex items-center gap-4">
                          <div className="w-14 h-14 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-md shrink-0">
                            <Award className="w-8 h-8" />
                          </div>
                          <div className="space-y-1">
                            <div className="flex items-center gap-2">
                              <h3 className="text-lg font-bold text-stone-900">
                                اختبار اجتياز المستوى ({lvl.code} ➔ {lvl.code === 'A1' ? 'A2' : lvl.code === 'A2' ? 'B1' : 'المستوى التالي'})
                              </h3>
                              {isCompleted && (
                                <span className="text-xs px-2.5 py-0.5 rounded-full bg-emerald-600 text-white font-bold">
                                  ناجح ومجتاز
                                </span>
                              )}
                            </div>
                            <p className="text-xs text-stone-600 max-w-xl leading-relaxed">
                              اختبار شامل لتقييم المفردات، القواعد، تصريف الأفعال، والاستماع. الحصول على 70% يفتح المستوى التالي تلقائياً ويمنحك +100 XP!
                            </p>
                          </div>
                        </div>

                        <Link
                          to={`/levels/${lvl.code}/exam`}
                          className="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2 shrink-0 self-stretch sm:self-auto justify-center"
                        >
                          <Sparkles className="w-4 h-4" />
                          <span>{isCompleted ? 'إعادة الاختبار للتحسين' : 'بدء اختبار الاجتياز'}</span>
                          <ChevronRight className="w-4 h-4 rtl:rotate-180" />
                        </Link>
                      </div>
                    </div>
                  ) : (
                    /* Locked Level Placeholder */
                    <div className="bg-stone-50 rounded-3xl p-8 border border-dashed border-stone-300 text-center space-y-3">
                      <div className="w-12 h-12 bg-stone-200 text-stone-500 rounded-full flex items-center justify-center mx-auto">
                        <Lock className="w-6 h-6" />
                      </div>
                      <h3 className="text-base font-bold text-stone-800">
                        محتوى المستوى ({lvl.code}) مقفل حالياً
                      </h3>
                      <p className="text-xs text-stone-500 max-w-md mx-auto">
                        لفتح هذا المستوى بكافة وحداته، يجب عليك إكمال المستوى السابق واجتياز اختبار التخرج الخاص به بنجاح.
                      </p>
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </main>
      </div>
    </div>
  );
};
export default LearnPage;
