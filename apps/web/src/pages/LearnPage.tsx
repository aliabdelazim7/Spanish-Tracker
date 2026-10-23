import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { curriculumApi } from '../api/curriculum';
import { Course, Unit, Lesson } from '../types';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import {
  Compass,
  CheckCircle2,
  Lock,
  Play,
  Sparkles,
  BookOpen,
  ArrowRight
} from 'lucide-react';

export const LearnPage: React.FC = () => {
  const [course, setCourse] = useState<Course | null>(null);
  const [completedLessonIds, setCompletedLessonIds] = useState<number[]>([]);
  const [userLevel, setUserLevel] = useState('A1');
  const [selectedLevelFilter, setSelectedLevelFilter] = useState<string>('ALL');
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
          <p className="text-sm font-semibold text-stone-600">جاري تحميل المسار التعليمي...</p>
        </div>
      </div>
    );
  }

  const completedSet = new Set(completedLessonIds);

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8 max-w-5xl w-full pb-24 md:pb-8">
          {/* Header */}
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <span className="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold border border-emerald-300">
                مستواك الحالي: {userLevel}
              </span>
              <span className="text-xs px-3 py-1 rounded-full bg-stone-100 text-stone-600 font-semibold">
                6 مستويات معتمدة (CEFR) • 12 وحدة تعليمية
              </span>
            </div>
            <h1 className="text-3xl font-black text-stone-900">
              مسار تعلّم اللغة الإسبانية الشامل
            </h1>
            <p className="text-sm text-stone-600 leading-relaxed max-w-2xl">
              تدرج منظم خطوة بخطوة من الصفر وحتى الإتقان، يجمع بين القواعد المبسطة، التمارين التفاعلية المتنوعة، والنطق الصوتي الأصيل.
            </p>
          </div>

          {/* Quick Level Filter Tabs */}
          <div className="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            {['ALL', 'A1', 'A2', 'B1', 'B2', 'C1', 'C2'].map((lvlCode) => (
              <button
                key={lvlCode}
                type="button"
                onClick={() => setSelectedLevelFilter(lvlCode)}
                className={`px-4 py-2 rounded-2xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap ${
                  selectedLevelFilter === lvlCode
                    ? 'bg-[#1B4D3E] text-white shadow-sm scale-105'
                    : 'bg-white border border-stone-200 text-stone-600 hover:border-emerald-300 hover:text-stone-900 shadow-2xs'
                }`}
              >
                <span>{lvlCode === 'ALL' ? 'كل المستويات (A1-C2)' : `المستوى ${lvlCode}`}</span>
              </button>
            ))}
          </div>

          {/* Levels & Units Hierarchy */}
          <div className="space-y-10">
            {course?.levels
              ?.filter((lvl) => selectedLevelFilter === 'ALL' || lvl.code === selectedLevelFilter)
              ?.map((lvl) => (
              <div key={lvl.id} className="space-y-4">
                {/* Level Title Header */}
                <div className="flex items-center gap-3 border-b border-stone-200 pb-3">
                  <div className="w-10 h-10 rounded-2xl bg-[#1B4D3E] text-amber-300 font-black text-base flex items-center justify-center shadow-xs">
                    {lvl.code}
                  </div>
                  <div>
                    <h2 className="text-xl font-bold text-stone-900">
                      {lvl.name_ar} ({lvl.name})
                    </h2>
                    <p className="text-xs text-stone-500">{lvl.description_ar}</p>
                  </div>
                </div>

                {/* Units under Level */}
                <div className="space-y-6">
                  {lvl.units?.map((unit: Unit) => (
                    <div
                      key={unit.id}
                      className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-xs space-y-5"
                    >
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                          <div className="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                            <BookOpen className="w-5 h-5 text-amber-700" />
                          </div>
                          <div>
                            <h3 className="text-lg font-bold text-stone-900">{unit.title_ar}</h3>
                            <div className="text-xs text-stone-400 font-semibold bidi-isolate">{unit.title}</div>
                          </div>
                        </div>

                        <span className="text-xs font-bold text-stone-400 bg-stone-100 px-3 py-1 rounded-full">
                          {unit.lessons?.length || 0} دروس
                        </span>
                      </div>

                      {/* Lessons List */}
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        {unit.lessons?.map((lesson: Lesson) => {
                          const isDone = completedSet.has(lesson.id);

                          return (
                            <Link
                              key={lesson.id}
                              to={`/learn/lessons/${lesson.id}`}
                              className={`p-4 rounded-2xl border-2 transition-all flex items-center justify-between group ${
                                isDone
                                  ? 'border-emerald-200 bg-emerald-50/40 hover:border-emerald-300'
                                  : 'border-stone-200 bg-stone-50/60 hover:border-[#1B4D3E] hover:bg-white shadow-2xs'
                              }`}
                            >
                              <div className="space-y-1 text-right">
                                <div className="flex items-center gap-2">
                                  <span className="font-bold text-sm text-stone-900 group-hover:text-[#1B4D3E] transition-colors">
                                    {lesson.title_ar}
                                  </span>
                                  {isDone && <CheckCircle2 className="w-4 h-4 text-emerald-600 flex-shrink-0" />}
                                </div>
                                <div className="text-xs text-stone-500 bidi-isolate">{lesson.title}</div>
                                <div className="text-[11px] text-stone-400">
                                  {lesson.estimated_minutes} د • +{lesson.xp_reward} XP
                                </div>
                              </div>

                              <div className="w-9 h-9 rounded-xl bg-white border border-stone-200 flex items-center justify-center text-stone-600 group-hover:bg-[#1B4D3E] group-hover:text-white transition-all shadow-2xs">
                                <Play className="w-4 h-4 ml-0.5 fill-current" />
                              </div>
                            </Link>
                          );
                        })}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </main>
      </div>
    </div>
  );
};
