import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { authApi } from '../api/auth';
import { useAuth } from '../context/AuthContext';
import {
  Compass,
  Plane,
  Briefcase,
  GraduationCap,
  Sparkles,
  Heart,
  Clock,
  CheckCircle,
  ArrowRight,
  TrendingUp,
  BookOpen
} from 'lucide-react';

export const OnboardingPage: React.FC = () => {
  const { user, refreshUser } = useAuth();
  const navigate = useNavigate();

  const [step, setStep] = useState(1);
  const [learningReason, setLearningReason] = useState('travel');
  const [dailyMinutes, setDailyMinutes] = useState(15);
  const [experienceLevel, setExperienceLevel] = useState('zero');
  const [explanationDialect, setExplanationDialect] = useState('egyptian_arabic');
  const [targetVariant, setTargetVariant] = useState('spain');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const reasons = [
    { id: 'travel', title: 'السياحة والسفر إلى إسبانيا', desc: 'للتنقل، طلب الطعام، وحجز الفنادق بسهولة', icon: Plane },
    { id: 'career', title: 'التطوير الوظيفي وفرص العمل', desc: 'فتح آفاق عمل جديدة مع شركات دولية', icon: Briefcase },
    { id: 'education', title: 'الدراسة الجامعية والابتعاث', desc: 'التحضير لشهادات DELE والجامعات الإسبانية', icon: GraduationCap },
    { id: 'culture', title: 'الشغف بالثقافة والفنون والرياضة', desc: 'فهم الدوري الإسباني، الأدب، والموسيقى', icon: Sparkles },
    { id: 'personal', title: 'تطوير الذات وتمرين الذاكرة', desc: 'اكتساب مهارة عقلية جديدة وممتعة', icon: Heart },
  ];

  const times = [
    { minutes: 5, label: '5 دقائق يومياً', subtitle: 'وتيرة خفيفة ومستمرة' },
    { minutes: 15, label: '15 دقيقة يومياً', subtitle: 'الوتيرة المثالية والموصى بها' },
    { minutes: 30, label: '30 دقيقة يومياً', subtitle: 'تقدم مكثف وسريع' },
    { minutes: 60, label: '60 دقيقة يومياً', subtitle: 'للمستعدين للتفرغ التام' },
  ];

  const handleFinish = async (nextDestination: 'placement' | 'dashboard') => {
    setIsSubmitting(true);
    try {
      await authApi.updateOnboarding({
        learning_reason: learningReason,
        daily_goal_minutes: dailyMinutes,
        experience_level: experienceLevel,
        explanation_dialect: explanationDialect,
        target_variant: targetVariant,
      });

      await refreshUser();

      if (nextDestination === 'placement') {
        navigate('/placement-test');
      } else {
        navigate('/dashboard');
      }
    } catch (err) {
      console.error(err);
      navigate('/dashboard');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-2xl mx-auto w-full space-y-8">
        {/* Progress Header */}
        <div className="text-center space-y-2">
          <div className="inline-flex items-center gap-2 text-[#1B4D3E] font-black text-xl">
            <Compass className="w-6 h-6 text-amber-500" />
            <span>Ruta Español</span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-black text-stone-900">
            {step === 1 && 'ما هو دافعك الرئيسي لتعلم الإسبانية؟'}
            {step === 2 && 'كم من الوقت يمكنك تخصيصه يومياً؟'}
            {step === 3 && 'ما هي خبرتك السابقة في اللغة الإسبانية؟'}
            {step === 4 && 'كيف تحب أن نشرح لك القواعد اللغوية؟'}
            {step === 5 && 'جاهز! كيف تود أن تبدأ رحلتك؟'}
          </h1>
          <div className="w-full bg-stone-200 h-2 rounded-full overflow-hidden max-w-xs mx-auto mt-4">
            <div
              className="bg-[#1B4D3E] h-full transition-all duration-300"
              style={{ width: `${(step / 5) * 100}%` }}
            />
          </div>
        </div>

        {/* Step 1: Reason */}
        {step === 1 && (
          <div className="space-y-3">
            {reasons.map((r) => {
              const Icon = r.icon;
              const isSelected = learningReason === r.id;
              return (
                <button
                  key={r.id}
                  onClick={() => setLearningReason(r.id)}
                  className={`w-full p-4 rounded-2xl border-2 text-right flex items-center justify-between transition-all ${
                    isSelected
                      ? 'border-[#1B4D3E] bg-emerald-50/70 shadow-sm'
                      : 'border-stone-200 bg-white hover:border-stone-300'
                  }`}
                >
                  <div className="flex items-center gap-4">
                    <div
                      className={`w-11 h-11 rounded-xl flex items-center justify-center ${
                        isSelected ? 'bg-[#1B4D3E] text-white' : 'bg-stone-100 text-stone-600'
                      }`}
                    >
                      <Icon className="w-5 h-5" />
                    </div>
                    <div>
                      <div className="font-bold text-stone-900 text-base">{r.title}</div>
                      <div className="text-xs text-stone-500">{r.desc}</div>
                    </div>
                  </div>
                  {isSelected && <CheckCircle className="w-5 h-5 text-[#1B4D3E]" />}
                </button>
              );
            })}
          </div>
        )}

        {/* Step 2: Daily Goal */}
        {step === 2 && (
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {times.map((t) => {
              const isSelected = dailyMinutes === t.minutes;
              return (
                <button
                  key={t.minutes}
                  onClick={() => setDailyMinutes(t.minutes)}
                  className={`p-6 rounded-3xl border-2 text-right space-y-2 transition-all ${
                    isSelected
                      ? 'border-[#1B4D3E] bg-emerald-50/70 shadow-sm scale-102'
                      : 'border-stone-200 bg-white hover:border-stone-300'
                  }`}
                >
                  <Clock className={`w-6 h-6 ${isSelected ? 'text-[#1B4D3E]' : 'text-stone-400'}`} />
                  <div className="text-xl font-bold text-stone-900">{t.label}</div>
                  <div className="text-xs text-stone-500">{t.subtitle}</div>
                </button>
              );
            })}
          </div>
        )}

        {/* Step 3: Experience Level */}
        {step === 3 && (
          <div className="space-y-4">
            {[
              { id: 'zero', title: 'مبتدئ تماماً من الصفر', desc: 'لا أعرف أي كلمات أو حروف إسبانية سابقة' },
              { id: 'basics', title: 'أعرف بعض الأساسيات والكلمات', desc: 'أستطيع تحية الناس ومعرفة أرقام وكلمات بسيطة' },
              { id: 'intermediate', title: 'مستواي متوسط فما فوق', desc: 'درست الإسبانية سابقاً وأستطيع تكوين جمل وفهم محادثات' },
            ].map((exp) => {
              const isSelected = experienceLevel === exp.id;
              return (
                <button
                  key={exp.id}
                  onClick={() => setExperienceLevel(exp.id)}
                  className={`w-full p-5 rounded-2xl border-2 text-right flex items-center justify-between transition-all ${
                    isSelected
                      ? 'border-[#1B4D3E] bg-emerald-50/70 shadow-sm'
                      : 'border-stone-200 bg-white hover:border-stone-300'
                  }`}
                >
                  <div>
                    <div className="font-bold text-stone-900 text-base">{exp.title}</div>
                    <div className="text-xs text-stone-500 mt-0.5">{exp.desc}</div>
                  </div>
                  {isSelected && <CheckCircle className="w-5 h-5 text-[#1B4D3E]" />}
                </button>
              );
            })}
          </div>
        )}

        {/* Step 4: Dialect Preference */}
        {step === 4 && (
          <div className="space-y-4">
            {[
              {
                id: 'egyptian_arabic',
                title: 'شرح بشري دافئ ومبسط بالمصري (موصى به)',
                desc: 'بنشرحلك القواعد بهدوء وبأمثلة من مواقف الحياة اليومية، كأنك بتدردش مع صاحبك بيبسطهالك خطوة بخطوة',
              },
              {
                id: 'msa',
                title: 'شروحات باللغة العربية الفصحى',
                desc: 'شرح نحوي أكاديمي قياسي ورسمي باللغة العربية الفصحى',
              },
            ].map((d) => {
              const isSelected = explanationDialect === d.id;
              return (
                <button
                  key={d.id}
                  onClick={() => setExplanationDialect(d.id)}
                  className={`w-full p-5 rounded-2xl border-2 text-right flex items-center justify-between transition-all ${
                    isSelected
                      ? 'border-[#1B4D3E] bg-emerald-50/70 shadow-sm'
                      : 'border-stone-200 bg-white hover:border-stone-300'
                  }`}
                >
                  <div>
                    <div className="font-bold text-stone-900 text-base">{d.title}</div>
                    <div className="text-xs text-stone-500 mt-1 leading-relaxed">{d.desc}</div>
                  </div>
                  {isSelected && <CheckCircle className="w-5 h-5 text-[#1B4D3E]" />}
                </button>
              );
            })}

            <div className="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
              <span className="font-bold">اللهجة المستهدفة:</span> الإسبانية المعتمدة (لهجة إسبانيا القياسية). يمكنك تعديل هذا الخيار لاحقاً من صفحة الإعدادات.
            </div>
          </div>
        )}

        {/* Step 5: Start Option Choice */}
        {step === 5 && (
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              onClick={() => handleFinish('placement')}
              className="p-6 rounded-3xl border-2 border-emerald-500 bg-emerald-50/80 cursor-pointer hover:shadow-lg transition-all space-y-3 text-right"
            >
              <div className="w-12 h-12 rounded-2xl bg-emerald-700 text-white flex items-center justify-center">
                <TrendingUp className="w-6 h-6" />
              </div>
              <div className="font-black text-xl text-[#1B4D3E]">أجرِ اختبار تحديد المستوى</div>
              <p className="text-xs text-stone-600 leading-relaxed">
                اختبار تكيفي سريع يقيس معرفتك الحقيقية بالقواعد والمفردات والقراءة ليضعك في الدرس المناسب فوراً.
              </p>
              <span className="inline-block text-xs font-bold text-emerald-800 bg-white px-3 py-1 rounded-full border border-emerald-200">
                موصى به لمن لديه معرفة سابقة
              </span>
            </div>

            <div
              onClick={() => handleFinish('dashboard')}
              className="p-6 rounded-3xl border-2 border-stone-200 bg-white cursor-pointer hover:border-stone-400 hover:shadow-lg transition-all space-y-3 text-right"
            >
              <div className="w-12 h-12 rounded-2xl bg-stone-100 text-[#1B4D3E] flex items-center justify-center">
                <BookOpen className="w-6 h-6" />
              </div>
              <div className="font-black text-xl text-stone-900">ابدأ من الصفر تماماً (A1)</div>
              <p className="text-xs text-stone-600 leading-relaxed">
                ابدأ رحلتك من الوحدة الأولى: الحروف الأبجدية، التحيات والتعارف وبناء أول جملة إسبانية.
              </p>
              <span className="inline-block text-xs font-bold text-stone-600 bg-stone-100 px-3 py-1 rounded-full">
                مناسب للمبتدئين كلياً
              </span>
            </div>
          </div>
        )}

        {/* Navigation buttons */}
        <div className="flex items-center justify-between pt-4">
          {step > 1 ? (
            <button
              onClick={() => setStep(step - 1)}
              className="px-5 py-2.5 rounded-xl border border-stone-300 text-stone-700 font-bold text-sm hover:bg-stone-50 transition-colors"
            >
              السابق
            </button>
          ) : (
            <div></div>
          )}

          {step < 5 && (
            <button
              onClick={() => setStep(step + 1)}
              className="px-8 py-3 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm hover:bg-[#153e32] shadow-sm flex items-center gap-2 transition-all"
            >
              <span>التالي</span>
              <ArrowRight className="w-4 h-4 rotate-180" />
            </button>
          )}
        </div>
      </div>
    </div>
  );
};
