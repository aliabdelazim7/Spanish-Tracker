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

  const filteredStories = selectedLevel === 'all'
    ? stories
    : stories.filter((s) => s.cefr_level === selectedLevel);

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8 max-w-5xl w-full pb-24 md:pb-8">
          {/* Header */}
          <div className="space-y-2">
            <h1 className="text-3xl font-black text-stone-900">
              قصص تفاعلية في شوارع مدريد
            </h1>
            <p className="text-sm text-stone-600 leading-relaxed max-w-2xl">
              عش تجربة التعلم السياقي الواقعي من خلال حوارات مشوقة في المطارات، المقاهي، محطات المترو والأسواق الإسبانية. انقر على أي كلمة أثناء القراءة للاطلاع على معناها الفوري وإضافتها لبطاقات مراجعتك.
            </p>

            {/* Level Filter Tabs */}
            <div className="flex items-center gap-2 pt-2">
              {['all', 'A1', 'A2', 'B1'].map((lvl) => (
                <button
                  key={lvl}
                  onClick={() => setSelectedLevel(lvl)}
                  className={`px-4 py-1.5 rounded-full text-xs font-bold transition-all ${
                    selectedLevel === lvl
                      ? 'bg-[#1B4D3E] text-white shadow-xs'
                      : 'bg-white border border-stone-200 text-stone-600 hover:border-stone-300'
                  }`}
                >
                  {lvl === 'all' ? 'جميع المستويات' : `مستوى ${lvl}`}
                </button>
              ))}
            </div>
          </div>

          {/* Stories Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {filteredStories.map((story) => (
              <div
                key={story.id}
                className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-xs hover:border-emerald-300 transition-all flex flex-col justify-between space-y-4"
              >
                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                      مستوى {story.cefr_level}
                    </span>
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
            ))}
          </div>
        </main>
      </div>
    </div>
  );
};
