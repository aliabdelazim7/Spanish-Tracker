import React, { useState, useRef } from 'react';
import { Link } from 'react-router-dom';
import {
  Compass,
  Sparkles,
  BookOpen,
  Volume2,
  Mic,
  PenTool,
  CheckCircle,
  HelpCircle,
  ArrowRight,
  Flame,
  Award,
  Globe,
  RotateCcw,
  Play,
  Pause
} from 'lucide-react';
import { Navbar } from '../components/Navbar';
import { Footer } from '../components/Footer';
import { AudioSpeakerButton } from '../components/AudioSpeakerButton';

export const LandingPage: React.FC = () => {
  const [isPlayingTutorVoice, setIsPlayingTutorVoice] = useState(false);
  const tutorAudioRef = useRef<HTMLAudioElement | null>(null);

  const toggleTutorVoice = () => {
    if (!tutorAudioRef.current) {
      tutorAudioRef.current = new Audio('/audio/tutor-voice-sample.mp3');
      tutorAudioRef.current.onended = () => setIsPlayingTutorVoice(false);
      tutorAudioRef.current.onerror = () => setIsPlayingTutorVoice(false);
    }

    if (isPlayingTutorVoice) {
      tutorAudioRef.current.pause();
      setIsPlayingTutorVoice(false);
    } else {
      tutorAudioRef.current.play().catch(() => setIsPlayingTutorVoice(false));
      setIsPlayingTutorVoice(true);
    }
  };
  return (
    <div className="min-h-screen bg-[#FAF8F5] text-stone-900 flex flex-col selection:bg-emerald-200">
      <Navbar />

      {/* Hero Section */}
      <section className="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-28">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            {/* Right Column: Clean, Conversational, Human Egyptian Copy */}
            <div className="lg:col-span-7 space-y-6 text-center lg:text-right">
              <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100/80 border border-emerald-300/60 text-emerald-900 text-xs sm:text-sm font-bold shadow-2xs">
                <Sparkles className="w-4 h-4 text-emerald-700" />
                <span>مش مجرد حفظ كلمات... رفيقك الحقيقي لإتقان الإسبانية</span>
              </div>

              <h1 className="text-4xl sm:text-5xl lg:text-6xl font-black text-[#1B4D3E] tracking-tight leading-tight sm:leading-tight">
                بتسمع إسباني بس لسانك بيربط؟ <br />
                <span className="text-[#E76F51]">اتعلم تتكلم وتفهم</span> بطبيعية من أول يوم
              </h1>

              <p className="text-lg sm:text-xl text-stone-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                المشكلة مش فيك... المشكلة إن أغلب الطرق بتعلمك قوائم كلمات جافة من غير ما تفهم لسانك بينطق إزاي. بنشرحلك القواعد بنبرة بشرية هادية ومبسطة، وبندرب ودانك على النطق الحقيقي الأصيل.
              </p>

              {/* CTAs */}
              <div className="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                <Link
                  to="/register"
                  className="w-full sm:w-auto px-8 py-4 rounded-2xl bg-[#1B4D3E] text-white font-bold text-base shadow-lg shadow-emerald-950/20 hover:bg-[#153e32] hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                >
                  <span>ابدأ رحلتك مجاناً</span>
                  <ArrowRight className="w-5 h-5 rotate-180" />
                </Link>

                <Link
                  to="/placement-test"
                  className="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white border border-stone-200 text-[#1B4D3E] font-bold text-base hover:bg-stone-50 hover:border-emerald-300 shadow-2xs transition-all flex items-center justify-center gap-2"
                >
                  <span>قس مستواك الحقيقي (3 دقائق)</span>
                </Link>
              </div>

              {/* Trust badges */}
              <div className="pt-4 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-stone-500 font-semibold">
                <div className="flex items-center gap-1.5">
                  <CheckCircle className="w-4 h-4 text-emerald-600" />
                  <span>معايير الإتقان الأوروبية CEFR</span>
                </div>
                <div className="flex items-center gap-1.5">
                  <CheckCircle className="w-4 h-4 text-emerald-600" />
                  <span>نطق إسباني أصيل مسموع لكل كلمة</span>
                </div>
                <div className="flex items-center gap-1.5">
                  <CheckCircle className="w-4 h-4 text-emerald-600" />
                  <span>شرح مصري رايق بدون روبوتات</span>
                </div>
              </div>
            </div>

            {/* Left Column: Interactive Card Preview with Audio */}
            <div className="lg:col-span-5 relative">
              <div className="w-full max-w-md mx-auto bg-white rounded-3xl p-6 shadow-xl border border-stone-200/80 space-y-5 relative">
                <div className="flex items-center justify-between border-b border-stone-100 pb-4">
                  <div className="flex items-center gap-2">
                    <span className="w-3 h-3 rounded-full bg-red-400"></span>
                    <span className="w-3 h-3 rounded-full bg-amber-400"></span>
                    <span className="w-3 h-3 rounded-full bg-emerald-400"></span>
                  </div>
                  <span className="text-xs font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                    مستوى A1 - تجربة عملية
                  </span>
                </div>

                {/* Sample Exercise Preview with Audio */}
                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <p className="text-xs font-semibold text-stone-400">استمع وتدرب:</p>
                    <span className="text-xs text-emerald-700 font-bold">اضغط للاستماع للصوت</span>
                  </div>

                  <div className="p-4 rounded-2xl bg-stone-50 border border-stone-200 text-stone-800 font-bold text-lg flex items-center justify-between">
                    <span className="bidi-isolate">"Un café con leche, por favor"</span>
                    <AudioSpeakerButton text="Un café con leche, por favor" size="md" />
                  </div>

                  <p className="text-xs font-semibold text-stone-500">المعنى الصحيح باللغة العربية:</p>
                  <div className="space-y-2">
                    <div className="p-3 rounded-xl border-2 border-emerald-500 bg-emerald-50/70 text-emerald-950 font-bold text-sm flex items-center justify-between">
                      <span>قهوة بالحليب من فضلك</span>
                      <CheckCircle className="w-4 h-4 text-emerald-600" />
                    </div>
                    <div className="p-3 rounded-xl border border-stone-200 bg-white text-stone-600 text-sm opacity-60">
                      شاي بالليمون من فضلك
                    </div>
                  </div>
                </div>

                {/* Tutor Voice Sample Card - Human & Warm */}
                <div className="p-4 rounded-2xl bg-emerald-950 text-white shadow-sm space-y-2.5 text-right">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <div className="w-8 h-8 rounded-full bg-emerald-800 flex items-center justify-center text-amber-300">
                        <Sparkles className="w-4 h-4" />
                      </div>
                      <div className="text-right">
                        <div className="text-xs font-bold text-white">نبرة الشرح والتوجيه</div>
                        <div className="text-[10px] text-emerald-300">صوت بشري دافئ ومريح للأذن</div>
                      </div>
                    </div>
                    <button
                      type="button"
                      onClick={toggleTutorVoice}
                      className="px-3 py-1.5 rounded-xl bg-amber-400 text-stone-950 font-bold text-xs hover:bg-amber-300 transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer"
                    >
                      {isPlayingTutorVoice ? <Pause className="w-3.5 h-3.5" /> : <Play className="w-3.5 h-3.5" />}
                      <span>{isPlayingTutorVoice ? 'إيقاف' : 'استمع للنبرة'}</span>
                    </button>
                  </div>
                  <p className="text-[11px] text-emerald-100/80 leading-relaxed font-normal">
                    شرح هادي ومبسط كأنك قاعد مع صاحبك، بعيد تماماً عن كلام الروبوتات والذكاء الاصطناعي الجاف.
                  </p>
                </div>

                {/* Pronunciation tip */}
                <div className="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-950 space-y-1">
                  <div className="font-bold flex items-center gap-1.5 text-amber-900">
                    <Sparkles className="w-3.5 h-3.5 text-amber-600" />
                    <span>فائدة في النطق:</span>
                  </div>
                  <p className="text-stone-700 leading-relaxed">
                    حرف <span className="font-bold">"c"</span> إذا تبعه e أو i يُنطق كالثاء: <span className="font-bold">gracias / con leche</span>.
                  </p>
                </div>

                {/* Footer stats */}
                <div className="pt-1 flex items-center justify-between text-xs text-stone-500 font-medium">
                  <span className="flex items-center gap-1 text-orange-600 font-bold">
                    <Flame className="w-4 h-4 fill-orange-500" />
                    سلسلة التزام: 3 أيام
                  </span>
                  <span className="flex items-center gap-1 text-amber-600 font-bold">
                    +20 XP مكافأة الدرس
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 4 Core Skills Section */}
      <section className="py-16 bg-white border-y border-stone-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-3xl mx-auto mb-14 space-y-3">
            <h2 className="text-3xl font-bold text-[#1B4D3E]">
              تطوير متكامل للمهارات اللغوية الأربع
            </h2>
            <p className="text-stone-600 text-base leading-relaxed">
              خطة تعليمية متوازنة تضمن قدرتك على التحدث والفهم والاستخدام اليومي:
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div className="p-6 rounded-2xl bg-[#FAF8F5] border border-stone-200 hover:border-emerald-300 transition-all space-y-3">
              <div className="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
                <BookOpen className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-lg text-stone-900">1. القراءة وفهم النصوص</h3>
              <p className="text-sm text-stone-600 leading-relaxed">
                قصص ونصوص يومية مع نطق صوتي فوري وترجمة عند النقر على أي كلمة.
              </p>
            </div>

            <div className="p-6 rounded-2xl bg-[#FAF8F5] border border-stone-200 hover:border-emerald-300 transition-all space-y-3">
              <div className="w-12 h-12 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center">
                <Volume2 className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-lg text-stone-900">2. الاستماع الصوتي</h3>
              <p className="text-sm text-stone-600 leading-relaxed">
                نطق صوتي نقي لجميع الكلمات والعبارات لتدريب الأذن على تمييز الأصوات.
              </p>
            </div>

            <div className="p-6 rounded-2xl bg-[#FAF8F5] border border-stone-200 hover:border-emerald-300 transition-all space-y-3">
              <div className="w-12 h-12 rounded-xl bg-orange-100 text-orange-800 flex items-center justify-center">
                <Mic className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-lg text-stone-900">3. التحدث والنطق</h3>
              <p className="text-sm text-stone-600 leading-relaxed">
                تدريب تفاعلي عبر الميكروفون مع تقييم دقيق للنطق وتوضيح الأخطاء كلمة بكلمة.
              </p>
            </div>

            <div className="p-6 rounded-2xl bg-[#FAF8F5] border border-stone-200 hover:border-emerald-300 transition-all space-y-3">
              <div className="w-12 h-12 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center">
                <PenTool className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-lg text-stone-900">4. بناء الجمل والتراكيب</h3>
              <p className="text-sm text-stone-600 leading-relaxed">
                تمارين ترتيب الكلمات وتصريف الأفعال مع توضيحات سريعة تمنع اللبس.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* CEFR Level Progression */}
      <section className="py-20 bg-[#FAF8F5]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <h2 className="text-3xl sm:text-4xl font-bold text-[#1B4D3E]">
              تدرج تعليمي واضح عبر معايير CEFR
            </h2>
            <p className="text-stone-600 text-base leading-relaxed">
              تنتقل عبر مستويات معتمدة خطوة بخطوة، مع إمكانية تحديد مستواك الحقيقي في دقائق.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[
              { code: 'A1', title: 'مبتدئ أول', desc: 'الأساسيات، التحيات، الأرقام، تكوين أول جملة، واستخدام أفعال الكينونة.', color: 'border-emerald-400 bg-emerald-50/40' },
              { code: 'A2', title: 'مبتدئ متقدم', desc: 'المواقف اليومية، التسوق، طلب الخدمات، والحديث عن الروتين والماضي.', color: 'border-teal-400 bg-teal-50/40' },
              { code: 'B1', title: 'متوسط', desc: 'إبداء الرأي، وصف التجارب، واستخدام تراكيب أكثر مرونة في المحادثة.', color: 'border-blue-400 bg-blue-50/40' },
              { code: 'B2', title: 'فوق المتوسط', desc: 'الطلاقة في الحوارات، استيعاب الأفكار المعقدة، والتواصل المهني.', color: 'border-indigo-400 bg-indigo-50/40' },
              { code: 'C1', title: 'متقدم', desc: 'المرونة اللغوية التامة والقدرة على التعبير بسلاسة في مختلف المواقف.', color: 'border-purple-400 bg-purple-50/40' },
              { code: 'C2', title: 'إتقان', desc: 'استيعاب شامل ودقيق لكل ما يُقرأ أو يُسمع بكفاءة لغوية متقدمة.', color: 'border-amber-400 bg-amber-50/40' },
            ].map((lvl) => (
              <div key={lvl.code} className={`p-6 rounded-3xl border-2 ${lvl.color} space-y-3 shadow-xs`}>
                <div className="flex items-center justify-between">
                  <span className="text-2xl font-black text-[#1B4D3E]">{lvl.code}</span>
                  <span className="text-xs font-bold px-3 py-1 rounded-full bg-white border border-stone-200 text-stone-700">
                    {lvl.title}
                  </span>
                </div>
                <p className="text-sm text-stone-600 leading-relaxed font-normal">
                  {lvl.desc}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Spaced Repetition (SRS) Section */}
      <section className="py-16 bg-white border-y border-stone-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div className="space-y-5">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-900 text-xs font-bold">
                <RotateCcw className="w-3.5 h-3.5" />
                <span>نظام المراجعة الذكي SM-2</span>
              </div>
              <h2 className="text-3xl sm:text-4xl font-bold text-[#1B4D3E]">
                حفظ الكلمات في الذاكرة طويلة المدى
              </h2>
              <p className="text-stone-600 leading-relaxed text-base">
                يعتمد التطبيق على خوارزمية التكرار المتباعد لتحديد الوقت الأمثل لمراجعة كل مفردة، بحيث تراجعها في اللحظة المناسبة لتثبيتها دون إضاعة وقتك في تكرار ما تعرفه بالفعل.
              </p>
              <div className="grid grid-cols-2 gap-4 pt-2">
                <div className="p-4 rounded-2xl bg-stone-50 border border-stone-200">
                  <div className="text-2xl font-black text-[#1B4D3E]">تثبيت فوري</div>
                  <div className="text-xs text-stone-500 font-semibold">مراجعة المفردات حسب صعوبتها</div>
                </div>
                <div className="p-4 rounded-2xl bg-stone-50 border border-stone-200">
                  <div className="text-2xl font-black text-[#E76F51]">دقائق يومياً</div>
                  <div className="text-xs text-stone-500 font-semibold">جلسة مراجعة سريعة قبل الدرس</div>
                </div>
              </div>
            </div>

            <div className="bg-stone-50 rounded-3xl p-6 border border-stone-200 shadow-inner space-y-4">
              <div className="text-xs font-bold text-stone-400">نموذج لبطاقة مراجعة تفاعلية:</div>
              <div className="bg-white rounded-2xl p-6 border border-stone-200 shadow-sm text-center space-y-3">
                <div className="flex items-center justify-center gap-2">
                  <span className="text-2xl font-black text-[#1B4D3E] bidi-isolate">Entender</span>
                  <AudioSpeakerButton text="Entender" size="sm" />
                </div>
                <div className="text-xs text-stone-400">فعل شائع • المجموعة الثانية (-er)</div>
                <div className="text-sm font-semibold text-emerald-800 bg-emerald-50 py-1.5 px-4 rounded-xl inline-block">
                  المعنى: يفهم / يستوعب
                </div>
                <div className="text-xs text-stone-500 italic bidi-isolate flex items-center justify-center gap-1.5">
                  <span>"¿Entiendes esta frase?"</span>
                  <AudioSpeakerButton text="¿Entiendes esta frase?" size="sm" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* FAQ Section */}
      <section className="py-20 bg-[#FAF8F5]">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
          <div className="text-center space-y-3">
            <h2 className="text-3xl font-bold text-[#1B4D3E]">الأسئلة الشائعة</h2>
            <p className="text-stone-600 text-sm">كل ما تود معرفته عن المنصة وكيفية التعلم</p>
          </div>

          <div className="space-y-4">
            {[
              {
                q: 'هل يمكنني البدء وأنا لا أعرف أي كلمة بالإسبانية؟',
                a: 'نعم، نبدأ معك من الصفر تماماً: الحروف، مخارج الأصوات، والتحيات البسيطة.'
              },
              {
                q: 'هل توجد أصوات لجميع المفردات والتمارين؟',
                a: 'نعم، كل كلمة وجملة إسبانية مزودة بنطق صوتي نقي يمكنك الاستماع إليه بلمسة واحدة.'
              },
              {
                q: 'كيف يعمل اختبار تحديد المستوى؟',
                a: 'اختبار سريع من أسئلة متعددة يقيس المفردات والقواعد وفهم الجمل ليوجهك مباشرة إلى أول درس مناسب لمستواك.'
              },
              {
                q: 'هل يتطلب التطبيق دفع أي رسوم للتجربة؟',
                a: 'لا، الدروس الأساسية واختبار تحديد المستوى والمراجعة اليومية متاحة مجاناً.'
              }
            ].map((faq, idx) => (
              <div key={idx} className="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-2xs space-y-2">
                <div className="font-bold text-stone-900 flex items-center gap-2">
                  <HelpCircle className="w-5 h-5 text-emerald-700 flex-shrink-0" />
                  <span>{faq.q}</span>
                </div>
                <p className="text-sm text-stone-600 leading-relaxed pr-7 font-normal">
                  {faq.a}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Footer Banner */}
      <section className="py-16 bg-[#1B4D3E] text-white text-center">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
          <h2 className="text-3xl sm:text-4xl font-black">
            ابدأ رحلتك في تعلّم اللغة الإسبانية اليوم
          </h2>
          <p className="text-emerald-100 max-w-xl mx-auto text-base">
            خطوات يومية بسيطة تبني بها لغة جديدة بثقة وثبات.
          </p>
          <div>
            <Link
              to="/register"
              className="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-amber-400 text-stone-950 font-bold text-base shadow-lg hover:bg-amber-300 hover:scale-105 transition-all"
            >
              <span>إنشاء حساب مجاني</span>
              <ArrowRight className="w-5 h-5 rotate-180" />
            </Link>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  );
};
