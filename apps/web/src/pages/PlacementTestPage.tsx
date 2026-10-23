import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { placementApi } from '../api/placement';
import { PlacementAttempt, PlacementQuestion } from '../types';
import { Navbar } from '../components/Navbar';
import {
  Compass,
  CheckCircle2,
  XCircle,
  ArrowRight,
  TrendingUp,
  Sparkles,
  HelpCircle,
  Clock
} from 'lucide-react';

export const PlacementTestPage: React.FC = () => {
  const navigate = useNavigate();

  const [attempt, setAttempt] = useState<PlacementAttempt | null>(null);
  const [questions, setQuestions] = useState<PlacementQuestion[]>([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [selectedOption, setSelectedOption] = useState<string | null>(null);
  const [isAnswerSubmitted, setIsAnswerSubmitted] = useState(false);
  const [answerFeedback, setAnswerFeedback] = useState<{ is_correct: boolean; explanation_ar: string } | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isFinishing, setIsFinishing] = useState(false);
  const [startTime, setStartTime] = useState<number>(Date.now());

  useEffect(() => {
    loadTest();
  }, []);

  const loadTest = async () => {
    setIsLoading(true);
    try {
      const data = await placementApi.start();
      setAttempt(data.attempt);
      setQuestions(data.questions);

      // Resume at first unanswered question if returning to an in-progress attempt
      const answeredSet = new Set(data.answered_question_ids || []);
      const firstUnanswered = data.questions.findIndex((q) => !answeredSet.has(q.id));
      if (firstUnanswered >= 0) {
        setCurrentIndex(firstUnanswered);
      }
      setStartTime(Date.now());
    } catch (err) {
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  const handleSelectOption = (opt: string) => {
    if (isAnswerSubmitted) return;
    setSelectedOption(opt);
  };

  const handleSubmitAnswer = async () => {
    if (!selectedOption || !attempt || isAnswerSubmitted) return;

    const currentQ = questions[currentIndex];
    const timeSpent = Math.round((Date.now() - startTime) / 1000);

    try {
      const res = await placementApi.submitAnswer(attempt.id, currentQ.id, selectedOption, timeSpent);
      setAnswerFeedback({
        is_correct: res.result.is_correct,
        explanation_ar: res.result.explanation_ar,
      });
      setIsAnswerSubmitted(true);
    } catch (err) {
      console.error(err);
    }
  };

  const handleNext = async () => {
    if (!attempt) return;

    if (currentIndex < questions.length - 1) {
      setCurrentIndex(currentIndex + 1);
      setSelectedOption(null);
      setIsAnswerSubmitted(false);
      setAnswerFeedback(null);
      setStartTime(Date.now());
    } else {
      // Last question completed -> finish test
      setIsFinishing(true);
      try {
        const finishRes = await placementApi.finish(attempt.id);
        navigate('/placement-test/results', { state: { attempt: finishRes.attempt } });
      } catch (err) {
        console.error(err);
        navigate('/placement-test/results');
      } finally {
        setIsFinishing(false);
      }
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col items-center justify-center gap-3">
        <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
        <p className="text-sm font-semibold text-stone-600">جاري إعداد اختبار تحديد المستوى...</p>
      </div>
    );
  }

  const currentQ = questions[currentIndex];
  if (!currentQ) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col items-center justify-center">
        <p>لا توجد أسئلة متاحة حالياً.</p>
      </div>
    );
  }

  const progressPercent = Math.round(((currentIndex + 1) / questions.length) * 100);

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <main className="flex-1 max-w-3xl mx-auto w-full px-4 sm:px-6 py-8 flex flex-col justify-between">
        {/* Progress Bar & Header */}
        <div className="space-y-3">
          <div className="flex items-center justify-between text-xs font-bold text-stone-500">
            <span className="flex items-center gap-1.5 text-[#1B4D3E]">
              <TrendingUp className="w-4 h-4" />
              اختبار تحديد المستوى التكيفي
            </span>
            <span>
              السؤال {currentIndex + 1} من {questions.length}
            </span>
          </div>

          <div className="w-full bg-stone-200 h-2.5 rounded-full overflow-hidden">
            <div
              className="bg-[#1B4D3E] h-full transition-all duration-300"
              style={{ width: `${progressPercent}%` }}
            />
          </div>
        </div>

        {/* Question Card */}
        <div className="my-8 bg-white rounded-3xl p-6 sm:p-8 shadow-lg border border-stone-200/80 space-y-6">
          {/* Category Pill */}
          <div className="flex items-center justify-between">
            <span className="text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
              {currentQ.skill_category === 'vocabulary' && 'مهارة المفردات'}
              {currentQ.skill_category === 'grammar' && 'القواعد اللغوية'}
              {currentQ.skill_category === 'reading' && 'فهم المقروء'}
            </span>
            <span className="text-xs font-semibold text-stone-400">
              مستوى الصعوبة: {currentQ.difficulty_level}
            </span>
          </div>

          {/* Reading Passage Context if any */}
          {currentQ.context_text && (
            <div className="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/70 text-stone-800 text-sm leading-relaxed bidi-isolate font-medium">
              <div className="text-xs font-bold text-amber-900 mb-1">اقرأ النص التالي للإجابة:</div>
              "{currentQ.context_text}"
            </div>
          )}

          {/* Prompt */}
          <div className="space-y-2">
            <h2 className="text-xl sm:text-2xl font-bold text-stone-900 text-right leading-snug">
              {currentQ.prompt_ar}
            </h2>
            <p className="text-lg font-semibold text-[#1B4D3E] bidi-isolate text-right">
              {currentQ.question_text}
            </p>
          </div>

          {/* Options */}
          <div className="space-y-3 pt-2">
            {currentQ.options.map((opt, idx) => {
              const isSelected = selectedOption === opt;
              let btnClass = 'border-stone-200 bg-white hover:border-emerald-300 text-stone-800';

              if (isSelected && !isAnswerSubmitted) {
                btnClass = 'border-[#1B4D3E] bg-emerald-50/70 text-[#1B4D3E] font-bold shadow-xs';
              } else if (isAnswerSubmitted) {
                if (isSelected) {
                  btnClass = answerFeedback?.is_correct
                    ? 'border-emerald-500 bg-emerald-50 text-emerald-950 font-bold'
                    : 'border-red-400 bg-red-50 text-red-950 font-bold';
                } else {
                  btnClass = 'border-stone-100 bg-stone-50 text-stone-400 opacity-60';
                }
              }

              return (
                <button
                  key={idx}
                  disabled={isAnswerSubmitted}
                  onClick={() => handleSelectOption(opt)}
                  className={`w-full p-4 rounded-2xl border-2 text-right text-base font-semibold transition-all flex items-center justify-between ${btnClass}`}
                >
                  <span className="bidi-isolate">{opt}</span>
                  {isSelected && isAnswerSubmitted && (
                    <span>
                      {answerFeedback?.is_correct ? (
                        <CheckCircle2 className="w-5 h-5 text-emerald-600" />
                      ) : (
                        <XCircle className="w-5 h-5 text-red-600" />
                      )}
                    </span>
                  )}
                </button>
              );
            })}
          </div>

          {/* Feedback Section once answered */}
          {isAnswerSubmitted && answerFeedback && (
            <div
              className={`p-4 rounded-2xl border text-sm leading-relaxed space-y-1 ${
                answerFeedback.is_correct
                  ? 'bg-emerald-50 border-emerald-200 text-emerald-950'
                  : 'bg-red-50 border-red-200 text-red-950'
              }`}
            >
              <div className="font-bold flex items-center gap-1.5">
                {answerFeedback.is_correct ? (
                  <>
                    <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                    <span>إجابة صحيحة! أحسنت.</span>
                  </>
                ) : (
                  <>
                    <XCircle className="w-4 h-4 text-red-600" />
                    <span>إجابة غير دقيقة.</span>
                  </>
                )}
              </div>
              <p className="text-xs pt-1">{answerFeedback.explanation_ar}</p>
            </div>
          )}
        </div>

        {/* Action Button */}
        <div className="pt-2">
          {!isAnswerSubmitted ? (
            <button
              disabled={!selectedOption}
              onClick={handleSubmitAnswer}
              className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] transition-all disabled:opacity-40"
            >
              تأكيد الإجابة
            </button>
          ) : (
            <button
              onClick={handleNext}
              disabled={isFinishing}
              className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] transition-all flex items-center justify-center gap-2"
            >
              {isFinishing ? (
                <span>جاري حساب النتيجة والتحليل النهائي...</span>
              ) : (
                <>
                  <span>{currentIndex < questions.length - 1 ? 'السؤال التالي' : 'عرض النتيجة والتقرير'}</span>
                  <ArrowRight className="w-5 h-5 rotate-180" />
                </>
              )}
            </button>
          )}
        </div>
      </main>
    </div>
  );
};
