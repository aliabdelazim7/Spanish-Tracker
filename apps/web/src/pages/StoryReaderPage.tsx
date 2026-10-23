import React, { useEffect, useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { storiesApi } from '../api/stories';
import { vocabularyApi } from '../api/vocabulary';
import { Story, StoryQuestion } from '../types';
import { Navbar } from '../components/Navbar';
import { AudioSpeakerButton } from '../components/AudioSpeakerButton';
import { playSpanishAudio } from '../utils/speech';
import {
  Compass,
  ArrowRight,
  BookMarked,
  CheckCircle2,
  XCircle,
  Volume2,
  BookmarkPlus,
  Award
} from 'lucide-react';
import confetti from 'canvas-confetti';

export const StoryReaderPage: React.FC = () => {
  const { storyId } = useParams<{ storyId: string }>();
  const navigate = useNavigate();

  const [story, setStory] = useState<Story | null>(null);
  const [currentSegmentIndex, setCurrentSegmentIndex] = useState(0);
  const [selectedWordGloss, setSelectedWordGloss] = useState<string | null>(null);
  const [savedWordSuccess, setSavedWordSuccess] = useState<boolean>(false);

  // Question interaction
  const [activeQuestion, setActiveQuestion] = useState<StoryQuestion | null>(null);
  const [selectedQuestionOption, setSelectedQuestionOption] = useState<string | null>(null);
  const [isQuestionAnswered, setIsQuestionAnswered] = useState(false);
  const [isQuestionCorrect, setIsQuestionCorrect] = useState(false);

  // Finish
  const [isStoryFinished, setIsStoryFinished] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (storyId) {
      loadStory(parseInt(storyId, 10));
    }
  }, [storyId]);

  const loadStory = async (id: number) => {
    setIsLoading(true);
    try {
      const data = await storiesApi.getStory(id);
      setStory(data.story);
      setCurrentSegmentIndex(data.progress?.current_segment_index || 0);
    } catch (err) {
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  // Click or hover on any word plays pronunciation and shows definition
  const handleWordClick = (rawWord: string) => {
    const clean = rawWord.replace(/[.,¿?¡!]/g, '').trim();
    if (!clean) return;

    setSelectedWordGloss(clean);
    setSavedWordSuccess(false);
    playSpanishAudio(clean);
  };

  const handleNextSegment = () => {
    if (!story || !story.segments) return;

    // Check if there is a question attached after this segment
    const q = story.questions?.find((qu) => qu.after_segment_index === currentSegmentIndex + 1);
    if (q && !activeQuestion) {
      setActiveQuestion(q);
      setSelectedQuestionOption(null);
      setIsQuestionAnswered(false);
      return;
    }

    if (currentSegmentIndex < story.segments.length - 1) {
      const nextIdx = currentSegmentIndex + 1;
      setCurrentSegmentIndex(nextIdx);
      setSelectedWordGloss(null);
      setActiveQuestion(null);
      storiesApi.updateProgress(story.id, nextIdx).catch(() => {});
    } else {
      finishStory();
    }
  };

  const handleAnswerQuestion = () => {
    if (!activeQuestion || !selectedQuestionOption) return;

    const isMatch = selectedQuestionOption.trim() === activeQuestion.correct_answer.trim();
    setIsQuestionCorrect(isMatch);
    setIsQuestionAnswered(true);

    if (isMatch) {
      confetti({ particleCount: 30, spread: 50, origin: { y: 0.7 } });
    }
  };

  const finishStory = async () => {
    if (!story) return;
    try {
      await storiesApi.completeStory(story.id);
      setIsStoryFinished(true);
      confetti({ particleCount: 80, spread: 70, origin: { y: 0.5 } });
    } catch (err) {
      console.error(err);
      setIsStoryFinished(true);
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col items-center justify-center gap-3">
        <Compass className="w-10 h-10 text-[#1B4D3E] animate-spin" />
        <p className="text-sm font-semibold text-stone-600">جاري تحميل القصة...</p>
      </div>
    );
  }

  if (!story || !story.segments || story.segments.length === 0) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col items-center justify-center space-y-4">
        <p className="text-stone-700 font-semibold">لم يتم العثور على محتوى لهذه القصة.</p>
        <Link to="/stories" className="px-5 py-2.5 rounded-xl bg-[#1B4D3E] text-white text-xs font-bold">
          العودة للقصص
        </Link>
      </div>
    );
  }

  if (isStoryFinished) {
    return (
      <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
        <Navbar />
        <main className="flex-1 max-w-xl mx-auto w-full px-4 py-16 flex flex-col items-center justify-center text-center space-y-6">
          <div className="w-20 h-20 rounded-3xl bg-amber-400 text-stone-950 flex items-center justify-center shadow-lg">
            <Award className="w-12 h-12" />
          </div>

          <div className="space-y-2">
            <h1 className="text-3xl font-black text-stone-900">أحسنت! أتممت القراءة</h1>
            <p className="text-sm text-stone-600">
              أنهيت قراءة قصة <span className="font-bold text-[#1B4D3E]">"{story.title_ar}"</span> وحصلت على +{story.xp_reward} XP!
            </p>
          </div>

          <div className="w-full space-y-3 pt-4">
            <Link
              to="/stories"
              className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] block text-center"
            >
              استكشف المزيد من القصص
            </Link>

            <Link
              to="/review"
              className="block w-full py-3 text-xs text-stone-500 font-bold hover:text-stone-800 text-center"
            >
              مراجعة المفردات التي تعلمتها
            </Link>
          </div>
        </main>
      </div>
    );
  }

  const currentSegment = story.segments[currentSegmentIndex];
  const progressPercent = Math.round(((currentSegmentIndex + 1) / story.segments.length) * 100);

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <main className="flex-1 max-w-2xl mx-auto w-full px-4 py-8 flex flex-col justify-between">
        {/* Progress & Header */}
        <div className="space-y-3">
          <div className="flex items-center justify-between text-xs font-bold text-stone-500">
            <span className="flex items-center gap-1.5 text-[#1B4D3E]">
              <BookMarked className="w-4 h-4" />
              {story.title_ar} ({story.title_es})
            </span>
            <span>
              الجملة {currentSegmentIndex + 1} من {story.segments.length}
            </span>
          </div>

          <div className="w-full bg-stone-200 h-2 rounded-full overflow-hidden">
            <div
              className="bg-[#1B4D3E] h-full transition-all duration-300"
              style={{ width: `${progressPercent}%` }}
            />
          </div>
        </div>

        {/* Reader Card */}
        {!activeQuestion ? (
          <div className="my-8 bg-white rounded-3xl p-6 sm:p-8 shadow-md border border-stone-200/80 space-y-6">
            <div className="flex items-center justify-between">
              {currentSegment.speaker_name ? (
                <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold">
                  <span>المتحدث:</span>
                  <span className="bidi-isolate">{currentSegment.speaker_name}</span>
                </div>
              ) : <div />}

              {/* Full sentence audio playback */}
              <AudioSpeakerButton
                text={currentSegment.spanish_text}
                label="استمع للجملة كاملة"
                size="sm"
                showSlowButton={true}
              />
            </div>

            {/* Interactive Spanish Text (clickable words that speak) */}
            <div className="text-xl sm:text-2xl font-bold text-stone-900 leading-relaxed text-right bidi-isolate flex flex-wrap gap-1.5 justify-end">
              {currentSegment.spanish_text.split(' ').map((word, idx) => (
                <button
                  key={idx}
                  type="button"
                  onClick={() => handleWordClick(word)}
                  className="hover:text-emerald-700 hover:bg-emerald-50 px-1 py-0.5 rounded-lg transition-colors cursor-pointer border-b border-dashed border-stone-300 hover:border-emerald-500"
                  title="انقر للاستماع للنطق الفوري"
                >
                  {word}
                </button>
              ))}
            </div>

            {/* Arabic Translation */}
            <div className="p-4 rounded-2xl bg-stone-50 border border-stone-200 text-right space-y-1">
              <div className="text-xs font-bold text-stone-400">الترجمة والمعنى:</div>
              <p className="text-sm font-semibold text-stone-800 leading-relaxed">
                {currentSegment.arabic_translation}
              </p>
            </div>

            {/* Cultural note if available */}
            {currentSegment.explanation_ar && (
              <div className="p-3.5 rounded-2xl bg-amber-50 border border-amber-200/80 text-xs text-amber-950 text-right leading-relaxed">
                <span className="font-bold">فائدة لغوية:</span> {currentSegment.explanation_ar}
              </div>
            )}

            {/* Word Gloss Card when a word is clicked */}
            {selectedWordGloss && (
              <div className="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-right space-y-2">
                <div className="flex items-center justify-between">
                  <button
                    type="button"
                    onClick={() => setSelectedWordGloss(null)}
                    className="text-xs text-stone-400 hover:text-stone-700 cursor-pointer"
                  >
                    إغلاق ×
                  </button>
                  <div className="flex items-center gap-2">
                    <span className="text-base font-black text-emerald-950 bidi-isolate">
                      {selectedWordGloss}
                    </span>
                    <AudioSpeakerButton text={selectedWordGloss} size="sm" />
                  </div>
                </div>
                <p className="text-xs text-emerald-900 leading-relaxed">
                  تم تشغيل النطق الصوتي للكلمة. يمكنك الاستماع إليها مرة أخرى بالنقر على أيقونة الصوت.
                </p>
              </div>
            )}
          </div>
        ) : (
          /* Question Checkpoint Card */
          <div className="my-8 bg-white rounded-3xl p-6 sm:p-8 shadow-md border border-stone-200/80 space-y-6">
            <div className="text-xs font-bold px-3 py-1 rounded-full bg-purple-50 text-purple-800 border border-purple-200 inline-block">
              سؤال استيعاب وفهم
            </div>

            <div className="text-right space-y-1">
              <h3 className="text-lg font-bold text-stone-900">{activeQuestion.question_ar}</h3>
              <div className="flex items-center justify-end gap-2">
                <p className="text-sm text-[#1B4D3E] font-semibold bidi-isolate">{activeQuestion.question_es}</p>
                <AudioSpeakerButton text={activeQuestion.question_es} size="sm" />
              </div>
            </div>

            <div className="space-y-3 pt-2">
              {activeQuestion.options.map((opt, i) => {
                const isSelected = selectedQuestionOption === opt;
                let btnStyle = 'border-stone-200 bg-white hover:border-emerald-300 text-stone-800';

                if (isSelected && !isQuestionAnswered) {
                  btnStyle = 'border-[#1B4D3E] bg-emerald-50 text-[#1B4D3E] font-bold';
                } else if (isQuestionAnswered) {
                  if (isSelected) {
                    btnStyle = isQuestionCorrect
                      ? 'border-emerald-500 bg-emerald-50 text-emerald-950 font-bold'
                      : 'border-red-400 bg-red-50 text-red-950 font-bold';
                  } else {
                    btnStyle = 'border-stone-100 bg-stone-50 text-stone-400 opacity-60';
                  }
                }

                return (
                  <button
                    key={i}
                    disabled={isQuestionAnswered}
                    onClick={() => setSelectedQuestionOption(opt)}
                    className={`w-full p-4 rounded-2xl border-2 text-right text-sm font-semibold transition-all flex items-center justify-between ${btnStyle}`}
                  >
                    <div className="flex items-center gap-2">
                      <span className="bidi-isolate">{opt}</span>
                      <AudioSpeakerButton text={opt} size="sm" />
                    </div>
                    {isSelected && isQuestionAnswered && (
                      <span>
                        {isQuestionCorrect ? (
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

            {isQuestionAnswered && (
              <div
                className={`p-4 rounded-2xl border text-xs leading-relaxed ${
                  isQuestionCorrect
                    ? 'bg-emerald-50 border-emerald-200 text-emerald-950'
                    : 'bg-red-50 border-red-200 text-red-950'
                }`}
              >
                <div className="font-bold mb-1">
                  {isQuestionCorrect ? 'إجابة صحيحة وفقاً لأحداث القصة!' : 'إجابة غير صحيحة.'}
                </div>
                <p>{activeQuestion.explanation_ar}</p>
              </div>
            )}
          </div>
        )}

        {/* Footer Actions */}
        <div className="pt-2">
          {!activeQuestion ? (
            <button
              onClick={handleNextSegment}
              className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] flex items-center justify-center gap-2"
            >
              <span>{currentSegmentIndex < story.segments.length - 1 ? 'الجملة التالية' : 'إتمام القصة'}</span>
              <ArrowRight className="w-4 h-4 rotate-180" />
            </button>
          ) : !isQuestionAnswered ? (
            <button
              disabled={!selectedQuestionOption}
              onClick={handleAnswerQuestion}
              className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] disabled:opacity-40"
            >
              تأكيد الإجابة
            </button>
          ) : (
            <button
              onClick={() => {
                setActiveQuestion(null);
                handleNextSegment();
              }}
              className="w-full py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-md hover:bg-[#153e32] flex items-center justify-center gap-2"
            >
              <span>مواصلة القراءة</span>
              <ArrowRight className="w-4 h-4 rotate-180" />
            </button>
          )}
        </div>
      </main>
    </div>
  );
};
