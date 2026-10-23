import React, { useEffect, useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { curriculumApi } from '../api/curriculum';
import { Lesson, LessonExercise } from '../types';
import { Navbar } from '../components/Navbar';
import { AudioSpeakerButton } from '../components/AudioSpeakerButton';
import { playSpanishAudio } from '../utils/speech';
import {
  Compass,
  CheckCircle2,
  XCircle,
  Sparkles,
  ArrowRight,
  RotateCcw,
  Award,
  BookOpen,
  Headphones,
  Volume2,
  Download,
  WifiOff
} from 'lucide-react';
import confetti from 'canvas-confetti';
import {
  saveLessonOffline,
  getOfflineLesson,
  isLessonSavedOffline,
  removeOfflineLesson,
  queueOfflineProgress
} from '../utils/offlineStorage';

export const LessonPlayerPage: React.FC = () => {
  const { lessonId } = useParams<{ lessonId: string }>();
  const navigate = useNavigate();

  const [lesson, setLesson] = useState<Lesson | null>(null);
  const [exercises, setExercises] = useState<LessonExercise[]>([]);
  const [currentIndex, setCurrentIndex] = useState(-1); // -1 = intro card
  const [isLoading, setIsLoading] = useState(true);

  // Exercise interaction states
  const [selectedOption, setSelectedOption] = useState<string | null>(null);
  const [translationInput, setTranslationInput] = useState('');
  const [wordOrderSelected, setWordOrderSelected] = useState<string[]>([]);
  const [wordOrderBank, setWordOrderBank] = useState<string[]>([]);
  const [matchingPairsAnswer, setMatchingPairsAnswer] = useState<Record<string, string>>({});
  const [selectedMatchingKey, setSelectedMatchingKey] = useState<string | null>(null);

  // Listening exercise specific states
  const [showTranscript, setShowTranscript] = useState(false);
  const [replayCount, setReplayCount] = useState(0);

  // Submission & feedback
  const [isAnswered, setIsAnswered] = useState(false);
  const [isCorrect, setIsCorrect] = useState(false);
  const [explanationAr, setExplanationAr] = useState<string | null>(null);
  const [hintAr, setHintAr] = useState<string | null>(null);
  const [correctAnswersRevealed, setCorrectAnswersRevealed] = useState<any>(null);

  // Completion
  const [isCompleted, setIsCompleted] = useState(false);
  const [completionResult, setCompletionResult] = useState<{
    score_percent: number;
    xp_awarded: number;
    next_lesson_id: number | null;
  } | null>(null);

  // Offline capabilities
  const [isOfflineMode, setIsOfflineMode] = useState(!navigator.onLine);
  const [isSavedOffline, setIsSavedOffline] = useState(false);
  const [localCorrectCount, setLocalCorrectCount] = useState(0);

  useEffect(() => {
    if (lessonId) {
      const id = parseInt(lessonId, 10);
      setIsSavedOffline(isLessonSavedOffline(id));
      loadLesson(id);
    }
  }, [lessonId]);

  const loadLesson = async (id: number) => {
    setIsLoading(true);
    try {
      const data = await curriculumApi.startLesson(id);
      setLesson(data.lesson);
      setExercises(data.exercises);
      setCurrentIndex(-1); // start at intro notes
      setIsCompleted(false);
      setIsOfflineMode(false);
      // Auto-cache for offline access
      saveLessonOffline(data.lesson, data.exercises);
      setIsSavedOffline(true);
    } catch (err) {
      console.warn('Network startLesson failed, attempting offline cache:', err);
      const cached = getOfflineLesson(id);
      if (cached) {
        setLesson(cached.lesson);
        setExercises(cached.exercises);
        setCurrentIndex(-1);
        setIsCompleted(false);
        setIsOfflineMode(true);
      } else {
        console.error('Lesson not found in offline storage');
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handleToggleSaveOffline = () => {
    if (!lesson) return;
    if (isSavedOffline) {
      removeOfflineLesson(lesson.id);
      setIsSavedOffline(false);
    } else {
      saveLessonOffline(lesson, exercises);
      setIsSavedOffline(true);
    }
  };

  // Reset exercise state when moving to next question
  const setupExercise = (idx: number) => {
    setCurrentIndex(idx);
    setIsAnswered(false);
    setIsCorrect(false);
    setExplanationAr(null);
    setHintAr(null);
    setCorrectAnswersRevealed(null);
    setSelectedOption(null);
    setTranslationInput('');
    setMatchingPairsAnswer({});
    setSelectedMatchingKey(null);
    setShowTranscript(false);
    setReplayCount(0);

    const ex = exercises[idx];
    if (ex && ex.exercise_type === 'word_order' && ex.options) {
      const optionsArray = Array.isArray(ex.options) ? [...ex.options] : [];
      setWordOrderBank(optionsArray);
      setWordOrderSelected([]);
    }
  };

  const handleRetryCurrentExercise = () => {
    setIsAnswered(false);
    setIsCorrect(false);
    setExplanationAr(null);
    setHintAr(null);
    setCorrectAnswersRevealed(null);
    setSelectedOption(null);
    setSelectedMatchingKey(null);
    setMatchingPairsAnswer({});

    const currentEx = exercises[currentIndex];
    if (currentEx && currentEx.exercise_type === 'word_order' && currentEx.options) {
      const optionsArray = Array.isArray(currentEx.options) ? [...currentEx.options] : [];
      setWordOrderBank(optionsArray);
      setWordOrderSelected([]);
    }
  };

  const renderCorrectAnswer = (ans: any): string => {
    if (!ans) return '';
    if (Array.isArray(ans)) return ans.join(', ');
    if (typeof ans === 'object') {
      return Object.entries(ans)
        .map(([k, v]) => `${k} ➔ ${v}`)
        .join(' | ');
    }
    return String(ans);
  };

  const handleStartExercises = () => {
    if (exercises.length > 0) {
      setupExercise(0);
    } else {
      finishLesson();
    }
  };

  const handleCheckAnswer = async () => {
    if (!lesson) return;
    const currentEx = exercises[currentIndex];
    if (!currentEx || isAnswered) return;

    let payload: any = null;

    if (currentEx.exercise_type === 'multiple_choice' || currentEx.exercise_type === 'fill_blank' || currentEx.exercise_type === 'listening') {
      payload = selectedOption;
    } else if (currentEx.exercise_type === 'translation') {
      payload = translationInput;
    } else if (currentEx.exercise_type === 'word_order') {
      payload = wordOrderSelected.join(' ');
    } else if (currentEx.exercise_type === 'matching_pairs') {
      payload = matchingPairsAnswer;
    }

    if (!payload && typeof payload !== 'object') return;

    if (isOfflineMode) {
      // Offline local evaluation
      let correct = false;
      if (Array.isArray(currentEx.correct_answers)) {
        correct = currentEx.correct_answers.some(
          (ans: any) => String(ans).trim().toLowerCase() === String(payload).trim().toLowerCase()
        );
      } else if (typeof currentEx.correct_answers === 'object') {
        correct = JSON.stringify(currentEx.correct_answers) === JSON.stringify(payload);
      } else {
        correct = String(currentEx.correct_answers).trim().toLowerCase() === String(payload).trim().toLowerCase();
      }

      setIsCorrect(correct);
      if (correct) {
        setLocalCorrectCount((prev) => prev + 1);
        confetti({ particleCount: 30, spread: 45, origin: { y: 0.7 } });
      }
      setCorrectAnswersRevealed(currentEx.correct_answers);
      setIsAnswered(true);
      return;
    }

    try {
      const res = await curriculumApi.answerExercise(lesson.id, currentEx.id, payload);
      setIsCorrect(res.is_correct);
      setExplanationAr(res.explanation_ar);
      setHintAr(res.hint_ar || null);
      setCorrectAnswersRevealed(res.correct_answers);
      setIsAnswered(true);

      if (res.is_correct) {
        confetti({ particleCount: 35, spread: 50, origin: { y: 0.7 } });
      }
    } catch (err) {
      console.warn('Network answer check failed, falling back to local evaluation:', err);
      setIsOfflineMode(true);
      let correct = false;
      if (Array.isArray(currentEx.correct_answers)) {
        correct = currentEx.correct_answers.some(
          (ans: any) => String(ans).trim().toLowerCase() === String(payload).trim().toLowerCase()
        );
      } else {
        correct = String(currentEx.correct_answers).trim().toLowerCase() === String(payload).trim().toLowerCase();
      }
      setIsCorrect(correct);
      if (correct) {
        setLocalCorrectCount((prev) => prev + 1);
      }
      setCorrectAnswersRevealed(currentEx.correct_answers);
      setIsAnswered(true);
    }
  };

  const handleNextStep = () => {
    if (currentIndex < exercises.length - 1) {
      setupExercise(currentIndex + 1);
    } else {
      finishLesson();
    }
  };

  const finishLesson = async () => {
    if (!lesson) return;

    if (isOfflineMode) {
      const total = Math.max(1, exercises.length);
      const score = Math.round((localCorrectCount / total) * 100);
      const xp = Math.round((lesson.xp_reward || 20) * (score / 100));

      queueOfflineProgress(lesson.id, score, xp);

      setCompletionResult({
        score_percent: score,
        xp_awarded: xp,
        next_lesson_id: null,
      });
      setIsCompleted(true);
      confetti({ particleCount: 90, spread: 70, origin: { y: 0.5 } });
      return;
    }

    try {
      const res = await curriculumApi.completeLesson(lesson.id);
      setCompletionResult({
        score_percent: res.score_percent,
        xp_awarded: res.xp_awarded,
        next_lesson_id: res.next_lesson_id,
      });
      setIsCompleted(true);
      confetti({ particleCount: 90, spread: 70, origin: { y: 0.5 } });
    } catch (err) {
      console.warn('Online completion failed, queuing offline progress:', err);
      const total = Math.max(1, exercises.length);
      const score = Math.round((localCorrectCount / total) * 100);
      const xp = Math.round((lesson.xp_reward || 20) * (score / 100));

      queueOfflineProgress(lesson.id, score, xp);

      setCompletionResult({
        score_percent: score,
        xp_awarded: xp,
        next_lesson_id: null,
      });
      setIsCompleted(true);
      confetti({ particleCount: 90, spread: 70, origin: { y: 0.5 } });
    }
  };

  // Word order chip interactions
  const pickWord = (word: string, indexInBank: number) => {
    if (isAnswered) return;
    playSpanishAudio(word);
    const newBank = [...wordOrderBank];
    newBank.splice(indexInBank, 1);
    setWordOrderBank(newBank);
    setWordOrderSelected([...wordOrderSelected, word]);
  };

  const returnWord = (word: string, indexInSelected: number) => {
    if (isAnswered) return;
    playSpanishAudio(word);
    const newSelected = [...wordOrderSelected];
    newSelected.splice(indexInSelected, 1);
    setWordOrderSelected(newSelected);
    setWordOrderBank([...wordOrderBank, word]);
  };

  // Matching pairs interactions
  const handlePairMatch = (spanishWord: string, arabicWord: string) => {
    if (isAnswered) return;
    playSpanishAudio(spanishWord);
    setMatchingPairsAnswer({
      ...matchingPairsAnswer,
      [spanishWord]: arabicWord,
    });
    setSelectedMatchingKey(null);
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col items-center justify-center gap-3">
        <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
        <p className="text-sm font-semibold text-stone-600">جاري تجهيز الدرس والتمارين...</p>
      </div>
    );
  }

  if (!lesson) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col items-center justify-center space-y-4">
        <p className="font-semibold text-stone-700">لم يتم العثور على الدرس.</p>
        <Link to="/learn" className="px-5 py-2.5 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs">
          العودة للمسار التعليمي
        </Link>
      </div>
    );
  }

  // Final Celebration Screen
  if (isCompleted && completionResult) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
        <Navbar />
        <main className="flex-1 max-w-xl mx-auto w-full px-4 py-16 flex flex-col items-center justify-center text-center space-y-6">
          <div className="w-20 h-20 rounded-3xl bg-amber-400 text-stone-950 flex items-center justify-center shadow-lg">
            <Award className="w-12 h-12" />
          </div>

          <div className="space-y-2">
            <h1 className="text-3xl font-black text-stone-900">¡Excelente! درس مكتمل</h1>
            <p className="text-sm text-stone-600">
              أنهيت درس <span className="font-bold text-[#1B4D3E]">"{lesson.title_ar}"</span> بنجاح.
            </p>
            {isOfflineMode && (
              <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold mt-1">
                <WifiOff className="w-3.5 h-3.5" />
                <span>تم حفظ إنجازك محلياً، وستتم مزامنة نقاط الـ XP مع الخادم عند عودة الإنترنت.</span>
              </div>
            )}
          </div>

          <div className="grid grid-cols-2 gap-4 w-full">
            <div className="p-4 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-1">
              <div className="text-xs text-stone-400 font-semibold">دقة الإجابات</div>
              <div className="text-2xl font-black text-emerald-700">{completionResult.score_percent}%</div>
            </div>

            <div className="p-4 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-1">
              <div className="text-xs text-stone-400 font-semibold">النقاط المكتسبة</div>
              <div className="text-2xl font-black text-amber-600">+{completionResult.xp_awarded} XP</div>
            </div>
          </div>

          <div className="w-full space-y-3 pt-4">
            {completionResult.next_lesson_id ? (
              <button
                onClick={() => navigate(`/learn/lessons/${completionResult.next_lesson_id}`)}
                className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] flex items-center justify-center gap-2"
              >
                <span>الانتقال للدرس التالي</span>
                <ArrowRight className="w-4 h-4 rotate-180" />
              </button>
            ) : (
              <Link
                to="/learn"
                className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] block text-center"
              >
                العودة إلى خريطة الدروس
              </Link>
            )}

            <Link
              to="/dashboard"
              className="block w-full py-3 text-xs text-stone-500 font-bold hover:text-stone-800 text-center"
            >
              الذهاب إلى لوحة التحكم
            </Link>
          </div>
        </main>
      </div>
    );
  }

  // Introduction Card Screen (currentIndex === -1)
  if (currentIndex === -1) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
        <Navbar />

        <main className="flex-1 max-w-2xl mx-auto w-full px-4 py-10 flex flex-col justify-between">
          <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-md space-y-6">
            <div className="flex items-center justify-between border-b border-stone-100 pb-4">
              <span className="text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                {lesson.unit?.title_ar || 'وحدة تعليمية'}
              </span>
              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={handleToggleSaveOffline}
                  className={`px-3 py-1 rounded-xl text-xs font-bold border transition-all flex items-center gap-1.5 cursor-pointer ${
                    isSavedOffline
                      ? 'bg-emerald-100 text-emerald-800 border-emerald-300'
                      : 'bg-stone-50 text-stone-600 border-stone-200 hover:border-emerald-300'
                  }`}
                  title="حفظ الدرس للتدرب بدون اتصال بالإنترنت"
                >
                  <Download className="w-3.5 h-3.5" />
                  <span>{isSavedOffline ? 'محفوظ بدون إنترنت ✓' : 'حفظ بدون إنترنت'}</span>
                </button>
                <span className="text-xs text-stone-400 font-medium">
                  {lesson.estimated_minutes} دقائق • {exercises.length} تدريبات
                </span>
              </div>
            </div>

            <div className="space-y-2 text-right">
              <h1 className="text-2xl sm:text-3xl font-black text-stone-900">
                {lesson.title_ar}
              </h1>
              <div className="flex items-center justify-end gap-2 text-sm font-semibold text-[#1B4D3E]">
                <span className="bidi-isolate">{lesson.title}</span>
                <AudioSpeakerButton text={lesson.title} size="sm" />
              </div>
            </div>

            <div className="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 space-y-1 text-right">
              <div className="text-xs font-bold text-stone-500">هدف الدرس التعليمي:</div>
              <p className="text-sm text-stone-800 leading-relaxed font-medium">
                {lesson.objective_ar}
              </p>
            </div>

            {lesson.grammar_notes_ar && (
              <div className="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 space-y-2 text-right">
                <div className="flex items-center gap-1.5 text-xs font-bold text-amber-900">
                  <Sparkles className="w-4 h-4 text-amber-600" />
                  <span>شرح قواعد مبسط (بالعامية المصرية والفصحى):</span>
                </div>
                <p className="text-xs text-stone-700 leading-relaxed font-normal">
                  {lesson.grammar_notes_ar}
                </p>
              </div>
            )}
          </div>

          <div className="pt-6">
            <button
              onClick={handleStartExercises}
              className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] flex items-center justify-center gap-2"
            >
              <span>ابدأ التمارين التفاعلية</span>
              <ArrowRight className="w-4 h-4 rotate-180" />
            </button>
          </div>
        </main>
      </div>
    );
  }

  // Exercise Player Screen
  const currentEx = exercises[currentIndex];
  const progressPercent = Math.round(((currentIndex + 1) / exercises.length) * 100);

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <main className="flex-1 max-w-2xl mx-auto w-full px-4 py-8 flex flex-col justify-between">
        {/* Progress Bar */}
        <div className="space-y-3">
          <div className="flex items-center justify-between text-xs font-bold text-stone-500">
            <span className="text-[#1B4D3E] font-bold">
              {lesson.title_ar}
            </span>
            <span>
              {currentIndex + 1} / {exercises.length}
            </span>
          </div>

          <div className="w-full bg-stone-200 h-2.5 rounded-full overflow-hidden">
            <div
              className="bg-[#1B4D3E] h-full transition-all duration-300"
              style={{ width: `${progressPercent}%` }}
            />
          </div>
        </div>

        {/* Exercise Card */}
        <div className="my-6 bg-white rounded-3xl p-6 sm:p-8 shadow-md border border-stone-200/80 space-y-6">
          <div className="text-right space-y-2">
            <span className="text-xs font-bold px-3 py-1 rounded-full bg-stone-100 text-stone-600">
              {currentEx.exercise_type === 'multiple_choice' && 'اختيار من متعدد'}
              {currentEx.exercise_type === 'translation' && 'ترجمة'}
              {currentEx.exercise_type === 'fill_blank' && 'أكمل الفراغ'}
              {currentEx.exercise_type === 'word_order' && 'ترتيب الكلمات'}
              {currentEx.exercise_type === 'matching_pairs' && 'توصيل الكلمات'}
              {currentEx.exercise_type === 'listening' && 'استماع وفهم سياقي'}
            </span>
            <h2 className="text-lg sm:text-xl font-bold text-stone-900 pt-1">
              {currentEx.prompt_ar}
            </h2>
            {currentEx.exercise_type !== 'listening' && (
              <div className="flex items-center justify-end gap-2 text-base font-semibold text-[#1B4D3E]">
                <span className="bidi-isolate">{currentEx.prompt}</span>
                <AudioSpeakerButton text={currentEx.prompt} size="sm" showSlowButton={true} />
              </div>
            )}
          </div>

          {/* Listening Exercise Audio Player & Scaffolding */}
          {currentEx.exercise_type === 'listening' && (
            <div className="p-6 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-4 text-center">
              <div className="flex items-center justify-between text-xs text-stone-600">
                <span className="font-bold text-amber-900 flex items-center gap-1.5">
                  <Headphones className="w-4 h-4 text-amber-600" />
                  <span>استمع بانتباه للمقطع الصوتي الإسباني، ثم اختر الإجابة الصحيحة:</span>
                </span>
                <span className="px-2.5 py-1 rounded-md bg-white border border-amber-200 text-amber-900 font-bold">
                  مرات الاستماع: {replayCount}
                </span>
              </div>

              <div className="py-3 flex flex-wrap items-center justify-center gap-3">
                <button
                  type="button"
                  onClick={() => {
                    setReplayCount((c) => c + 1);
                    playSpanishAudio(currentEx.prompt);
                  }}
                  className="px-6 py-3 rounded-2xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] flex items-center gap-2 cursor-pointer transition-all"
                >
                  <Volume2 className="w-5 h-5 text-amber-300" />
                  <span>تشغيل المقطع (نطق أصيل)</span>
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setReplayCount((c) => c + 1);
                    playSpanishAudio(currentEx.prompt, 0.72);
                  }}
                  className="px-4 py-3 rounded-2xl bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold text-xs shadow-2xs flex items-center gap-1.5 cursor-pointer transition-all"
                  title="استمع بنطق تعليمي متمهل"
                >
                  <span>🐢 بطيء (0.7x)</span>
                </button>
              </div>

              {/* Scaffolding Transcript Toggle */}
              <div className="pt-1 border-t border-amber-200/60">
                <button
                  type="button"
                  onClick={() => setShowTranscript(!showTranscript)}
                  className="text-xs font-semibold text-stone-500 hover:text-stone-800 underline cursor-pointer"
                >
                  {showTranscript ? 'إخفاء النص المكتوب ✕' : 'إظهار النص المكتوب للمساعدة (Scaffolding) 👁️'}
                </button>
                {showTranscript && (
                  <div className="mt-2 p-3 rounded-xl bg-white border border-amber-200 text-sm font-bold text-stone-800" dir="ltr">
                    "{currentEx.prompt}"
                  </div>
                )}
              </div>
            </div>
          )}

          {/* Exercise Type 1, 2 & Listening: Multiple Choice, Fill Blank, Listening */}
          {(currentEx.exercise_type === 'multiple_choice' || currentEx.exercise_type === 'fill_blank' || currentEx.exercise_type === 'listening') && (
            <div className="space-y-3 pt-2">
              {currentEx.options?.map((opt: string, i: number) => {
                const isSelected = selectedOption === opt;
                let btnStyle = 'border-stone-200 bg-white hover:border-emerald-300 text-stone-800';

                if (isSelected && !isAnswered) {
                  btnStyle = 'border-[#1B4D3E] bg-emerald-50 text-[#1B4D3E] font-bold';
                } else if (isAnswered) {
                  if (isSelected) {
                    btnStyle = isCorrect
                      ? 'border-emerald-500 bg-emerald-50 text-emerald-950 font-bold'
                      : 'border-red-400 bg-red-50 text-red-950 font-bold';
                  } else {
                    btnStyle = 'border-stone-100 bg-stone-50 text-stone-400 opacity-60';
                  }
                }

                return (
                  <button
                    key={i}
                    disabled={isAnswered}
                    onClick={() => {
                      setSelectedOption(opt);
                      playSpanishAudio(opt);
                    }}
                    className={`w-full p-4 rounded-2xl border-2 text-right text-base font-semibold transition-all flex items-center justify-between ${btnStyle}`}
                  >
                    <div className="flex items-center gap-2">
                      <span className="bidi-isolate">{opt}</span>
                      <AudioSpeakerButton text={opt} size="sm" />
                    </div>
                    {isSelected && isAnswered && (
                      <span>
                        {isCorrect ? (
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
          )}

          {/* Exercise Type 3: Translation Input */}
          {currentEx.exercise_type === 'translation' && (
            <div className="space-y-3 pt-2">
              <input
                type="text"
                disabled={isAnswered}
                value={translationInput}
                onChange={(e) => setTranslationInput(e.target.value)}
                placeholder="اكتب إجابتك هنا..."
                className="w-full p-4 rounded-2xl border-2 border-stone-300 focus:border-[#1B4D3E] focus:outline-none text-base font-semibold text-stone-900 transition-colors"
              />
            </div>
          )}

          {/* Exercise Type 4: Word Order */}
          {currentEx.exercise_type === 'word_order' && (
            <div className="space-y-6 pt-2">
              {/* Target Drop Zone */}
              <div className="min-h-[64px] p-4 rounded-2xl border-2 border-dashed border-stone-300 bg-stone-50/70 flex flex-wrap gap-2 items-center">
                {wordOrderSelected.length === 0 ? (
                  <span className="text-xs text-stone-400">انقر على الكلمات بالأسفل لترتيب الجملة</span>
                ) : (
                  wordOrderSelected.map((w, idx) => (
                    <button
                      key={idx}
                      disabled={isAnswered}
                      onClick={() => returnWord(w, idx)}
                      className="px-3.5 py-2 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm shadow-xs bidi-isolate hover:bg-emerald-900 transition-colors"
                    >
                      {w}
                    </button>
                  ))
                )}
              </div>

              {/* Word Bank */}
              <div className="flex flex-wrap gap-2 justify-center">
                {wordOrderBank.map((w, idx) => (
                  <button
                    key={idx}
                    disabled={isAnswered}
                    onClick={() => pickWord(w, idx)}
                    className="px-3.5 py-2 rounded-xl bg-white border border-stone-300 text-stone-800 font-bold text-sm shadow-2xs hover:border-[#1B4D3E] transition-all bidi-isolate"
                  >
                    {w}
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Exercise Type 5: Matching Pairs */}
          {currentEx.exercise_type === 'matching_pairs' && currentEx.options?.pairs && (
            <div className="space-y-4 pt-2">
              <p className="text-xs text-stone-500 font-semibold text-right">
                انقر على الكلمة الإسبانية ثم انقر على معناها بالعربية للتوصيل:
              </p>

              <div className="grid grid-cols-2 gap-3">
                {/* Spanish column */}
                <div className="space-y-2">
                  <div className="text-xs font-bold text-stone-400 text-center">الإسبانية</div>
                  {currentEx.options.pairs.map((p: any, i: number) => {
                    const matched = matchingPairsAnswer[p.es];
                    const isSelected = selectedMatchingKey === p.es;
                    return (
                      <button
                        key={i}
                        disabled={isAnswered || !!matched}
                        onClick={() => setSelectedMatchingKey(p.es)}
                        className={`w-full p-3 rounded-xl border text-sm font-bold transition-all bidi-isolate ${
                          matched
                            ? 'bg-emerald-100 border-emerald-300 text-emerald-950 opacity-80'
                            : isSelected
                            ? 'bg-[#1B4D3E] border-[#1B4D3E] text-white shadow-xs'
                            : 'bg-white border-stone-200 text-stone-800 hover:border-emerald-400'
                        }`}
                      >
                        {p.es}
                      </button>
                    );
                  })}
                </div>

                {/* Arabic column */}
                <div className="space-y-2">
                  <div className="text-xs font-bold text-stone-400 text-center">العربية</div>
                  {currentEx.options.pairs.map((p: any, i: number) => {
                    const isMatchedWithKey = Object.values(matchingPairsAnswer).includes(p.ar);
                    return (
                      <button
                        key={i}
                        disabled={isAnswered || isMatchedWithKey || !selectedMatchingKey}
                        onClick={() => selectedMatchingKey && handlePairMatch(selectedMatchingKey, p.ar)}
                        className={`w-full p-3 rounded-xl border text-sm font-bold transition-all ${
                          isMatchedWithKey
                            ? 'bg-emerald-100 border-emerald-300 text-emerald-950 opacity-80'
                            : selectedMatchingKey
                            ? 'bg-amber-50 border-amber-300 text-amber-950 hover:bg-amber-100'
                            : 'bg-white border-stone-200 text-stone-800'
                        }`}
                      >
                        {p.ar}
                      </button>
                    );
                  })}
                </div>
              </div>
            </div>
          )}

          {/* Feedback Card once answered */}
          {isAnswered && (
            <div
              className={`p-4 rounded-2xl border text-sm leading-relaxed space-y-2 ${
                isCorrect
                  ? 'bg-emerald-50 border-emerald-200 text-emerald-950'
                  : 'bg-red-50 border-red-200 text-red-950'
              }`}
            >
              <div className="font-bold flex items-center gap-1.5">
                {isCorrect ? (
                  <>
                    <CheckCircle2 className="w-5 h-5 text-emerald-600" />
                    <span>إجابة صحيحة! أحسنت العمل.</span>
                  </>
                ) : (
                  <>
                    <XCircle className="w-5 h-5 text-red-600" />
                    <span>إجابة غير صحيحة.</span>
                  </>
                )}
              </div>

              {explanationAr && (
                <p className="text-xs pt-1 text-stone-700">{explanationAr}</p>
              )}

              {!isCorrect && correctAnswersRevealed && (
                <div className="text-xs font-semibold text-stone-800 pt-1">
                  الإجابة النموذجية:{' '}
                  <span className="font-bold text-emerald-800 bidi-isolate">
                    {renderCorrectAnswer(correctAnswersRevealed)}
                  </span>
                </div>
              )}
            </div>
          )}
        </div>

        {/* Footer Actions */}
        <div className="pt-2">
          {!isAnswered ? (
            <button
              onClick={handleCheckAnswer}
              className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] transition-all focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 cursor-pointer"
            >
              تحقق من الإجابة
            </button>
          ) : !isCorrect ? (
            <div className="flex flex-col sm:flex-row items-center gap-3 w-full">
              <button
                onClick={handleRetryCurrentExercise}
                className="w-full sm:w-1/2 py-3.5 rounded-2xl bg-white border-2 border-stone-300 text-stone-800 font-bold text-sm hover:border-[#1B4D3E] hover:text-[#1B4D3E] transition-all flex items-center justify-center gap-2 cursor-pointer shadow-2xs focus:outline-none focus:ring-2 focus:ring-[#1B4D3E]"
              >
                <RotateCcw className="w-4 h-4 text-stone-600" />
                <span>إعادة المحاولة (تمرن مرة تانية)</span>
              </button>
              <button
                onClick={handleNextStep}
                className="w-full sm:w-1/2 py-3.5 rounded-2xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] transition-all flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
              >
                <span>{currentIndex < exercises.length - 1 ? 'تخطي ومتابعة' : 'إنهاء الدرس'}</span>
                <ArrowRight className="w-4 h-4 rotate-180" />
              </button>
            </div>
          ) : (
            <button
              onClick={handleNextStep}
              className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] transition-all flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 cursor-pointer"
            >
              <span>{currentIndex < exercises.length - 1 ? 'التمرين التالي' : 'إنهاء الدرس وحساب النتائج'}</span>
              <ArrowRight className="w-4 h-4 rotate-180" />
            </button>
          )}
        </div>
      </main>
    </div>
  );
};
