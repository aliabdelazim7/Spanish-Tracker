import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { storiesApi } from '../api/stories';
import { Story } from '../types';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import {
  Compass,
  BookMarked,
  Clock,
  Sparkles,
  CheckCircle2,
  ArrowRight,
  Flame,
  Globe
} from 'lucide-react';

export const StoriesPage: React.FC = () => {
  const [stories, setStories] = useState<Story[]>([]);
  const [selectedLevel, setSelectedLevel] = useState<string>('all');
  const [selectedTopic, setSelectedTopic] = useState<string>('all');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadStories();
  }, []);

  const loadStories = async () => {
    setIsLoading(true);
    try {
      const data = await storiesApi.getStories();
      setStories(data.stories || []);
    } catch (err) {
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  const getTopicBadge = (topic: string) => {
    switch (topic) {
      case 'football':
        return { label: '⚽ إسبانية كرة القدم', color: 'bg-rose-50 text-rose-800 border-rose-200' };
      case 'culture':
        return { label: '🏛️ ثقافة وتاريخ الأندلس', color: 'bg-amber-50 text-amber-900 border-amber-200' };
      case 'cafe':
      case 'mercado':
      case 'hotel':
        return { label: '☕ الحياة اليومية في مدريد', color: 'bg-emerald-50 text-emerald-800 border-emerald-200' };
      case 'aeropuerto':
      case 'metro':
        return { label: '✈️ السفر والتنقل', color: 'bg-blue-50 text-blue-800 border-blue-200' };
      default:
        return { label: '📖 قصة سياقية', color: 'bg-stone-50 text-stone-700 border-stone-200' };
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
        <Navbar />
        <div className="flex-1 flex items-center justify-center gap-3">
          <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
          <p className="text-sm font-semibold text-stone-600">جاري تحميل القصص التفاعلية...</p>
        </div>
      </div>
    );
  }

  const filteredStories = stories.filter((s) => {
    const matchesLevel = selectedLevel === 'all' || s.cefr_level === selectedLevel;
    if (!matchesLevel) return false;
    if (selectedTopic === 'all') return true;
    if (selectedTopic === 'football') return s.topic === 'football';
    if (selectedTopic === 'culture') return s.topic === 'culture';
    if (selectedTopic === 'daily_life') return ['cafe', 'mercado', 'hotel'].includes(s.topic);
    if (selectedTopic === 'travel') return ['aeropuerto', 'metro'].includes(s.topic);
    return true;
  });

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8 max-w-5xl w-full pb-24 md:pb-8">
          {/* Header */}
          <div className="space-y-3">
            <h1 className="text-3xl font-black text-stone-900">
              قصص تفاعلية في شوارع مدريد وثقافة الأندلس
            </h1>
            <p className="text-sm text-stone-600 leading-relaxed max-w-2xl">
              عش تجربة التعلم السياقي الواقعي من خلال حوارات مشوقة في ملاعب كرة القدم (الكلاسيكو)، قصر الحمراء، المقاهي والأسواق الإسبانية. انقر على أي كلمة أثناء القراءة للاطلاع على معناها الفوري وإضافتها لبطاقات مراجعتك.
            </p>

            {/* Topic Filter Pills */}
            <div className="space-y-1.5 pt-1">
              <div className="text-xs font-bold text-stone-500">اختر المسار المعرفي:</div>
              <div className="flex flex-wrap items-center gap-2">
                {[
                  { id: 'all', label: 'جميع المسارات' },
                  { id: 'football', label: '⚽ مسار إسبانية كرة القدم' },
                  { id: 'culture', label: '🏛️ مسار الثقافة والأندلس' },
                  { id: 'daily_life', label: '☕ الحياة اليومية والمطاعم' },
                  { id: 'travel', label: '✈️ السفر والمطارات' },
                ].map((track) => (
                  <button
                    key={track.id}
                    onClick={() => setSelectedTopic(track.id)}
                    className={`px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all ${
                      selectedTopic === track.id
                        ? 'bg-[#1B4D3E] text-white shadow-2xs'
                        : 'bg-white border border-stone-200 text-stone-700 hover:border-stone-300'
                    }`}
                  >
                    {track.label}
                  </button>
                ))}
              </div>
            </div>

            {/* Level Filter Tabs */}
            <div className="flex items-center gap-2 pt-1">
              <span className="text-xs font-bold text-stone-400">المستوى:</span>
              {['all', 'A1', 'A2', 'B1'].map((lvl) => (
                <button
                  key={lvl}
                  onClick={() => setSelectedLevel(lvl)}
                  className={`px-3 py-1 rounded-full text-xs font-bold transition-all ${
                    selectedLevel === lvl
                      ? 'bg-amber-500 text-stone-950 shadow-2xs'
                      : 'bg-white border border-stone-200 text-stone-600 hover:border-stone-300'
                  }`}
                >
                  {lvl === 'all' ? 'الكل' : `مستوى ${lvl}`}
                </button>
              ))}
            </div>
          </div>

          {/* Stories Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {filteredStories.map((story) => {
              const topicBadge = getTopicBadge(story.topic);

              return (
                <div
                  key={story.id}
                  className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-xs hover:border-emerald-300 transition-all flex flex-col justify-between space-y-4"
                >
                  <div className="space-y-3">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-1.5">
                        <span className="text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                          مستوى {story.cefr_level}
                        </span>
                        <span className={`text-[11px] font-bold px-2.5 py-1 rounded-full border ${topicBadge.color}`}>
                          {topicBadge.label}
                        </span>
                      </div>
                      <div className="flex items-center gap-1.5 text-xs text-stone-400 font-medium">
                        <Clock className="w-3.5 h-3.5" />
                        <span>{story.estimated_minutes} دقائق</span>
                      </div>
                    </div>

                    <div className="space-y-1 text-right">
                      <h2 className="text-xl font-bold text-stone-900">{story.title_ar}</h2>
                      <div className="text-sm font-semibold text-[#1B4D3E] bidi-isolate">{story.title_es}</div>
                    </div>

                    <p className="text-xs text-stone-600 leading-relaxed text-right font-normal">
                      {story.summary_ar}
                    </p>
                  </div>

                  <div className="pt-4 border-t border-stone-100 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      {story.is_completed ? (
                        <span className="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg">
                          <CheckCircle2 className="w-4 h-4" />
                          <span>مكتملة</span>
                        </span>
                      ) : (
                        <span className="text-xs text-amber-600 font-bold flex items-center gap-1">
                          <Sparkles className="w-3.5 h-3.5" />
                          <span>+{story.xp_reward} XP</span>
                        </span>
                      )}
                    </div>

                    <Link
                      to={`/stories/${story.id}`}
                      className="px-5 py-2.5 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs shadow-xs hover:bg-[#153e32] flex items-center gap-1.5 transition-all"
                    >
                      <span>{story.is_completed ? 'إعادة القراءة' : 'ابدأ القراءة'}</span>
                      <ArrowRight className="w-3.5 h-3.5 rotate-180" />
                    </Link>
                  </div>
                </div>
              );
            })}
          </div>
        </main>
      </div>
    </div>
  );
};
