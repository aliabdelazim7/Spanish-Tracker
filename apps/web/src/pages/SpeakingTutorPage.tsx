import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
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
  Check,
  MessageSquare,
  ArrowRight,
  ArrowLeft,
  Award,
  ChevronRight,
  User,
  Bot,
  Lightbulb,
  Crown
} from 'lucide-react';
import confetti from 'canvas-confetti';

interface RoleplayTurnHistory {
  character_text_es: string;
  character_text_ar: string;
  user_input: string;
  score: number;
  feedback_ar: string;
  is_passed: boolean;
}

export const SpeakingTutorPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'roleplays' | 'speak' | 'explain' | 'correct'>('roleplays');

  // Roleplay simulator state
  const [scenarios, setScenarios] = useState<any[]>([]);
  const [isLoadingScenarios, setIsLoadingScenarios] = useState(false);
  const [selectedScenario, setSelectedScenario] = useState<any | null>(null);
  const [currentTurnIndex, setCurrentTurnIndex] = useState(0);
  const [roleplayInput, setRoleplayInput] = useState('');
  const [isSubmittingTurn, setIsSubmittingTurn] = useState(false);
  const [turnFeedback, setTurnFeedback] = useState<any | null>(null);
  const [conversationHistory, setConversationHistory] = useState<RoleplayTurnHistory[]>([]);
  const [showHint, setShowHint] = useState(false);
  const [scenarioCompleted, setScenarioCompleted] = useState(false);
  const [totalXpEarned, setTotalXpEarned] = useState(0);

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

  useEffect(() => {
    loadRoleplayScenarios();
  }, []);

  const loadRoleplayScenarios = async () => {
    setIsLoadingScenarios(true);
    try {
      const res = await tutorApi.getRoleplays();
      setScenarios(res.scenarios || []);
    } catch (err) {
      console.error('Failed to load roleplay scenarios:', err);
    } finally {
      setIsLoadingScenarios(false);
    }
  };

  const startScenario = (sc: any) => {
    setSelectedScenario(sc);
    setCurrentTurnIndex(0);
    setRoleplayInput('');
    setTurnFeedback(null);
    setConversationHistory([]);
    setShowHint(false);
    setScenarioCompleted(false);
    setTotalXpEarned(0);
  };

  const handleSelectSuggestedOption = (option: string) => {
    setRoleplayInput(option);
  };

  const handleSubmitRoleplayTurn = async () => {
    if (!roleplayInput.trim() || !selectedScenario) return;

    setIsSubmittingTurn(true);
    setTurnFeedback(null);

    try {
      const res = await tutorApi.replyRoleplay({
        scenario_id: selectedScenario.id,
        turn_index: currentTurnIndex,
        user_input: roleplayInput.trim(),
      });

      const currentTurn = selectedScenario.turns[currentTurnIndex];

      const historyEntry: RoleplayTurnHistory = {
        character_text_es: currentTurn?.character_text_es || '',
        character_text_ar: currentTurn?.character_text_ar || '',
        user_input: roleplayInput.trim(),
        score: res.score,
        feedback_ar: res.feedback_ar,
        is_passed: res.is_passed,
      };

      setConversationHistory((prev) => [...prev, historyEntry]);
      setTurnFeedback(res);
      setTotalXpEarned((prev) => prev + (res.xp_awarded || 0));

      if (res.scenario_completed) {
        setScenarioCompleted(true);
        confetti({ particleCount: 70, spread: 80, origin: { y: 0.6 } });
      } else if (res.next_turn_index !== null) {
        // Move to next turn after small delay
        setTimeout(() => {
          setCurrentTurnIndex(res.next_turn_index!);
          setRoleplayInput('');
          setTurnFeedback(null);
          setShowHint(false);
        }, 1800);
      }
    } catch (err: any) {
      alert(err.message || 'حدث خطأ أثناء معالجة المحادثة');
    } finally {
      setIsSubmittingTurn(false);
    }
  };

  // Real Speech Recognition API for roleplay or target phrase
  const handleToggleRecordRoleplay = () => {
    setMicError(null);

    if (isRecording) {
      setIsRecording(false);
      return;
    }

    const SpeechRecognition =
      (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;

    if (!SpeechRecognition) {
      setMicError('متصفحك لا يدعم التعرف الصوتي المباشر. يرجى استخدام متصفح Chrome أو كتابة الجملة.');
      return;
    }

    try {
      const recognition = new SpeechRecognition();
      recognition.lang = 'es-ES';
      recognition.continuous = false;
      recognition.interimResults = false;
      recognition.maxAlternatives = 1;

      setIsRecording(true);

      recognition.onresult = (event: any) => {
        const text = event.results[0][0].transcript;
        setRoleplayInput(text);
        setIsRecording(false);
      };

      recognition.onerror = (e: any) => {
        setIsRecording(false);
        if (e.error === 'not-allowed') {
          setMicError('تم رفض إذن الميكروفون. يرجى تفعيله من إعدادات المتصفح.');
        } else if (e.error === 'no-speech') {
          setMicError('لم نتمكن من سماع صوتك. تحدث بوضوح بالإسبانية.');
        } else {
          setMicError(`خطأ في التسجيل (${e.error}).`);
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

  // Real Speech Recognition API for speaking studio
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
          <div className="flex flex-wrap items-center gap-2 bg-stone-100 p-1 rounded-2xl w-fit">
            <button
              onClick={() => setActiveTab('roleplays')}
              className={`px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 ${
                activeTab === 'roleplays' ? 'bg-white text-[#1B4D3E] shadow-2xs' : 'text-stone-600'
              }`}
            >
              <span>🎭 محاكي المواقف الحية</span>
              <span className="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded-full font-black">جديد</span>
            </button>
            <button
              onClick={() => setActiveTab('speak')}
              className={`px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                activeTab === 'speak' ? 'bg-white text-[#1B4D3E] shadow-2xs' : 'text-stone-600'
              }`}
            >
              استوديو التحدث ومخارج الحروف
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

          {/* TAB 0: ROLEPLAY SCENARIOS */}
          {activeTab === 'roleplays' && (
            <div className="space-y-6">
              {!selectedScenario ? (
                /* Scenario Selection Grid */
                <div className="space-y-4">
                  <div className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-xs space-y-2 text-right">
                    <div className="flex items-center justify-between">
                      <span className="text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                        مواقف حية بالذكاء الاصطناعي
                      </span>
                      <span className="text-xs text-stone-500 font-medium">5 مواقف تفاعلية متوفرة</span>
                    </div>
                    <h2 className="text-xl font-black text-stone-900">
                      محاكي المواقف الإسبانية الواقعية
                    </h2>
                    <p className="text-xs text-stone-600 leading-relaxed font-normal">
                      لا تكتفِ بحفظ القواعد مجردة؛ ضع نفسك في مواقف حقيقية في شوارع مدريد وإشبيلية! تحدث مع النادل، موظف المطار، وسائق التاكسي بالصوت أو النص، وتلقَّ توجيهاً فورياً لكل رد تقوله.
                    </p>
                  </div>

                  {/* Pro SaaS Promotion Banner */}
                  <div className="p-4 rounded-2xl bg-gradient-to-r from-amber-500/10 via-emerald-500/10 to-amber-500/10 border border-amber-300 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-right">
                    <div className="flex items-center gap-2.5">
                      <div className="w-9 h-9 rounded-xl bg-amber-400 text-stone-950 flex items-center justify-center shrink-0">
                        <Crown className="w-5 h-5 fill-stone-950" />
                      </div>
                      <div className="space-y-0.5">
                        <div className="text-xs font-black text-stone-900">ترقية Pro: محاكي مواقف غير محدود وتأهيل وظيفي</div>
                        <div className="text-[11px] text-stone-500">افتح كافة سيناريوهات BPO والسياحة القادمة مع تقييم نطق قشتالي متقدم</div>
                      </div>
                    </div>
                    <Link
                      to="/pricing"
                      className="px-4 py-2 rounded-xl bg-[#1B4D3E] hover:bg-[#153e32] text-white font-bold text-xs shrink-0 shadow-2xs transition-colors"
                    >
                      عرض باقات Pro
                    </Link>
                  </div>

                  {isLoadingScenarios ? (
                    <div className="p-12 text-center text-stone-500 font-semibold text-xs">
                      جاري تحميل السيناريوهات التفاعلية...
                    </div>
                  ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      {scenarios.map((sc) => (
                        <div
                          key={sc.id}
                          className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-xs hover:border-[#1B4D3E] transition-all flex flex-col justify-between space-y-4 text-right"
                        >
                          <div className="space-y-3">
                            <div className="flex items-center justify-between">
                              <span className="text-3xl">{sc.emoji}</span>
                              <div className="flex items-center gap-1.5">
                                <span className="text-xs font-bold px-2.5 py-0.5 rounded-full bg-stone-100 text-stone-700">
                                  {sc.cefr_level}
                                </span>
                                <span className="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                                  +{sc.xp_reward} XP
                                </span>
                              </div>
                            </div>

                            <div className="space-y-1">
                              <h3 className="font-bold text-base text-stone-900">{sc.title_ar}</h3>
                              <div className="text-xs font-semibold text-[#1B4D3E] bidi-isolate">{sc.title_es}</div>
                            </div>

                            <p className="text-xs text-stone-600 leading-relaxed font-normal">
                              {sc.description_ar}
                            </p>

                            <div className="flex items-center gap-2 text-[11px] text-stone-500 pt-1">
                              <span>المتحدث: <strong className="text-stone-800">{sc.character_name}</strong></span>
                              <span>•</span>
                              <span>المكان: <strong className="text-stone-800">{sc.location}</strong></span>
                              <span>•</span>
                              <span>{sc.total_turns} أدوار</span>
                            </div>
                          </div>

                          <button
                            onClick={() => startScenario(sc)}
                            className="w-full py-3 rounded-2xl bg-[#1B4D3E] text-white font-bold text-xs shadow-xs hover:bg-[#153e32] flex items-center justify-center gap-1.5 transition-all"
                          >
                            <span>ابدأ هذا الموقف الآن</span>
                            <ArrowLeft className="w-4 h-4" />
                          </button>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              ) : (
                /* Active Scenario Conversation View */
                <div className="space-y-5">
                  {/* Top Bar Navigation */}
                  <div className="flex items-center justify-between bg-white rounded-2xl p-4 border border-stone-200 shadow-2xs">
                    <button
                      onClick={() => setSelectedScenario(null)}
                      className="flex items-center gap-1.5 text-xs font-bold text-stone-600 hover:text-stone-900"
                    >
                      <ArrowRight className="w-4 h-4" />
                      <span>جميع السيناريوهات</span>
                    </button>

                    <div className="flex items-center gap-3">
                      <span className="text-2xl">{selectedScenario.emoji}</span>
                      <div className="text-right">
                        <div className="text-xs font-bold text-stone-900">{selectedScenario.title_ar}</div>
                        <div className="text-[11px] text-stone-500">
                          {selectedScenario.character_name} ({selectedScenario.location})
                        </div>
                      </div>
                    </div>

                    <div className="flex items-center gap-2">
                      <span className="text-xs font-bold px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                        الدور {scenarioCompleted ? selectedScenario.total_turns : currentTurnIndex + 1} من {selectedScenario.total_turns}
                      </span>
                      {totalXpEarned > 0 && (
                        <span className="text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1">
                          <Sparkles className="w-3.5 h-3.5" />
                          <span>+{totalXpEarned} XP</span>
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Conversation Chat Stream */}
                  <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200 shadow-xs space-y-6">
                    {/* Previous Turns History */}
                    {conversationHistory.map((turn, i) => (
                      <div key={i} className="space-y-4 pb-4 border-b border-stone-100">
                        {/* Character dialogue */}
                        <div className="flex items-start gap-3">
                          <div className="w-10 h-10 rounded-2xl bg-amber-100 text-amber-900 font-bold flex items-center justify-center shrink-0 text-base">
                            {selectedScenario.character_name.charAt(0)}
                          </div>
                          <div className="flex-1 space-y-1.5 text-right">
                            <div className="flex items-center justify-between">
                              <span className="text-xs font-bold text-stone-800">
                                {selectedScenario.character_name}
                              </span>
                              <AudioSpeakerButton text={turn.character_text_es} size="sm" />
                            </div>
                            <div className="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 text-sm font-semibold text-stone-900 bidi-isolate text-left">
                              {turn.character_text_es}
                            </div>
                            <div className="text-xs text-stone-500 font-normal">
                              {turn.character_text_ar}
                            </div>
                          </div>
                        </div>

                        {/* User response */}
                        <div className="flex items-start gap-3 flex-row-reverse">
                          <div className="w-10 h-10 rounded-2xl bg-emerald-600 text-white font-bold flex items-center justify-center shrink-0 text-sm">
                            أنت
                          </div>
                          <div className="flex-1 space-y-1.5 text-left">
                            <div className="flex items-center justify-between flex-row-reverse">
                              <span className="text-xs font-bold text-stone-800">إجابتك</span>
                              <span
                                className={`text-[11px] font-bold px-2 py-0.5 rounded-full ${
                                  turn.is_passed
                                    ? 'bg-emerald-100 text-emerald-800'
                                    : 'bg-amber-100 text-amber-800'
                                }`}
                              >
                                دقة الرد: {turn.score}% {turn.is_passed ? '✓' : '✗'}
                              </span>
                            </div>
                            <div className="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-sm font-semibold text-emerald-950 bidi-isolate">
                              {turn.user_input}
                            </div>
                            <div className="text-xs text-stone-600 text-right font-medium">
                              {turn.feedback_ar}
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}

                    {/* Current Turn Active Prompts */}
                    {!scenarioCompleted && selectedScenario.turns[currentTurnIndex] && (
                      <div className="space-y-5 pt-2">
                        {/* Character says */}
                        <div className="flex items-start gap-3">
                          <div className="w-10 h-10 rounded-2xl bg-amber-500 text-white font-bold flex items-center justify-center shrink-0 text-base shadow-xs">
                            {selectedScenario.character_name.charAt(0)}
                          </div>
                          <div className="flex-1 space-y-2 text-right">
                            <div className="flex items-center justify-between">
                              <span className="text-xs font-bold text-stone-800">
                                {selectedScenario.character_name} يوجه لك الحديث:
                              </span>
                              <AudioSpeakerButton
                                text={selectedScenario.turns[currentTurnIndex].character_text_es}
                                size="sm"
                              />
                            </div>
                            <div className="p-4 rounded-2xl bg-stone-100/90 border border-stone-200 text-base font-bold text-stone-900 bidi-isolate text-left">
                              {selectedScenario.turns[currentTurnIndex].character_text_es}
                            </div>
                            <div className="text-xs text-stone-500">
                              {selectedScenario.turns[currentTurnIndex].character_text_ar}
                            </div>
                          </div>
                        </div>

                        {/* Task guidance for learner */}
                        <div className="p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-right space-y-1">
                          <div className="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                            <Lightbulb className="w-4 h-4 text-amber-600" />
                            <span>المطلوب منك في هذا الدور:</span>
                          </div>
                          <p className="text-xs text-stone-700 font-medium">
                            {selectedScenario.turns[currentTurnIndex].prompt_ar}
                          </p>
                        </div>

                        {/* Suggested Options Chips */}
                        {selectedScenario.turns[currentTurnIndex].suggested_options && (
                          <div className="space-y-1.5 text-right">
                            <div className="text-[11px] font-bold text-stone-500">
                              صيغ مقترحة يمكنك استخدامها أو الاستلهام منها:
                            </div>
                            <div className="flex flex-wrap gap-2 justify-end">
                              {selectedScenario.turns[currentTurnIndex].suggested_options.map(
                                (opt: string, idx: number) => (
                                  <button
                                    key={idx}
                                    type="button"
                                    onClick={() => handleSelectSuggestedOption(opt)}
                                    className="px-3 py-1.5 rounded-xl bg-stone-100 hover:bg-emerald-50 text-stone-700 hover:text-emerald-900 border border-stone-200/80 hover:border-emerald-300 text-xs font-medium bidi-isolate transition-all"
                                  >
                                    "{opt}"
                                  </button>
                                )
                              )}
                            </div>
                          </div>
                        )}

                        {/* User Input & Audio Recorder */}
                        <div className="space-y-3 pt-2">
                          <div className="relative">
                            <textarea
                              rows={3}
                              dir="ltr"
                              value={roleplayInput}
                              onChange={(e) => setRoleplayInput(e.target.value)}
                              placeholder="Escribe tu respuesta en español..."
                              className="w-full p-4 rounded-2xl border-2 border-stone-200 focus:border-[#1B4D3E] outline-none text-base font-semibold text-stone-900"
                            />
                            {isRecording && (
                              <div className="absolute top-3 right-3 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold animate-pulse">
                                <span className="w-2 h-2 rounded-full bg-red-600"></span>
                                <span>جاري الاستماع...</span>
                              </div>
                            )}
                          </div>

                          {micError && (
                            <div className="p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium flex items-center gap-2">
                              <AlertCircle className="w-4 h-4 shrink-0 text-red-600" />
                              <span>{micError}</span>
                            </div>
                          )}

                          {/* Action Buttons Row */}
                          <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="flex items-center gap-2">
                              <button
                                type="button"
                                onClick={handleToggleRecordRoleplay}
                                className={`px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 transition-all ${
                                  isRecording
                                    ? 'bg-red-600 text-white animate-pulse'
                                    : 'bg-stone-100 hover:bg-stone-200 text-stone-700'
                                }`}
                              >
                                {isRecording ? <MicOff className="w-4 h-4" /> : <Mic className="w-4 h-4 text-emerald-700" />}
                                <span>{isRecording ? 'إيقاف التسجيل' : 'تحدث بالمايك (إسباني)'}</span>
                              </button>

                              <button
                                type="button"
                                onClick={() => setShowHint(!showHint)}
                                className="px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-50 font-bold text-xs flex items-center gap-1.5 transition-all"
                              >
                                <HelpCircle className="w-4 h-4 text-amber-500" />
                                <span>{showHint ? 'إخفاء التلميح' : 'تلميح'}</span>
                              </button>
                            </div>

                            <button
                              type="button"
                              onClick={handleSubmitRoleplayTurn}
                              disabled={isSubmittingTurn || !roleplayInput.trim()}
                              className="px-6 py-2.5 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs shadow-xs hover:bg-[#153e32] disabled:opacity-50 flex items-center gap-2 transition-all"
                            >
                              <span>{isSubmittingTurn ? 'جاري الفحص والرد...' : 'إرسال الرد'}</span>
                              <Send className="w-3.5 h-3.5 rotate-180" />
                            </button>
                          </div>

                          {/* Optional Hint Banner */}
                          {showHint && (
                            <div className="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs text-right space-y-1">
                              <div className="font-bold">💡 تلميح للمساعدة:</div>
                              <div>{selectedScenario.turns[currentTurnIndex].hint_ar}</div>
                            </div>
                          )}

                          {/* Temporary Turn Feedback Alert before next turn */}
                          {turnFeedback && (
                            <div
                              className={`p-4 rounded-2xl border text-right space-y-2 ${
                                turnFeedback.is_passed
                                  ? 'bg-emerald-50 border-emerald-300 text-emerald-950'
                                  : 'bg-amber-50 border-amber-300 text-amber-950'
                              }`}
                            >
                              <div className="flex items-center justify-between">
                                <span className="text-xs font-bold px-2.5 py-0.5 rounded-full bg-white border border-stone-200">
                                  درجة الرد: {turnFeedback.score}%
                                </span>
                                <span className="font-bold text-xs flex items-center gap-1">
                                  {turnFeedback.is_passed ? (
                                    <>
                                      <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                                      <span>رد ممتاز ومفهوم! الانتقال للدور التالي...</span>
                                    </>
                                  ) : (
                                    <>
                                      <AlertCircle className="w-4 h-4 text-amber-600" />
                                      <span>رد يحتاج لمزيد من الدقة</span>
                                    </>
                                  )}
                                </span>
                              </div>
                              <p className="text-xs leading-relaxed">{turnFeedback.feedback_ar}</p>
                            </div>
                          )}
                        </div>
                      </div>
                    )}

                    {/* Scenario Completed Celebration Card */}
                    {scenarioCompleted && (
                      <div className="p-8 rounded-3xl bg-emerald-50 border-2 border-emerald-300 text-center space-y-5">
                        <div className="w-16 h-16 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto shadow-md">
                          <Award className="w-8 h-8" />
                        </div>

                        <div className="space-y-1">
                          <h3 className="text-2xl font-black text-emerald-950">
                            ¡Enhorabuena! أتممت الموقف بنجاح!
                          </h3>
                          <p className="text-xs text-emerald-800">
                            أنهيت كافة أدوار الحوار في "{selectedScenario.title_ar}" بنجاح وتجاوزت حاجز التحدث في هذا الموقف.
                          </p>
                        </div>

                        <div className="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-emerald-200 shadow-2xs font-black text-emerald-900 text-sm">
                          <Sparkles className="w-5 h-5 text-amber-500" />
                          <span>حصلت على +{selectedScenario.xp_reward} XP مكافأة إتمام</span>
                        </div>

                        <div className="flex flex-wrap items-center justify-center gap-3 pt-3">
                          <button
                            type="button"
                            onClick={() => startScenario(selectedScenario)}
                            className="px-5 py-2.5 rounded-xl border border-emerald-600 text-emerald-800 font-bold text-xs hover:bg-emerald-100 transition-colors"
                          >
                            إعادة هذا الموقف للتدرب مجدداً
                          </button>
                          <button
                            type="button"
                            onClick={() => setSelectedScenario(null)}
                            className="px-6 py-2.5 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs shadow-xs hover:bg-[#153e32] transition-colors"
                          >
                            اختيار موقف آخر من المواقف الخمسة
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              )}
            </div>
          )}

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
