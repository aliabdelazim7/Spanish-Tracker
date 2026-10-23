import React, { useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { levelsApi } from '../api/levels';
import { playSpanishAudio } from '../utils/speech';
import confetti from 'canvas-confetti';
import {
  Award,
  CheckCircle2,
  XCircle,
  Volume2,
  ArrowRight,
  ArrowLeft,
  RotateCcw,
  Sparkles,
  Lock,
  ChevronRight,
  BookOpen
} from 'lucide-react';
import { useTranslation } from 'react-i18next';

export const LevelExamPage: React.FC = () => {
  const { levelCode = 'A1' } = useParams<{ levelCode: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const { i18n } = useTranslation();
  const isRtl = i18n.language === 'ar';

  const [currentIndex, setCurrentIndex] = useState(0);
  const [selectedAnswers, setSelectedAnswers] = useState<Record<number, string>>({});
  const [examResult, setExamResult] = useState<any | null>(null);

  const { data: examData, isLoading, error } = useQuery({
    queryKey: ['levelExam', levelCode],
    queryFn: () => levelsApi.getExam(levelCode),
  });

  const submitMutation = useMutation({
    mutationFn: (answers: { question_id: number; answer: string }[]) =>
      levelsApi.submitExam(levelCode, answers),
    onSuccess: (data) => {
      setExamResult(data);
      queryClient.invalidateQueries({ queryKey: ['learningPath'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard'] });
      queryClient.invalidateQueries({ queryKey: ['userProfile'] });

      if (data.passed) {
        confetti({
          particleCount: 120,
          spread: 80,
          origin: { y: 0.6 },
          colors: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'],
        });
      }
    },
  });

  if (isLoading) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] space-y-4">
        <div className="w-12 h-12 border-4 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
        <p className="text-gray-600 font-medium">جاري تجهيز اختبار اجتياز المستوى...</p>
      </div>
    );
  }

  if (error || !examData) {
    return (
      <div className="max-w-md mx-auto my-12 p-6 bg-white rounded-2xl shadow-sm border border-red-100 text-center space-y-4">
        <div className="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto">
          <Lock className="w-8 h-8" />
        </div>
        <h3 className="text-xl font-bold text-gray-900">المستوى مغلق أو غير متاح</h3>
        <p className="text-gray-600 text-sm">
          يجب اجتياز المستويات والدروس السابقة لفتح هذا الاختبار.
        </p>
        <Link
          to="/learn"
          className="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-medium transition"
        >
          {isRtl ? <ArrowRight className="w-4 h-4" /> : <ArrowLeft className="w-4 h-4" />}
          العودة للمسار التعليمي
        </Link>
      </div>
    );
  }

  const questions = examData.questions;
  const currentQ = questions[currentIndex];
  const totalQ = questions.length;
  const progressPercent = Math.round(((currentIndex + 1) / totalQ) * 100);

  const handleSelectOption = (option: string) => {
    setSelectedAnswers((prev) => ({
      ...prev,
      [currentQ.id]: option,
    }));
  };

  const handleNext = () => {
    if (currentIndex < totalQ - 1) {
      setCurrentIndex((prev) => prev + 1);
    } else {
      // Submit exam
      const payload = Object.entries(selectedAnswers).map(([qId, ans]) => ({
        question_id: Number(qId),
        answer: ans,
      }));
      submitMutation.mutate(payload);
    }
  };

  const handlePrevious = () => {
    if (currentIndex > 0) {
      setCurrentIndex((prev) => prev - 1);
    }
  };

  // Result View
  if (examResult) {
    const passed = examResult.passed;
    return (
      <div className="max-w-2xl mx-auto py-8 px-4 space-y-6">
        <div
          className={`p-8 rounded-3xl text-center space-y-4 border ${
            passed ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200'
          }`}
        >
          <div
            className={`w-20 h-20 rounded-full mx-auto flex items-center justify-center shadow-lg ${
              passed ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'
            }`}
          >
            {passed ? <Award className="w-10 h-10" /> : <XCircle className="w-10 h-10" />}
          </div>

          <h2 className="text-3xl font-extrabold text-gray-900">
            {passed ? '🎉 تهانينا! اجتزت المستوى بنجاح!' : 'فرصة أخرى قريبة!'}
          </h2>

          <div className="inline-block px-4 py-1.5 rounded-full bg-white shadow-sm border text-lg font-bold">
            النتيجة: {examResult.score_percent}% (المطلوب 70%)
          </div>

          <p className="text-gray-700 leading-relaxed max-w-lg mx-auto">
            {examResult.feedback_ar}
          </p>

          {passed && examResult.next_level && (
            <div className="p-4 bg-white/80 backdrop-blur rounded-2xl border border-emerald-300 max-w-md mx-auto text-emerald-900 flex items-center justify-center gap-3">
              <Sparkles className="w-6 h-6 text-emerald-600 animate-pulse" />
              <span className="font-bold text-lg">
                تم فتح المستوى الجديد: {examResult.next_level}! (+{examResult.xp_awarded} XP)
              </span>
            </div>
          )}

          <div className="flex flex-wrap items-center justify-center gap-3 pt-4">
            {passed ? (
              <button
                onClick={() => navigate('/learn')}
                className="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2"
              >
                <span>الانتقال للمسار التعليمي</span>
                <ChevronRight className="w-5 h-5 rtl:rotate-180" />
              </button>
            ) : (
              <>
                <button
                  onClick={() => {
                    setExamResult(null);
                    setCurrentIndex(0);
                    setSelectedAnswers({});
                  }}
                  className="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2"
                >
                  <RotateCcw className="w-4 h-4" />
                  <span>إعادة المحاولة الآن</span>
                </button>
                <button
                  onClick={() => navigate('/learn')}
                  className="px-5 py-3 bg-white hover:bg-gray-50 text-gray-700 font-semibold rounded-xl border border-gray-200 transition"
                >
                  مراجعة الدروس أولاً
                </button>
              </>
            )}
          </div>
        </div>

        {/* Detailed Breakdown */}
        {examResult.detailed_results && (
          <div className="bg-white rounded-2xl border p-6 space-y-4 shadow-sm">
            <h4 className="text-lg font-bold text-gray-900 flex items-center gap-2">
              <BookOpen className="w-5 h-5 text-amber-500" />
              <span>مراجعة إجابات الاختبار:</span>
            </h4>
            <div className="space-y-3">
              {examResult.detailed_results.map((r: any, idx: number) => (
                <div
                  key={idx}
                  className={`p-4 rounded-xl border text-sm ${
                    r.is_correct ? 'bg-emerald-50/50 border-emerald-200' : 'bg-red-50/50 border-red-200'
                  }`}
                >
                  <div className="flex items-start gap-2">
                    {r.is_correct ? (
                      <CheckCircle2 className="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" />
                    ) : (
                      <XCircle className="w-5 h-5 text-red-600 shrink-0 mt-0.5" />
                    )}
                    <div className="space-y-1">
                      <p className="font-semibold text-gray-900">{r.question_text}</p>
                      <p className="text-gray-600">
                        إجابتك: <span className="font-medium text-gray-900">{r.user_answer}</span>
                      </p>
                      {!r.is_correct && (
                        <p className="text-emerald-700 font-medium">
                          الإجابة الصحيحة: {r.correct_answer}
                        </p>
                      )}
                      {r.explanation_ar && (
                        <p className="text-xs text-gray-500 pt-1 border-t border-gray-100">
                          {r.explanation_ar}
                        </p>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    );
  }

  const isCurrentAnswered = !!selectedAnswers[currentQ?.id];

  return (
    <div className="max-w-2xl mx-auto py-8 px-4 space-y-6">
      {/* Top Header */}
      <div className="bg-white p-5 rounded-2xl border shadow-sm space-y-3">
        <div className="flex items-center justify-between text-sm text-gray-600 font-medium">
          <div className="flex items-center gap-2">
            <span className="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg font-bold text-xs uppercase">
              {examData.level.code}
            </span>
            <span>اختبار اجتياز المستوى ({examData.level.name_ar})</span>
          </div>
          <span>
            سؤال {currentIndex + 1} من {totalQ}
          </span>
        </div>

        {/* Progress Bar */}
        <div className="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
          <div
            className="bg-amber-500 h-full rounded-full transition-all duration-300"
            style={{ width: `${progressPercent}%` }}
          />
        </div>
      </div>

      {/* Question Card */}
      <div className="bg-white p-6 sm:p-8 rounded-3xl border shadow-sm space-y-6">
        <div className="flex items-center justify-between">
          <span className="text-xs px-3 py-1 bg-gray-100 text-gray-700 font-semibold rounded-full uppercase">
            قسم {currentQ.skill_category}
          </span>
          <button
            onClick={() => playSpanishAudio(currentQ.question_text)}
            className="p-2 text-amber-600 hover:bg-amber-50 rounded-full transition"
            title="استمع للنطق الإسباني"
          >
            <Volume2 className="w-5 h-5" />
          </button>
        </div>

        <div className="space-y-2">
          <h3 className="text-xl sm:text-2xl font-bold text-gray-900 leading-snug" dir="ltr">
            {currentQ.question_text}
          </h3>
          <p className="text-gray-600 text-sm">{currentQ.prompt_ar}</p>
        </div>

        {/* Options */}
        <div className="space-y-3 pt-2">
          {currentQ.options.map((option, idx) => {
            const isSelected = selectedAnswers[currentQ.id] === option;
            return (
              <button
                key={idx}
                onClick={() => handleSelectOption(option)}
                className={`w-full p-4 rounded-2xl border-2 text-start font-medium transition-all flex items-center justify-between ${
                  isSelected
                    ? 'border-amber-500 bg-amber-50/50 text-amber-900 shadow-sm'
                    : 'border-gray-200 hover:border-gray-300 text-gray-800'
                }`}
              >
                <span className="text-base" dir="auto">
                  {option}
                </span>
                <div
                  className={`w-5 h-5 rounded-full border-2 flex items-center justify-center ${
                    isSelected ? 'border-amber-500 bg-amber-500 text-white' : 'border-gray-300'
                  }`}
                >
                  {isSelected && <div className="w-2 h-2 bg-white rounded-full" />}
                </div>
              </button>
            );
          })}
        </div>
      </div>

      {/* Navigation Buttons */}
      <div className="flex items-center justify-between pt-2">
        <button
          onClick={handlePrevious}
          disabled={currentIndex === 0}
          className={`px-5 py-2.5 rounded-xl border font-medium text-sm transition ${
            currentIndex === 0
              ? 'opacity-40 cursor-not-allowed bg-gray-50 text-gray-400'
              : 'hover:bg-gray-50 text-gray-700'
          }`}
        >
          السابق
        </button>

        <button
          onClick={handleNext}
          disabled={!isCurrentAnswered || submitMutation.isPending}
          className={`px-7 py-3 rounded-xl font-bold text-sm shadow-md transition flex items-center gap-2 ${
            !isCurrentAnswered || submitMutation.isPending
              ? 'opacity-50 cursor-not-allowed bg-gray-300 text-gray-500'
              : 'bg-amber-500 hover:bg-amber-600 text-white'
          }`}
        >
          {submitMutation.isPending ? (
            <span>جاري التقييم...</span>
          ) : currentIndex === totalQ - 1 ? (
            <span>إنهاء وتسليم الاختبار</span>
          ) : (
            <>
              <span>التالي</span>
              <ChevronRight className="w-4 h-4 rtl:rotate-180" />
            </>
          )}
        </button>
      </div>
    </div>
  );
};
export default LevelExamPage;
