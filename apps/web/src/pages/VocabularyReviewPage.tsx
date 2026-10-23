import React, { useEffect, useState } from 'react';
import { vocabularyApi } from '../api/vocabulary';
import { UserVocabulary, VocabularyItem } from '../types';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import { AudioSpeakerButton } from '../components/AudioSpeakerButton';
import {
  Compass,
  CheckCircle2,
  BookmarkPlus,
  BookmarkCheck,
  Eye,
  Search,
  BookOpen,
  Zap,
  MessageSquare,
  Sparkles,
  Filter
} from 'lucide-react';
import confetti from 'canvas-confetti';

export const VocabularyReviewPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'catalog' | 'review'>('catalog');

  // Review states
  const [dueItems, setDueItems] = useState<UserVocabulary[]>([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isFlipped, setIsFlipped] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  // Catalog states
  const [catalogItems, setCatalogItems] = useState<VocabularyItem[]>([]);
  const [savedIds, setSavedIds] = useState<Set<number>>(new Set());
  const [vocabLevelFilter, setVocabLevelFilter] = useState<string>('A1');
  const [selectedTopic, setSelectedTopic] = useState<string>('ALL');
  const [searchQuery, setSearchQuery] = useState<string>('');

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
      const dueRes = await vocabularyApi.getDueReviews();
      setDueItems(dueRes.due_items || []);
    } catch (err) {
      console.error(err);
    }
  };

  const topicOptions = [
    { key: 'ALL', label: 'الكل' },
    { key: 'core_verbs', label: '⚡ أفعال أساسية' },
    { key: 'essential_sentences', label: '💬 جمل وتعبيرات يومية' },
    { key: 'greetings', label: '👋 تحيات ومجاملات' },
    { key: 'numbers_time', label: '⏰ أرقام ووقت وأيام' },
    { key: 'family_people', label: '👨‍👩‍👧 عائلة وأشخاص' },
    { key: 'food_dining', label: '🍽️ طعام ومطعم' },
    { key: 'city_transport', label: '🏙️ مدينة ومواصلات' },
    { key: 'house_daily', label: '🏠 المنزل وأشياء يومية' },
    { key: 'feelings_adjectives', label: '✨ صفات ومشاعر' },
  ];

  const filteredCatalog = catalogItems.filter((item) => {
    const matchLevel = vocabLevelFilter === 'ALL' || item.cefr_level === vocabLevelFilter;
    const matchTopic = selectedTopic === 'ALL' || item.topic === selectedTopic;
    const matchSearch =
      !searchQuery.trim() ||
      item.spanish_word.toLowerCase().includes(searchQuery.toLowerCase()) ||
      item.arabic_meaning.includes(searchQuery) ||
      (item.example_sentence_es && item.example_sentence_es.toLowerCase().includes(searchQuery.toLowerCase()));

    return matchLevel && matchTopic && matchSearch;
  });

  const getPartOfSpeechBadge = (pos: string) => {
    switch (pos) {
      case 'verb':
        return { label: 'فعل', color: 'bg-amber-100 text-amber-800' };
      case 'sentence':
        return { label: 'جملة مهمة', color: 'bg-teal-100 text-teal-800' };
      case 'expression':
        return { label: 'تعبير / مجاملة', color: 'bg-blue-100 text-blue-800' };
      case 'phrase':
        return { label: 'عبارة', color: 'bg-purple-100 text-purple-800' };
      case 'adjective':
        return { label: 'صفة', color: 'bg-rose-100 text-rose-800' };
      case 'adverb':
        return { label: 'ظرف', color: 'bg-indigo-100 text-indigo-800' };
      default:
        return { label: 'اسم', color: 'bg-stone-100 text-stone-700' };
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
        <Navbar />
        <div className="flex-1 flex items-center justify-center gap-3">
          <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
          <p className="text-sm font-semibold text-stone-600">جاري تحميل بنك المفردات...</p>
        </div>
      </div>
    );
  }

  const currentDue = dueItems[currentIndex];

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl w-full pb-24 md:pb-8">
          {/* Header */}
          <div className="space-y-3">
            <div className="flex flex-wrap items-center gap-2">
              <span className="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold border border-emerald-300">
                حصيلة لغوية ضخمة ({catalogItems.length} كلمة وجملة معتمدة)
              </span>
              <span className="text-xs px-3 py-1 rounded-full bg-amber-100 text-amber-900 font-bold border border-amber-300">
                نظام التكرار المتباعد (SM-2)
              </span>
            </div>

            <h1 className="text-2xl sm:text-3xl font-black text-stone-900">
              بنك المفردات والجمل والأفعال الإسبانية
            </h1>
            <p className="text-sm text-stone-600 leading-relaxed max-w-2xl">
              تصفح أكثر من 250+ كلمة وجملة للمستوى مع النطق الصوتي الأصيل، واحفظ أي كلمة إلى قائمة مراجعتك اليومية لترسيخها في الذاكرة الدائمة.
            </p>
          </div>

          {/* Mode Switch Tabs */}
          <div className="flex items-center gap-3 border-b border-stone-200 pb-3">
            <button
              onClick={() => setActiveTab('catalog')}
              className={`px-5 py-2.5 rounded-2xl text-xs font-bold transition-all flex items-center gap-2 ${
                activeTab === 'catalog'
                  ? 'bg-[#1B4D3E] text-white shadow-sm'
                  : 'bg-white border border-stone-200 text-stone-600 hover:border-emerald-300'
              }`}
            >
              <BookOpen className="w-4 h-4" />
              <span>مكتبة المفردات الشاملة ({filteredCatalog.length})</span>
            </button>

            <button
              onClick={() => setActiveTab('review')}
              className={`px-5 py-2.5 rounded-2xl text-xs font-bold transition-all flex items-center gap-2 ${
                activeTab === 'review'
                  ? 'bg-[#1B4D3E] text-white shadow-sm'
                  : 'bg-white border border-stone-200 text-stone-600 hover:border-emerald-300'
              }`}
            >
              <Sparkles className="w-4 h-4 text-amber-400" />
              <span>جلسة المراجعة اليومية ({dueItems.length} مستحقة)</span>
            </button>
          </div>

          {/* TAB 1: CATALOG BROWSER */}
          {activeTab === 'catalog' && (
            <div className="space-y-5">
              {/* Filter Controls */}
              <div className="bg-white p-4 rounded-3xl border border-stone-200 space-y-3 shadow-xs">
                {/* Search Box */}
                <div className="relative">
                  <Search className="w-5 h-5 text-stone-400 absolute right-3.5 top-1/2 -translate-y-1/2" />
                  <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="ابحث عن أي كلمة، فعل، أو جملة بالإسبانية أو العربية..."
                    className="w-full pr-11 pl-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:outline-none focus:border-[#1B4D3E]"
                  />
                </div>

                {/* CEFR Level Selector */}
                <div className="flex flex-wrap items-center gap-2 pt-1">
                  <span className="text-xs font-bold text-stone-400 ml-2">المستوى:</span>
                  {['A1', 'A2', 'ALL'].map((lvl) => (
                    <button
                      key={lvl}
                      type="button"
                      onClick={() => setVocabLevelFilter(lvl)}
                      className={`px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all ${
                        vocabLevelFilter === lvl
                          ? 'bg-[#1B4D3E] text-white shadow-xs'
                          : 'bg-stone-50 text-stone-600 hover:bg-stone-100 border border-stone-200'
                      }`}
                    >
                      {lvl === 'ALL' ? 'كل المستويات' : `مستوى ${lvl}`}
                    </button>
                  ))}
                </div>

                {/* Topics Filter */}
                <div className="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none pt-1">
                  {topicOptions.map((tp) => (
                    <button
                      key={tp.key}
                      type="button"
                      onClick={() => setSelectedTopic(tp.key)}
                      className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all ${
                        selectedTopic === tp.key
                          ? 'bg-amber-500 text-white shadow-xs'
                          : 'bg-stone-50 text-stone-600 hover:bg-stone-100 border border-stone-200'
                      }`}
                    >
                      {tp.label}
                    </button>
                  ))}
                </div>
              </div>

              {/* Items Grid */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {filteredCatalog.map((item) => {
                  const isSaved = savedIds.has(item.id);
                  const posBadge = getPartOfSpeechBadge(item.part_of_speech);

                  return (
                    <div
                      key={item.id}
                      className="p-5 rounded-2xl bg-white border border-stone-200 hover:border-emerald-300 shadow-2xs flex items-start justify-between gap-3 transition-all"
                    >
                      <div className="space-y-2 text-start flex-1">
                        <div className="flex flex-wrap items-center gap-2">
                          <span className="font-black text-lg text-stone-900" dir="ltr">
                            {item.spanish_word}
                          </span>
                          <AudioSpeakerButton text={item.spanish_word} size="sm" />
                          <span
                            className={`text-[10px] font-bold px-2 py-0.5 rounded-md ${posBadge.color}`}
                          >
                            {posBadge.label}
                          </span>
                          <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-stone-100 text-stone-600">
                            {item.cefr_level}
                          </span>
                        </div>

                        <div className="text-sm font-bold text-emerald-800">
                          {item.arabic_meaning}
                        </div>

                        {item.phonetic && (
                          <div className="text-xs text-stone-400">
                            نطق: <span className="text-stone-600 font-medium">{item.phonetic}</span>
                          </div>
                        )}

                        {item.example_sentence_es && (
                          <div className="p-2.5 rounded-xl bg-stone-50 border border-stone-100 space-y-1">
                            <div className="text-xs text-stone-800 font-medium flex items-center justify-between gap-2" dir="ltr">
                              <span>"{item.example_sentence_es}"</span>
                              <AudioSpeakerButton text={item.example_sentence_es} size="sm" />
                            </div>
                            <div className="text-[11px] text-stone-500">
                              {item.example_sentence_ar}
                            </div>
                          </div>
                        )}
                      </div>

                      <button
                        disabled={isSaved}
                        onClick={() => handleSaveToQueue(item)}
                        className={`p-2.5 rounded-xl transition-all shrink-0 ${
                          isSaved
                            ? 'bg-emerald-50 text-emerald-700 cursor-default'
                            : 'bg-stone-100 hover:bg-[#1B4D3E] hover:text-white text-stone-600'
                        }`}
                        title={isSaved ? 'تمت إضافتها لمراجعاتك' : 'إضافة إلى المراجعة اليومية (SM-2)'}
                      >
                        {isSaved ? <BookmarkCheck className="w-5 h-5" /> : <BookmarkPlus className="w-5 h-5" />}
                      </button>
                    </div>
                  );
                })}
              </div>

              {filteredCatalog.length === 0 && (
                <div className="p-12 text-center bg-white rounded-3xl border border-stone-200 text-stone-500 space-y-2">
                  <p className="font-semibold text-base">لا توجد مفردات مطابقة للبحث أو الفلتر المحدد</p>
                  <p className="text-xs">جرب تغيير كلمات البحث أو اختيار تصنيف آخر.</p>
                </div>
              )}
            </div>
          )}

          {/* TAB 2: SM-2 REVIEW SESSION */}
          {activeTab === 'review' && (
            <div className="space-y-6 max-w-2xl mx-auto">
              {dueItems.length > 0 && currentDue ? (
                <div className="space-y-6">
                  {/* Progress Header */}
                  <div className="flex items-center justify-between text-xs text-stone-500 font-bold">
                    <span>
                      بطاقة {currentIndex + 1} من {dueItems.length}
                    </span>
                    <span>
                      {Math.round(((currentIndex + 1) / dueItems.length) * 100)}% مكتمل
                    </span>
                  </div>

                  {/* Flashcard */}
                  <div
                    onClick={() => setIsFlipped(!isFlipped)}
                    className="min-h-[260px] p-8 rounded-3xl bg-white border-2 border-stone-200 hover:border-emerald-300 shadow-sm cursor-pointer transition-all flex flex-col items-center justify-center text-center space-y-4"
                  >
                    {!isFlipped ? (
                      /* Front: Spanish */
                      <div className="space-y-4">
                        <span className="text-xs px-3 py-1 bg-amber-100 text-amber-800 rounded-full font-bold">
                          {currentDue.vocabulary_item?.cefr_level}
                        </span>
                        <div className="text-3xl sm:text-4xl font-black text-stone-900" dir="ltr">
                          {currentDue.vocabulary_item?.spanish_word}
                        </div>
                        {currentDue.vocabulary_item?.phonetic && (
                          <div className="text-xs text-stone-500">
                            النطق: {currentDue.vocabulary_item.phonetic}
                          </div>
                        )}
                        <div className="pt-4 flex items-center justify-center gap-1.5 text-xs text-stone-400 font-medium">
                          <Eye className="w-4 h-4" />
                          <span>انقر لكشف المعنى والمثال العربي</span>
                        </div>
                      </div>
                    ) : (
                      /* Back: Arabic Meaning & Example */
                      <div className="space-y-4 animate-fade-in w-full">
                        <div className="text-3xl font-black text-[#1B4D3E]">
                          {currentDue.vocabulary_item?.arabic_meaning}
                        </div>
                        <div className="flex items-center justify-center gap-2">
                          <AudioSpeakerButton
                            text={currentDue.vocabulary_item?.spanish_word || ''}
                            size="md"
                          />
                        </div>

                        {currentDue.vocabulary_item?.example_sentence_es && (
                          <div className="p-3 bg-stone-50 rounded-2xl border border-stone-100 space-y-1 max-w-md mx-auto text-center">
                            <div className="text-sm font-bold text-stone-800" dir="ltr">
                              "{currentDue.vocabulary_item.example_sentence_es}"
                            </div>
                            <div className="text-xs text-stone-500">
                              {currentDue.vocabulary_item.example_sentence_ar}
                            </div>
                          </div>
                        )}
                      </div>
                    )}
                  </div>

                  {/* SM-2 Rating Buttons */}
                  {isFlipped && (
                    <div className="space-y-3 pt-2">
                      <p className="text-xs text-center font-bold text-stone-600">
                        قيّم سهولة تذكرك للكلمة لتحديد موعد المراجعة القادمة:
                      </p>

                      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <button
                          onClick={() => handleRate(1)}
                          className="p-3 rounded-2xl border-2 border-red-200 bg-red-50 hover:bg-red-100 text-red-900 text-xs font-bold transition-colors text-center space-y-1"
                        >
                          <div>لم أتذكرها</div>
                          <div className="text-[10px] text-red-600 font-normal">إعادة غداً</div>
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
                          <div>تذكرتها جيداً</div>
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
                /* Completed State */
                <div className="bg-white rounded-3xl p-10 text-center border border-stone-200 shadow-xs space-y-4">
                  <div className="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                    <CheckCircle2 className="w-8 h-8" />
                  </div>
                  <h3 className="text-xl font-bold text-stone-900">
                    أحسنت! لا توجد بطاقات مستحقة للمراجعة حالياً
                  </h3>
                  <p className="text-xs text-stone-500 max-w-md mx-auto leading-relaxed">
                    لقد أكملت جميع مراجعاتك المجدولة لهذا اليوم. يمكنك إضافة كلمات جديدة من المكتبة لحفظها ومراجعتها.
                  </p>
                  <button
                    onClick={() => setActiveTab('catalog')}
                    className="px-6 py-2.5 rounded-xl bg-[#1B4D3E] text-white text-xs font-bold shadow-xs hover:bg-[#153e32]"
                  >
                    استعراض مكتبة الكلمات
                  </button>
                </div>
              )}
            </div>
          )}
        </main>
      </div>
    </div>
  );
};
export default VocabularyReviewPage;
