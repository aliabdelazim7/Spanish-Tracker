import React, { useState, useEffect } from 'react';
import { tutorApi } from '../api/tutor';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import { AudioSpeakerButton } from '../components/AudioSpeakerButton';
import { isSpeechRecognitionSupported } from '../utils/speech';
import {
  Mic,
  MicOff,
  Sparkles,
  CheckCircle2,
  AlertCircle,
  Volume2,
  Send,
  HelpCircle,
  RefreshCw,
  Info,
  Check
} from 'lucide-react';
import confetti from 'canvas-confetti';

export const SpeakingTutorPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'speak' | 'explain' | 'correct'>('speak');

  // Speaking studio state
  const targetPhrases = [
    { es: 'Buenos días, ¿dónde está la estación de metro?', ar: 'صباح الخير، أين تقع محطة المترو؟' },
    { es: 'Un café con leche y una tostada, por favor', ar: 'قهوة بحليب وتوست من فضلك' },
    { es: 'Hola, me llamo Tariq y soy de Egipto', ar: 'مرحباً، اسمي طارق وأنا من مصر' },
    { es: 'Madrid es una ciudad hermosa y acogedora', ar: 'مدريد مدينة جميلة ومضيافة' },
    { es: '¿Cuánto cuesta este billete para Sol?', ar: 'كم سعر هذه التذكرة لمحطة سول؟' },
  ];

  const [selectedTargetIndex, setSelectedTargetIndex] = useState(0);
  const [isRecording, setIsRecording] = useState(false);
  const [spokenTranscript, setSpokenTranscript] = useState('');
  const [isEvaluating, setIsEvaluating] = useState(false);
  const [speakingFeedback, setSpeakingFeedback] = useState<any | null>(null);
  const [micError, setMicError] = useState<string | null>(null);

  // Grammar explainer state
  const [explainConcept, setExplainConcept] = useState('ser vs estar');
  const [isExplaining, setIsExplaining] = useState(false);
  const [explainResult, setExplainResult] = useState<any | null>(null);

  // Text corrector state
  const [userTextToCorrect, setUserTextToCorrect] = useState('Yo soy en Madrid y yo gusto el café');
  const [isCorrecting, setIsCorrecting] = useState(false);
  const [correctResult, setCorrectResult] = useState<any | null>(null);

  // Real Speech Recognition API
  const handleToggleRecord = () => {
    setMicError(null);

    if (isRecording) {
      setIsRecording(false);
      return;
    }

    const SpeechRecognition =
      (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;

    if (!SpeechRecognition) {
      setMicError('متصفحك الحالي لا يدعم التعرف الصوتي المباشر. يرجى استخدام متصفح Chrome أو Edge.');
      return;
    }

    try {
      const recognition = new SpeechRecognition();
      recognition.lang = 'es-ES';
      recognition.continuous = false;
      recognition.interimResults = false;
      recognition.maxAlternatives = 1;

      setIsRecording(true);
      setSpokenTranscript('');
      setSpeakingFeedback(null);

      recognition.onresult = (event: any) => {
        const text = event.results[0][0].transcript;
        setSpokenTranscript(text);
        setIsRecording(false);
        evaluateVoice(text);
      };

      recognition.onerror = (e: any) => {
        setIsRecording(false);
        if (e.error === 'not-allowed') {
          setMicError('تم رفض إذن الميكروفون. يرجى تفعيل إذن الميكروفون من شريط العنوان بالمتصفح.');
        } else if (e.error === 'no-speech') {
          setMicError('لم نتمكن من سماع أي صوت. اضغط على الميكروفون وتحدث بوضوح بالإسبانية.');
        } else {
          setMicError(`خطأ في التعرف الصوتي (${e.error}). تأكد من عمل الميكروفون وأعد المحاولة.`);
        }
      };

      recognition.onend = () => {
        setIsRecording(false);
      };

      recognition.start();
    } catch (err: any) {
      setIsRecording(false);
      setMicError(err.message || 'تعذر تشغيل الميكروفون.');
    }
  };

  const evaluateVoice = async (transcript: string) => {
    if (!transcript || !transcript.trim()) {
      setMicError('لم يتم التقاط كلمات واضحة.');
      return;
    }

    setIsEvaluating(true);
    setSpeakingFeedback(null);
    try {
      const res = await tutorApi.speakingFeedback(
        transcript,
        targetPhrases[selectedTargetIndex].es
      );
      setSpeakingFeedback(res.result);
      if (res.result.passed) {
        confetti({ particleCount: 40, spread: 50, origin: { y: 0.6 } });
      }
    } catch (err) {
      console.error(err);
    } finally {
      setIsEvaluating(false);
    }
  };

  const handleExplain = async () => {
    if (!explainConcept) return;
    setIsExplaining(true);
    setExplainResult(null);
    try {
      const res = await tutorApi.explain(explainConcept);
      setExplainResult(res.result);
    } catch (err) {
      console.error(err);
    } finally {
      setIsExplaining(false);
    }
  };

  const handleCorrect = async () => {
    if (!userTextToCorrect) return;
    setIsCorrecting(true);
    setCorrectResult(null);
    try {
      const res = await tutorApi.correct(userTextToCorrect);
      setCorrectResult(res.result);
    } catch (err) {
      console.error(err);
    } finally {
      setIsCorrecting(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-4xl w-full pb-24 md:pb-8">
          {/* Header */}
          <div className="space-y-1">
            <h1 className="text-3xl font-black text-stone-900">
              معلم المحادثة والذكاء الاصطناعي
            </h1>
            <p className="text-xs sm:text-sm text-stone-600">
              تدرب على التحدث والنطق الصحيح مع تحليل فوري للأخطاء وشرح القواعد بأسلوب مفهوم.
            </p>
          </div>

          {/* Mode Switcher Tabs */}
          <div className="flex items-center gap-2 bg-stone-100 p-1 rounded-2xl w-fit">
            <button
              onClick={() => setActiveTab('speak')}
              className={`px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                activeTab === 'speak' ? 'bg-white text-[#1B4D3E] shadow-2xs' : 'text-stone-600'
              }`}
            >
              استوديو التحدث الصوتي
            </button>
            <button
              onClick={() => setActiveTab('explain')}
              className={`px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                activeTab === 'explain' ? 'bg-white text-[#1B4D3E] shadow-2xs' : 'text-stone-600'
              }`}
            >
              شرح القواعد اللغوية
            </button>
            <button
              onClick={() => setActiveTab('correct')}
              className={`px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                activeTab === 'correct' ? 'bg-white text-[#1B4D3E] shadow-2xs' : 'text-stone-600'
              }`}
            >
              تصحيح الجمل
            </button>
          </div>

          {/* TAB 1: SPEAKING STUDIO */}
          {activeTab === 'speak' && (
            <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs space-y-6">
              <div className="space-y-2 text-right">
                <span className="text-xs font-bold px-3 py-1 rounded-full bg-orange-50 text-orange-800 border border-orange-200">
                  استوديو التحدث ومخارج الحروف
                </span>
                <p className="text-xs text-stone-600 leading-relaxed font-normal">
                  التحدث هو السر اللي بيكسر حاجز الخوف من اللغة. اسمع الجملة براحتك (بالنطق الطبيعي أو المتمهل 🐢)، دوس على المايك وكررها بصوتك... هنسمعك ونراجع مخارج حروفك كلمة كلمة:
                </p>
              </div>

              {/* Target phrase selector */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {targetPhrases.map((tp, idx) => (
                  <button
                    key={idx}
                    onClick={() => {
                      setSelectedTargetIndex(idx);
                      setSpeakingFeedback(null);
                      setSpokenTranscript('');
                      setMicError(null);
                    }}
                    className={`p-4 rounded-2xl border-2 text-right transition-all flex items-center justify-between ${
                      selectedTargetIndex === idx
                        ? 'border-[#1B4D3E] bg-emerald-50/70 shadow-xs'
                        : 'border-stone-200 bg-stone-50/40 hover:border-stone-300'
                    }`}
                  >
                    <div className="space-y-1">
                      <div className="font-bold text-sm text-stone-900 bidi-isolate">{tp.es}</div>
                      <div className="text-xs text-stone-500">{tp.ar}</div>
                    </div>
                    <AudioSpeakerButton text={tp.es} size="sm" />
                  </button>
                ))}
              </div>

              {/* Recording Box */}
              <div className="p-8 rounded-3xl bg-stone-50 border border-stone-200/80 text-center space-y-5">
                <div className="flex items-center justify-center gap-3">
                  <div className="text-2xl font-black text-stone-900 bidi-isolate">
                    "{targetPhrases[selectedTargetIndex].es}"
                  </div>
                  <AudioSpeakerButton
                    text={targetPhrases[selectedTargetIndex].es}
                    size="lg"
                    label="استمع"
                    showSlowButton={true}
                  />
                </div>

                <div className="text-sm font-semibold text-emerald-800">
                  {targetPhrases[selectedTargetIndex].ar}
                </div>

                {/* Audio Caveat or Self-Echo Mode */}
                {!isSpeechRecognitionSupported() ? (
                  <div className="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-3">
                    <div className="font-bold flex items-center justify-center gap-1.5 text-sm">
                      <Info className="w-4 h-4 text-amber-600" />
                      <span>نمط الاستماع والمحاكاة الذاتية (Listen & Echo Mode)</span>
                    </div>
                    <p className="text-stone-600 leading-relaxed font-normal">
                      متصفحك الحالي لا يدعم التعرف الصوتي المباشر. يمكنك ممارسة النطق بفعالية عبر الاستماع للنموذج الأصيل (طبيعي أو بطيء 🐢)، الترديد بصوت مرتفع، ثم الاستماع مجدداً للمقارنة السمعية.
                    </p>
                    <button
                      type="button"
                      onClick={() => {
                        confetti({ particleCount: 30, spread: 50, origin: { y: 0.6 } });
                        alert('ممتاز! الاستمرار في التدريب السمعي والمحاكاة بصوت مرتفع يبني طلاقتك الحقيقية.');
                      }}
                      className="px-5 py-2 rounded-xl bg-emerald-700 text-white font-bold text-xs hover:bg-emerald-800 transition-colors shadow-2xs"
                    >
                      نطقت الجملة بوضوح ومطابقة للنطق الأصيل ✓
                    </button>
                  </div>
                ) : (
                  <div className="p-2.5 rounded-xl bg-stone-100 border border-stone-200 text-[11px] text-stone-500 flex items-center justify-center gap-2">
                    <Info className="w-3.5 h-3.5 text-stone-400 shrink-0" />
                    <span>تنبيه تدريبي: التقييم الآلي يعتمد على مطابقة النصوص المستخرجة ولا يمثل تحليلاً صوتياً معملياً لمخارج الحروف.</span>
                  </div>
                )}

                {/* Error message */}
                {micError && (
                  <div className="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold max-w-md mx-auto flex items-center justify-center gap-2">
                    <AlertCircle className="w-4 h-4 text-red-600 flex-shrink-0" />
                    <span>{micError}</span>
                  </div>
                )}

                {spokenTranscript && (
                  <div className="p-3 bg-white rounded-xl border border-stone-200 text-xs text-stone-700 max-w-md mx-auto">
                    <span className="font-bold text-stone-500">ما تم التقاطه: </span>
                    <span className="font-bold text-stone-900 bidi-isolate">"{spokenTranscript}"</span>
                  </div>
                )}
              </div>

              {/* Feedback Alert with Word-by-Word Analysis */}
              {isEvaluating && (
                <div className="text-center text-xs text-stone-500 font-semibold py-2">
                  جاري تحليل الكلمات ومخارج الحروف...
                </div>
              )}

              {speakingFeedback && (
                <div
                  className={`p-6 rounded-3xl border space-y-4 text-right ${
                    speakingFeedback.passed
                      ? 'bg-emerald-50 border-emerald-300 text-emerald-950'
                      : 'bg-amber-50 border-amber-300 text-amber-950'
                  }`}
                >
                  <div className="flex items-center justify-between">
                    <span className="text-sm font-black px-3 py-1 rounded-full bg-white border border-stone-200">
                      دقة النطق: {speakingFeedback.similarity_score}%
                    </span>
                    <span className="font-bold flex items-center gap-1.5 text-sm">
                      {speakingFeedback.passed ? (
                        <>
                          <CheckCircle2 className="w-5 h-5 text-emerald-600" />
                          <span>نطق ممتاز وصحيح</span>
                        </>
                      ) : (
                        <>
                          <AlertCircle className="w-5 h-5 text-amber-600" />
                          <span>نطق غير مكتمل أو غير دقيق</span>
                        </>
                      )}
                    </span>
                  </div>

                  {/* Word-by-word Highlight Analysis */}
                  {speakingFeedback.words_analysis && speakingFeedback.words_analysis.length > 0 && (
                    <div className="space-y-1.5 pt-2 border-t border-amber-200/50">
                      <div className="text-xs font-bold text-stone-600">تحليل كل كلمة:</div>
                      <div className="flex flex-wrap gap-2 justify-end">
                        {speakingFeedback.words_analysis.map((item: any, i: number) => (
                          <span
                            key={i}
                            className={`px-3 py-1 rounded-xl text-xs font-bold bidi-isolate border ${
                              item.status === 'correct'
                                ? 'bg-emerald-100 text-emerald-900 border-emerald-300'
                                : 'bg-red-100 text-red-900 border-red-300'
                            }`}
                          >
                            {item.word} {item.status === 'correct' ? '✓' : '✗'}
                          </span>
                        ))}
                      </div>
                    </div>
                  )}

                  <p className="text-xs text-stone-700 leading-relaxed font-medium">
                    {speakingFeedback.feedback_ar}
                  </p>
                </div>
              )}
            </div>
          )}

          {/* TAB 2: GRAMMAR EXPLAINER */}
          {activeTab === 'explain' && (
            <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs space-y-6">
              <div className="space-y-1 text-right">
                <h3 className="font-bold text-stone-900 text-base">
                  اسأل في أي قاعدة إسبانية ملخبطاك
                </h3>
                <p className="text-xs text-stone-500">
                  شرح بالبلدي وبأمثلة من مواقف الحياة اليومية عشان تفهم أصل القاعدة من غير حفظ أعمى:
                </p>
              </div>

              {/* Quick suggestions */}
              <div className="flex flex-wrap gap-2">
                {['ser vs estar', 'verbo gustar', 'المذكر والمؤنث', 'الأفعال الانعكاسية'].map((tag) => (
                  <button
                    key={tag}
                    onClick={() => setExplainConcept(tag)}
                    className="px-3 py-1.5 rounded-xl bg-stone-100 hover:bg-emerald-50 text-stone-700 hover:text-emerald-900 text-xs font-semibold transition-colors"
                  >
                    {tag}
                  </button>
                ))}
              </div>

              <div className="flex gap-2">
                <input
                  type="text"
                  value={explainConcept}
                  onChange={(e) => setExplainConcept(e.target.value)}
                  placeholder="مثال: الفرق بين ser و estar..."
                  className="flex-1 px-4 py-3 rounded-xl border border-stone-300 text-sm focus:border-[#1B4D3E] outline-none"
                />
                <button
                  onClick={handleExplain}
                  disabled={isExplaining}
                  className="px-6 py-3 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs shadow-xs hover:bg-[#153e32] disabled:opacity-50"
                >
                  {isExplaining ? 'جاري الشرح...' : 'اشرح لي'}
                </button>
              </div>

              {explainResult && (
                <div className="p-6 rounded-3xl bg-amber-50/70 border border-amber-200/80 text-right space-y-4">
                  <div className="flex items-center gap-2 text-[#1B4D3E] font-bold">
                    <Sparkles className="w-5 h-5 text-amber-600" />
                    <span>{explainResult.concept}</span>
                  </div>

                  <p className="text-sm text-stone-800 leading-relaxed font-normal">
                    {explainResult.explanation_ar}
                  </p>

                  {explainResult.examples && (
                    <div className="space-y-2 pt-2 border-t border-amber-200/60">
                      <div className="text-xs font-bold text-stone-500">أمثلة توضيحية:</div>
                      {explainResult.examples.map((ex: any, i: number) => (
                        <div key={i} className="p-3 rounded-xl bg-white/80 border border-amber-100 text-xs flex items-center justify-between">
                          <div className="space-y-0.5 text-right">
                            <div className="font-bold text-stone-900 bidi-isolate">{ex.es}</div>
                            <div className="text-stone-500">{ex.ar}</div>
                          </div>
                          <AudioSpeakerButton text={ex.es} size="sm" />
                        </div>
                      ))}
                    </div>
                  )}

                  {explainResult.common_mistakes_ar && (
                    <div className="p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-900">
                      <span className="font-bold">أخطاء يقع فيها المبتدئون:</span> {explainResult.common_mistakes_ar}
                    </div>
                  )}
                </div>
              )}
            </div>
          )}

          {/* TAB 3: TEXT CORRECTOR */}
          {activeTab === 'correct' && (
            <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs space-y-6">
              <div className="space-y-1 text-right">
                <h3 className="font-bold text-stone-900 text-base">
                  شاكك في جملة وبتسأل: "هل دي تتقال كده؟"
                </h3>
                <p className="text-xs text-stone-500">
                  اكتب أي جملة بالإسبانية، وهنقولك لو فيها غلطة ونقترح عليك الصياغة الأقرب للسان الإسباني الطبيعي:
                </p>
              </div>

              <div className="space-y-3">
                <textarea
                  rows={3}
                  value={userTextToCorrect}
                  onChange={(e) => setUserTextToCorrect(e.target.value)}
                  dir="ltr"
                  className="w-full p-4 rounded-2xl border border-stone-300 text-base font-semibold focus:border-[#1B4D3E] outline-none"
                />

                <button
                  onClick={handleCorrect}
                  disabled={isCorrecting}
                  className="px-6 py-3 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs shadow-xs hover:bg-[#153e32] disabled:opacity-50"
                >
                  {isCorrecting ? 'جاري الفحص اللغوي...' : 'تصحيح الجملة'}
                </button>
              </div>

              {correctResult && (
                <div className="p-6 rounded-3xl bg-stone-50 border border-stone-200 text-right space-y-4">
                  <div className="flex items-center justify-between">
                    <span className="text-xs text-stone-400">تحليل الصياغة</span>
                    <span
                      className={`text-xs font-bold px-3 py-1 rounded-full ${
                        correctResult.is_grammatically_correct
                          ? 'bg-emerald-100 text-emerald-800'
                          : 'bg-amber-100 text-amber-800'
                      }`}
                    >
                      {correctResult.is_grammatically_correct ? 'صحيحة لغوياً' : 'تتطلب تصويباً'}
                    </span>
                  </div>

                  <div className="space-y-1">
                    <div className="text-xs font-bold text-stone-400">الصياغة الطبيعية الموصى بها:</div>
                    <div className="flex items-center justify-between bg-white p-3 rounded-xl border border-stone-200">
                      <div className="text-lg font-bold text-emerald-800 bidi-isolate">
                        "{correctResult.corrected_text}"
                      </div>
                      <AudioSpeakerButton text={correctResult.corrected_text} size="sm" />
                    </div>
                  </div>

                  <p className="text-xs text-stone-700 leading-relaxed">
                    {correctResult.explanation_ar}
                  </p>

                  {correctResult.errors && correctResult.errors.length > 0 && (
                    <div className="space-y-2 pt-2 border-t border-stone-200">
                      <div className="text-xs font-bold text-stone-500">تفاصيل الملاحظات القواعدية:</div>
                      {correctResult.errors.map((err: any, i: number) => (
                        <div key={i} className="p-3 rounded-xl bg-white border border-stone-200 text-xs space-y-1">
                          <div className="text-red-600 line-through bidi-isolate">{err.original}</div>
                          <div className="text-emerald-700 font-bold bidi-isolate">{err.correction}</div>
                          <div className="text-stone-500 text-[11px]">{err.reason_ar}</div>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              )}
            </div>
          )}
        </main>
      </div>
    </div>
  );
};
