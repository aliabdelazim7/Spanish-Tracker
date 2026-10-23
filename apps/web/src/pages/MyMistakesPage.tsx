import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { mistakesApi } from '../api/mistakes';
import { LearnerErrorConcept, LearningRecommendation } from '../types';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import {
  Compass,
  AlertTriangle,
  CheckCircle2,
  XCircle,
  Sparkles,
  TrendingUp,
  Award,
  ArrowRight,
  BookOpen,
  RotateCcw
} from 'lucide-react';
import confetti from 'canvas-confetti';

export const MyMistakesPage: React.FC = () => {
  const { t } = useTranslation();
  const [concepts, setConcepts] = useState<LearnerErrorConcept[]>([]);
  const [recommendations, setRecommendations] = useState<LearningRecommendation[]>([]);
  const [categories, setCategories] = useState<Record<string, number>>({});
  const [totalMistakes, setTotalMistakes] = useState(0);
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [isLoading, setIsLoading] = useState(true);

  // Active Practice Modal state
  const [activeConcept, setActiveConcept] = useState<LearnerErrorConcept | null>(null);
  const [quizData, setQuizData] = useState<any | null>(null);
  const [selectedAnswer, setSelectedAnswer] = useState<string | null>(null);
  const [quizResult, setQuizResult] = useState<any | null>(null);
  const [isSubmittingAnswer, setIsSubmittingAnswer] = useState(false);

  useEffect(() => {
    loadMistakes();
  }, []);

  const loadMistakes = async () => {
    setIsLoading(true);
    try {
      const res = await mistakesApi.getMistakes();
      setConcepts(res.concepts || []);
      setRecommendations(res.recommendations || []);
      setCategories(res.categories || {});
      setTotalMistakes(res.total_recorded_mistakes || 0);
    } catch (err) {
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  const handleStartPractice = async (concept: LearnerErrorConcept) => {
    setActiveConcept(concept);
    setSelectedAnswer(null);
    setQuizResult(null);
    try {
      const res = await mistakesApi.getPracticeQuiz(concept.id);
      setQuizData(res.quiz);
    } catch (err) {
      console.error(err);
    }
  };

  const handleSubmitPractice = async () => {
    if (!activeConcept || !selectedAnswer) return;
    setIsSubmittingAnswer(true);
    try {
      const res = await mistakesApi.submitPracticeAnswer(activeConcept.id, selectedAnswer);
      setQuizResult(res);
      if (res.is_correct) {
        confetti({ particleCount: 40, spread: 60, origin: { y: 0.6 } });
      }
      loadMistakes();
    } catch (err) {
      console.error(err);
    } finally {
      setIsSubmittingAnswer(false);
    }
  };

  const filteredConcepts = selectedCategory === 'all'
    ? concepts
    : concepts.filter((c) => c.category === selectedCategory);

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl w-full pb-24 md:pb-8">
          {/* Header */}
          <div className="space-y-1 text-right">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold">
              <AlertTriangle className="w-3.5 h-3.5" />
              <span>ملف التطور والتحسين اللغوي</span>
            </div>
            <h1 className="text-3xl font-black text-stone-900">
              أخطائي الشائعة ونقاط الضعف
            </h1>
            <p className="text-xs sm:text-sm text-stone-600 max-w-2xl">
              الأخطاء ليست عيباً بل هي خريطتك نحو الطلاقة. نتتبع هنا الصعوبات المتكررة في كتابتك باليوميات وتمارينك لنمنحك تدريبات مخصصة لتجاوزها.
            </p>
          </div>

          {/* Targeted Recommendations Row */}
          {recommendations.length > 0 && (
            <div className="space-y-2">
              <div className="text-xs font-bold text-stone-500 text-right">توصيات تعليمية مستهدفة لك:</div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {recommendations.map((rec) => (
                  <div
                    key={rec.id}
                    className="p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 text-right space-y-2"
                  >
                    <div className="flex items-center justify-between">
                      <Sparkles className="w-4 h-4 text-emerald-700" />
                      <span className="font-bold text-sm text-[#1B4D3E]">{rec.title_ar}</span>
                    </div>
                    <p className="text-xs text-stone-600 leading-relaxed font-normal">{rec.reason_ar}</p>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Category Filter Chips */}
          <div className="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none justify-end">
            {[
              { id: 'all', label: `الكل (${concepts.length})` },
              { id: 'grammar', label: `قواعد (${categories.grammar || 0})` },
              { id: 'verb_conjugation', label: `تصريف أفعال (${categories.verb_conjugation || 0})` },
              { id: 'gender_agreement', label: `تطابق المذكر والمؤنث (${categories.gender_agreement || 0})` },
            ].map((cat) => (
              <button
                key={cat.id}
                type="button"
                onClick={() => setSelectedCategory(cat.id)}
                className={`px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap ${
                  selectedCategory === cat.id
                    ? 'bg-[#1B4D3E] text-white shadow-2xs'
                    : 'bg-white border border-stone-200 text-stone-600 hover:border-emerald-300'
                }`}
              >
                {cat.label}
              </button>
            ))}
          </div>

          {/* Concepts List */}
          {filteredConcepts.length === 0 && !isLoading ? (
            <div className="bg-white rounded-3xl p-10 border border-stone-200 text-center space-y-3">
              <div className="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mx-auto">
                <CheckCircle2 className="w-8 h-8" />
              </div>
              <h3 className="font-bold text-stone-900 text-base">رائع! لم تسجل أي أخطاء متكررة بعد</h3>
              <p className="text-xs text-stone-500 max-w-md mx-auto leading-relaxed">
                كلما كتبت في دفتر اليوميات وحللت التمارين، سنقوم بتحليل الأنماط ومساعدتك في تثبيت ما يمثل صعوبة لك.
              </p>
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              {filteredConcepts.map((concept) => (
                <div
                  key={concept.id}
                  className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-xs hover:border-emerald-300 transition-all text-right space-y-4 flex flex-col justify-between"
                >
                  <div className="space-y-3">
                    <div className="flex items-center justify-between">
                      <span className="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-stone-100 text-stone-600">
                        {concept.category}
                      </span>
                      <span className="text-xs font-black text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                        تكرر {concept.total_errors_count} مرات
                      </span>
                    </div>

                    <h3 className="text-base font-bold text-stone-900">
                      {concept.concept_name_ar}
                    </h3>
                    <div className="text-xs text-stone-400 font-semibold bidi-isolate">
                      {concept.concept_name_en}
                    </div>

                    {/* Mastery Bar */}
                    <div className="space-y-1.5 pt-1">
                      <div className="flex justify-between text-[11px] font-bold text-stone-500">
                        <span className="text-emerald-700">{concept.mastery_score}%</span>
                        <span>مستوى الإتقان والتثبيت</span>
                      </div>
                      <div className="w-full bg-stone-100 h-2 rounded-full overflow-hidden">
                        <div
                          className="bg-emerald-600 h-full transition-all duration-300"
                          style={{ width: `${concept.mastery_score}%` }}
                        />
                      </div>
                    </div>
                  </div>

                  <button
                    type="button"
                    onClick={() => handleStartPractice(concept)}
                    className="w-full py-3 rounded-2xl bg-emerald-50 text-[#1B4D3E] font-bold text-xs hover:bg-[#1B4D3E] hover:text-white transition-all flex items-center justify-center gap-2 cursor-pointer border border-emerald-200"
                  >
                    <span>تمرن على القاعدة الآن</span>
                    <ArrowRight className="w-3.5 h-3.5 rotate-180" />
                  </button>
                </div>
              ))}
            </div>
          )}
        </main>
      </div>

      {/* Targeted Practice Quiz Modal */}
      {activeConcept && quizData && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-xs animate-fade-in" dir="rtl">
          <div className="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl text-right">
            <div className="flex items-center justify-between border-b border-stone-100 pb-3">
              <span className="text-xs font-bold px-3 py-1 rounded-full bg-emerald-100 text-emerald-900">
                تدريب مستهدف
              </span>
              <button
                type="button"
                onClick={() => {
                  setActiveConcept(null);
                  setQuizData(null);
                }}
                className="text-xs text-stone-400 hover:text-stone-700 cursor-pointer"
              >
                ✕
              </button>
            </div>

            <div className="space-y-1">
              <h3 className="text-lg font-bold text-stone-900">{activeConcept.concept_name_ar}</h3>
              <p className="text-xs text-stone-500">{quizData.prompt_ar}</p>
            </div>

            {/* Prompt sentence */}
            <div className="p-4 rounded-2xl bg-stone-50 border border-stone-200 text-center font-black text-lg text-stone-900 bidi-isolate">
              "{quizData.prompt}"
            </div>

            {/* Options */}
            <div className="space-y-2.5">
              {quizData.options?.map((opt: string, i: number) => {
                const isSelected = selectedAnswer === opt;
                return (
                  <button
                    key={i}
                    type="button"
                    disabled={!!quizResult}
                    onClick={() => setSelectedAnswer(opt)}
                    className={`w-full p-3.5 rounded-2xl border-2 font-bold text-sm transition-all text-right flex items-center justify-between ${
                      isSelected
                        ? 'border-[#1B4D3E] bg-emerald-50 text-[#1B4D3E]'
                        : 'border-stone-200 bg-white hover:border-stone-300'
                    }`}
                  >
                    <span className="bidi-isolate">{opt}</span>
                    {isSelected && <CheckCircle2 className="w-4 h-4 text-[#1B4D3E]" />}
                  </button>
                );
              })}
            </div>

            {/* Result feedback */}
            {quizResult && (
              <div
                className={`p-4 rounded-2xl border text-xs leading-relaxed space-y-1.5 ${
                  quizResult.is_correct
                    ? 'bg-emerald-50 border-emerald-300 text-emerald-950'
                    : 'bg-red-50 border-red-300 text-red-950'
                }`}
              >
                <div className="font-bold flex items-center gap-1.5">
                  {quizResult.is_correct ? (
                    <>
                      <CheckCircle2 className="w-4 h-4 text-emerald-700" />
                      <span>إجابة صحيحة (+15 XP)! زادت نسبة إتقانك للقاعدة.</span>
                    </>
                  ) : (
                    <>
                      <XCircle className="w-4 h-4 text-red-600" />
                      <span>إجابة غير صحيحة. الإجابة الصحيحة: {quizResult.correct_answer}</span>
                    </>
                  )}
                </div>
                <p>{quizResult.explanation_ar}</p>
              </div>
            )}

            {/* Actions */}
            <div className="pt-2">
              {!quizResult ? (
                <button
                  type="button"
                  disabled={!selectedAnswer || isSubmittingAnswer}
                  onClick={handleSubmitPractice}
                  className="w-full py-3.5 rounded-2xl bg-[#1B4D3E] text-white font-bold text-sm shadow-xs hover:bg-[#153e32] disabled:opacity-50"
                >
                  {isSubmittingAnswer ? 'جاري التحقق...' : 'تأكيد الإجابة'}
                </button>
              ) : (
                <button
                  type="button"
                  onClick={() => {
                    setActiveConcept(null);
                    setQuizData(null);
                  }}
                  className="w-full py-3.5 rounded-2xl bg-[#1B4D3E] text-white font-bold text-sm shadow-xs hover:bg-[#153e32]"
                >
                  إنهاء التدريب
                </button>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
