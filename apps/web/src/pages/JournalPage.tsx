import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { journalApi } from '../api/journal';
import { JournalEntry, WritingAnalysis } from '../types';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import { AudioSpeakerButton } from '../components/AudioSpeakerButton';
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
  Plus
} from 'lucide-react';
import confetti from 'canvas-confetti';

export const JournalPage: React.FC = () => {
  const { t } = useTranslation();
  const [entries, setEntries] = useState<JournalEntry[]>([]);
  const [stats, setStats] = useState({ total_entries: 0, total_words_written: 0, analyzed_count: 0 });
  const [isLoading, setIsLoading] = useState(true);

  // Active Writing Form state
  const [isEditorOpen, setIsEditorOpen] = useState(false);
  const [editingEntryId, setEditingEntryId] = useState<number | null>(null);
  const [entryTitle, setEntryTitle] = useState('');
  const [entryContent, setEntryContent] = useState('');
  const [selectedPrompt, setSelectedPrompt] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);

  // Analysis / Feedback Modal state
  const [activeAnalysis, setActiveAnalysis] = useState<{ analysis: WritingAnalysis; entry: JournalEntry } | null>(null);
  const [isAnalyzing, setIsAnalyzing] = useState(false);

  // Level-appropriate daily prompts
  const dailyPrompts = [
    { level: 'A1', topic: 'daily_routine', es: '¿Qué hiciste hoy por la mañana?', ar: 'ماذا فعلت اليوم في الصباح؟ (استخدم أفعالاً بسيطة مثل: comí, fui, hablé)' },
    { level: 'A1', topic: 'favorite_food', es: 'Describe tu comida o restaurante favorito.', ar: 'صف طعامك المفضل أو مطعماً تحب زيارته.' },
    { level: 'A2', topic: 'last_weekend', es: 'Cuéntame sobre tu último fin de semana con amigos.', ar: 'احكِ عن عطلة نهاية الأسبوع الماضية مع الأصدقاء (زمن الماضي).' },
    { level: 'B1', topic: 'life_goal', es: '¿Cuáles son tus metas profesionales y por qué aprendes español?', ar: 'ما هي أهدافك المهنية ولماذا تتعلم الإسبانية؟' },
  ];

  const spanishSpecialChars = ['á', 'é', 'í', 'ó', 'ú', 'ñ', '¿', '¡'];

  useEffect(() => {
    loadJournal();
  }, []);

  const loadJournal = async () => {
    setIsLoading(true);
    try {
      const res = await journalApi.getEntries();
      setEntries(res.entries.data || []);
      setStats(res.stats || { total_entries: 0, total_words_written: 0, analyzed_count: 0 });
    } catch (err) {
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  const insertChar = (char: string) => {
    setEntryContent((prev) => prev + char);
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
          title: entryTitle || undefined,
          content: entryContent,
          prompt_topic: selectedPrompt || undefined,
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
      const res = await journalApi.analyzeEntry(entry.id);
      setActiveAnalysis({
        analysis: res.analysis,
        entry: res.entry,
      });
      confetti({ particleCount: 35, spread: 60, origin: { y: 0.6 } });
      loadJournal();
    } catch (err) {
      console.error(err);
    } finally {
      setIsAnalyzing(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!window.confirm('هل أنت متأكد من حذف هذه التدوينة؟')) return;
    try {
      await journalApi.deleteEntry(id);
      loadJournal();
    } catch (err) {
      console.error(err);
    }
  };

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl w-full pb-24 md:pb-8">
          {/* Header */}
          <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div className="space-y-1 text-right">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-[#1B4D3E] text-xs font-bold">
                <PenTool className="w-3.5 h-3.5" />
                <span>التعلم من خلال حياتك الواقعية</span>
              </div>
              <h1 className="text-3xl font-black text-stone-900">
                دفتر اليوميات الإسباني
              </h1>
              <p className="text-xs sm:text-sm text-stone-600 max-w-2xl">
                اكتب عن يومك وأفكارك بالإسبانية. المعلم الذكي يراجع كتابتك، يحدد أخطاءك بدقة، ويصمم لك تمارين لتقوية نقاط ضعفك.
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
                <span>تدوينة جديدة</span>
              </button>
            )}
          </div>

          {/* Quick Stats Grid */}
          <div className="grid grid-cols-3 gap-3 sm:gap-4">
            <div className="p-4 rounded-2xl bg-white border border-stone-200 shadow-2xs text-center space-y-1">
              <div className="text-xs text-stone-400 font-semibold">إجمالي التدوينات</div>
              <div className="text-2xl font-black text-[#1B4D3E]">{stats.total_entries}</div>
            </div>
            <div className="p-4 rounded-2xl bg-white border border-stone-200 shadow-2xs text-center space-y-1">
              <div className="text-xs text-stone-400 font-semibold">الكلمات المكتوبة</div>
              <div className="text-2xl font-black text-amber-600">{stats.total_words_written}</div>
            </div>
            <div className="p-4 rounded-2xl bg-white border border-stone-200 shadow-2xs text-center space-y-1">
              <div className="text-xs text-stone-400 font-semibold">التدوينات المصححة</div>
              <div className="text-2xl font-black text-emerald-600">{stats.analyzed_count}</div>
            </div>
          </div>

          {/* Active Writing Editor Card */}
          {isEditorOpen && (
            <div className="bg-white rounded-3xl p-6 sm:p-8 border-2 border-emerald-500/70 shadow-lg space-y-5 animate-fade-in text-right">
              <div className="flex items-center justify-between border-b border-stone-100 pb-3">
                <span className="text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-800">
                  {editingEntryId ? 'تعديل التدوينة' : 'كتابة تدوينة جديدة'}
                </span>
                <button
                  type="button"
                  onClick={() => setIsEditorOpen(false)}
                  className="text-xs text-stone-400 hover:text-stone-700 cursor-pointer"
                >
                  إلغاء ×
                </button>
              </div>

              {/* Daily Prompts Selector */}
              <div className="space-y-2">
                <div className="text-xs font-bold text-stone-500 flex items-center gap-1.5 justify-end">
                  <span>أفكار مقترحة للكتابة اليوم:</span>
                  <Lightbulb className="w-3.5 h-3.5 text-amber-500" />
                </div>
                <div className="flex flex-wrap gap-2 justify-end">
                  {dailyPrompts.map((p, idx) => (
                    <button
                      key={idx}
                      type="button"
                      onClick={() => {
                        setSelectedPrompt(p.topic);
                        setEntryTitle(p.es);
                      }}
                      className="px-3 py-1.5 rounded-xl border border-stone-200 hover:border-emerald-400 text-xs text-stone-700 bg-stone-50/70 transition-colors text-right"
                      title={p.ar}
                    >
                      <span className="font-bold text-emerald-800 ml-1">[{p.level}]</span>
                      <span>{p.es}</span>
                    </button>
                  ))}
                </div>
              </div>

              {/* Title input */}
              <input
                type="text"
                value={entryTitle}
                onChange={(e) => setEntryTitle(e.target.value)}
                placeholder="عنوان التدوينة بالإسبانية (اختياري)..."
                dir="ltr"
                className="w-full p-3.5 rounded-xl border border-stone-300 font-bold text-base focus:border-[#1B4D3E] outline-none"
              />

              {/* Spanish special characters toolbar */}
              <div className="flex items-center gap-1.5 justify-end flex-wrap pt-1">
                <span className="text-xs text-stone-400 ml-2">حروف إسبانية خاصة:</span>
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
                  rows={6}
                  value={entryContent}
                  onChange={(e) => setEntryContent(e.target.value)}
                  placeholder="Escribe aquí en español sobre tu día, tus planes, o lo que hiciste hoy..."
                  dir="ltr"
                  className="w-full p-4 rounded-2xl border border-stone-300 text-base leading-relaxed focus:border-[#1B4D3E] outline-none font-medium text-stone-900"
                />
                <div className="flex justify-between items-center text-xs text-stone-400 px-1">
                  <span>عدد الكلمات: {entryContent.trim() ? entryContent.trim().split(/\s+/).length : 0} كلمة</span>
                  <span>يدعم الإسبانية القياسية وأزمنة الماضي والحاضر</span>
                </div>
              </div>

              {/* Action buttons */}
              <div className="flex items-center justify-end gap-3 pt-2">
                <button
                  type="button"
                  onClick={() => setIsEditorOpen(false)}
                  className="px-5 py-2.5 rounded-xl border border-stone-200 text-stone-600 font-bold text-xs hover:bg-stone-50"
                >
                  إلغاء
                </button>
                <button
                  type="button"
                  disabled={isSaving || !entryContent.trim()}
                  onClick={handleSaveDraft}
                  className="px-6 py-2.5 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs shadow-xs hover:bg-[#153e32] disabled:opacity-50"
                >
                  {isSaving ? 'جاري الحفظ...' : 'حفظ التدوينة'}
                </button>
              </div>
            </div>
          )}

          {/* Journal Entries List */}
          <div className="space-y-4">
            <h2 className="text-lg font-bold text-stone-900 text-right">تدويناتك السابقة</h2>

            {entries.length === 0 && !isLoading ? (
              <div className="bg-white rounded-3xl p-10 border border-stone-200 text-center space-y-4">
                <div className="w-14 h-14 rounded-2xl bg-emerald-50 text-[#1B4D3E] flex items-center justify-center mx-auto">
                  <PenTool className="w-7 h-7" />
                </div>
                <h3 className="font-bold text-stone-900 text-base">دفترك فارغ حالياً</h3>
                <p className="text-xs text-stone-500 max-w-md mx-auto leading-relaxed">
                  ابدأ كتابة أول تدوينة لك بالإسبانية عن يومك، وسيقوم المعلم الذكي بتحليل القواعد ومساعدتك على بناء لغتك من واقعك اليومي!
                </p>
                <button
                  type="button"
                  onClick={() => setIsEditorOpen(true)}
                  className="px-6 py-3 rounded-2xl bg-[#1B4D3E] text-white font-bold text-xs shadow-sm hover:bg-[#153e32]"
                >
                  اكتب أول تدوينة الآن
                </button>
              </div>
            ) : (
              <div className="space-y-4">
                {entries.map((entry) => (
                  <div
                    key={entry.id}
                    className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-xs hover:border-emerald-300 transition-all space-y-4 text-right"
                  >
                    {/* Entry Header */}
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <button
                          type="button"
                          onClick={() => handleDelete(entry.id)}
                          className="p-1.5 rounded-lg text-stone-300 hover:text-red-600 hover:bg-red-50 transition-colors"
                          title="حذف التدوينة"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                        <button
                          type="button"
                          onClick={() => {
                            setEditingEntryId(entry.id);
                            setEntryTitle(entry.title || '');
                            setEntryContent(entry.content);
                            setIsEditorOpen(true);
                          }}
                          className="p-1.5 rounded-lg text-stone-400 hover:text-[#1B4D3E] hover:bg-emerald-50 transition-colors"
                          title="تعديل النص"
                        >
                          <Edit3 className="w-4 h-4" />
                        </button>
                      </div>

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
                          {entry.status === 'analyzed' ? 'تم الفحص لغوياً' : 'مسودة غير مفحوصة'}
                        </span>
                      </div>
                    </div>

                    {/* Entry Title & Body */}
                    {entry.title && (
                      <h3 className="text-base font-bold text-stone-900 bidi-isolate">
                        {entry.title}
                      </h3>
                    )}
                    <p className="text-sm font-medium text-stone-800 leading-relaxed bidi-isolate bg-stone-50/60 p-4 rounded-2xl border border-stone-100">
                      {entry.content}
                    </p>

                    {/* Footer Actions & Analysis Results */}
                    <div className="flex items-center justify-between pt-2 border-t border-stone-100">
                      <div className="flex items-center gap-2">
                        <span className="text-xs text-stone-400">{entry.word_count} كلمة</span>
                        <AudioSpeakerButton text={entry.content} size="sm" />
                      </div>

                      <div className="flex items-center gap-2">
                        {entry.analysis ? (
                          <button
                            type="button"
                            onClick={() => setActiveAnalysis({ analysis: entry.analysis!, entry })}
                            className="px-4 py-2 rounded-xl bg-emerald-50 text-[#1B4D3E] font-bold text-xs hover:bg-emerald-100 flex items-center gap-1.5 transition-colors border border-emerald-200"
                          >
                            <Sparkles className="w-3.5 h-3.5 text-amber-500" />
                            <span>عرض نتائج التصحيح ({entry.analysis.fluency_score}%)</span>
                          </button>
                        ) : (
                          <button
                            type="button"
                            disabled={isAnalyzing}
                            onClick={() => handleAnalyzeEntry(entry)}
                            className="px-4 py-2 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs hover:bg-[#153e32] flex items-center gap-1.5 transition-colors shadow-2xs cursor-pointer"
                          >
                            <Sparkles className="w-3.5 h-3.5 text-amber-300" />
                            <span>{isAnalyzing ? 'جاري الفحص...' : 'فحص وتصحيح النص'}</span>
                          </button>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </main>
      </div>

      {/* Analysis & Writing Correction Modal */}
      {activeAnalysis && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-xs animate-fade-in" dir="rtl">
          <div className="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 space-y-6 shadow-2xl text-right">
            {/* Modal Header */}
            <div className="flex items-center justify-between border-b border-stone-200 pb-4">
              <div className="flex items-center gap-2">
                <div className="w-10 h-10 rounded-xl bg-emerald-100 text-[#1B4D3E] flex items-center justify-center font-black">
                  <Sparkles className="w-5 h-5 text-amber-600" />
                </div>
                <div>
                  <h3 className="font-bold text-stone-900 text-lg">تحليل وتصحيح التدوينة</h3>
                  <div className="text-xs text-stone-400">تقييم دقيق يحدد الأخطاء القواعدية والأسلوبية</div>
                </div>
              </div>

              <button
                type="button"
                onClick={() => setActiveAnalysis(null)}
                className="p-1.5 rounded-xl bg-stone-100 text-stone-500 hover:bg-stone-200"
              >
                ✕
              </button>
            </div>

            {/* Score & Summary */}
            <div className="grid grid-cols-3 gap-3">
              <div className="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-center">
                <div className="text-xs text-emerald-800 font-bold">مؤشر الطلاقة</div>
                <div className="text-2xl font-black text-emerald-700">{activeAnalysis.analysis.fluency_score}%</div>
              </div>
              <div className="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-center">
                <div className="text-xs text-amber-800 font-bold">أخطاء قواعدية</div>
                <div className="text-2xl font-black text-amber-700">{activeAnalysis.analysis.total_errors}</div>
              </div>
              <div className="p-3.5 rounded-2xl bg-stone-50 border border-stone-200 text-center">
                <div className="text-xs text-stone-600 font-bold">اقتراحات أسلوبية</div>
                <div className="text-2xl font-black text-stone-700">{activeAnalysis.analysis.total_suggestions}</div>
              </div>
            </div>

            {/* Feedback message */}
            <div className="p-4 rounded-2xl bg-stone-50 border border-stone-200 text-xs font-semibold text-stone-700 leading-relaxed">
              {activeAnalysis.analysis.overall_feedback_ar}
            </div>

            {/* Original vs Corrected side-by-side */}
            <div className="space-y-2">
              <div className="text-xs font-bold text-stone-500">مقارنة النص:</div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div className="p-4 rounded-2xl bg-red-50/40 border border-red-200 text-right space-y-1">
                  <div className="text-[11px] font-bold text-red-700">نصك الأصلي:</div>
                  <div className="text-sm text-stone-800 bidi-isolate leading-relaxed">{activeAnalysis.entry.content}</div>
                </div>
                <div className="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-300 text-right space-y-1">
                  <div className="flex items-center justify-between">
                    <span className="text-[11px] font-bold text-emerald-800">الصياغة الطبيعية الموصى بها:</span>
                    <AudioSpeakerButton text={activeAnalysis.analysis.corrected_content} size="sm" />
                  </div>
                  <div className="text-sm font-bold text-emerald-950 bidi-isolate leading-relaxed">
                    "{activeAnalysis.analysis.corrected_content}"
                  </div>
                </div>
              </div>
            </div>

            {/* Detailed Suggestions breakdown */}
            {activeAnalysis.analysis.suggestions && activeAnalysis.analysis.suggestions.length > 0 && (
              <div className="space-y-3 pt-2">
                <div className="text-xs font-bold text-stone-500">الملاحظات اللغوية المستخرجة:</div>
                <div className="space-y-2.5">
                  {activeAnalysis.analysis.suggestions.map((sug) => (
                    <div
                      key={sug.id}
                      className={`p-4 rounded-2xl border text-xs space-y-2 ${
                        sug.severity === 'error'
                          ? 'bg-amber-50/60 border-amber-300'
                          : 'bg-stone-50 border-stone-200'
                      }`}
                    >
                      <div className="flex items-center justify-between">
                        <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-white border border-stone-200 text-stone-600">
                          {sug.error_category}
                        </span>
                        <div className="flex items-center gap-2">
                          <span className="text-red-600 line-through bidi-isolate">{sug.original_text}</span>
                          <span className="text-stone-400">←</span>
                          <span className="text-emerald-700 font-bold bidi-isolate">{sug.suggested_text}</span>
                        </div>
                      </div>

                      <p className="text-stone-700 leading-relaxed font-normal">{sug.explanation_ar}</p>

                      {sug.example_sentence_es && (
                        <div className="p-2.5 rounded-xl bg-white border border-stone-200 text-stone-800 bidi-isolate flex items-center justify-between">
                          <span>مثال: "{sug.example_sentence_es}"</span>
                          <AudioSpeakerButton text={sug.example_sentence_es} size="sm" />
                        </div>
                      )}
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
                className="w-full py-3.5 rounded-2xl bg-[#1B4D3E] text-white font-bold text-sm shadow-xs hover:bg-[#153e32]"
              >
                فهمت الملاحظات وإغلاق
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
