import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { journalApi } from '../api/journal';
import { JournalEntry, WritingAnalysis, WritingSuggestion, JournalPrompt, FollowUpExercise, LearningRecommendation, LearnerErrorConcept } from '../types';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import { AudioSpeakerButton } from '../components/AudioSpeakerButton';
import { Link } from 'react-router-dom';
import {
  Compass,
  PenTool,
  Sparkles,
  BookOpen,
  Calendar,
  CheckCircle2,
  AlertCircle,
  Trash2,
  Edit3,
  Lightbulb,
  ArrowRight,
  TrendingUp,
  Award,
  Plus,
  BookmarkCheck,
  Clock,
  HelpCircle,
  ExternalLink,
  Target,
  RefreshCw,
  Check,
  Info
} from 'lucide-react';
import confetti from 'canvas-confetti';

export const JournalPage: React.FC = () => {
  const { t, i18n } = useTranslation();
  const isEn = i18n.language === 'en';

  // Navigation tab
  const [activeTab, setActiveTab] = useState<'entries' | 'followup' | 'insights'>('entries');

  // Entries and Stats state
  const [entries, setEntries] = useState<JournalEntry[]>([]);
  const [stats, setStats] = useState({
    total_entries: 0,
    total_words_written: 0,
    active_writing_days: 0,
    concepts_monitored: 0,
    concepts_mastered: 0,
    average_words_per_entry: 0,
  });
  const [progressDisclaimer, setProgressDisclaimer] = useState({ ar: '', en: '' });
  const [isLoading, setIsLoading] = useState(true);

  // Active Writing Form state
  const [isEditorOpen, setIsEditorOpen] = useState(false);
  const [editingEntryId, setEditingEntryId] = useState<number | null>(null);
  const [entryTitle, setEntryTitle] = useState('');
  const [entryContent, setEntryContent] = useState('');
  const [writingMode, setWritingMode] = useState<'guided' | 'free'>('guided');
  const [selectedPrompt, setSelectedPrompt] = useState<JournalPrompt | null>(null);
  const [availablePrompts, setAvailablePrompts] = useState<JournalPrompt[]>([]);
  const [dailyPrompt, setDailyPrompt] = useState<JournalPrompt | null>(null);
  const [isSaving, setIsSaving] = useState(false);

  // Analysis / Feedback Modal state
  const [activeAnalysis, setActiveAnalysis] = useState<{ analysis: WritingAnalysis; entry: JournalEntry } | null>(null);
  const [isAnalyzing, setIsAnalyzing] = useState(false);

  // Follow-Up Exercises state
  const [followUpExercises, setFollowUpExercises] = useState<FollowUpExercise[]>([]);
  const [activeExerciseIndex, setActiveExerciseIndex] = useState(0);
  const [selectedAnswer, setSelectedAnswer] = useState<string | null>(null);
  const [exerciseFeedback, setExerciseFeedback] = useState<{ isCorrect: boolean; message: string } | null>(null);
  const [isSubmittingExercise, setIsSubmittingExercise] = useState(false);

  // Recurring Mistakes & Curriculum Recommendations
  const [recurringMistakes, setRecurringMistakes] = useState<LearnerErrorConcept[]>([]);
  const [recommendedLessons, setRecommendedLessons] = useState<LearningRecommendation[]>([]);

  const spanishSpecialChars = ['á', 'é', 'í', 'ó', 'ú', 'ñ', '¿', '¡'];

  useEffect(() => {
    loadJournal();
    loadPrompts();
    loadInsights();
  }, []);

  const loadJournal = async () => {
    setIsLoading(true);
    try {
      const res = await journalApi.getEntries();
      setEntries(res.entries.data || []);

      const statsRes = await journalApi.getProgressStats();
      if (statsRes?.stats) {
        setStats(statsRes.stats);
        setProgressDisclaimer({
          ar: statsRes.disclaimer_ar,
          en: statsRes.disclaimer_en,
        });
      }
    } catch (err) {
      console.error('Failed to load journal:', err);
    } finally {
      setIsLoading(false);
    }
  };

  const loadPrompts = async () => {
    try {
      const res = await journalApi.getPrompts();
      setAvailablePrompts(res.prompts || []);
      setDailyPrompt(res.daily_prompt || null);
      if (res.daily_prompt && !selectedPrompt) {
        setSelectedPrompt(res.daily_prompt);
      }
    } catch (err) {
      console.error('Failed to load prompts:', err);
    }
  };

  const loadInsights = async () => {
    try {
      const mistakesRes = await journalApi.getRecurringMistakes();
      setRecurringMistakes(mistakesRes.recurring_mistakes || []);

      const recsRes = await journalApi.getRecommendedLessons();
      setRecommendedLessons(recsRes.recommendations || []);

      const followUpRes = await journalApi.getFollowUpExercises();
      setFollowUpExercises(followUpRes.exercises || []);
    } catch (err) {
      console.error('Failed to load insights:', err);
    }
  };

  const insertChar = (char: string) => {
    setEntryContent((prev) => prev + char);
  };

  const insertStarterPhrase = (phrase: string) => {
    setEntryContent((prev) => (prev ? `${prev} ${phrase} ` : `${phrase} `));
  };

  const handleSaveDraft = async () => {
    if (!entryContent.trim()) return;
    setIsSaving(true);
    try {
      if (editingEntryId) {
        await journalApi.updateEntry(editingEntryId, {
          title: entryTitle,
          content: entryContent,
        });
      } else {
        await journalApi.createEntry({
          title: entryTitle || selectedPrompt?.title_es || undefined,
          content: entryContent,
          prompt_topic: writingMode === 'guided' ? selectedPrompt?.id : 'free_writing',
          cefr_level: selectedPrompt?.level || 'A1',
        });
      }
      setIsEditorOpen(false);
      setEditingEntryId(null);
      setEntryTitle('');
      setEntryContent('');
      loadJournal();
    } catch (err) {
      console.error(err);
    } finally {
      setIsSaving(false);
    }
  };

  const handleAnalyzeEntry = async (entry: JournalEntry) => {
    setIsAnalyzing(true);
    try {
      const res = await journalApi.analyzeEntry(entry.id, isEn ? 'en' : 'ar');
      setActiveAnalysis({
        analysis: res.analysis,
        entry: res.entry,
      });
      confetti({ particleCount: 35, spread: 60, origin: { y: 0.6 } });
      loadJournal();
      loadInsights();
    } catch (err) {
      console.error(err);
    } finally {
      setIsAnalyzing(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!window.confirm(isEn ? 'Are you sure you want to delete this journal entry?' : 'هل أنت متأكد من حذف هذه التدوينة؟')) return;
    try {
      await journalApi.deleteEntry(id);
      loadJournal();
    } catch (err) {
      console.error(err);
    }
  };

  const handleUpdateSuggestionStatus = async (
    suggestionId: number,
    status: 'understood' | 'review_later'
  ) => {
    try {
      const res = await journalApi.updateSuggestionStatus(suggestionId, status);
      if (activeAnalysis) {
        const updatedSuggestions = activeAnalysis.analysis.suggestions?.map((s) =>
          s.id === suggestionId ? { ...s, status, is_understood: status === 'understood' } : s
        );
        setActiveAnalysis({
          ...activeAnalysis,
          analysis: {
            ...activeAnalysis.analysis,
            suggestions: updatedSuggestions,
          },
        });
      }
      loadInsights();
    } catch (err) {
      console.error('Failed to update suggestion status:', err);
    }
  };

  const handleSubmitExercise = async () => {
    if (!selectedAnswer || !followUpExercises[activeExerciseIndex]) return;
    const current = followUpExercises[activeExerciseIndex];
    setIsSubmittingExercise(true);
    try {
      const res = await journalApi.submitFollowUp({
        concept_slug: current.concept_slug,
        user_answer: selectedAnswer,
        correct_answer: current.correct_answer,
      });

      setExerciseFeedback({
        isCorrect: res.is_correct,
        message: res.message,
      });

      if (res.is_correct) {
        confetti({ particleCount: 25, spread: 45, origin: { y: 0.7 } });
        loadInsights();
      }
    } catch (err) {
      console.error(err);
    } finally {
      setIsSubmittingExercise(false);
    }
  };

  const currentWordCount = entryContent.trim()
    ? entryContent.trim().split(/\s+/).length
    : 0;

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col font-sans" dir={isEn ? 'ltr' : 'rtl'}>
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl w-full pb-24 md:pb-8">
          {/* Header */}
          <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div className="space-y-1">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-[#1B4D3E] text-xs font-bold">
                <PenTool className="w-3.5 h-3.5" />
                <span>{isEn ? 'Real-life Language Acquisition' : 'التعلم من خلال حياتك الواقعية'}</span>
              </div>
              <h1 className="text-2xl sm:text-3xl font-black text-stone-900">
                {isEn ? 'Spanish Journal & Writing Lab' : 'دفتر اليوميات ومختبر الكتابة'}
              </h1>
              <p className="text-xs sm:text-sm text-stone-600 max-w-2xl">
                {isEn
                  ? 'Express your thoughts and daily life in Spanish. Our system pinpoints grammar, conjugations, and false friends without overwriting your original voice.'
                  : 'اكتب عن يومك وأفكارك بالإسبانية. يحلل النظام قواعدك وأفعالك وتراكيبك بدقة مع الحفاظ الكامل على نصك الأصلي.'}
              </p>
            </div>

            {!isEditorOpen && (
              <button
                type="button"
                onClick={() => {
                  setEditingEntryId(null);
                  setEntryTitle('');
                  setEntryContent('');
                  setIsEditorOpen(true);
                }}
                className="px-5 py-3 rounded-2xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] flex items-center gap-2 cursor-pointer transition-all self-end sm:self-auto"
              >
                <Plus className="w-4 h-4" />
                <span>{isEn ? 'New Entry' : 'تدوينة جديدة'}</span>
              </button>
            )}
          </div>

          {/* Navigation Tabs */}
          <div className="flex items-center gap-2 border-b border-stone-200 pb-2 overflow-x-auto">
            <button
              type="button"
              onClick={() => setActiveTab('entries')}
              className={`px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center gap-2 ${
                activeTab === 'entries'
                  ? 'bg-[#1B4D3E] text-white shadow-2xs'
                  : 'bg-white text-stone-600 hover:bg-stone-100 border border-stone-200'
              }`}
            >
              <BookOpen className="w-4 h-4" />
              <span>{isEn ? 'My Entries' : 'تدويناتي'}</span>
              <span className="px-1.5 py-0.5 rounded-full bg-white/20 text-[10px]">
                {entries.length}
              </span>
            </button>

            <button
              type="button"
              onClick={() => setActiveTab('followup')}
              className={`px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center gap-2 ${
                activeTab === 'followup'
                  ? 'bg-[#1B4D3E] text-white shadow-2xs'
                  : 'bg-white text-stone-600 hover:bg-stone-100 border border-stone-200'
              }`}
            >
              <Target className="w-4 h-4 text-amber-400" />
              <span>{isEn ? 'Follow-Up Practice' : 'تمارين المتابعة المستهدفة'}</span>
              {followUpExercises.length > 0 && (
                <span className="px-1.5 py-0.5 rounded-full bg-amber-500 text-white text-[10px]">
                  {followUpExercises.length}
                </span>
              )}
            </button>

            <button
              type="button"
              onClick={() => setActiveTab('insights')}
              className={`px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center gap-2 ${
                activeTab === 'insights'
                  ? 'bg-[#1B4D3E] text-white shadow-2xs'
                  : 'bg-white text-stone-600 hover:bg-stone-100 border border-stone-200'
              }`}
            >
              <TrendingUp className="w-4 h-4 text-emerald-400" />
              <span>{isEn ? 'Recurring Mistakes & Lessons' : 'الأخطاء المتكررة والدروس'}</span>
              {recurringMistakes.length > 0 && (
                <span className="px-1.5 py-0.5 rounded-full bg-red-500 text-white text-[10px]">
                  {recurringMistakes.length}
                </span>
              )}
            </button>
          </div>

          {/* Quick Stats Grid with Honest Transparency */}
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div className="p-3.5 rounded-2xl bg-white border border-stone-200 shadow-2xs text-center space-y-1">
              <div className="text-[11px] text-stone-400 font-semibold">{isEn ? 'Total Entries' : 'إجمالي التدوينات'}</div>
              <div className="text-xl sm:text-2xl font-black text-[#1B4D3E]">{stats.total_entries}</div>
            </div>
            <div className="p-3.5 rounded-2xl bg-white border border-stone-200 shadow-2xs text-center space-y-1">
              <div className="text-[11px] text-stone-400 font-semibold">{isEn ? 'Words Written' : 'الكلمات المكتوبة'}</div>
              <div className="text-xl sm:text-2xl font-black text-amber-600">{stats.total_words_written}</div>
            </div>
            <div className="p-3.5 rounded-2xl bg-white border border-stone-200 shadow-2xs text-center space-y-1">
              <div className="text-[11px] text-stone-400 font-semibold">{isEn ? 'Active Days' : 'أيام الكتابة'}</div>
              <div className="text-xl sm:text-2xl font-black text-emerald-600">{stats.active_writing_days}</div>
            </div>
            <div className="p-3.5 rounded-2xl bg-white border border-stone-200 shadow-2xs text-center space-y-1">
              <div className="text-[11px] text-stone-400 font-semibold">{isEn ? 'Rules Monitored' : 'القواعد المتابعة'}</div>
              <div className="text-xl sm:text-2xl font-black text-blue-600">{stats.concepts_monitored}</div>
            </div>
          </div>

          {/* Honest Fluency Disclaimer Card */}
          {progressDisclaimer.ar && (
            <div className="p-3.5 rounded-2xl bg-stone-100 border border-stone-200/80 flex items-start gap-2.5 text-xs text-stone-600 leading-relaxed">
              <Info className="w-4 h-4 text-stone-400 shrink-0 mt-0.5" />
              <span>{isEn ? progressDisclaimer.en : progressDisclaimer.ar}</span>
            </div>
          )}

          {/* TAB 1: ENTRIES & ACTIVE EDITOR */}
          {activeTab === 'entries' && (
            <div className="space-y-6">
              {/* Active Writing Editor Card */}
              {isEditorOpen && (
                <div className="bg-white rounded-3xl p-6 sm:p-8 border-2 border-emerald-500/70 shadow-lg space-y-5 animate-fade-in">
                  <div className="flex items-center justify-between border-b border-stone-100 pb-3">
                    <div className="flex items-center gap-2">
                      <span className="text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-800">
                        {editingEntryId ? (isEn ? 'Edit Entry' : 'تعديل التدوينة') : (isEn ? 'New Entry' : 'تدوينة جديدة')}
                      </span>
                      <div className="inline-flex rounded-xl bg-stone-100 p-0.5 text-xs">
                        <button
                          type="button"
                          onClick={() => setWritingMode('guided')}
                          className={`px-3 py-1 rounded-lg font-bold transition-colors ${
                            writingMode === 'guided' ? 'bg-white text-emerald-900 shadow-2xs' : 'text-stone-500'
                          }`}
                        >
                          {isEn ? 'Guided Mode' : 'كتابة موجهة'}
                        </button>
                        <button
                          type="button"
                          onClick={() => setWritingMode('free')}
                          className={`px-3 py-1 rounded-lg font-bold transition-colors ${
                            writingMode === 'free' ? 'bg-white text-emerald-900 shadow-2xs' : 'text-stone-500'
                          }`}
                        >
                          {isEn ? 'Free Writing' : 'كتابة حرة'}
                        </button>
                      </div>
                    </div>
                    <button
                      type="button"
                      onClick={() => setIsEditorOpen(false)}
                      className="text-xs text-stone-400 hover:text-stone-700 cursor-pointer"
                    >
                      {isEn ? 'Cancel ×' : 'إلغاء ×'}
                    </button>
                  </div>

                  {/* Guided Mode Scaffolding */}
                  {writingMode === 'guided' && selectedPrompt && (
                    <div className="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200/70 space-y-3">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <span className="px-2 py-0.5 rounded-md bg-emerald-700 text-white font-black text-[10px]">
                            {selectedPrompt.level}
                          </span>
                          <span className="font-bold text-xs text-emerald-900">
                            {isEn ? selectedPrompt.title_en : selectedPrompt.title_ar}
                          </span>
                        </div>
                        <span className="text-[11px] text-emerald-700 font-semibold">
                          {isEn ? `Target: ~${selectedPrompt.target_word_count} words` : `الهدف: ~${selectedPrompt.target_word_count} كلمة`}
                        </span>
                      </div>

                      <p className="text-xs text-stone-700 leading-relaxed font-medium">
                        {isEn ? selectedPrompt.description_en : selectedPrompt.description_ar}
                      </p>

                      {/* Starter phrases */}
                      {selectedPrompt.starter_phrases && selectedPrompt.starter_phrases.length > 0 && (
                        <div className="space-y-1.5 pt-1">
                          <div className="text-[11px] font-bold text-stone-500 flex items-center gap-1">
                            <Lightbulb className="w-3.5 h-3.5 text-amber-500" />
                            <span>{isEn ? 'Click to insert starter phrase:' : 'جمل افتتاحية مقترحة (اضغط للإدراج):'}</span>
                          </div>
                          <div className="flex flex-wrap gap-1.5">
                            {selectedPrompt.starter_phrases.map((phrase, idx) => (
                              <button
                                key={idx}
                                type="button"
                                onClick={() => insertStarterPhrase(phrase)}
                                className="px-2.5 py-1 rounded-lg bg-white border border-emerald-300 text-emerald-950 text-xs font-semibold hover:bg-emerald-100 transition-colors"
                              >
                                + {phrase}
                              </button>
                            ))}
                          </div>
                        </div>
                      )}

                      {/* Target grammar & vocab */}
                      <div className="flex flex-wrap items-center gap-3 text-[11px] text-stone-600 pt-1 border-t border-emerald-200/50">
                        <div>
                          <span className="font-bold text-emerald-900">{isEn ? 'Target Grammar: ' : 'القاعدة المستهدفة: '}</span>
                          <span>{selectedPrompt.target_grammar}</span>
                        </div>
                      </div>
                    </div>
                  )}

                  {/* Title input */}
                  <input
                    type="text"
                    value={entryTitle}
                    onChange={(e) => setEntryTitle(e.target.value)}
                    placeholder={isEn ? "Entry title in Spanish (optional)..." : "عنوان التدوينة بالإسبانية (اختياري)..."}
                    dir="ltr"
                    className="w-full p-3.5 rounded-xl border border-stone-300 font-bold text-base focus:border-[#1B4D3E] outline-none"
                  />

                  {/* Spanish special characters toolbar */}
                  <div className="flex items-center gap-1.5 flex-wrap pt-1">
                    <span className="text-xs text-stone-400 mr-2">{isEn ? 'Spanish Accents:' : 'حروف إسبانية خاصة:'}</span>
                    {spanishSpecialChars.map((ch) => (
                      <button
                        key={ch}
                        type="button"
                        onClick={() => insertChar(ch)}
                        className="w-8 h-8 rounded-lg bg-stone-100 hover:bg-emerald-100 font-bold text-stone-800 hover:text-[#1B4D3E] transition-colors shadow-2xs"
                      >
                        {ch}
                      </button>
                    ))}
                  </div>

                  {/* Content textarea */}
                  <div className="space-y-1">
                    <textarea
                      rows={7}
                      value={entryContent}
                      onChange={(e) => setEntryContent(e.target.value)}
                      placeholder={
                        isEn
                          ? "Write here in Spanish about your day, memories, or opinions..."
                          : "اكتب هنا بالإسبانية عن يومك، خططك، أو ذكرياتك..."
                      }
                      dir="ltr"
                      className="w-full p-4 rounded-2xl border border-stone-300 text-base leading-relaxed focus:border-[#1B4D3E] outline-none font-medium text-stone-900"
                    />
                    <div className="flex justify-between items-center text-xs text-stone-400 px-1">
                      <span>
                        {isEn ? `Word count: ${currentWordCount}` : `عدد الكلمات: ${currentWordCount} كلمة`}
                      </span>
                      <span>
                        {selectedPrompt?.target_word_count
                          ? `${Math.min(100, Math.round((currentWordCount / selectedPrompt.target_word_count) * 100))}% ${isEn ? 'of target' : 'من الهدف'}`
                          : ''}
                      </span>
                    </div>
                  </div>

                  {/* Action buttons */}
                  <div className="flex items-center justify-end gap-3 pt-2">
                    <button
                      type="button"
                      onClick={() => setIsEditorOpen(false)}
                      className="px-5 py-2.5 rounded-xl border border-stone-200 text-stone-600 font-bold text-xs hover:bg-stone-50"
                    >
                      {isEn ? 'Cancel' : 'إلغاء'}
                    </button>
                    <button
                      type="button"
                      disabled={isSaving || !entryContent.trim()}
                      onClick={handleSaveDraft}
                      className="px-6 py-2.5 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs shadow-xs hover:bg-[#153e32] disabled:opacity-50"
                    >
                      {isSaving ? (isEn ? 'Saving...' : 'جاري الحفظ...') : (isEn ? 'Save Entry' : 'حفظ التدوينة')}
                    </button>
                  </div>
                </div>
              )}

              {/* Journal Entries List */}
              <div className="space-y-4">
                <h2 className="text-base sm:text-lg font-bold text-stone-900">
                  {isEn ? 'Your Journal Entries' : 'تدويناتك السابقة'}
                </h2>

                {entries.length === 0 && !isLoading ? (
                  <div className="bg-white rounded-3xl p-10 border border-stone-200 text-center space-y-4">
                    <div className="w-14 h-14 rounded-2xl bg-emerald-50 text-[#1B4D3E] flex items-center justify-center mx-auto">
                      <PenTool className="w-7 h-7" />
                    </div>
                    <h3 className="font-bold text-stone-900 text-base">
                      {isEn ? 'Your journal is empty' : 'دفترك فارغ حالياً'}
                    </h3>
                    <p className="text-xs text-stone-500 max-w-md mx-auto leading-relaxed">
                      {isEn
                        ? 'Start your first Spanish entry today. Writing about your actual routines builds lasting retention.'
                        : 'ابدأ كتابة أول تدوينة لك بالإسبانية عن يومك، وسيقوم النظام بتحليل القواعد ومساعدتك على بناء لغتك!'}
                    </p>
                    <button
                      type="button"
                      onClick={() => setIsEditorOpen(true)}
                      className="px-6 py-3 rounded-2xl bg-[#1B4D3E] text-white font-bold text-xs shadow-sm hover:bg-[#153e32]"
                    >
                      {isEn ? 'Write First Entry' : 'اكتب أول تدوينة الآن'}
                    </button>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {entries.map((entry) => (
                      <div
                        key={entry.id}
                        className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-xs hover:border-emerald-300 transition-all space-y-4"
                      >
                        {/* Entry Header */}
                        <div className="flex items-center justify-between">
                          <div className="flex items-center gap-2">
                            <span className="text-[11px] font-bold text-stone-400 flex items-center gap-1">
                              <Calendar className="w-3.5 h-3.5" />
                              <span>{entry.entry_date}</span>
                            </span>
                            <span
                              className={`text-[10px] font-bold px-2.5 py-0.5 rounded-full ${
                                entry.status === 'analyzed'
                                  ? 'bg-emerald-100 text-emerald-800'
                                  : 'bg-amber-100 text-amber-800'
                              }`}
                            >
                              {entry.status === 'analyzed'
                                ? (isEn ? 'Analyzed' : 'تم الفحص لغوياً')
                                : (isEn ? 'Draft' : 'مسودة غير مفحوصة')}
                            </span>
                          </div>

                          <div className="flex items-center gap-1.5">
                            <button
                              type="button"
                              onClick={() => {
                                setEditingEntryId(entry.id);
                                setEntryTitle(entry.title || '');
                                setEntryContent(entry.content);
                                setIsEditorOpen(true);
                              }}
                              className="p-1.5 rounded-lg text-stone-400 hover:text-[#1B4D3E] hover:bg-emerald-50 transition-colors"
                              title={isEn ? 'Edit Text' : 'تعديل النص'}
                            >
                              <Edit3 className="w-4 h-4" />
                            </button>
                            <button
                              type="button"
                              onClick={() => handleDelete(entry.id)}
                              className="p-1.5 rounded-lg text-stone-300 hover:text-red-600 hover:bg-red-50 transition-colors"
                              title={isEn ? 'Delete Entry' : 'حذف التدوينة'}
                            >
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </div>
                        </div>

                        {/* Title & Body */}
                        {entry.title && (
                          <h3 className="text-base font-bold text-stone-900 bidi-isolate" dir="ltr">
                            {entry.title}
                          </h3>
                        )}
                        <p className="text-sm font-medium text-stone-800 leading-relaxed bidi-isolate bg-stone-50/60 p-4 rounded-2xl border border-stone-100" dir="ltr">
                          {entry.content}
                        </p>

                        {/* Footer Actions */}
                        <div className="flex items-center justify-between pt-2 border-t border-stone-100">
                          <div className="flex items-center gap-2">
                            <span className="text-xs text-stone-400">
                              {entry.word_count} {isEn ? 'words' : 'كلمة'}
                            </span>
                            <AudioSpeakerButton text={entry.content} size="sm" />
                          </div>

                          <div className="flex items-center gap-2">
                            {entry.analysis ? (
                              <button
                                type="button"
                                onClick={() => setActiveAnalysis({ analysis: entry.analysis!, entry })}
                                className="px-4 py-2 rounded-xl bg-emerald-50 text-[#1B4D3E] font-bold text-xs hover:bg-emerald-100 flex items-center gap-1.5 transition-colors border border-emerald-200 cursor-pointer"
                              >
                                <Sparkles className="w-3.5 h-3.5 text-amber-500" />
                                <span>
                                  {isEn
                                    ? `View Analysis (${entry.analysis.fluency_score}%)`
                                    : `عرض نتائج الفحص (${entry.analysis.fluency_score}%)`}
                                </span>
                              </button>
                            ) : (
                              <button
                                type="button"
                                disabled={isAnalyzing}
                                onClick={() => handleAnalyzeEntry(entry)}
                                className="px-4 py-2 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs hover:bg-[#153e32] flex items-center gap-1.5 transition-colors shadow-2xs cursor-pointer"
                              >
                                <Sparkles className="w-3.5 h-3.5 text-amber-300" />
                                <span>
                                  {isAnalyzing
                                    ? (isEn ? 'Analyzing...' : 'جاري الفحص...')
                                    : (isEn ? 'Analyze Entry' : 'فحص وتصحيح النص')}
                                </span>
                              </button>
                            )}
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          )}

          {/* TAB 2: FOLLOW-UP PRACTICE EXERCISES */}
          {activeTab === 'followup' && (
            <div className="space-y-6">
              <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs space-y-6">
                <div className="flex items-center justify-between border-b border-stone-100 pb-4">
                  <div className="space-y-1">
                    <h2 className="text-lg font-bold text-stone-900 flex items-center gap-2">
                      <Target className="w-5 h-5 text-amber-500" />
                      <span>{isEn ? 'Exercises Built from Your Real Mistakes' : 'تمارين تفاعلية مبنية على أخطائك السابقة'}</span>
                    </h2>
                    <p className="text-xs text-stone-500">
                      {isEn
                        ? 'Master recurring patterns identified in your journal entries to eliminate mistakes permanently.'
                        : 'تدرب على الأنماط القواعدية التي واجهت فيها صعوبة أثناء الكتابة لترسيخها ومنع تكرارها.'}
                    </p>
                  </div>
                  <span className="text-xs font-bold px-3 py-1 rounded-full bg-amber-50 text-amber-800">
                    {followUpExercises.length} {isEn ? 'exercises' : 'تمارين'}
                  </span>
                </div>

                {followUpExercises.length === 0 ? (
                  <div className="p-8 text-center space-y-3">
                    <CheckCircle2 className="w-10 h-10 text-emerald-500 mx-auto" />
                    <p className="text-sm font-bold text-stone-800">
                      {isEn ? 'No recurring mistakes identified yet!' : 'لم يتم رصد أخطاء متكررة بعد!'}
                    </p>
                    <p className="text-xs text-stone-500 max-w-sm mx-auto">
                      {isEn
                        ? 'Write more journal entries and run analysis. Any recurring challenges will appear here for practice.'
                        : 'واصل الكتابة في دفتر اليوميات وسيقوم النظام بتصميم تمارين مخصصة لأي نقاط تحتاج تقوية.'}
                    </p>
                  </div>
                ) : (
                  <div className="space-y-5">
                    {/* Navigation between exercises */}
                    <div className="flex items-center justify-between text-xs text-stone-500">
                      <span>
                        {isEn
                          ? `Exercise ${activeExerciseIndex + 1} of ${followUpExercises.length}`
                          : `التمرين ${activeExerciseIndex + 1} من ${followUpExercises.length}`}
                      </span>
                      <div className="flex gap-1.5">
                        {followUpExercises.map((_, i) => (
                          <button
                            key={i}
                            type="button"
                            onClick={() => {
                              setActiveExerciseIndex(i);
                              setSelectedAnswer(null);
                              setExerciseFeedback(null);
                            }}
                            className={`w-6 h-6 rounded-lg text-xs font-bold transition-colors ${
                              i === activeExerciseIndex
                                ? 'bg-[#1B4D3E] text-white'
                                : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
                            }`}
                          >
                            {i + 1}
                          </button>
                        ))}
                      </div>
                    </div>

                    {/* Exercise Question Card */}
                    {followUpExercises[activeExerciseIndex] && (
                      <div className="p-6 rounded-2xl bg-stone-50 border border-stone-200 space-y-4">
                        <div className="space-y-1">
                          <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-stone-200 text-stone-700">
                            {followUpExercises[activeExerciseIndex].concept_slug}
                          </span>
                          <p className="text-sm font-bold text-stone-900 pt-1">
                            {isEn
                              ? followUpExercises[activeExerciseIndex].prompt_en
                              : followUpExercises[activeExerciseIndex].prompt_ar}
                          </p>
                        </div>

                        <div className="p-4 rounded-xl bg-white border border-stone-200 text-base font-bold text-stone-900" dir="ltr">
                          {followUpExercises[activeExerciseIndex].sentence}
                        </div>

                        {/* Options */}
                        <div className="grid grid-cols-2 gap-2.5">
                          {followUpExercises[activeExerciseIndex].options.map((opt, oIdx) => (
                            <button
                              key={oIdx}
                              type="button"
                              onClick={() => setSelectedAnswer(opt)}
                              className={`p-3 rounded-xl border text-sm font-bold transition-all text-center ${
                                selectedAnswer === opt
                                  ? 'bg-emerald-50 border-emerald-500 text-emerald-950 shadow-2xs'
                                  : 'bg-white border-stone-200 text-stone-800 hover:border-stone-300'
                              }`}
                            >
                              {opt}
                            </button>
                          ))}
                        </div>

                        {/* Feedback message */}
                        {exerciseFeedback && (
                          <div
                            className={`p-3.5 rounded-xl border text-xs font-semibold flex items-center gap-2 ${
                              exerciseFeedback.isCorrect
                                ? 'bg-emerald-50 border-emerald-300 text-emerald-900'
                                : 'bg-red-50 border-red-300 text-red-900'
                            }`}
                          >
                            {exerciseFeedback.isCorrect ? (
                              <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
                            ) : (
                              <AlertCircle className="w-4 h-4 text-red-600 shrink-0" />
                            )}
                            <span>{exerciseFeedback.message}</span>
                          </div>
                        )}

                        <div className="flex items-center justify-end pt-2">
                          <button
                            type="button"
                            disabled={!selectedAnswer || isSubmittingExercise}
                            onClick={handleSubmitExercise}
                            className="px-6 py-2.5 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs shadow-xs hover:bg-[#153e32] disabled:opacity-50"
                          >
                            {isSubmittingExercise
                              ? (isEn ? 'Checking...' : 'جاري التحقق...')
                              : (isEn ? 'Submit Answer' : 'تأكيد الإجابة')}
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                )}
              </div>
            </div>
          )}

          {/* TAB 3: RECURRING MISTAKES & CURRICULUM RECOMMENDATIONS */}
          {activeTab === 'insights' && (
            <div className="space-y-6">
              {/* Recommended Lessons Linking to Real Curriculum */}
              <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs space-y-4">
                <div className="flex items-center justify-between border-b border-stone-100 pb-3">
                  <div className="space-y-1">
                    <h2 className="text-base sm:text-lg font-bold text-stone-900 flex items-center gap-2">
                      <BookOpen className="w-5 h-5 text-emerald-700" />
                      <span>{isEn ? 'Recommended Curriculum Lessons' : 'دروس المنهج المقترحة لتقوية نقاط ضعفك'}</span>
                    </h2>
                    <p className="text-xs text-stone-500">
                      {isEn
                        ? 'Targeted lessons from the structured course directly addressing your writing mistakes.'
                        : 'دروس محددة من المنهج التعليمي تعالج الأخطاء التي ظهرت في تدويناتك الأخيرة.'}
                    </p>
                  </div>
                </div>

                {recommendedLessons.length === 0 ? (
                  <p className="text-xs text-stone-400 py-4 text-center">
                    {isEn ? 'No curriculum recommendations yet.' : 'لا توجد توصيات دروس حالياً. اكتب تدوينات ليتم فحصها.'}
                  </p>
                ) : (
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {recommendedLessons.map((rec) => (
                      <div
                        key={rec.id}
                        className="p-4 rounded-2xl border border-stone-200 bg-stone-50/70 hover:border-emerald-300 transition-all space-y-2.5"
                      >
                        <div className="flex items-center justify-between">
                          <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800">
                            {rec.action_payload?.category || 'grammar'}
                          </span>
                          <span className="text-xs font-bold text-stone-900">
                            {isEn ? rec.title_en : rec.title_ar}
                          </span>
                        </div>

                        <p className="text-xs text-stone-600 leading-relaxed">
                          {isEn ? rec.reason_en : rec.reason_ar}
                        </p>

                        <div className="pt-1 flex items-center justify-between">
                          <span className="text-[11px] font-bold text-emerald-800">
                            {rec.action_payload?.lesson_title || 'Lección recomendada'}
                          </span>
                          <Link
                            to={`/lessons/${rec.action_payload?.lesson_id || 15}`}
                            className="px-3 py-1.5 rounded-xl bg-[#1B4D3E] text-white text-xs font-bold hover:bg-[#153e32] flex items-center gap-1"
                          >
                            <span>{isEn ? 'Go to Lesson' : 'ابدأ الدرس'}</span>
                            <ExternalLink className="w-3 h-3" />
                          </Link>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Monitored Error Concepts Table */}
              <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs space-y-4">
                <div className="space-y-1 border-b border-stone-100 pb-3">
                  <h2 className="text-base sm:text-lg font-bold text-stone-900 flex items-center gap-2">
                    <TrendingUp className="w-5 h-5 text-amber-500" />
                    <span>{isEn ? 'Recurring Error Concepts Tracking' : 'سجل الأخطاء المتكررة ونسبة الإتقان'}</span>
                  </h2>
                  <p className="text-xs text-stone-500">
                    {isEn
                      ? 'Monitors how many times each grammar or vocabulary pattern occurred and tracks mastery recovery.'
                      : 'متابعة عدد مرات تكرار كل قاعدة لغوية ورصد تحسن الإتقان مع استمرار الممارسة.'}
                  </p>
                </div>

                {recurringMistakes.length === 0 ? (
                  <p className="text-xs text-stone-400 py-4 text-center">
                    {isEn ? 'No recurring mistakes detected yet.' : 'سجلك نظيف! لم يتم رصد أخطاء متكررة مرتين أو أكثر.'}
                  </p>
                ) : (
                  <div className="space-y-3">
                    {recurringMistakes.map((concept) => (
                      <div
                        key={concept.id}
                        className="p-4 rounded-2xl bg-stone-50 border border-stone-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs"
                      >
                        <div className="space-y-1">
                          <div className="flex items-center gap-2">
                            <span className="font-bold text-stone-900 text-sm">
                              {isEn ? concept.concept_name_en : concept.concept_name_ar}
                            </span>
                            <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-stone-200 text-stone-700">
                              {concept.category}
                            </span>
                          </div>
                          <div className="text-stone-500">
                            {isEn
                              ? `Occurred ${concept.total_errors_count} times • Practiced ${concept.successful_practices_count} times`
                              : `تكررت ${concept.total_errors_count} مرات • تم التدرب عليها ${concept.successful_practices_count} مرات`}
                          </div>
                        </div>

                        <div className="flex items-center gap-3">
                          <div className="text-right sm:text-left space-y-1 min-w-[100px]">
                            <div className="flex justify-between text-[11px] font-bold">
                              <span>{isEn ? 'Mastery' : 'الإتقان'}</span>
                              <span className="text-emerald-700">{concept.mastery_score}%</span>
                            </div>
                            <div className="w-full bg-stone-200 h-2 rounded-full overflow-hidden">
                              <div
                                className="bg-emerald-600 h-full rounded-full transition-all"
                                style={{ width: `${concept.mastery_score}%` }}
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          )}
        </main>
      </div>

      {/* Analysis & Writing Correction Modal */}
      {activeAnalysis && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-xs animate-fade-in" dir={isEn ? 'ltr' : 'rtl'}>
          <div className="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 space-y-6 shadow-2xl">
            {/* Modal Header */}
            <div className="flex items-center justify-between border-b border-stone-200 pb-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-emerald-100 text-[#1B4D3E] flex items-center justify-center font-black">
                  <Sparkles className="w-5 h-5 text-amber-600" />
                </div>
                <div>
                  <h3 className="font-bold text-stone-900 text-lg">
                    {isEn ? 'Writing Analysis & Corrections' : 'تحليل وتصحيح التدوينة'}
                  </h3>
                  <div className="flex items-center gap-2 pt-0.5">
                    <span className="text-[11px] font-bold px-2 py-0.5 rounded-full bg-stone-100 text-stone-600">
                      {activeAnalysis.analysis.is_ai_generated
                        ? (isEn ? 'AI Cloud Powered' : 'مدعوم بالذكاء الاصطناعي')
                        : (isEn ? 'Offline Linguistic Rules Engine' : 'المحرك اللغوي المدمج (Offline)')}
                    </span>
                  </div>
                </div>
              </div>

              <button
                type="button"
                onClick={() => setActiveAnalysis(null)}
                className="p-1.5 rounded-xl bg-stone-100 text-stone-500 hover:bg-stone-200 cursor-pointer"
              >
                ✕
              </button>
            </div>

            {/* Score & Summary */}
            <div className="grid grid-cols-3 gap-3">
              <div className="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-center">
                <div className="text-xs text-emerald-800 font-bold">{isEn ? 'Fluency Index' : 'مؤشر الطلاقة'}</div>
                <div className="text-2xl font-black text-emerald-700">{activeAnalysis.analysis.fluency_score}%</div>
              </div>
              <div className="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-center">
                <div className="text-xs text-amber-800 font-bold">{isEn ? 'Grammar Notes' : 'أخطاء قواعدية'}</div>
                <div className="text-2xl font-black text-amber-700">{activeAnalysis.analysis.total_errors}</div>
              </div>
              <div className="p-3.5 rounded-2xl bg-stone-50 border border-stone-200 text-center">
                <div className="text-xs text-stone-600 font-bold">{isEn ? 'Style Suggestions' : 'اقتراحات أسلوبية'}</div>
                <div className="text-2xl font-black text-stone-700">{activeAnalysis.analysis.total_suggestions}</div>
              </div>
            </div>

            {/* Feedback message */}
            <div className="p-4 rounded-2xl bg-stone-50 border border-stone-200 text-xs font-semibold text-stone-700 leading-relaxed">
              {isEn
                ? (activeAnalysis.analysis.overall_feedback_en || activeAnalysis.analysis.overall_feedback_ar)
                : activeAnalysis.analysis.overall_feedback_ar}
            </div>

            {/* Original vs Corrected side-by-side */}
            <div className="space-y-2">
              <div className="text-xs font-bold text-stone-500">
                {isEn ? 'Text Comparison (Original preserved):' : 'مقارنة النص (نصك الأصلي محفوظ دون تغيير):'}
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div className="p-4 rounded-2xl bg-red-50/40 border border-red-200 space-y-1">
                  <div className="text-[11px] font-bold text-red-700">{isEn ? 'Your Original Writing:' : 'نصك الأصلي:'}</div>
                  <div className="text-sm text-stone-800 bidi-isolate leading-relaxed" dir="ltr">
                    {activeAnalysis.entry.content}
                  </div>
                </div>
                <div className="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-300 space-y-1">
                  <div className="flex items-center justify-between">
                    <span className="text-[11px] font-bold text-emerald-800">{isEn ? 'Recommended Natural Phrasing:' : 'الصياغة الطبيعية الموصى بها:'}</span>
                    <AudioSpeakerButton text={activeAnalysis.analysis.corrected_content} size="sm" />
                  </div>
                  <div className="text-sm font-bold text-emerald-950 bidi-isolate leading-relaxed" dir="ltr">
                    "{activeAnalysis.analysis.corrected_content}"
                  </div>
                </div>
              </div>
            </div>

            {/* Detailed Suggestions with Understood / Review Later actions */}
            {activeAnalysis.analysis.suggestions && activeAnalysis.analysis.suggestions.length > 0 && (
              <div className="space-y-3 pt-2">
                <div className="text-xs font-bold text-stone-500">
                  {isEn ? 'Categorized Linguistic Corrections:' : 'الملاحظات اللغوية المستخرجة:'}
                </div>
                <div className="space-y-3">
                  {activeAnalysis.analysis.suggestions.map((sug) => (
                    <div
                      key={sug.id}
                      className={`p-4 rounded-2xl border text-xs space-y-3 transition-colors ${
                        sug.is_understood
                          ? 'bg-emerald-50/40 border-emerald-300'
                          : sug.severity === 'error'
                          ? 'bg-amber-50/60 border-amber-300'
                          : 'bg-stone-50 border-stone-200'
                      }`}
                    >
                      <div className="flex items-center justify-between">
                        <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-white border border-stone-200 text-stone-600">
                          {sug.error_category}
                        </span>
                        <div className="flex items-center gap-2" dir="ltr">
                          <span className="text-red-600 line-through font-semibold">{sug.original_text}</span>
                          <span className="text-stone-400">→</span>
                          <span className="text-emerald-700 font-bold">{sug.suggested_text}</span>
                        </div>
                      </div>

                      <p className="text-stone-700 leading-relaxed font-normal">
                        {isEn ? (sug.explanation_en || sug.explanation_ar) : sug.explanation_ar}
                      </p>

                      {sug.natural_alternative && (
                        <div className="p-2 rounded-xl bg-white/80 border border-stone-200 text-stone-800 flex items-center justify-between" dir="ltr">
                          <span className="text-xs font-medium">Alternative: "{sug.natural_alternative}"</span>
                          <AudioSpeakerButton text={sug.natural_alternative} size="sm" />
                        </div>
                      )}

                      {/* Understood / Review Later Buttons */}
                      <div className="flex items-center justify-between pt-1 border-t border-stone-200/60">
                        <div className="text-[11px] font-semibold text-stone-500">
                          {sug.is_understood ? (
                            <span className="text-emerald-700 font-bold flex items-center gap-1">
                              <Check className="w-3.5 h-3.5" />
                              <span>{isEn ? 'Understood' : 'تم استيعاب القاعدة'}</span>
                            </span>
                          ) : sug.status === 'review_later' ? (
                            <span className="text-amber-700 font-bold flex items-center gap-1">
                              <Clock className="w-3.5 h-3.5" />
                              <span>{isEn ? 'Marked for Review' : 'محدد للمراجعة لاحقاً'}</span>
                            </span>
                          ) : (
                            <span>{isEn ? 'Action:' : 'الإجراء:'}</span>
                          )}
                        </div>

                        <div className="flex items-center gap-2">
                          <button
                            type="button"
                            onClick={() => handleUpdateSuggestionStatus(sug.id, 'review_later')}
                            className={`px-2.5 py-1 rounded-lg text-xs font-bold border transition-colors ${
                              sug.status === 'review_later'
                                ? 'bg-amber-100 border-amber-300 text-amber-900'
                                : 'bg-white border-stone-200 text-stone-600 hover:bg-stone-50'
                            }`}
                          >
                            {isEn ? 'Review Later' : 'مراجعة لاحقاً'}
                          </button>
                          <button
                            type="button"
                            onClick={() => handleUpdateSuggestionStatus(sug.id, 'understood')}
                            className={`px-3 py-1 rounded-lg text-xs font-bold transition-colors ${
                              sug.is_understood
                                ? 'bg-emerald-600 text-white'
                                : 'bg-[#1B4D3E] text-white hover:bg-[#153e32]'
                            }`}
                          >
                            {isEn ? 'Mark Understood' : 'فهمت القاعدة'}
                          </button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Close Button */}
            <div className="pt-2">
              <button
                type="button"
                onClick={() => setActiveAnalysis(null)}
                className="w-full py-3.5 rounded-2xl bg-[#1B4D3E] text-white font-bold text-sm shadow-xs hover:bg-[#153e32] cursor-pointer"
              >
                {isEn ? 'Close Review' : 'إغلاق المراجعة'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
