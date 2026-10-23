import React, { useEffect, useState } from 'react';
import { vocabularyApi } from '../api/vocabulary';
import { UserVocabulary, VocabularyItem } from '../types';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import { AudioSpeakerButton } from '../components/AudioSpeakerButton';
import { playSpanishAudio } from '../utils/speech';
import {
  Compass,
  CheckCircle2,
  BookmarkPlus,
  BookmarkCheck,
  Eye
} from 'lucide-react';
import confetti from 'canvas-confetti';

export const VocabularyReviewPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'review' | 'catalog'>('review');

  // Review states
  const [dueItems, setDueItems] = useState<UserVocabulary[]>([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isFlipped, setIsFlipped] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  // Catalog states
  const [catalogItems, setCatalogItems] = useState<VocabularyItem[]>([]);
  const [savedIds, setSavedIds] = useState<Set<number>>(new Set());
  const [vocabLevelFilter, setVocabLevelFilter] = useState<string>('ALL');

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    setIsLoading(true);
    try {
      const [dueRes, vocabRes] = await Promise.all([
        vocabularyApi.getDueReviews(),
        vocabularyApi.getVocabulary(),
      ]);

      setDueItems(dueRes.due_items || []);
      setCatalogItems(vocabRes.available_catalog || []);

      const saved = new Set(
        vocabRes.user_vocabulary.data.map((uv) => uv.vocabulary_item_id)
      );
      setSavedIds(saved);
    } catch (err) {
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  const handleRate = async (rating: number) => {
    const current = dueItems[currentIndex];
    if (!current) return;

    try {
      await vocabularyApi.reviewItem(current.id, rating);
      if (rating >= 4) {
        confetti({ particleCount: 25, spread: 40, origin: { y: 0.7 } });
      }

      if (currentIndex < dueItems.length - 1) {
        setCurrentIndex(currentIndex + 1);
        setIsFlipped(false);
      } else {
        // All due items completed
        setDueItems([]);
        confetti({ particleCount: 70, spread: 60, origin: { y: 0.5 } });
      }
    } catch (err) {
      console.error(err);
    }
  };

  const handleSaveToQueue = async (item: VocabularyItem) => {
    try {
      await vocabularyApi.saveItem(item.id);
      setSavedIds(new Set([...savedIds, item.id]));
      // Refresh due list
      const dueRes = await vocabularyApi.getDueReviews();
      setDueItems(dueRes.due_items || []);
    } catch (err) {
      console.error(err);
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
        <Navbar />
        <div className="flex-1 flex items-center justify-center gap-3">
          <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
          <p className="text-sm font-semibold text-stone-600">جاري تحميل نظام التكرار المتباعد...</p>
        </div>
      </div>
    );
  }

  const currentItem = dueItems[currentIndex];

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-4xl w-full pb-24 md:pb-8">
          {/* Header & Tabs */}
          <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div className="space-y-1">
              <h1 className="text-3xl font-black text-stone-900">
                مراجعة المفردات والتكرار المتباعد
              </h1>
              <p className="text-xs sm:text-sm text-stone-600">
                تثبيت الذاكرة الدائمة وفق خوارزمية SM-2 العلمية.
              </p>
            </div>

            <div className="flex items-center gap-2 bg-stone-100 p-1 rounded-2xl">
              <button
                onClick={() => setActiveTab('review')}
                className={`px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                  activeTab === 'review'
                    ? 'bg-white text-[#1B4D3E] shadow-2xs'
                    : 'text-stone-600 hover:text-stone-900'
                }`}
              >
                المراجعات المستحقة ({dueItems.length})
              </button>
              <button
                onClick={() => setActiveTab('catalog')}
                className={`px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                  activeTab === 'catalog'
                    ? 'bg-white text-[#1B4D3E] shadow-2xs'
                    : 'text-stone-600 hover:text-stone-900'
                }`}
              >
                مكتبة الكلمات المقترحة
              </button>
            </div>
          </div>

          {/* Tab 1: Spaced Repetition Flashcards */}
          {activeTab === 'review' && (
            <div className="space-y-6">
              {dueItems.length > 0 && currentItem ? (
                <div className="space-y-6">
                  {/* Progress Indicator */}
                  <div className="flex items-center justify-between text-xs font-bold text-stone-500">
                    <span>
                      البطاقة {currentIndex + 1} من {dueItems.length}
                    </span>
                    <span className="text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                      مستوى الإتقان: {currentItem.mastery_level}
                    </span>
                  </div>

                  {/* 3D-like Flashcard */}
                  <div
                    onClick={() => {
                      if (!isFlipped) {
                        setIsFlipped(true);
                        if (currentItem.vocabulary_item?.spanish_word) {
                          playSpanishAudio(currentItem.vocabulary_item.spanish_word);
                        }
                      }
                    }}
                    className={`min-h-[280px] sm:min-h-[320px] rounded-3xl p-8 border-2 shadow-lg transition-all flex flex-col items-center justify-center text-center cursor-pointer relative ${
                      isFlipped
                        ? 'bg-emerald-50/70 border-emerald-300'
                        : 'bg-white border-stone-200 hover:border-[#1B4D3E]'
                    }`}
                  >
                    {!isFlipped ? (
                      /* Front of card (Spanish) */
                      <div className="space-y-4">
                        <span className="text-xs font-bold px-3 py-1 rounded-full bg-stone-100 text-stone-500">
                          نطق الكلمة بالإسبانية
                        </span>
                        <div className="flex items-center justify-center gap-3">
                          <div className="text-4xl sm:text-5xl font-black text-stone-900 bidi-isolate tracking-wide">
                            {currentItem.vocabulary_item?.spanish_word}
                          </div>
                          {currentItem.vocabulary_item?.spanish_word && (
                            <AudioSpeakerButton
                              text={currentItem.vocabulary_item.spanish_word}
                              size="lg"
                              showSlowButton={true}
                            />
                          )}
                        </div>
                        {currentItem.vocabulary_item?.phonetic && (
                          <div className="text-xs sm:text-sm text-stone-400 font-semibold">
                            النطق التقريبي: {currentItem.vocabulary_item.phonetic}
                          </div>
                        )}
                        <div className="pt-4 flex items-center gap-1.5 text-xs text-stone-400 font-medium">
                          <Eye className="w-4 h-4" />
                          <span>انقر لكشف المعنى والمثال</span>
                        </div>
                      </div>
                    ) : (
                      /* Back of card (Arabic & Examples) */
                      <div className="space-y-5 animate-fade-in">
                        <div className="flex items-center justify-center gap-2">
                          <div className="text-3xl font-black text-[#1B4D3E]">
                            {currentItem.vocabulary_item?.arabic_meaning}
                          </div>
                          {currentItem.vocabulary_item?.spanish_word && (
                            <AudioSpeakerButton
                              text={currentItem.vocabulary_item.spanish_word}
                              size="md"
                            />
                          )}
                        </div>

                        <div className="space-y-1">
                          <div className="text-sm font-bold text-stone-800 bidi-isolate flex items-center justify-center gap-2">
                            <span>"{currentItem.vocabulary_item?.example_sentence_es}"</span>
                            {currentItem.vocabulary_item?.example_sentence_es && (
                              <AudioSpeakerButton
                                text={currentItem.vocabulary_item.example_sentence_es}
                                size="sm"
                              />
                            )}
                          </div>
                          <div className="text-xs text-stone-500">
                            {currentItem.vocabulary_item?.example_sentence_ar}
                          </div>
                        </div>

                        <div className="text-[11px] text-stone-400">
                          النوع: {currentItem.vocabulary_item?.part_of_speech}
                        </div>
                      </div>
                    )}
                  </div>

                  {/* Rating Buttons (SM-2) */}
                  {isFlipped && (
                    <div className="space-y-3 pt-2">
                      <p className="text-xs text-center font-bold text-stone-600">
                        قيّم سهولة تذكرك لهذه الكلمة (لتحديد موعد المراجعة القادمة):
                      </p>

                      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <button
                          onClick={() => handleRate(1)}
                          className="p-3 rounded-2xl border-2 border-red-200 bg-red-50 hover:bg-red-100 text-red-900 text-xs font-bold transition-colors text-center space-y-1"
                        >
                          <div>لم أتذكرها</div>
                          <div className="text-[10px] text-red-600 font-normal">إعادة غداً (1 يوم)</div>
                        </button>

                        <button
                          onClick={() => handleRate(3)}
                          className="p-3 rounded-2xl border-2 border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-bold transition-colors text-center space-y-1"
                        >
                          <div>بصعوبة</div>
                          <div className="text-[10px] text-amber-700 font-normal">بعد 3 أيام</div>
                        </button>

                        <button
                          onClick={() => handleRate(4)}
                          className="p-3 rounded-2xl border-2 border-teal-200 bg-teal-50 hover:bg-teal-100 text-teal-900 text-xs font-bold transition-colors text-center space-y-1"
                        >
                          <div>جيد بعد تردد</div>
                          <div className="text-[10px] text-teal-700 font-normal">بعد 6 أيام</div>
                        </button>

                        <button
                          onClick={() => handleRate(5)}
                          className="p-3 rounded-2xl border-2 border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-950 text-xs font-bold transition-colors text-center space-y-1"
                        >
                          <div>إتقان تام فوري!</div>
                          <div className="text-[10px] text-emerald-700 font-normal">بعد أسبوعين+</div>
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              ) : (
                /* Empty state: all done! */
                <div className="bg-white rounded-3xl p-10 text-center border border-stone-200 shadow-xs space-y-4">
                  <div className="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                    <CheckCircle2 className="w-8 h-8" />
                  </div>
                  <h3 className="text-xl font-bold text-stone-900">
                    أحسنت! لا توجد بطاقات مستحقة للمراجعة حالياً
                  </h3>
                  <p className="text-xs text-stone-500 max-w-md mx-auto leading-relaxed">
                    لقد أكملت جميع مراجعاتك المجدولة لهذا اليوم. يمكنك إضافة كلمات جديدة من المكتبة أدناه لحفظها.
                  </p>
                  <button
                    onClick={() => setActiveTab('catalog')}
                    className="px-6 py-2.5 rounded-xl bg-[#1B4D3E] text-white text-xs font-bold shadow-xs hover:bg-[#153e32]"
                  >
                    استعراض الكلمات المقترحة
                  </button>
                </div>
              )}
            </div>
          )}

          {/* Tab 2: Catalog Browser */}
          {activeTab === 'catalog' && (
            <div className="space-y-4">
              {/* Filter by CEFR Level */}
              <div className="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                {['ALL', 'A1', 'A2', 'B1', 'B2'].map((lvl) => (
                  <button
                    key={lvl}
                    type="button"
                    onClick={() => setVocabLevelFilter(lvl)}
                    className={`px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                      vocabLevelFilter === lvl
                        ? 'bg-[#1B4D3E] text-white shadow-2xs'
                        : 'bg-white border border-stone-200 text-stone-600 hover:border-emerald-300'
                    }`}
                  >
                    {lvl === 'ALL' ? 'كل المفردات' : `مستوى ${lvl}`}
                  </button>
                ))}
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {catalogItems
                  .filter((item) => vocabLevelFilter === 'ALL' || item.cefr_level === vocabLevelFilter)
                  .map((item) => {
                    const isSaved = savedIds.has(item.id);

                    return (
                  <div
                    key={item.id}
                    className="p-5 rounded-2xl bg-white border border-stone-200 hover:border-emerald-300 shadow-2xs flex items-center justify-between transition-all"
                  >
                    <div className="space-y-1 text-right">
                      <div className="flex items-center gap-2">
                        <span className="font-black text-lg text-stone-900 bidi-isolate">
                          {item.spanish_word}
                        </span>
                        <AudioSpeakerButton text={item.spanish_word} size="sm" />
                        <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-stone-100 text-stone-500">
                          {item.cefr_level}
                        </span>
                      </div>
                      <div className="text-sm font-semibold text-emerald-800">
                        {item.arabic_meaning}
                      </div>
                      <div className="text-xs text-stone-400 italic bidi-isolate flex items-center gap-1.5">
                        <span>{item.example_sentence_es}</span>
                        <AudioSpeakerButton text={item.example_sentence_es} size="sm" />
                      </div>
                    </div>

                    <button
                      disabled={isSaved}
                      onClick={() => handleSaveToQueue(item)}
                      className={`p-2.5 rounded-xl transition-all ${
                        isSaved
                          ? 'bg-emerald-50 text-emerald-700 cursor-default'
                          : 'bg-stone-100 hover:bg-[#1B4D3E] hover:text-white text-stone-600'
                      }`}
                      title={isSaved ? 'تمت إضافتها لمراجعاتك' : 'إضافة إلى المراجعة اليومية'}
                    >
                      {isSaved ? (
                        <BookmarkCheck className="w-5 h-5" />
                      ) : (
                        <BookmarkPlus className="w-5 h-5" />
                      )}
                    </button>
                  </div>
                );
              })}
              </div>
            </div>
          )}
        </main>
      </div>
    </div>
  );
};
